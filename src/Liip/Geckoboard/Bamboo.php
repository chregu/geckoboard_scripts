<?php

namespace Liip\Geckoboard;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;



class Bamboo
{
    /**
     * without push
     * http://localhost/gecko/index.php/bamboo/latest/DIG-DIG2
     * with push
     * http://localhost/gecko/index.php/bamboo/latest/DIG-DIG2/c415b6693f5fdedf5a09c34b0ac5666d/25428-7116fa8f7b8111a14654e793b7aca2f7
     */
    public function latest(Application $app, $key, $apiKey = null, $widgetKey = null)
    {
        // Create a client and provide a base URL
        $client = $app['http.client']($app['bamboo.host']);
        // Create a request with basic Auth
        $request = $client->get('/rss/createAllBuildsRssFeed.action?feedType=rssAll&buildKey=' . $key . '&os_authType=basic')->setAuth($app['bamboo.user'], $app['bamboo.pass']);
        // Send the request and get the response
        $response = $request->send();
        $channel = \Zend\Feed\Reader\Reader::importString($response->getBody());
        $message = array();
        foreach ($channel as $item) {
            $message['item']= array();
            $title = stripslashes($item->getTitle());
            $msg =  array();
            $t = "<div style='font-size: 14px; line-height: 1.2em;'> ";
            if (strpos( $title, "FAILED")) {
                $msg['type'] = 1;
                $css = "style = 'color: #AE432E;'";
            } else {
                $css = "style = 'color: #77AB13;'";
            }
            $msg["text"] = $t.'<a '.$css.' href="' . $item->getLink() .'">'. $title. '</a> - ' . str_replace(array("<p>","</p>"),"", stripslashes($item->getDescription()));
            $message['item'][] = $msg;

            break;
        }


        $payload = array(
            "data" => $message
        );
        if ($apiKey) {
            $payload["api_key"] = $apiKey;
        }


        $client = $app['http.client']('https://push.geckoboard.com/');
        $payload = $app->json($payload);
        if ($widgetKey) {
            $json =  $payload->getContent();
            if ($json != file_get_contents("../tmp/payload.last.txt")) {
                $request = $client->post('/v1/send/' . $widgetKey, null, $json);
                $request->send();
                file_put_contents("../tmp/payload.last.txt",$json);
            }
        }
        return $payload;
    }
}
