## Bundle usage (Working with image)

### Introduction

NovawayFileManagementBundle is able to manage your images files. It allow you make some basic process (like resizing,
croping, compressing and more...) on the picture which is upload.

### Entity setup

The entity setup is identical to [work with _commons_ files](02-working-with-file.md). You need to extend the
`BaseEntityWithImage` and add 2 properties :

1. The picture property (correspond to the _file input_)
2. The filename which will be store in the database. **This parameter should have the same name than the previous
with `Filename` suffix**.

``` php
<?php
// src/Novaway/Bundle/MyBundle/Entity/MyUserEntity

// use ...
use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile;

/**
 * MyUserEntity
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class MyUserEntity extends BaseEntityWithFile
{
    // ...
    protected $id; // Change property visibility. The id need to be accessible by parent class
    // ...

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     *
     * @Assert\File(maxSize="2M")
     */
    private $picture;

    /**
     * @var string
     *
     * @ORM\Column(name="pictureFilename", type="string", length=255)
     */
    private $pictureFilename; // same name than previous property with suffix "Filename" and should be nullable

    // ...
}
```

### Form setup

The form setup is identical to file processing. Just add the file input in your form class or add it to your form
builder.

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
            ->add('picture')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novaway\Bundle\MyBundle\Entity\MyUserEntity',
        ));
    }

    // ...
}
```

### Form processing

NovawayFileManagementBundle is provided with a manager that process your form picture (data, upload and manipulation).
To save the data and process the picture, you need to call `BaseEntityWithImageManager::saveWithFiles`.

The `BaseEntityWithImageManager` constructor require 4 parameters to process data :

* *$arrayFilepath*: Associative array containing the file path for each property of the managed entity. This array must
also contain a 'root' (optional) and a 'web' path. You can use some variables to setup a dynamic name to the file that is
uploaded :
    * *{propertyName}* : The value of property of an entity. Here 'propertyName'.
    * *{-ext-}* : The original file extension
    * *{-imgformat-}* : Image format definition given to manager
    * *{-origin-}* : The original filename
    * *{-custom-}* : Allow you to set a custom string to append to the uploaded filename. To get this custom string, you need
to add a `getCustomPath` method to your entity
* *$entityManager* : The entity manager used to persist and save data
* *$imageFormatDefinition* : Associative array to define image properties which be stored on filesystem
* *$imageFormatChoice* : Associative array to apply some format definitions to an entity property. Parameters allowed :
    * *crop* : [Boolean - Default: false] Active crop image
    * *crop_position* : [String - Default: 'MM'] Crop image position (L = left, T = top, M = middle, B = bottom, R = right)
    * *bg_color* : [String - Default: '#FFFFFF'] Background color when image does not fill expected output size
    * *enlarge* : [Boolean - Default: false] Enlarge image when source is smaller than output. Fill with bg_color when false
    * *height* : [Number - Default: 0] Height (if not square)
    * *keep_proportions* : [Boolean - Default: true] Keep source image proportions (and fill with blank if needed)
    * *max_height* : [Number - Default: 0] Resize to fit non square at maximum
    * *max_size* : [Number - Default: 0] Resize to fit square at maximum
    * *max_width* : [Number - Default: 0] Resize to fit non square at maximum
    * *quality* : [Number - Default: 85] Output image quality (from 0 to 100)
    * *size* : [Number - Default: 0] Square size (set to 0 if not square)
    * *trim_bg* : [Boolean - Default: false] Remove the background color when not enlarging
    * *width* : [Number - Default: 0] Width (if not square)

``` php
<?php
// src/Novaway/Bundle/MyBundle/Controller/DefaultController.php

// use ...
use Novaway\Bundle\FileManagementBundle\Manager\BaseEntityWithImageManager;

class DefaultController extends Controller
{
    /**
     * @Route("/add", name="add")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $user = new MyUserEntity();
        $form = $this->createForm(new MyEntityType(), $item);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $filePath = array(
                    'bundle.web' => '/',
                    'picture' => '/uploads/{id}-{-imgformat-}.{-ext-}',
                );

                // Define picture properties which will be store on file system
                $imageFormatDefinition = array(
                    'original' => array(),     // don't modify picture
                    'myFormatNormal' => array(
                        'size' => 500,
                        'quality' => 95,
                        'bg_color' => 'FFFFFF',
                    ),
                    'myFormatThumbnail' => array(
                        'crop' => false,
                        'quality' => 95,
                        'size' => 64,
                    ),
                    'myFormatNewsFeed' => array(
                        'width' => 500,
                        'enlarge' => false,
                        'trim_bg' => true,
                    ),
                );

                // Associative array to apply some format definitions to a entity properties
                $imageFormatChoice = array(
                    'picture' => array('original', 'myFormatNormal', 'myFormatThumbnail'), // These format going to be apply on 'picture' property of my entity
                );

                $manager = new BaseEntityWithImageManager($filePath, $this->getDoctrine()->getManager(), $imageFormatDefinition, $imageFormatChoice);
                $manager->saveWithFiles($user);
            }
        }

        return array('form' => $form->createView());
    }
}
```
