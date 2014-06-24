ResizeManager
=============

Image resizer helper

Namespace
---------

Novaway\Bundle\FileManagementBundle\Manager

Signature
---------

- It is a(n) **class**.

Methods
-------

The class defines the following methods:

- [`resize()`](#resize) &mdash; Resize an image

### `resize()` <a name="resize"></a>

Resize an image

#### Description

Image properties parameters:
  - size : Square size (set to 0 if not square)
  - width : Width (if not square)
  - height : Height (if not square)
  - max_size : Resize to fit square at maximum
  - max_width : Resize to fit non square at maximum
  - max_height : Resize to fit non square at maximum
  - crop : Crop image
  - crop_position : Crop image position (L = left, T = top, M = middle, B = bottom, R = right)
  - quality : Output image quality (from 0 to 100)
  - enlarge : Enlarge image when source is smaller than output. Fill with bg_color when false
  - trim_bg : Remove the background color when not enlarging
  - keep_proportions : Keep source image proportions (and fill with blank if needed)
  - bg_color : Background color when image does not fill expected output size

#### Signature

- It is a **public static** method.
- It accepts the following parameter(s):
    - `$sourcePath` (`string`) &mdash; Image path to process
    - `$destPathWithFormat` (`string`) &mdash; Folder to output processed image
    - `$dim` (`array`) &mdash; Image properties
- It does not return anything.
