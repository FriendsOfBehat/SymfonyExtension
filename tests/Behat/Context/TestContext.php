<?php

declare(strict_types=1);

namespace Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

final class TestContext implements Context
{
    /** @var string */
    private static $workingDir;

    /** @var Filesystem */
    private static $filesystem;

    /** @var string */
    private static $phpBin;

    /** @var Process */
    private $process;

    /** @var array */
    private $variables = [];

    /** @var array Running merge of all config fragments, written to behat.dist.php for behat 4.x */
    private array $mergedPhpConfig = [];

    /**
     * @BeforeFeature
     */
    #[\Behat\Hook\BeforeFeature]
    public static function beforeFeature(): void
    {
        self::$workingDir = sprintf('%s/%s/', sys_get_temp_dir(), uniqid('', true));
        self::$filesystem = new Filesystem();
        self::$phpBin = self::findPhpBinary();
    }

    /**
     * @BeforeScenario
     */
    #[\Behat\Hook\BeforeScenario]
    public function beforeScenario(): void
    {
        self::$filesystem->remove(self::$workingDir);
        self::$filesystem->mkdir(self::$workingDir, 0777);
        $this->mergedPhpConfig = [];
    }

    /**
     * @AfterScenario
     */
    #[\Behat\Hook\AfterScenario]
    public function afterScenario(): void
    {
        self::$filesystem->remove(self::$workingDir);
    }

    /**
     * @Given a standard Symfony autoloader configured
     */
    #[\Behat\Step\Given('a standard Symfony autoloader configured')]
    public function standardSymfonyAutoloaderConfigured(): void
    {
        $this->thereIsFile('vendor/autoload.php', sprintf(<<<'CON'
<?php

declare(strict_types=1);

$loader = require '%s';
$loader->addPsr4('App\\', __DIR__ . '/../src/');
$loader->addPsr4('App\\Tests\\', __DIR__ . '/../tests/');

return $loader;
CON
            , __DIR__ . '/../../../vendor/autoload.php'));
    }

    /**
     * @Given a working Symfony application with SymfonyExtension configured
     */
    #[\Behat\Step\Given('a working Symfony application with SymfonyExtension configured')]
    public function workingSymfonyApplicationWithExtension(): void
    {
        $this->thereIsConfiguration(
            <<<'CON'
default:
    extensions:
        FriendsOfBehat\SymfonyExtension:
            kernel:
                class: App\Kernel
CON
        );

        $this->standardSymfonyAutoloaderConfigured();

        $this->thereIsFile(
            'src/Kernel.php',
            <<<'CON'
<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as HttpKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends HttpKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle(),
        ];
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->loadFromExtension('framework', [
            'test' => $this->getEnvironment() === 'test',
            'secret' => 'Pigeon',
        ]);

        $loader->load(__DIR__ . '/../config/default.yaml');
        $loader->load(__DIR__ . '/../config/services.yaml');
    }

