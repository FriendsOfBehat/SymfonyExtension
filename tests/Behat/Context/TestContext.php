<?php

declare(strict_types=1);

namespace Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

final class TestContext implements Context
{
    /**
     * @var string
     */
    private static $workingDir;

    /**
     * @var Filesystem
     */
    private static $filesystem;

    /**
     * @var string
     */
    private static $phpBin;

    /**
     * @var Process
     */
    private $process;

    /**
     * @BeforeFeature
     */
    public static function beforeFeature(): void
    {
        self::$workingDir = sprintf('%s/%s/', sys_get_temp_dir(), uniqid('', true));
        self::$filesystem = new Filesystem();
        self::$phpBin = self::findPhpBinary();
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario(): void
    {
        self::$filesystem->remove(self::$workingDir);
        self::$filesystem->mkdir(self::$workingDir, 0777);
    }

    /**
     * @AfterScenario
     */
    public function afterScenario(): void
    {
        self::$filesystem->remove(self::$workingDir);
    }

    /**
     * @Given /^a Behat configuration containing(?: "([^"]+)"|:)$/
     */
    public function thereIsConfiguration($content): void
    {
        $this->thereIsFile('behat.yml', $content);
    }

    /**
     * @Given /^a (?:.+ |)file "([^"]+)" containing(?: "([^"]+)"|:)$/
     */
    public function thereIsFile($file, $content): void
    {
        self::$filesystem->dumpFile(self::$workingDir . '/' . $file, (string) $content);
    }

    /**
     * @Given /^a feature file containing(?: "([^"]+)"|:)$/
     */
    public function thereIsFeatureFile($content): void
    {
        $this->thereIsFile(sprintf('features/%s.feature', md5(uniqid('', true))), $content);
    }

    /**
     * @Given /^a feature file with passing scenario$/
     */
    public function thereIsFeatureFileWithPassingScenario(): void
    {
        $this->thereIsFile('features/bootstrap/FeatureContext.php', <<<CON
<?php

declare(strict_types=1);

class FeatureContext implements \Behat\Behat\Context\Context
{
    /** @Then it passes */
    public function itPasses() {}
}
CON
        );

        $this->thereIsFeatureFile(<<<FEA
Feature: Passing feature

    Scenario: Passing scenario
        Then it passes
FEA
        );
    }

    /**
     * @Given /^a feature file with failing scenario$/
     */
    public function thereIsFeatureFileWithFailingScenario(): void
    {
        $this->thereIsFile('features/bootstrap/FeatureContext.php', <<<CON
<?php

declare(strict_types=1);

class FeatureContext implements \Behat\Behat\Context\Context
{
    /** @Then it fails */
    public function itFails() { throw new \RuntimeException(); }
}
CON
        );

        $this->thereIsFeatureFile(<<<FEA
Feature: Failing feature

    Scenario: Failing scenario
        Then it fails
FEA
        );
    }

    /**
     * @Given /^a feature file with scenario with missing step$/
     */
    public function thereIsFeatureFileWithScenarioWithMissingStep(): void
    {
        $this->thereIsFile('features/bootstrap/FeatureContext.php', <<<CON
<?php

declare(strict_types=1); 

class FeatureContext implements \Behat\Behat\Context\Context {}
CON
        );

        $this->thereIsFeatureFile(<<<FEA
Feature: Feature with missing step

    Scenario: Scenario with missing step
        Then it does not have this step
FEA
        );
    }

    /**
     * @Given /^a feature file with scenario with pending step$/
     */
    public function thereIsFeatureFileWithScenarioWithPendingStep(): void
    {
        $this->thereIsFile('features/bootstrap/FeatureContext.php', <<<CON
<?php

declare(strict_types=1);

class FeatureContext implements \Behat\Behat\Context\Context 
{
    /** @Then it has this step as pending */
    public function itFails() { throw new \Behat\Behat\Tester\Exception\PendingException(); }
}
CON
        );

        $this->thereIsFeatureFile(<<<FEA
Feature: Feature with pending step

    Scenario: Scenario with pending step
        Then it has this step as pending
FEA
        );
    }

    /**
     * @When /^I run Behat$/
     */
    public function iRunBehat(): void
    {
        $this->process = new Process(sprintf('%s %s --strict -vvv --no-interaction --lang=en', self::$phpBin, escapeshellarg(BEHAT_BIN_PATH)));
        $this->process->setWorkingDirectory(self::$workingDir);
        $this->process->start();
        $this->process->wait();
    }

    /**
     * @Then /^it should pass$/
     */
    public function itShouldPass(): void
    {
        if (0 === $this->getProcessExitCode()) {
            return;
        }

        throw new \DomainException(
            'Behat was expecting to pass, but failed with the following output:' . PHP_EOL . PHP_EOL . $this->getProcessOutput()
        );
    }

    /**
     * @Then /^it should pass with(?: "([^"]+)"|:)$/
     */
    public function itShouldPassWith($expectedOutput): void
    {
        $this->itShouldPass();
        $this->assertOutputMatches((string) $expectedOutput);
    }

    /**
     * @Then /^it should fail$/
     */
    public function itShouldFail(): void
    {
        if (0 !== $this->getProcessExitCode()) {
            return;
        }

        throw new \DomainException(
            'Behat was expecting to fail, but passed with the following output:' . PHP_EOL . PHP_EOL . $this->getProcessOutput()
        );
    }

    /**
     * @Then /^it should fail with(?: "([^"]+)"|:)$/
     */
    public function itShouldFailWith($expectedOutput): void
    {
        $this->itShouldFail();
        $this->assertOutputMatches((string) $expectedOutput);
    }

    /**
     * @Then /^it should end with(?: "([^"]+)"|:)$/
     */
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
                'Pattern "%s" does not match the following output:' . PHP_EOL . PHP_EOL . '%s',
                $pattern,
                $output
            ));
        }
    }

    /**
     * @return string
     */
    private function getProcessOutput(): string
    {
        $this->assertProcessIsAvailable();

        return $this->process->getErrorOutput() . $this->process->getOutput();
    }

    /**
     * @return int
     */
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
     * @return string
     *
     * @throws \RuntimeException
     */
    private static function findPhpBinary(): string
    {
        $phpBinary = (new PhpExecutableFinder())->find();
        if (false === $phpBinary) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }

        return $phpBinary;
    }
}
