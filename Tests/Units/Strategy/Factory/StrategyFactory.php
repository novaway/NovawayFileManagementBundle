<?php

namespace Novaway\Bundle\FileManagementBundle\Tests\Units\Strategy\Factory;

use mageekguy\atoum;

class StrategyFactory extends atoum\test
{
    public function testCreateFromArrayPath()
    {
        $this
            ->given($paths = array('myProperty' => '/path/to/prop'))
            ->if($this->newTestedInstance('/root', $paths))
            ->then
                ->object($this->testedInstance->createFromArrayPath('myProperty'))
                    ->isInstanceOf('Novaway\Bundle\FileManagementBundle\Strategy\UploadStrategy')
            ->given(
                $paths = array(
                    'myProperty' => array('path' => '/path/to/prop', 'strategy' => 'upload'),
                    'foo' => array('path' => '/foo', 'strategy' => 'upload'),
                )
            )
            ->if($this->newTestedInstance('/root', $paths))
            ->then
                ->object($this->testedInstance->createFromArrayPath('myProperty'))
                    ->isInstanceOf('Novaway\Bundle\FileManagementBundle\Strategy\UploadStrategy')
                ->object($this->testedInstance->createFromArrayPath('foo'))
                    ->isInstanceOf('Novaway\Bundle\FileManagementBundle\Strategy\UploadStrategy')
        ;
    }
}
