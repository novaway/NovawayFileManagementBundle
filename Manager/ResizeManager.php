<?php

namespace Novaway\Bundle\FileManagementBundle\Manager;

use PHPImageWorkshop\ImageWorkshop;

class ResizeManager
{

    public static function resize($sourcePath, $destPathWithFormat, $dim)
    {
        $layer = ImageWorkshop::initFromPath($sourcePath);
        $initSize = array(
            'width' => $layer->getWidth(),
            'height' => $layer->getHeight(),
            'wh_ratio' => $layer->getWidth() / $layer->getHeight(),
            );

        if($dim['size'] > 0){
            $dim['width'] = $dim['size'];
            $dim['height'] = $dim['size'];
        }

        if($dim['width'] > 0 || $dim['height'] > 0) {

            if($dim['crop']){
                $layer->cropMaximumInPixel(0, 0, $dim['crop_position']);
            }

            if(!$dim['enlarge'] && $dim['trim_bg'])
            {

                if ($layer->getWidth() > $dim['width']){
                    $layer->resizeInPixel($dim['width'], 0, true, 0, 0, 'MM');
                }
                if($layer->getHeight() > $dim['height']) {
                    $layer->resizeInPixel(0, $dim['height'], true, 0, 0, 'MM');
                }

            } else {

                if (!$dim['enlarge'] && $layer->getWidth() <= $dim['width'] && $layer->getHeight() <= $dim['height']) {
                        $boxLayer = ImageWorkshop::initVirginLayer($dim['width'], $dim['height'], $dim['bg_color']);
                        $boxLayer->addLayer(1, $layer, 0, 0, 'MM');
                        $layer = $boxLayer;
                } else{
                    if($dim['trim_bg']){
                        $hratio = $dim['height'] / $layer->getHeight();
                        $wratio = $dim['width'] / $layer->getWidth();

                        $w = $wratio < $hratio ? $dim['width'] : $dim['height'] * $initSize['wh_ratio'];
                        $h = $hratio < $wratio ? $dim['height'] : $dim['width'] / $initSize['wh_ratio'];

                        $layer->resizeInPixel($w, $h, $dim['keep_proportions'], 0, 0, 'MM');
                    } else {
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