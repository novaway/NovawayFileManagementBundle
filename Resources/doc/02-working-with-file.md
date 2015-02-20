## Bundle usage (Working with file)

### Entity setup

To manage files in your entity, you just need to extend the `BaseEntityWithFile` class and add some properties :

* If you want to use upload strategy (used to upload a file from your computer) you need to add a dedicated property
* If you want to use copy strategy (used to retrieve an existing file from another location) you need to add a dedicated property
* The last is the filename which will be store in database. **This parameter should have the same name than the previous
with `Filename` suffix**.


``` php
<?php
// src/Novaway/Bundle/MyBundle/Entity/MyEntity

// use ...
use Novaway\Bundle\FileManagementBundle\Entity\BaseEntityWithFile;

/**
 * MyEntity
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class MyEntity extends BaseEntityWithFile
{
    // ...
    protected $id; // Change property visibility. The id need to be accessible by parent class
    // ...

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile This property is used by the upload strategy
     *
     * @Assert\File(maxSize="5M")
     */
    private $myFile;

    /**
     * @var string This property is used by the copy strategy
     */
    private $myFilePath;

    /**
     * @var string $myFileProperty
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $myFileFilename; // same name than previous property with suffix "Filename" and should be nullable

    // ...
}
```

### Form setup

There is nothing special to do to manage your form. Just add the file input in your form class or add it to your form
builder.

``` php
// src/Novaway/Bundle/MyBundle/Form/Type/MyEntityType.php

// use ...

class MyEntityType extends AbstractType
{
    // ...

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add(...)
            ->add('myFile')
            //->add('myFilePath') => if you want user to input an URL for file copy
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novaway\Bundle\MyBundle\Entity\MyEntity',
        ));
    }

    // ...
}
```

### Form processing

NovawayFileManagementBundle is provided with a manager that process your form (data and upload). To save the data and
process the file, you need to call `BaseEntityWithFileManager::saveWithFiles` or `BaseEntityWithImageManager::saveWithFiles`
(if you [process images](03-working-with-image.md)).

The `BaseEntityWithFileManager` constructor require 2 parameters to process data :

* *$arrayFilepath*: Associative array containing the file path for each property of the managed entity. This array must
also contain a 'root' (optional) and a 'web' path. You can use some variables to setup a dynamic name to the file that is
uploaded :
    * *{propertyName}* : The value of property of an entity. Here 'propertyName'. **Visibility level of each property used to build file path should be at least `protected`.**
    * *{-ext-}* : The original file extension
    * *{-origin-}* : The original filename
    * *{-custom-}* : Allow you to set a custom string to append to the uploaded filename. To get this custom string, you need
to add a `getCustomPath` method to your entity
* *$entityManager* : The entity manager used to persist and save data

Depending on the property which is filled (myFile or myFilePath) the manager will choose the right strategy to use.
*Note* that if both properties are populated, the copy will be used.

``` php
<?php
// src/Novaway/Bundle/MyBundle/Controller/DefaultController.php

// use ...
use Novaway\Bundle\FileManagementBundle\Manager\BaseEntityWithFileManager;

class DefaultController extends Controller
{
    /**
     * @Route("/add", name="add")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $item = new MyEntity();
        $form = $this->createForm(new MyEntityType(), $item);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $manager = new BaseEntityWithFileManager(array(
                    'bundle.web' => '/',
                    'item' => '/uploads/{id}.{-ext-}',
                ), $this->getDoctrine()->getManager());
                $manager->saveWithFiles($item);
            }
        }

        return array('form' => $form->createView());
    }
}
```
