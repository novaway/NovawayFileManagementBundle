BaseEntityWithFileManager
=========================

Extend your managers with this class to add File management.

Namespace
---------

Novaway\Bundle\FileManagementBundle\Manager

Signature
---------

- It is a(n) **class**.

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct) &mdash; The manager constructor
- [`getFileAbsolutePath()`](#getFileAbsolutePath) &mdash; Returns the absolute (root) filepath of a property for a specific entity
- [`getFileWebPath()`](#getFileWebPath) &mdash; Returns the relative (web) filepath of a property for a specific entity
- [`getFileProperties()`](#getFileProperties) &mdash; Returns all the document properties names for the managed entity
- [`save()`](#save) &mdash; Persist and flush the entity
- [`delete()`](#delete) &mdash; Remove and flush the entity
- [`saveWithFiles()`](#saveWithFiles) &mdash; Saves an entity and manages its file storage
- [`deleteWithFiles()`](#deleteWithFiles) &mdash; Deletes an entity and manages its file storage
- [`slug()`](#slug) &mdash; Creates a slug from a string
- [`removeFiles()`](#removeFiles) &mdash; Removes one or several file from the entity
- [`replaceFile()`](#replaceFile) &mdash; Replace a property file by another, giver it&#039;s path

### `__construct()` <a name="__construct"></a>

The manager constructor

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$arrayFilepath` (`array`) &mdash; Associative array containing the file path for each property of the managed entity. This array must also contain a &#039;root&#039; and a &#039;web&#039; path.
    - `$entityManager` (`mixed`) &mdash; The entity manager used to persist and save data.
- It does not return anything.

### `getFileAbsolutePath()` <a name="getFileAbsolutePath"></a>

Returns the absolute (root) filepath of a property for a specific entity

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$entity` (`BaseEntityWithFile`) &mdash; The current entity
    - `$propertyName` (`string`) &mdash; The property matching the file
- _Returns:_ The absolute filepath
    - `string`

### `getFileWebPath()` <a name="getFileWebPath"></a>

Returns the relative (web) filepath of a property for a specific entity

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$entity` (`BaseEntityWithFile`) &mdash; The current entity
    - `$propertyName` (`string`) &mdash; The property matching the file
- _Returns:_ The relative filepath
    - `string`

### `getFileProperties()` <a name="getFileProperties"></a>

Returns all the document properties names for the managed entity

#### Signature

- It is a **public** method.
- _Returns:_ An array of the property names
    - `array`

### `save()` <a name="save"></a>

Persist and flush the entity

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$entity` (`BaseEntityWithFile`) &mdash; The entity to save
- _Returns:_ The saved entity
    - `BaseEntityWithFile`

### `delete()` <a name="delete"></a>

Remove and flush the entity

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$entity` (`BaseEntityWithFile`) &mdash; The entity to delete
- _Returns:_ The deleted entity
    - `BaseEntityWithFile`

### `saveWithFiles()` <a name="saveWithFiles"></a>

Saves an entity and manages its file storage

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$entity` (`BaseEntityWithFile`) &mdash; The entity to save
    - `$callback` (`callable`|`null`) &mdash; A callback method. Ex : array(&amp;$obj, &#039;somePublicMethod&#039;) The callback may have 3 parameters : original filename, extension, file size
- _Returns:_ The saved entity
    - `BaseEntityWithFile`

### `deleteWithFiles()` <a name="deleteWithFiles"></a>

Deletes an entity and manages its file storage

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$entity` (`BaseEntityWithFile`) &mdash; The entity to delete
- _Returns:_ The deleted entity
    - `BaseEntityWithFile`

### `slug()` <a name="slug"></a>

Creates a slug from a string

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$str` (`string`) &mdash; The string to slug
- _Returns:_ The slugged string
    - `string`

### `removeFiles()` <a name="removeFiles"></a>

Removes one or several file from the entity

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$entity` (`BaseEntityWithFile`) &mdash; The entity from witch the file will be removed
    - `$properties` (`array`|`string`) &mdash; A file property name or an array containing file property names
    - `$doEraseFiles` (`boolean`) &mdash; Set to FALSE to keep file on the disk
    - `$doSave` (`boolean`) &mdash; Set to FALSE if you don&#039;t want to save the entity while file are deleted
- _Returns:_ The saved entity
    - `BaseEntityWithFile`

### `replaceFile()` <a name="replaceFile"></a>

Replace a property file by another, giver it&#039;s path

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$entity` (`BaseEntityWithFile`) &mdash; The entity owning the files
    - `$propertyName` (`string`) &mdash; The property linked to the file
    - `$sourceFilepath` (`string`) &mdash; The file source folder
    - `$destFilepath` (`string`|`null`) &mdash; The folder where the file will be copied
    - `$operation` (`string`) &mdash; &#039;copy&#039; or &#039;rename&#039;
- _Returns:_ An array containing informations about the copied file
    - `array`
    - `null`
