<?php

namespace Novaway\Bundle\FileManagementBundle\Manager;

use PHPImageWorkshop\ImageWorkshop;

class ResizeManager{

    public static function resize($sourcePath, $destPathWithFormat, $dim)
    {
        $layer = ImageWorkshop::initFromPath($sourcePath);
        $initSize = array(
            'width' => $layer->getWidth(),
            'height' => $layer->getHeight(),
            'hw_ratio' => $layer->getHeight() / $layer->getWidth(),
            );

        if($dim['size'] > 0){
            $dim['width'] = $dim['size'];
            $dim['height'] = $dim['size'];
        }
        if($dim['max_size'] > 0){
            $dim['max_width'] = $dim['max_size'];
            $dim['max_height'] = $dim['max_size'];
        }

        if($dim['width'] > 0 || $dim['height'] > 0) {

            if($dim['crop']){
                $layer->cropMaximumInPixel(0, 0, $dim['crop_position']);
            }

            if (!$dim['enlarge'] && $layer->getWidth() <= $dim['width'] && $layer->getHeight() <= $dim['height']) {
                    $boxLayer = ImageWorkshop::initVirginLayer($dim['width'], $dim['height'], $dim['bg_color']);
                    $boxLayer->addLayer(1, $layer, 0, 0, 'MM');
                    $layer = $boxLayer;
            } else{
                $layer->resizeInPixel($dim['width'], $dim['height'], $dim['keep_proportions'], 0, 0, 'MM');
            }

            if ($layer->getWidth() <= $dim['width'] && $layer->getHeight() <= $dim['height']) {
                $colorLayer = ImageWorkshop::initVirginLayer($dim['width'], $dim['height'], $dim['bg_color']);
                $colorLayer->addLayer(1, $layer, 0, 0, 'MM');
                $layer = $colorLayer;
            }

        } else if($dim['max_width'] > 0 || $dim['max_height'] > 0){

            if ($layer->getWidth() > $dim['max_width']){
                $layer->resizeInPixel($dim['max_width'], 0, true, 0, 0, 'MM');
            }
            if($layer->getHeight() > $dim['max_height']) {
                $layer->resizeInPixel(0, $dim['max_height'], true, 0, 0, 'MM');
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