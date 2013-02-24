<?php

namespace Liip\Geckoboard;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;



class Bamboo extends \Liip\Geckoboard
{
    /**
     * without push
     * http://localhost/gecko/index.php/bamboo/latest/DIG-DIG2
     * with push
     * http://localhost/gecko/index.php/bamboo/latest/DIG-DIG2?apikey=c415b6693f5fdedf5a09c34b0ac5666d&widgetkey=25428-7116fa8f7b8111a14654e793b7aca2f7
     */

    public function latest(Request $req, Application $app, $key)
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

        $this->push($message, $req, $app);
        $payload =  $app->json($message);
        return $payload;
    }

}