    protected function configureRoutes($routes): void
    {
        if ($routes instanceof RoutingConfigurator) { // available since Symfony 5.1
            $routes
                ->add('app_hello', '/hello-world')
                ->controller('App\Controller::helloWorld')
            ;
        } else { // support Symfony 4.4
            $routes->add('/hello-world', 'App\Controller:helloWorld');
        }
    }
}
CON
        );

        $this->thereIsFile(
            'src/Controller.php',
            <<<'CON'
<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\Response;

final class Controller
{
    private $counter;

    public function __construct(Counter $counter)
    {
        $this->counter = $counter;
    }

    public function helloWorld(): Response
    {
        $this->counter->increase();

        return new Response('Hello world! The counter value is ' . $this->counter->get());
    }
}
CON
        );

        $this->thereIsFile(
            'src/Counter.php',
            <<<'CON'
<?php

declare(strict_types=1);

namespace App;

final class Counter
{
    private $counter = 0;

    public function increase(): void
    {
        $this->counter++;
    }

    public function get(): int
    {
        return $this->counter;
    }
}
CON
        );

        $this->thereIsFile(
            'config/default.yaml',
            <<<'YML'
services:
    App\Controller:
        arguments:
            - '@App\Counter'
        public: true

    App\Counter:
        public: false
YML
        );

        $this->thereIsFile('config/services.yaml', '');
    }

    /**
     * @Given /^an? (server|environment) variable "([^"]++)" set to "([^"]++)"$/
     */
    #[\Behat\Step\Given('/^an? (server|environment) variable "([^"]++)" set to "([^"]++)"$/')]
    public function variableSetTo(string $type, string $name, string $value): void
    {
        $this->variables[$type][$name] = $value;
    }

    /**
     * @Given /^a YAML services file containing:$/
     */
    #[\Behat\Step\Given('/^a YAML services file containing:$/')]
    public function yamlServicesFile($content): void
    {
        $this->thereIsFile('config/services.yaml', (string) $content);
    }

    /**
     * @Given /^a Behat configuration containing(?: "([^"]+)"|:)$/
     */
    #[\Behat\Step\Given('/^a Behat configuration containing(?: "([^"]+)"|:)$/')]
    public function thereIsConfiguration($content): void
    {
        // Behat 3.x: YAML config files with imports
        $mainConfigFile = sprintf('%s/behat.yml', self::$workingDir);
        $newConfigFile = sprintf('%s/behat-%s.yml', self::$workingDir, md5((string) $content));

        self::$filesystem->dumpFile($newConfigFile, (string) $content);

        if (!file_exists($mainConfigFile)) {
            self::$filesystem->dumpFile($mainConfigFile, Yaml::dump(['imports' => []]));
        }

        $mainBehatConfiguration = Yaml::parseFile($mainConfigFile);
        $mainBehatConfiguration['imports'][] = $newConfigFile;

        self::$filesystem->dumpFile($mainConfigFile, Yaml::dump($mainBehatConfiguration));

        // Behat 4.x: PHP config file, updated incrementally as a running merge
        $this->mergedPhpConfig = array_replace_recursive($this->mergedPhpConfig, Yaml::parse((string) $content));

        self::$filesystem->dumpFile(
            sprintf('%s/behat.dist.php', self::$workingDir),
            sprintf(
                "<?php\nreturn new \\Tests\\Behat\\Config\\ArrayConfig(%s);\n",
                var_export($this->resolveExtensionClassNames($this->mergedPhpConfig), true),
            ),
        );
    }

    /**
     * @Given /^a (?:.+ |)file "([^"]+)" containing(?: "([^"]+)"|:)$/
     */
    #[\Behat\Step\Given('/^a (?:.+ |)file "([^"]+)" containing(?: "([^"]+)"|:)$/')]
    public function thereIsFile($file, $content): string
    {
        $path = self::$workingDir . '/' . $file;
        $content = (string) $content;

        if (interface_exists(\Behat\Config\ConfigInterface::class) && str_ends_with($file, '.php')) {
            $content = $this->injectBehat4Attributes($content);
        }

        self::$filesystem->dumpFile($path, $content);

        return $path;
    }

    /**
     * @Given /^a feature file containing(?: "([^"]+)"|:)$/
     */
    #[\Behat\Step\Given('/^a feature file containing(?: "([^"]+)"|:)$/')]
    public function thereIsFeatureFile($content): void
    {
        $this->thereIsFile(sprintf('features/%s.feature', md5(uniqid('', true))), $content);
    }

    /**
     * @When /^I run Behat$/
     */
    #[\Behat\Step\When('/^I run Behat$/')]
    public function iRunBehat(): void
    {
        $executablePath = BEHAT_BIN_PATH;

        if ($this->variables !== []) {
            $content = '<?php ';

            foreach ($this->variables['server'] ?? [] as $name => $value) {
                $content .= sprintf('$_SERVER["%s"] = "%s"; ', $name, $value);
            }

            foreach ($this->variables['environment'] ?? [] as $name => $value) {
                $content .= sprintf('$_ENV["%s"] = "%s"; ', $name, $value);
            }

            $content .= sprintf('require_once("%s"); ', $executablePath);

            $executablePath = $this->thereIsFile('__executable.php', $content);
        }

        $this->process = new Process([self::$phpBin, $executablePath, '--strict', '-vvv', '--no-interaction', '--lang=en'], self::$workingDir);
        $this->process->start();
        $this->process->wait();
    }

    /**
     * @Then /^it should pass$/
     */
    #[\Behat\Step\Then('/^it should pass$/')]
    public function itShouldPass(): void
    {
        if (0 === $this->getProcessExitCode()) {
            return;
        }

        throw new \DomainException(
            'Behat was expecting to pass, but failed with the following output:' . \PHP_EOL . \PHP_EOL . $this->getProcessOutput(),
        );
    }

    /**
     * @Then /^it should pass with(?: "([^"]+)"|:)$/
     */
    #[\Behat\Step\Then('/^it should pass with(?: "([^"]+)"|:)$/')]
    public function itShouldPassWith($expectedOutput): void
    {
        $this->itShouldPass();
        $this->assertOutputMatches((string) $expectedOutput);
    }

    /**
     * @Then /^it should fail$/
     */
    #[\Behat\Step\Then('/^it should fail$/')]
    public function itShouldFail(): void
    {
        if (0 !== $this->getProcessExitCode()) {
            return;
        }

        throw new \DomainException(
            'Behat was expecting to fail, but passed with the following output:' . \PHP_EOL . \PHP_EOL . $this->getProcessOutput(),
        );
    }

    /**
     * @Then /^it should fail with(?: "([^"]+)"|:)$/
     */
    #[\Behat\Step\Then('/^it should fail with(?: "([^"]+)"|:)$/')]
    public function itShouldFailWith($expectedOutput): void
    {
        $this->itShouldFail();
        $this->assertOutputMatches((string) $expectedOutput);
    }

    /**
     * @Then /^it should end with(?: "([^"]+)"|:)$/
     */
    #[\Behat\Step\Then('/^it should end with(?: "([^"]+)"|:)$/')]
    public function itShouldEndWith($expectedOutput): void
    {
        $this->assertOutputMatches((string) $expectedOutput);
    }

    /**
     * @param string $expectedOutput
     */
    private function assertOutputMatches($expectedOutput): void
    {
        $pattern = '/' . preg_quote($expectedOutput, '/') . '/sm';
        $output = $this->getProcessOutput();

        $result = preg_match($pattern, $output);
        if (false === $result) {
            throw new \InvalidArgumentException('Invalid pattern given:' . $pattern);
        }

        if (0 === $result) {
            throw new \DomainException(sprintf(
                'Pattern "%s" does not match the following output:' . \PHP_EOL . \PHP_EOL . '%s',
                $pattern,
                $output,
            ));
        }
    }

    private function getProcessOutput(): string
    {
        $this->assertProcessIsAvailable();

        return $this->process->getErrorOutput() . $this->process->getOutput();
    }

    private function getProcessExitCode(): int
    {
        $this->assertProcessIsAvailable();

        return $this->process->getExitCode();
    }

    /**
     * @throws \BadMethodCallException
     */
    private function assertProcessIsAvailable(): void
    {
        if (null === $this->process) {
            throw new \BadMethodCallException('Behat proccess cannot be found. Did you run it before making assertions?');
        }
    }

    /**
     * @throws \RuntimeException
     */
    private function injectBehat4Attributes(string $code): string
    {
        // Add PHP 8 step attributes alongside @Given/@When/@Then docblock annotations
        $code = (string) preg_replace_callback(
            '/^( *)\/\*\*\s*@(Given|When|Then)\s+(.+?)\s*\*\//m',
            static function (array $m): string {
                $indent = $m[1];
                $keyword = ucfirst(strtolower($m[2]));
                $pattern = str_replace("'", "\\'", $m[3]);

                return "{$m[0]}\n{$indent}#[\\Behat\\Step\\{$keyword}('{$pattern}')]";
            },
            $code,
        );

        // Add PHP 8 hook attributes alongside @BeforeScenario/@AfterScenario etc.
        $code = (string) preg_replace_callback(
            '/^( *)\/\*\*\s*@(BeforeScenario|AfterScenario|BeforeFeature|AfterFeature)\s*\*\//m',
            static function (array $m): string {
                $indent = $m[1];
                $hook = $m[2];

                return "{$m[0]}\n{$indent}#[\\Behat\\Hook\\{$hook}]";
            },
            $code,
        );

        return $code;
    }

    private function resolveExtensionClassNames(array $config): array
    {
        foreach ($config as &$profileConfig) {
            if (!is_array($profileConfig) || !isset($profileConfig['extensions'])) {
                continue;
            }
            $resolved = [];
            foreach ($profileConfig['extensions'] as $name => $settings) {
                $resolved[$this->resolveExtensionClass((string) $name)] = $settings;
            }
            $profileConfig['extensions'] = $resolved;
        }

        return $config;
    }

    private function resolveExtensionClass(string $name): string
    {
        // Map of behat 3.x short names → behat 4.x full class names
        $map = [
            'FriendsOfBehat\SymfonyExtension' => 'FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension',
            'FriendsOfBehat\ServiceContainerExtension' => 'FriendsOfBehat\ServiceContainerExtension\ServiceContainer\ServiceContainerExtension',
            'FriendsOfBehat\MinkExtension' => 'FriendsOfBehat\MinkExtension\ServiceContainer\MinkExtension',
            'Behat\MinkExtension' => 'Behat\MinkExtension\ServiceContainer\MinkExtension',
            'FriendsOfBehat\PageObjectExtension' => 'FriendsOfBehat\PageObjectExtension\ServiceContainer\PageObjectExtension',
        ];

        return $map[$name] ?? $name;
    }

    private static function findPhpBinary(): string
    {
        $phpBinary = (new PhpExecutableFinder())->find();
        if (false === $phpBinary) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }

        return $phpBinary;
    }
}
