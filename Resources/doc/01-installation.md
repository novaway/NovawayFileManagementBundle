## Installation

### Install NovawayFileManagementBundle

Simply run assuming you have installed composer (composer.phar or composer binary) :

``` bash
$ php composer.phar require novaway/filemanagementbundle "2.*"
```

### Configuration

Register the bundle in `app/AppKernel.php`:

``` php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new \Novaway\Bundle\FileManagementBundle\NovawayFileManagementBundle(),
    );
}
```

In your *app/config.yml* file, you may need to set up the upload root path (relative to symfony *web* path)

```yaml
novaway_file_management :
    web_file_path: /uploads/
```
> **Note** : The *web* path will be used as upload root path if this parameter is not provided.
