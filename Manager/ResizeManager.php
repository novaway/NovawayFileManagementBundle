<?php

namespace Novaway\Bundle\FileManagementBundle\Manager;

use PHPImageWorkshop\ImageWorkshop;

/**
 * Image resizer helper
 */
class ResizeManager
{
    /**
     * Resize an image
     *
     * Image properties parameters:
     *   - size : Square size (set to 0 if not square)
     *   - width : Width (if not square)
     *   - height : Height (if not square)
     *   - max_size : Resize to fit square at maximum
     *   - max_width : Resize to fit non square at maximum
     *   - max_height : Resize to fit non square at maximum
     *   - crop : Crop image
     *   - crop_position : Crop image position (L = left, T = top, M = middle, B = bottom, R = right)
     *   - quality : Output image quality (from 0 to 100)
     *   - enlarge : Enlarge image when source is smaller than output. Fill with bg_color when false
     *   - trim_bg : Remove the background color when not enlarging
     *   - keep_proportions : Keep source image proportions (and fill with blank if needed)
     *   - bg_color : Background color when image does not fill expected output size
     *
     * @param string $sourcePath         Image path to process
     * @param string $destPathWithFormat Folder to output processed image
     * @param array  $dim                Image properties
     */
    public static function resize($sourcePath, $destPathWithFormat, $dim)
    {
        $layer = ImageWorkshop::initFromPath($sourcePath);
        $initSize = array(
            'width'    => $layer->getWidth(),
            'height'   => $layer->getHeight(),
            'wh_ratio' => $layer->getWidth() / $layer->getHeight(),
            );

        if ($dim['size'] > 0) {
            $dim['width']  = $dim['size'];
            $dim['height'] = $dim['size'];
        }
        if ($dim['max_size'] > 0) {
            $dim['max_width']  = $dim['max_size'];
            $dim['max_height'] = $dim['max_size'];
        }

        if ($dim['width'] > 0 || $dim['height'] > 0) {

            if ($dim['crop']) {
                if ($dim['keep_proportions']) {
                    if ($layer->getWidth() / $dim['width'] > $layer->getHeight() / $dim['height'] ) {
                        $layer->resizeInPixel(null, $dim['height'], true, 0, 0, 'MM');
                    } else {
                        $layer->resizeInPixel($dim['width'], null, true, 0, 0, 'MM');
                    }
                    $layer->cropInPixel($dim['width'], $dim['height'], 0, 0, $dim['crop_position']);
                } else {
                    $layer->resizeInPixel($dim['width'], $dim['height']);
                    $layer->cropInPixel($dim['width'], $dim['height'], 0, 0, $dim['crop_position']);
                }
            }

            if (!$dim['enlarge'] && $dim['trim_bg']) {

                if ($layer->getWidth() > $dim['width'] && $dim['width'] > 0) {
                    $layer->resizeInPixel($dim['width'], null, true, 0, 0, 'MM');
                }
                if ($layer->getHeight() > $dim['height'] && $dim['height'] > 0) {
                    $layer->resizeInPixel(null, $dim['height'], true, 0, 0, 'MM');
                }

            } else {

                if (!$dim['enlarge'] && $layer->getWidth() <= $dim['width'] && $layer->getHeight() <= $dim['height']) {
                        $boxLayer = ImageWorkshop::initVirginLayer($dim['width'], $dim['height'], $dim['bg_color']);
                        $boxLayer->addLayer(1, $layer, 0, 0, 'MM');
                        $layer = $boxLayer;
                } else {
                    if ($dim['trim_bg']) {
                        $hratio = $dim['height'] / $layer->getHeight();
                        $wratio = $dim['width'] / $layer->getWidth();

                        $w = $wratio < $hratio ? $dim['width'] : $dim['height'] * $initSize['wh_ratio'];
                        $h = $hratio < $wratio ? $dim['height'] : $dim['width'] / $initSize['wh_ratio'];

                        $layer->resizeInPixel($w, $h, $dim['keep_proportions'], 0, 0, 'MM');
                    } else {
                        $dim['width'] = $dim['width'] > 0 ? $dim['width'] : null;
                        $dim['height'] = $dim['height'] > 0 ? $dim['height'] : null;
                        $layer->resizeInPixel($dim['width'], $dim['height'], $dim['keep_proportions'], 0, 0, 'MM');
                    }
                }

            }

            //Add color background
            if (!$dim['trim_bg'] && ($layer->getWidth() <= $dim['width'] && $layer->getHeight() <= $dim['height'])) {
                $colorLayer = ImageWorkshop::initVirginLayer($dim['width'], $dim['height'], $dim['bg_color']);
                $colorLayer->addLayer(1, $layer, 0, 0, 'MM');
                $layer = $colorLayer;
            }

        }

        if ($dim['max_width'] > 0 || $dim['max_height'] > 0) {

            if ($layer->getWidth() / $dim['max_width'] > $layer->getHeight() / $dim['max_height'] ) {
                $layer->resizeInPixel(min($dim['max_width'], $layer->getWidth()), null, true, 0, 0, 'MM');
            } else {
                $layer->resizeInPixel(null, min($dim['max_height'], $layer->getHeight()), true, 0, 0, 'MM');
            }

        }

        $layer->save(
            substr($destPathWithFormat, 0, strrpos($destPathWithFormat, '/')),
            substr($destPathWithFormat, strrpos($destPathWithFormat, '/') + 1),
            true,
            null,
            $dim['quality']
            );

        $layer = null;
    }

}
