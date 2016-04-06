<?php

$beaverLibs = realpath(__DIR__ . '/../beaver') . '/';

include $beaverLibs . 'ClassLoader.php';

$classLoader = new \Beaver\ClassLoader();
$classLoader->addNamespace('Beaver', $beaverLibs);
$classLoader->register();

return $classLoader;