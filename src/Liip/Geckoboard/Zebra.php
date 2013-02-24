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


        $client = $app['http.client']('https://zebra.liip.ch/');
        $request = $client->get('en/project/'.$key . '.json?token=' . $app['zebra.token']);
        $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYHOST, false);
        $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);

        $response = $request->send();
  //      file_put_contents("/tmp/foo.txt",$response->getBody());
        $values = json_decode($response->getBody(), true);

//        $values =  json_decode(file_get_contents("/tmp/foo.txt"), true);
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

        $item["comparative"] = array( "point" => $used + (($values['command']['zero']['total']['totaltime'] * 80) /1000));

        $chart['item'] = $item;
        $payload = array('data' => $chart);
        $this->push($payload, $req, $app);

        return  $app->json($payload);

    }

    public function highcharts(Request $req, Application $app)
    {
        $chart = array();
        $chart = '
        {"chart":{
                    "renderTo":"container",
                    "type":"line",
                    "marginRight":130,
                    "marginBottom":25,
                    "plotBackgroundColor": "rgba(35,37,38,0)",
                    "backgroundColor": "rgba(35,37,38,100)",
                    "borderColor": "rgba(35,37,38,100)",
                    "lineColor": "rgba(35,37,38,100)",
                    "plotBorderColor": "rgba(35,37,38,100)",
                    "plotBorderWidth": null,
                    "plotShadow": false,
                },
         "title":{
                    "text":"Monthly Average Temperature",
                    "x":-20
                },
         "subtitle":{
                    "text":"Source: WorldClimate.com",
                    "x":-20
                },
          "tooltip": {"formatter": function() {
            return  this.series.name + "<br/>"+ this.x +": "+ this.y +"°C";

                  }
         },

         "xAxis":{
                    "categories":["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"]
                },
         "yAxis":{
                    "title":{"text":"Temperature (°C)"},
                    "plotLines":[{"value":0,"width":1,"color":"#808080"}]
                  },
         "legend":{
                    "layout":"vertical",
                    "align":"right",
                    "verticalAlign":"top",
                    "x":-10,
                    "y":100,
                    "borderWidth":0
                   },
          "series":[
                    {"name":"Tokyo","data":[7,6.9,9.5,14.5,18.2,21.5,25.2,26.5,23.3,18.3,13.9,9.6]},
                    {"name":"New York","data":[-0.2,0.8,5.7,11.3,17,22,24.8,24.1,20.1,14.1,8.6,2.5]},
                    {"name":"Berlin","data":[-0.9,0.6,3.5,8.4,13.5,17,18.6,17.9,14.3,9,3.9,1]},
                    {"name":"London","data":[3.9,4.2,5.7,8.5,11.9,15.2,17,16.6,14.2,10.3,6.6,4.8]}
                    ]
           }


          ';

        return  $chart;

    }


    /**
     * Not finished
     */
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
}
