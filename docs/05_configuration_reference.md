## Configuration reference

By default, if no confguration is passed, *SymfonyExtension* will try its best to guess it.
The full configuration tree looks like that:

```yaml
# behat.yml.dist / behat.yml

default:
    extensions:
        FriendsOfBehat\SymfonyExtension:
            bootstrap: ~
            kernel:
                class: ~
                file: ~
                environment: ~
                debug: ~
```

 * **`bootstrap`**: 
 
    It is a path to the file requried once while the extension is loaded. You can use this file to set up your testing 
    environment - set some enviornment variables or preload an external file.
    If you do not pass any, it would look for either `config/bootstrap.php` (Symfony 4) or `app/autoload.php` (Symfony 3). 
    If none are found, no file would be loaded.
    
 * **`kernel.class`**:
 
    It is a fully qualified class name of the application kernel class.
    If you do not pass any, it would look for either `App\Kernel` (Symfony 4) or `AppKernel` (Symfony 3).
    If none are found, an exception would be thrown and you would be required to specify it explicitly.
    
 * **`kernel.file`**:
 
    It is a path to the file containing the application kernel class. You might want to set it if your kernel is not
    autoloaded by Composer's autoloaded.
    If `kernel.class` is not defined, it would automatically use `app/AppKernel.php` if `AppKernel` class was autoconfigured.
    
 * **`kernel.environment`**:
 
    It allows you to force using a given environment. If it is not set, it uses `APP_ENV` environment variable if defined
    or falls back to `test`.
    
 * **`kernel.debug`**:
 
    It allows you to force enabling or disabling debug mode. If it is not set, it uses `APP_DEBUG` environment variable 
    if defined or falls back to `true`.
