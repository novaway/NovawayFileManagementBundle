## Installation

### Install NovawayFileManagementBundle

Simply run assuming you have installed composer (composer.phar or composer binary) :

``` bash
$ php composer.phar require novaway/filemanagementbundle 1.*
```

### Configuration

In your *config.yml* file, you may need to set up the upload root path (realtive to symfony *web* path)

```yaml
novaway_file_management :
    web_file_path: /uploads/
```
> **Note** : The *web* path will be used as upload root path if this parameter is not provided.