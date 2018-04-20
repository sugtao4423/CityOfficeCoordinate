<?php

$baseUrl = 'http://www.gsi.go.jp/KOKUJYOHO/CENTER/kendata/';
$pref = array(
    // 北海道
    'hokkaido',
    // 東北
    'aomori', 'iwate', 'miyagi', 'akita', 'yamagata', 'fukushima',
    // 関東
    'ibaraki', 'tochigi', 'gunma', 'saitama', 'chiba',
    'tokyo', 'kanagawa', 'yamanashi', 'nagano',
    // 北陸
    'niigata', 'toyama', 'ishikawa', 'fukui',
    // 中部
    'gifu', 'shizuoka', 'aichi', 'mie',
    // 近畿
    'shiga', 'kyoto', 'osaka', 'hyogo', 'nara', 'wakayama',
    // 中国
    'tottori', 'shimane', 'okayama', 'hiroshima', 'yamaguchi',
    // 四国
    'tokushima', 'kagawa', 'ehime', 'kochi',
    // 九州
    'fukuoka', 'saga', 'nagasaki', 'kumamoto',
    'oita', 'miyazaki', 'kagoshima',
    // 沖縄
    'okinawa'
);

$result = array();
foreach($pref as $p){
    $html = file_get_contents("${baseUrl}/${p}_heso.htm");
    $coordinates = getCoordinatesFromTable($html);
    $result = array_merge($result, $coordinates);
}
$json = json_encode($result, JSON_UNESCAPED_UNICODE);
echo $json;


function getCoordinatesFromTable($html){
    $result = array();
    if(preg_match_all('/(表示されます。<\/p>|<br>)(.+?)<table.+?<\/table>/si', $html, $cities) > 0){
        $cityTables = $cities[0];
        $cityNames = $cities[2];
        $cityNames = array_map('strip_tags', $cityNames);
        $cityNames = array_map('trim', $cityNames);
        for($i = 0; $i < count($cityTables); $i++){
            if(preg_match_all('|<TD.+?</TD>|s', $cityTables[$i], $tds) > 0){
                $tds = $tds[0];
                $tds = array_map('strip_tags', $tds);
                $tds = array_map('trim', $tds);

                $name = $cityNames[$i];
                $lon = $tds[array_search('経　度', $tds) + 1];
                $lat = $tds[array_search('緯　度', $tds) + 1];
                $coord = get10DecimalCoordinate($lon, $lat);
                $lon = $coord['lon'];
                $lat = $coord['lat'];

                $result = array_merge($result,
                    array($name => array('lon' => $lon, 'lat' => $lat)));
            }
        }
    }
    return $result;
}

function get10DecimalCoordinate($lon, $lat){
    $lonArr = preg_split('/°|′|″/', $lon);
    $latArr = preg_split('/°|′|″/', $lat);
    $lonArr = array_filter($lonArr, 'strlen');
    $latArr = array_filter($latArr, 'strlen');

    $lon = $lonArr[0] + ($lonArr[1] / 60) + ($lonArr[2] / 60 / 60);
    $lat = $latArr[0] + ($latArr[1] / 60) + ($latArr[2] / 60 / 60);

    $lon = round($lon, 6);
    $lat = round($lat, 6);
    return array('lon' => $lon, 'lat' => $lat);
}
