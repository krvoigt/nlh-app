<?php

$config = Symfony\CS\Config\Config::create();
$config
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->getFinder()
    ->files()
    ->in(__DIR__)
    ->exclude('build')
    ->exclude('cache')
    ->exclude('var')
    ->exclude('vendor');

return $config;
