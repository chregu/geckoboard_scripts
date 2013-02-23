<?php

namespace Liip\Geckoboard;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Guzzle\Http\Client;



class Bamboo
{
    public function latest(Application $app, $key)
    {

        include ( __DIR__.'/../../../config.php');

        // Create a client and provide a base URL
        $client = new Client('http://bamboo.liip.ch');
        // Create a request with basic Auth
        $request = $client->get('/rss/createAllBuildsRssFeed.action?feedType=rssAll&buildKey=' . $key . '&os_authType=basic')->setAuth($bamboo_user, $bamboo_pass);
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
        "api_key"=>$geckoboard_api,
        "data" => $message
        );

        $client = new Client('https://push.geckoboard.com/');
        $payload = $app->json($payload);
        $json =  $payload->getContent();
        if ($json != file_get_contents("../tmp/payload.last.txt")) {
            $request = $client->post('/v1/send/' . $widget_key, null, $json);
            $request->send();
            file_put_contents("../tmp/payload.last.txt",$json);
        }
        return $payload;
    }
}
