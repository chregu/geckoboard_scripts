<?php

// web/index.php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

/* Some general config */
$app['http.client'] = $app->protect(function ($host) {
    return new \Guzzle\Http\Client($host);
});

$app['cache'] = $app->share(function ($host) {
   return new \Doctrine\Common\Cache\FilesystemCache(__DIR__.'/../tmp/');

});

$app['debug'] = true;

include ( __DIR__.'/../config.php');

// definitions
$app->get('/bamboo/latest/{key}', 'Liip\Geckoboard\Bamboo::latest');
$app->get('/zebra/budgetSum/{key}', 'Liip\Geckoboard\Zebra::budgetSum');
$app->get('/zebra/budgetCurve/', 'Liip\Geckoboard\Zebra::budgetCurve');
$app->get('/zebra/highcharts', 'Liip\Geckoboard\Zebra::highcharts');
$app->get('/drive/geckometer/{key}', 'Liip\Geckoboard\Drive::geckometer');
$app->get('/drive/geckometer/{key}/{topleft}', 'Liip\Geckoboard\Drive::geckometer');

$app->run();


