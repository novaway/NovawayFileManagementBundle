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
        if($dim['crop']){
            $layer->cropMaximumInPixel(0, 0, $dim['crop_position']);
            $layer->resizeInPixel($dim['size'], $dim['size']);
        }
        else {
            $layer->resizeInPixel($dim['size'], $dim['size'], $dim['keep_proportions'], 0, 0, 'MM');
        }
    }
    elseif($dim['width'] > 0 || $dim['height'] > 0) {
            //First case Scenario: Source image is smaller than expected output
        if ($layer->getWidth() <= $dim['width'] && $layer->getHeight() <= $dim['height']) {

            if($dim['enlarge']){
                $layer->resizeInPixel($dim['width'], $dim['height']);
            }
            else {
                $boxLayer = ImageWorkshop::initVirginLayer($dim['width'], $dim['height'], $dim['bg_color']);

                     //Stack source image on top of box layer (middle position)
                $boxLayer->addLayer(1, $layer, 0, 0, 'MM');
                $layer = $boxLayer;
            }

            //Second case scenario: Source image is bigger than expected output
        } else{
            if($dim['crop']) {
                if($dim['keep_proportions']){
                    if($layer->getWidth() / $dim['width'] > $layer->getHeight() / $dim['height'] )
                    {
                        $layer->resizeInPixel(null, $dim['height'], true, 0, 0, 'MM');
                    } else {
                        $layer->resizeInPixel($dim['width'], null, true, 0, 0, 'MM');
                    }
                    $layer->cropInPixel($dim['width'], $dim['height'], 0, 0, $dim['crop_position']);

                } else {
                    $layer->resizeInPixel($dim['width'], $dim['height']);
                    $layer->cropInPixel($dim['width'], $dim['height'], 0, 0, $dim['crop_position']);
                }
            } else {
                $layer->resizeInPixel($dim['width'], $dim['height'], $dim['keep_proportions'], 0, 0, 'MM');
            }

        }
    }
    else if($dim['max_size'] > 0 || $dim['max_width'] > 0 || $dim['max_height'] > 0){
        if($dim['max_size'] > 0){
            $dim['max_width'] = $dim['max_size'];
            $dim['max_height'] = $dim['max_size'];
        }

        $trimWidth = $dim['max_width'] === 0 ? ($dim['max_height'] * $initSize['hw_ratio']) : $dim['max_width'];
        $trimHeight = $dim['max_height'] === 0 ? ($dim['max_width'] / $initSize['hw_ratio']) : $dim['max_height'];
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