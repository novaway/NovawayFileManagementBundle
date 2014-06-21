## Bundle usage (Working with file)

### Entity setup

To manage files in your entity, you just need to extend the `BaseEntityWithFile` class and add 2 properties :

* The first one for the file which is uploaded
* The second is the filename which will be store in database. **This parameter should have the same name than the previous
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
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     *
     * @Assert\File(maxSize="5M")
     */
    private $myFile;

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
    * *{propertyName}* : The value of property of an entity. Here 'propertyName'.
    * *{-ext-}* : The original file extension
    * *{-origin-}* : The original filename
    * *{-custom-}* : Allow you to set a custom string to append to the uploaded filename. To get this custom string, you need
to add a `getCustomPath` method to your entity
* *$entityManager* : The entity manager used to persist and save data

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
