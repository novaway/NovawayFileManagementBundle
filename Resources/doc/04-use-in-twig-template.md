## Usage in Twig template

### Introduction

In order to facilitate usage of uploaded file in the view of application, the bundle provide a Twig extensions to add
some helpers.

### Register the extension as service

First you must let the [Service Continer](http://symfony.com/doc/current/cookbook/templating/twig_extension.html#register-an-extension-as-a-service)
know about the extensions.

``` yaml
# src/Novaway/Bundle/MyBundle/Resources/config/services.yml
services:
    novaway.twig.filemanagement_extension:
        class: Novaway\Bundle\FileManagementBundle\Twig\Extension\FileManagementExtension
        arguments:
            - { type: service, id: router }
            - "%kernel.root_dir%/../web/uploads"
        tags:
            - { name: twig.extension }
```

### Usage

The Twig extensions provide 3 filters you can use in your templates.

* *filepath* : That filter return a relative link to a file
> Usage : {{ entity|filepath('myEntityProperty') }}
* *imagepath* : Same a precedent, but for image
> Usage : {{ entity|imagepath('myEntityProperty', 'myPictureFormat') }}
* *fileUrl* : This filter return an absolute link to a file
> Usage : {{ entity|fileUrl('myEntityProperty') }}
