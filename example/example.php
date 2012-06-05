<?php
require 'autoload.php';

$classLoader = new SplClassLoader('Matrixcode', realpath(__DIR__.'/../src/'));
$classLoader->register();

$matrix = \Matrixcode\Factory::factory(array());
