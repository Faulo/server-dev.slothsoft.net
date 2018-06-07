<?php
declare(strict_types = 1);
namespace Slothsoft\Dev\Bahn;

use Slothsoft\Dev\DOMDocumentSmart;
use DOMElement;
use Exception;

class Request
{

    public static $_get = [];

    public static $_post = [];

    public static $_cookie = [];

    public static $_request = [];

    public static $stations = [
        'DD' => [
            self::REQ_STATION_NAME => 'Dresden Hbf',
            self::REQ_STATION_ID => 'A=1@O=Dresden Hbf@X=13732038@Y=51040562@U=80@L=008010085@B=1@p=1294247940@'
        ],
        'IGB' => [
            self::REQ_STATION_NAME => 'St. Ingbert Bahnhof',
            self::REQ_STATION_ID => 'A=1@O=St. Ingbert Bahnhof@X=7111707@Y=49274703@U=80@L=000402243@B=1@p=1294247940@'
        ],
        'DIE' => [
            self::REQ_STATION_NAME => 'Dieburg',
            self::REQ_STATION_ID => 'A=1@O==Dieburg'
        ],
        'DÜ' => [
            self::REQ_STATION_NAME => 'Düsseldorf',
            self::REQ_STATION_ID => 'A=1@O=Düsseldorf Hbf@X=6794316@Y=51219960@U=80@L=008000085@B=1@p=1364975788@'
        ],
        'B' => [
            self::REQ_STATION_NAME => 'Bonn',
            self::REQ_STATION_ID => 'A=1@O=Bonn Hbf@X=7097136@Y=50732007@U=80@L=008000044@B=1@p=1369161210@'
        ],
        'K' => [
            self::REQ_STATION_NAME => 'Köln',
            self::REQ_STATION_ID => 'A=1@O=Köln Hbf@X=6958729@Y=50943029@U=80@L=008000207@B=1@p=1320260835@'
        ],
        'F' => [
            self::REQ_STATION_NAME => 'Frankfurt(Main)Hbf',
            self::REQ_STATION_ID => 'A=1@O=Frankfurt(Main)Hbf@X=8663785@Y=50107149@U=80@L=008000105@B=1@p=1392372819@'
        ],
        'FF' => [
            self::REQ_STATION_NAME => 'Frankfurt(M) Flughafen Fernbf',
            self::REQ_STATION_ID => 'A=1@O=Frankfurt(M) Flughafen Fernbf@X=8570180@Y=50053169@U=80@L=008070003@B=1@p=1404241273@'
        ],
        'L' => [
            self::REQ_STATION_NAME => 'Leipzig Hbf',
            self::REQ_STATION_ID => 'A=1@O=Leipzig Hbf@L=008010205@X=12383333@Y=51346546@'
        ]
    
    ];

    const REQ_HOST = 'http://reiseauskunft.bahn.de/';

    const REQ_URL = 'bin/query.exe/dn';

    // http://reiseauskunft.bahn.de/bin/query.exe/dn?ld=96244&seqnr=3&ident=l3.08963244.1294523437&rt=1&OK#focus
    const REQ_START_STATION = 'REQ0JourneyStopsS0';

    const REQ_END_STATION = 'REQ0JourneyStopsZ0';

    const REQ_STATION_NAME = 'G';

    const REQ_STATION_ID = 'ID';

    const REQ_START_DATE = 'REQ0JourneyDate';

    const REQ_START_TIME = 'REQ0JourneyTime';

    const REQ_END_DATE = 'REQ1JourneyDate';

    const REQ_END_TIME = 'REQ1JourneyTime';

    const REQ_SEARCH_DEPARTURE = 'REQ0HafasSearchForw';

    const REQ_SEARCH_AMOUNT = 'seqnr';

    const REQ_MODE_UNSET = 0;

    const REQ_MODE_FORWARD = 1;

    const REQ_MODE_BACKWARD = 2;

    const TABLE_ROW_START = ' firstrow';

    const TABLE_ROW_END = ' last';

    public static function construct()
    {
        self::$_post = json_decode(file_get_contents('_POST.json'), true);
        self::$_get = json_decode(file_get_contents('_GET.json'), true);
        self::$_cookie = json_decode(file_get_contents('_COOKIE.json'), true);
        self::$_request = json_decode(file_get_contents('_REQUEST.json'), true);
    }

