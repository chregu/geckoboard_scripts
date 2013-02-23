<?php

namespace Liip;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;



class Geckoboard
{
    function push(Array $payload, Request $req, Application $app) {
        $apiKey    = $req->query->get('apikey');
        if ($apiKey) {
            $payload["api_key"] = $apiKey;
            $widgetKey = $req->query->get('widgetkey');

            if ($widgetKey) {
                $filename = "../tmp/" . md5($req->getUri());
                $client = $app['http.client']('https://push.geckoboard.com/');
                $json = json_encode($payload);
                if (!file_exists($filename) || $json != file_get_contents($filename)) {
                    $request = $client->post('/v1/send/' . $widgetKey, null, $json);
                    $request->send();
                    file_put_contents($filename, $json);
                }
            }
        }
    }
}
