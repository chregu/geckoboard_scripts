<?php

// web/index.php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

// definitions
$app['debug'] = true;
$app->get('/bamboo/latest/{key}', 'Liip\Geckoboard\Bamboo::latest');

$app->run();