    private static function htmlDate2bahnDate($date)
    {
        $date = explode('-', $date);
        return 'Mo, ' . implode('.', array_reverse($date));
    }

    private static function bahnDate2htmlDate($date)
    {
        $date = explode(',', $date);
        $date = trim($date[1]);
        $date = explode('.', $date);
        return implode('-', array_reverse($date));
    }

    private static function htmlDatetime2Timestamp($date, $time)
    {
        $date = explode('-', $date);
        $time = explode(':', $time);
        @$datetime = mktime((int) $time[0], (int) $time[1], 0, (int) $date[1], (int) $date[2], (int) $date[0]);
        // if ($datetime < time()) die('wtf.' . var_dump($date) . var_dump($time));
        return $datetime;
    }

    public static function rateData(array &$NodeArray, $there, Request $Req)
    {
        foreach ($NodeArray as $key => $arr) {
            if (! is_array($arr))
                continue;
            $keys = explode('/', $key);
            if ($keys[0] === 'journeys' and $keys[1] === $there) {
                $rootkey = $keys[0] . '/' . $keys[1] . '/rating/';
                $key = $keys[2];
                foreach ($arr as $i => $val) {
                    $ret = null;
                    switch ($key) {
                        case 'cheapPrice':
                        case 'normalPrice':
                            if ($val === '-') {
                                $ret = 6;
                            } else {
                                $ret = ((float) $val) / 20;
                            }
                            break;
                        case 'duration':
                            $ret = ((int) $val) - 6;
                            break;
                        case 'changes':
                            $ret = $val;
                            break;
                        case 'products':
                            $prods = explode(',', $val);
                            $ret = 1;
                            foreach ($prods as $prod) {
                                $prod = trim($prod);
                                switch ($prod) {
                                    case 'IC':
                                    case 'EC':
                                        $ret += 0.5;
                                        break;
                                    case 'IRE':
                                    case 'RE':
                                    case 'TGV':
                                        $ret += 0.75;
                                        break;
                                    case 'S':
                                    case 'RB':
                                        $ret += 1;
                                        break;
                                    case 'BUS':
                                    case 'CNL':
                                        $ret += 3;
                                        break;
                                }
                            }
                            break;
                        case 'arrival':
                        case 'departure':
                            if (isset($keys[4]) and $keys[4] === 'datetime') {
                                // var_dump($NodeArray);
                                // die();
                                $ret = sqrt((max($val, $Req->datetime) - min($val, $Req->datetime))) / 60;
                                // echo "$val\n$Req->datetime\n";
                                // die((string) $ret . "|" . $rootkey . $key);
                            }
                            break;
                    }
                    if ($ret !== null) {
                        if (! isset($NodeArray[$rootkey . $key])) {
                            $NodeArray[$rootkey . $key] = [];
                        }
                        $NodeArray[$rootkey . $key][] = min(6, max(1, (int) $ret));
                        // die($rootkey.$key);
                    }
                }
            }
        }
    }

    public static function generateStations(array &$NodeArray)
    {
        $NodeArray['stations/id'] = [];
        $NodeArray['stations/name'] = [];
        $NodeArray['form/station/start'] = [];
        $NodeArray['form/station/end'] = [];
        foreach (self::$stations as $id => &$arr) {
            $NodeArray['stations/id'][] = $id;
            $NodeArray['stations/name'][] = $arr[self::REQ_STATION_NAME];
            $NodeArray['form/station/start'][] = (isset($_REQUEST['form/station/start']) and $_REQUEST['form/station/start'] === $id);
            $NodeArray['form/station/end'][] = (isset($_REQUEST['form/station/end']) and $_REQUEST['form/station/end'] === $id);
        }
        if (! in_array(true, $NodeArray['form/station/start'])) {
            $NodeArray['form/station/start'][0] = true;
        }
        if (! in_array(true, $NodeArray['form/station/end'])) {
            $NodeArray['form/station/end'][1] = true;
        }
    }

