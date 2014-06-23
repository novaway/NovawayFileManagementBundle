<small>Novaway\Bundle\FileManagementBundle\Manager</small>

BaseEntityWithImageManager
==========================

Novaway\Bundle\FileManagementBundle\Manager\BaseEntityWithFileManager

Description
-----------

Extend your managers with this class to add File management.

Signature
---------

- It is a(n) **class**.
- It is a subclass of [`BaseEntityWithFileManager`](../../../../Novaway/Bundle/FileManagementBundle/Manager/BaseEntityWithFileManager.md).

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct) &mdash; The manager constructor
- [`getFileAbsolutePath()`](#getFileAbsolutePath) &mdash; Returns the absolute (root) filepath of a property for a specific entity
- [`getFileWebPath()`](#getFileWebPath) &mdash; Returns the relative (web) filepath of a property for a specific entity
- [`removeFiles()`](#removeFiles) &mdash; Removes one or several file from the entity
- [`replaceFile()`](#replaceFile) &mdash; Replace a property file by another, giver it&#039;s path

### `__construct()` <a name="__construct"></a>

The manager constructor

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$arrayFilepath` (`array`) &mdash; Associative array containing the file path for each property of the managed entity. This array must also contain a &#039;root&#039; and a &#039;web&#039; path.
    - `$entityManager` (`mixed`) &mdash; The entity manager used to persist and save data.
    - `$imageFormatDefinition` (`array`) &mdash; Associative array to define image properties which be stored on filesystem
    - `$imageFormatChoices` (`array`) &mdash; Associative array to apply some format definitions to an entity property
- It does not return anything.

### `getFileAbsolutePath()` <a name="getFileAbsolutePath"></a>

Returns the absolute (root) filepath of a property for a specific entity

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$entity` ([`BaseEntityWithFile`](../../../../Novaway/Bundle/FileManagementBundle/Entity/BaseEntityWithFile.md)) &mdash; The current entity
    - `$propertyName` (`string`) &mdash; The property matching the file
    - `$format`
- _Returns:_ The absolute filepath
    - `string`

### `getFileWebPath()` <a name="getFileWebPath"></a>

Returns the relative (web) filepath of a property for a specific entity

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$entity` ([`BaseEntityWithFile`](../../../../Novaway/Bundle/FileManagementBundle/Entity/BaseEntityWithFile.md)) &mdash; The current entity
    - `$propertyName` (`string`) &mdash; The property matching the file
    - `$format`
- _Returns:_ The relative filepath
    - `string`

### `removeFiles()` <a name="removeFiles"></a>

Removes one or several file from the entity

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$entity` ([`BaseEntityWithFile`](../../../../Novaway/Bundle/FileManagementBundle/Entity/BaseEntityWithFile.md)) &mdash; The entity from witch the file will be removed
    - `$properties` (`mixed`) &mdash; A file property name or an array containing file property names
    - `$doEraseFiles` (`boolean`) &mdash; Set to FALSE to keep file on the disk
    - `$doSave` (`boolean`) &mdash; Set to FALSE if you don&#039;t want to save the entity while file are deleted
- _Returns:_ The saved entity
    - [`BaseEntityWithFile`](../../../../Novaway/Bundle/FileManagementBundle/Entity/BaseEntityWithFile.md)

### `replaceFile()` <a name="replaceFile"></a>

Replace a property file by another, giver it&#039;s path

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$entity` ([`BaseEntityWithFile`](../../../../Novaway/Bundle/FileManagementBundle/Entity/BaseEntityWithFile.md)) &mdash; The entity owning the files
    - `$propertyName` (`string`) &mdash; The property linked to the file
    - `$sourceFilepath` (`Novaway\Bundle\FileManagementBundle\Manager\[type]`) &mdash; [description]
    - `$destFilepath` (`Novaway\Bundle\FileManagementBundle\Manager\[type]`) &mdash; [description]
    - `$operation` (`string`) &mdash; &#039;copy&#039; or &#039;rename&#039;
- _Returns:_ An array containing informations about the copied file
    - `array`

