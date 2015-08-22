<?php
//開檔
$fh = fopen(__DIR__ . '/12015denguefeverendenmiccases.txt', 'r');
//areaCounter跟timeCounter為陣列
$areaCounter = $timeCounter = array();
$totalnum=0;
while ($line = fgetcsv($fh, 2048)) {
	//echo count($line);
	//for($line as $data)
	//照OpenData資料[0]日期 [1]台南市 [2]區 [3]里
	if ($line[0] === '日期') {
        //第一列略過
        //$currentArea = '';
        //$currentDay = array();
    } elseif (!empty($line[0])) { //若日期非空白 //照Kiang的做法維持JSON一致性
    	      $totalnum++;
            $dayParts = explode('/', $line[0]); //以/將原字串分成數組
            $currentDay = implode('-', array(   //串成西元日期
                $dayParts[0],   // + 1911
                str_pad(intval($dayParts[1]), 2, '0', STR_PAD_LEFT),  //由左邊補齊
                str_pad(intval($dayParts[2]), 2, '0', STR_PAD_LEFT),
            ));
            $areaKey = "{$line[2]}{$line[3]}";
            if (!isset($areaCounter[$areaKey])) 
            {
            		$areaCounter[$areaKey] = array(
                'total' => 1,
                'logs' => array(),);
        		} 
        		else 
        		{
            	$areaCounter[$areaKey]['total']++;   //這沒有用到
        		}
        		//處理時間的件數
            if (!isset($timeCounter[$currentDay])) 
            {
            	$timeCounter[$currentDay] = 1;  //算Total總量
        		} 
        		else 
        		{
            	$timeCounter[$currentDay]++;
        		}
        		//處理AreaCounter資料
        		if (!isset($areaCounter[$areaKey]['logs'][$currentDay]))
        		{
        			$areaCounter[$areaKey]['logs'][$currentDay][0]=$currentDay;
        			$areaCounter[$areaKey]['logs'][$currentDay][1]=1;
        		} 
        		else $areaCounter[$areaKey]['logs'][$currentDay][1]++;
        }	
	}//end of while($line = fgetcsv($fh, 2048))
$json = array();

foreach ($areaCounter AS $areaKey => $val) {
    $json[$areaKey] = array();
    foreach ($val['logs'] AS $log) {
        $log[1] = intval($log[1]);
        $json[$areaKey][] = $log;
    }
}

$json['total'] = array();

foreach($timeCounter AS $date => $val) {
    $json['total'][] = array(
        $date,
        intval($val),
    );
}
echo $totalnum;
file_put_contents(dirname(__DIR__) . '/OpenDataCase.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