    public static function generateRequest(array &$NodeArray, $req)
    {
        $mode = array(
            self::REQ_MODE_FORWARD,
            self::REQ_MODE_BACKWARD
        );
        $there = array(
            'there',
            'back'
        );
        $station_start = array(
            $req['form/station/start'],
            $req['form/station/end']
        );
        $station_end = array(
            $req['form/station/end'],
            $req['form/station/start']
        );
        $start_date = array(
            $req['form/start/date'],
            $req['form/end/date']
        );
        $start_time = array(
            $req['form/start/time'],
            $req['form/end/time']
        );
        
        $NodeArray['show/request'] = '1';
        
        for ($i = 0; $i < count($mode); $i ++) {
            
            $Req = new Request($station_start[$i], $station_end[$i], $start_date[$i], $start_time[$i], $mode[$i]);
            
            $Table = $Req->downloadTable();
            $NodeArray['journey/' . $there[$i] . '/html'] = $Table;
            
            $Journeys = self::getRows($Table);
            $rootkey = 'journeys/' . $there[$i] . '/';
            foreach ($Journeys as $Journey) {
                if (! isset($Journey[0]['cheapPrice'])) {
                    $Journey[0]['cheapPrice'] = '-';
                }
                for ($j = 0; $j < count($Journey[0]); $j ++) {
                    list ($key, $val) = each($Journey[0]);
                    switch ($key) {
                        case 'cheapPrice':
                        case 'normalPrice':
                            
                            if ($val = (float) str_replace(',', '.', $val)) {
                                $Journey[0]['data/' . $key] = $val;
                                $val = sprintf('%.2f', $val) . ' €';
                            } else {
                                $Journey[0]['data/' . $key] = - 1;
                                $val = '-';
                            }
                            break;
                        case 'date':
                            $Journey[0]['data/date'] = self::bahnDate2htmlDate($val);
                            $Journey[1]['data/date'] = self::bahnDate2htmlDate($Journey[1][$key]);
                            // echo self::htmlDatetime2Timestamp(self::bahnDate2htmlDate($val), '0:0')."\n\n";
                            break;
                        case 'changes':
                            $Journey[0]['data/changes'] = $val;
                            break;
                        case 'duration':
                            $time = explode(':', $val);
                            $Journey[0]['data/duration'] = $time[0] * 3600 + $time[1] * 60;
                            break;
                        case 'time':
                            $Journey[0]['data/datetime'] = self::htmlDatetime2Timestamp($Journey[0]['data/date'], $val);
                            $Journey[1]['data/datetime'] = self::htmlDatetime2Timestamp($Journey[1]['data/date'], $Journey[1][$key]);
                            break;
                    }
                    
                    if (isset($Journey[1][$key])) {
                        $key1 = $rootkey . 'departure/' . $key;
                        $key2 = $rootkey . 'arrival/' . $key;
                        if (! isset($NodeArray[$key1]))
                            $NodeArray[$key1] = [];
                        if (! isset($NodeArray[$key2]))
                            $NodeArray[$key2] = [];
                        $NodeArray[$key1][] = $val;
                        $NodeArray[$key2][] = $Journey[1][$key];
                    } else {
                        $key = $rootkey . $key;
                        if (! isset($NodeArray[$key]))
                            $NodeArray[$key] = [];
                        $NodeArray[$key][] = $val;
                    }
                }
            }
            // var_dump($NodeArray);
            self::rateData($NodeArray, $there[$i], $Req);
        }
        
        // echo "<pre>"; var_dump($NodeArray); die("</pre>");
    }

    public static function getRows(DOMElement $Table)
    {
        $Rows = $Table->getElementsByTagName('tr');
        $ret = [];
        $i = 0;
        foreach ($Rows as $Row) {
            if ($Row->parentNode !== $Table)
                continue;
            $class = $Row->getAttribute('class');
            
            if ($class === self::TABLE_ROW_START or $class === self::TABLE_ROW_END) {
                if ($class === self::TABLE_ROW_START) {
                    $ret[$i] = [];
                    $ret[$i][] = self::getCells($Row);
                } else {
                    $ret[$i][] = self::getCells($Row);
                    $i ++;
                }
            }
        }
        return $ret;
    }

