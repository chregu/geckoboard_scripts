<?php

namespace Liip\Geckoboard;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use ZendGData;


class Drive extends \Liip\Geckoboard
{

    /*
    http://localhost/gecko/index.php/drive/geckometer/0AmC0gxQw5-8TdFZIcHdReHhUb2VaOTBWS25uMDBYNVE?apikey=c415b6693f5fdedf5a09c34b0ac5666d&widgetkey=25428-ae40313bf071a527355ef1a489c4315c

    | min   | 1   |
    | max   | 5   |
    | value | 1.1 |

    */
    public function geckometer(Application $app,  Request $req, $key, $topleft = null) {

        $service = ZendGData\Spreadsheets::AUTH_SERVICE_NAME;
        $client =  ZendGData\ClientLogin::getHttpClient($app['google.user'], $app['google.pass'], $service);
        $service = new ZendGdata\Spreadsheets($client);

        $query = new ZendGdata\Spreadsheets\CellQuery();
        $query->setSpreadsheetKey($key);
        $query->setWorksheetId('od6');
        $minrow = 1;
        $mincol = 1;
        if ($topleft) {
             list($mincol, $minrow) = explode(",", $topleft);
        }
        $query->setMinRow($minrow);
        $query->setMaxRow($minrow + 2);
        $query->setMinCol($mincol);
        $query->setMaxCol($mincol + 1);

        $cellFeed = $service->getCellFeed($query);
        $chart = array();

        $chart['min'] = array("text" => $cellFeed[0]->getCell()->getText(), "value" => $cellFeed[1]->getCell()->getText());
        $chart['max'] = array("text" => $cellFeed[2]->getCell()->getText(), "value" => $cellFeed[3]->getCell()->getText());
        $chart['item'] = $cellFeed[5]->getCell()->getText();

        $payload = array('data' => $chart);
        $this->push($payload, $req, $app);

        return  $app->json($payload);

    }
}
