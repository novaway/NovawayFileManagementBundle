<?php

use \mageekguy\atoum;

$cloverWriter = new atoum\writers\file(__DIR__.'/build/atoum.clover.xml');
$cloverReport = new atoum\reports\asynchronous\clover();
$cloverReport->addWriter($cloverWriter);

$xunitWriter = new atoum\writers\file(__DIR__.'/build/atoum.xunit.xml');
$xunitReport = new atoum\reports\asynchronous\xunit();
$xunitReport->addWriter($xunitWriter);

$runner->addReport($script->addDefaultReport());
$runner->addReport($cloverReport);
$runner->addReport($xunitReport);
