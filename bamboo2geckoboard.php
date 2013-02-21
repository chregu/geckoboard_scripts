<?php


require_once 'vendor/autoload.php';
use Guzzle\Http\Client;

// Create a client and provide a base URL
$client = new Client('http://bamboo.liip.ch');
// Create a request with basic Auth
$request = $client->get('/rss/createAllBuildsRssFeed.action?feedType=rssAll&buildKey=DIG-DIG2&os_authType=basic')->setAuth('chregu', 'D8Zoec.2vW');
// Send the request and get the response
$response = $request->send();
$channel = Zend\Feed\Reader\Reader::importString($response->getBody());
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
        $css = "style = color: #77AB13;'";
    }
    $msg["text"] = $t.'<a '.$css.' href="' . $item->getLink() .'">'. $title. '</a> - ' . str_replace(array("<p>","</p>"),"", stripslashes($item->getDescription()));
    $message['item'][] = $msg;

    break;
}


$payload = array(
  "api_key"=>"c415b6693f5fdedf5a09c34b0ac5666d",
  "data" => $message

 );

$client = new Client('https://push.geckoboard.com/');
$request = $client->post('/v1/send/25428-7116fa8f7b8111a14654e793b7aca2f7', null, json_encode($payload));
$request->send();
