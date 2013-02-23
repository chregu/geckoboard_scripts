<?php

namespace Liip\Geckoboard;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;



class Zebra extends \Liip\Geckoboard
{

//25428-3dff2edbcd9e74d24988dc1db35b2f26
//http://localhost/gecko/index.php/zebra/budgetSum/1480?apikey=c415b6693f5fdedf5a09c34b0ac5666d&widgetkey=25428-3dff2edbcd9e74d24988dc1db35b2f26
    public function budgetSum(Application $app,  Request $req, $key) {
        //https://zebra.liip.ch/en/project/1480.json

/*
        $client = $app['http.client']('https://zebra.liip.ch/');
        $request = $client->get('en/project/'.$key . '.json?token=' . $app['zebra.token']);
        $response = $request->send();
  //      file_put_contents("/tmp/foo.txt",$response->getBody());
        $values = json_decode($response->getBody(), true);
*/
        $values =  json_decode(file_get_contents("/tmp/foo.txt"), true);
        $project = $values['command']['project'];
        $budget = $project['budget'] / 1000;
        $max = $budget * 1.2;
        $used = $values['command']['total']['cost']['sum'] /1000;

        $chart = array();

        $chart["orientation"] = "horizontal";

        $item = array("label" => "Project X", "sublabel" => "Budget: " . round($budget) . " - " . round((100 * $used)/$budget) . "% used");
        $item["axis"] = array("point" => array(0, round($max * 0.16) , round($max * 0.33) , round($max * 0.5), round($max * 0.66), round($max * 0.833333), round($max)));
        $item["range"] = array(
            array("color" => "green",   "start" => 0,   "end" => $budget),
            array("color" => "amber", "start" => $budget + 1, "end" => $budget * 1.05),
            array("color" => "red", "start" => $budget * 1.05 + 1, "end" => $max));

        $item["measure"] = array(
            "current" => array("start" => 0, "end" => $used),
            "projected" => array("start" => 0, "end" => $budget * 1.1)
            );
       // $item["comparative"] = array( "point" => $budget);

        $chart['item'] = $item;
        $payload = array('data' => $chart);
        $this->push($payload, $req, $app);

        return  $app->json($payload);

    }
    public function budgetLine(Request $req, Application $app, $key)
    {
        // Create a client and provide a base URL
        $client = $app['http.client']('https://zebra.liip.ch/');

    //    de/timesheet/report.json?filters=option_selector%3D%26users%255B0%255D%3D%252A%26projects%255B0%255D%3D1480%26activities%255B0%255D%3D%252A%26start%3D2012-11-12%26end%3D2013-02-04%26token%3Dchregu%3A562753f53edb1e30eb35ae91d78e984b0&dailybudget=&budget=270000);
        $filters = $req->query->get('filters');
        $filters = urldecode($filters);

        $request = $client->get('de/timesheet/report.json?'.$filters);
        // Send the request and get the response
        $response = $request->send();
        $values = json_decode($response->getBody());
        return $app->json($values);

        $payload = array(
            "data" => $message
        );
        $this->push($payload, $req, $app);
        $payload =  $app->json($payload);
        return $payload;
    }
// http://localhost/gecko/index.php/zebra/budgetCurve?filters=option_selector%3D%26users%255B0%255D%3D%252A%26projects%255B0%255D%3D1480%26activities%255B0%255D%3D%252A%26start%3D2012-11-12%26end%3D2013-02-04%26token%3Dchregu%3A562753f53edb1e30eb35ae91d78e984b0&dailybudget=&budget=270000
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
