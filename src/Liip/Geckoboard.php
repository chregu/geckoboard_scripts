<?php

namespace Liip;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;



class Geckoboard
{
    function push(Array $payload, Request $req, Application $app) {
        $apiKey    = $req->query->get('apikey');

        $payload = array('data' => $payload);

        if ($apiKey) {
            $payload["api_key"] = $apiKey;
            $widgetKey = $req->query->get('widgetkey');

            if ($widgetKey) {
                $cachehash = "spush" . md5($req->getUri());
                $client = $app['http.client']('https://push.geckoboard.com/');
                $json = json_encode($payload);
                if ($json != $app['cache']->fetch($cachehash)) {
                    $request = $client->post('/v1/send/' . $widgetKey, null, $json);
                    $request->send();
                    $app['cache']->save($cachehash,$json);
                }
            }
        }
    }
}