    public static function getCells(DOMElement $Row)
    {
        $Cells = $Row->getElementsByTagName('td');
        $ret = [];
        $Links = $Row->getElementsByTagName('a');
        foreach ($Links as $Link) {
            if (! strlen($txt = trim(DOMDocumentSmart::getTextFromNode($Link)))) {
                $txt = $Link->getAttribute('title');
            }
            $href = $Link->getAttribute('href');
            $Link = $Link->ownerDocument->createElement('a');
            $Link->appendChild($Link->ownerDocument->createTextNode($txt));
            $Link->setAttribute('href', $href);
            $ret['details'] = $Link;
            break;
        }
        // var_dump($ret['links']);
        foreach ($Cells as $Cell) {
            if ($Cell->parentNode !== $Row)
                continue;
            $class = trim($Cell->getAttribute('class'));
            $class = explode(' ', $class);
            $class = $class[0];
            $ret[$class] = trim(DOMDocumentSmart::getTextFromNode($Cell));
            
            switch ($class) {
                case 'farePep':
                    if (preg_match('/(\d+,\d{2})/', $ret[$class], $match)) {
                        $ret['cheapPrice'] = $match[1];
                    } else {
                        $ret['cheapPrice'] = '';
                    }
                    break;
                case 'fareStd':
                    if (preg_match('/(\d+,\d{2})/', $ret[$class], $match)) {
                        $ret['normalPrice'] = $match[1];
                    } else {
                        $ret['normalPrice'] = '';
                    }
                    break;
                case 'products':
                    $tmp = explode('\n', $ret[$class]);
                    $ret[$class] = trim(end($tmp));
                    break;
            }
        }
        return $ret;
    }

    public $datetime = 0;

    private $startStation;

    private $endStation;

    private $thereDate;

    private $backDate;

    private $reqMode = 0;

    public function __construct($startStation, $endStation, $date, $time, $reqMode = self::REQ_MODE_UNSET)
    {
        $this->startStation = $startStation;
        $this->endStation = $endStation;
        $this->datetime = self::htmlDatetime2Timestamp($date, $time);
        $this->thereDate = self::htmlDate2bahnDate($date);
        $this->thereTime = $time;
        // $this->backDate = self::htmlDate2bahnDate($backDate);
        // $this->backTime = $backTime;
        $this->reqMode = $reqMode;
        
        $this->prepareRequest();
        
        $url = $this->calcURL();
        parent::__construct($url);
    }

    public function prepareRequest()
    {
        $journey = array(
            self::REQ_START_STATION => self::$stations[$this->startStation],
            self::REQ_END_STATION => self::$stations[$this->endStation]
        );
        foreach ($journey as $key1 => $arr) {
            foreach ($arr as $key2 => $val) {
                self::$_request[$key1 . $key2] = $val;
            }
        }
        
        self::$_request[self::REQ_START_DATE] = $this->thereDate;
        self::$_request[self::REQ_START_TIME] = $this->thereTime;
        
        self::$_request[self::REQ_SEARCH_DEPARTURE] = (int) ($this->reqMode === self::REQ_MODE_FORWARD);
        
        // self::$_request[self::REQ_END_DATE] = $this->backDate;
        // die($this->backTime);
        // self::$_request[self::REQ_END_TIME] = $this->backTime;
        // self::$_request[self::REQ_SEARCH_AMOUNT] = 10;
        
        // self::$_request['REQ0HafasScrollDir'] = 2;
    }

    public function calcURL()
    {
        $url = self::REQ_HOST . self::REQ_URL;
        $query = [];
        foreach (self::$_request as $key => $val) {
            $query[] = $key . '=' . rawurlencode($val);
        }
        
        $url .= '?' . implode('&', $query);
        
        return $url;
    }

    public function downloadTable()
    {
        switch ($this->reqMode) {
            case self::REQ_MODE_BACKWARD:
                $linkClass = 'arrowlink arrowlinktop';
                break;
            case self::REQ_MODE_FORWARD:
                $linkClass = 'arrowlink arrowlinkbottom';
                break;
            default:
                $linkClass = false;
                break;
        }
        do {
            $continue = false;
            $Doc = $this->downloadToDocument();
            $Tables = $Doc->getElementsByTagName('table');
            $Table = null;
            foreach ($Tables as $Table) {
                if ($Table->getAttribute('class') === 'result') {
                    break;
                }
            }
            if (! $Table) {
                throw new Exception('WTF NO RESULT TABLE');
            }
            if ($linkClass) {
                $Links = $Table->getElementsByTagName('a');
                foreach ($Links as $Link) {
                    if ($Link->getAttribute('class') === $linkClass) {
                        $url = self::REQ_HOST . $Link->getAttribute('href');
                        parent::__construct($url);
                        $continue = true;
                    }
                }
            }
        } while ($continue);
        return $Table;
    }
}
Request::construct();