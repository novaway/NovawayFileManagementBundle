## Bundle usage (Extend your own managers)

The bundle allow you to add file or image processing in your own managers easily.
You just have to use `Novaway\Bundle\FileManagementBundle\Manager\Traits\FileManagerTrait`
or `Novaway\Bundle\FileManagementBundle\Manager\Traits\ImageManagerTrait` in your own
manager class and implement `Novaway\Bundle\FileManagementBundle\Manager\FileManagerInterface`
or `Novaway\Bundle\FileManagementBundle\Manager\ImageManagerInterface`.

These traits have 2 abstracts methods you have to implement :
* `public function save(BaseEntityWithFile $entity)` to save an entity in your database
* `public function delete(BaseEntityWithFile $entity)` to delete an entity in your database

You also, must provide bundle configuration information to your controller using the `initialize`
method (it's recommend to make this operation in the constructor).

`FileManagerTrait::initialize` method require 1 parameter :

* *$arrayFilepath*: Associative array containing the file path for each property of the managed entity. This array must
also contain a 'root' (optional) and a 'web' path. You can use some variables to setup a dynamic name to the file that is
uploaded :
    * *{propertyName}* : The value of property of an entity. Here 'propertyName'. **Visibility level of each property used to build file path should be at least `protected`.**
    * *{-ext-}* : The original file extension
    * *{-origin-}* : The original filename
    * *{-custom-}* : Allow you to set a custom string to append to the uploaded filename. To get this custom string, you need
to add a `getCustomPath` method to your entity

`ImageManagerTrait::initialize` method require 3 parameters :

* *$arrayFilepath*: Associative array containing the file path for each property of the managed entity. This array must
also contain a 'root' (optional) and a 'web' path. You can use some variables to setup a dynamic name to the file that is
uploaded :
    * *{propertyName}* : The value of property of an entity. Here 'propertyName'.
    * *{-ext-}* : The original file extension
    * *{-imgformat-}* : Image format definition given to manager
    * *{-origin-}* : The original filename
    * *{-custom-}* : Allow you to set a custom string to append to the uploaded filename. To get this custom string, you need
to add a `getCustomPath` method to your entity
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

#### Example :

```php
use Novaway\Bundle\FileManagementBundle\Manager\Traits\FileManagerTrait;
use Novaway\Bundle\FileManagementBundle\Manager\FileManagerInterface;

class MyEntityCustomManager implements FileManagerInterface
{
    use FileManagerTrait;

    private $ormEntityManager;

    public function __construct($arrayFilepath, $entityManager)
    {
        $this->initializeFileManager($arrayFilepath);

        $this->ormEntityManager = $entityManager;
    }

    public function save(BaseEntityWithFile $entity)
    {
        $this->ormEntityManager->persist($entity);
        $this->ormEntityManager->flush();
    }

    public function delete(BaseEntityWithFile $entity)
    {
        $this->ormEntityManager->remove($entity);
        $this->ormEntityManager->flush();
    }

    // ...
}
```

After that, you can call `saveWithFiles` and `deleteWithFiles` methods to save your entity
with files.

```php
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
                $manager = new MyEntityCustomManager(array(
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
