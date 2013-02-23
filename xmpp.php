<?php

require_once 'vendor/autoload.php';
$client = new JAXL(array(
    'jid' => 'liip.geckoboard@gmail.com',
    'pass' => 'eUHtiW2k9xbLGm',
    'log_level' => 5
));

ini_set("error_log","/tmp/php.err.log");

$client->add_cb('on_auth_success', function() {
    global $client;
    $client->set_status("available!");  // set your status
    $client->get_vcard();               // fetch your vcard
    $client->get_roster();              // fetch your roster list

$entity_jid = "chregu@liip.ch";
$x = new JAXLXml('query', NS_DISCO_ITEMS, array("node" =>'http://jabber.org/protocol/offline'));
$iq = $client->get_iq_pkt(
			array('type'=>'get'),
			$x
		);

        var_dump($iq->to_string());
        $client->send($iq);
        var_dump("sent");
       $client->add_cb('on_stanza_id_'.$iq->id, function($msg) {var_dump($msg);});




});
$client->add_cb('on_normal_message', function($msg) {
    var_dump("normal");
});

$client->add_cb('on_iq_stanza', function($msg) {
    var_dump("iq");
});

$client->add_cb('on_query_stanza', function($msg) {
    var_dump("query");
});


$client->add_cb('on_error_message', function($msg) {
    var_dump("error");
    var_dump($msg->to_string());
});
$client->add_cb('on_chat_message', function($msg) {
    global $client;

    // echo back
    //$msg->to = $msg->from;
    //$msg->from = $client->full_jid->to_string();
    if ($msg->body) {
    var_dump($msg->body);
    }
    var_dump("chat");
  //  $client->send($msg);
});

$client->add_cb('on_disconnect', function() {
    _debug("got on_disconnect cb");
});

$client->start();


