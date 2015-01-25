## The image form field

### Introduction

The bundle provides a form field displaying the image next to the upload field when the is one.

### Form Setup

The form setup is really quick, just set the field type to ```'image'```

``` php
// src/Novaway/Bundle/MyBundle/Form/Type/MyUserEntityType.php

// use ...

class MyUserEntityType extends AbstractType
{
    // ...

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add(...)
            ->add('picture', 'image')
        ;
    }
}
```

The **image** field type is a child of the **[file](http://symfony.com/doc/current/reference/forms/types/file.html)** field type.
It adds several new options :
 + **preview** (Default ```true```) : Hide the image display if set to false ;
 + **format** (Default ```'thumbnail'```) : The image format (must be existing for this property) ;
 + **update_cache** (Default ```true```) : Adds a version timestamp to the file name in HTML output.
 + **web_directory** (Default _novaway_file_management.web_file_path_ parameter value or '/' if the parameter isn't define) : Web directory path
 
 Here is an example of how to use these options:
 ``` php
 $builder
     //->add(...)
     ->add('picture', 'image', array(
        'format'       => 'original',
        'update_cache' => false
     ))
 ;
 ```

---

#### Note on update_cache ####
The choice had been made to set *update_cache* value to ```true``` by default because we think that the image should be refreshed when uploading a new one.
**This may have an impact on performance.**
You can still optimize performance while using the *update_cache* option by adding the ```updatedAt``` property to your entity.
The bundle will then set the version timestamp according to this property. The cached image will be reloaded each time its value changes.

---

### Display in twig ###

To display the form in a twig template, you will need to load the form theme.

```
{% form_theme form 'NovawayFileManagementBundle:Form:fields.html.twig' %}

{{ form(form) }}
```


