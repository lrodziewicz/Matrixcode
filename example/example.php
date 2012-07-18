<?php
require 'SplClassLoader.php';

$classLoader = new SplClassLoader(null, realpath(__DIR__.'/../src/'));
$classLoader->register();

Matrixcode_Factory::factory('qrcode', array(), 'svg');
