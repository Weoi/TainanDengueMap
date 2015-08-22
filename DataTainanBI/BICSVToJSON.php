<?php
//開檔
$fh = fopen(__DIR__ . '/2.txt', 'r');
//areaCounter跟timeCounter為陣列
$areaCounter = $timeCounter = array();
$totalnum=0;
while ($line = fgetcsv($fh, 2048)) {
	//echo count($line);
	//for($line as $data)
	//照OpenData資料[0]流水號 [1]調查日期 [2]縣市 [3]鄉鎮市區 [4]村里 [5]調查地區分類 [6]調查機關或代表地點
	//[7]緯度 [8]經度 [9]調查戶數 [10]陽性戶數 [11]陽性戶數(有埃及斑蚊幼蟲) [12]調查人員種類 [13]積水容器數量(戶內) [14]積水容器數量(戶外)
	//[15]陽性容器數量(戶內) [16]陽性容器數量(戶外) [17]採獲埃及斑蚊雌蟲數(戶內) [18]採獲埃及斑蚊雌蟲數(戶內)
	//[19]採獲白線斑蚊雌蟲數(戶內) [20]採獲白線斑蚊雌蟲數(戶內) [21]孳生埃及斑蚊幼蟲數 [22]孳生白線斑蚊幼蟲數
	//[23]孳生斑蚊幼蟲數(未分類) [24]孳生斑紋蛹數 [25]布氏指數 [26]布氏級數 [27]成蟲指數(埃及斑蚊) [28]成蟲指數(白線斑蚊)
	//[29]住宅指數[30]住宅級數[31]住宅指數(有白線斑蚊)[32]住宅級數(有白線斑蚊)[33]容器指數[34]容器級數[35]幼蟲指數
	//[36]幼蟲級數[37]蛹指數
	if (!$totalnum){
        //第一列略過
        $totalnum++;
        //$currentArea = '';
        //$currentDay = array();
    } elseif (!empty($line[1])) { //若日期非空白 //照Kiang的做法維持JSON一致性
    	      $totalnum++;
    	      //echo($line[0]);
    	      //echo($totalnum);
            $dayParts = explode('/', $line[1]); //以/將原字串分成數組
            $currentDay = implode('-', array(   //串成西元日期
                $dayParts[0],   // + 1911
                str_pad(intval($dayParts[1]), 2, '0', STR_PAD_LEFT),  //由左邊補齊
                str_pad(intval($dayParts[2]), 2, '0', STR_PAD_LEFT),
            ));
            //去掉區中間的全形空白
            $line[3]=str_replace("　","",$line[3]);
            $areaKey = "{$line[3]}{$line[4]}";
            if (!isset($areaCounter[$areaKey])) 
            {
            		$areaCounter[$areaKey] = array(
                'totalnum' => 1,    //案件數 
                'totalsum' => $line[25],//BI值
                'logs' => array(),);
        		} 
        		else 
        		{
        			$areaCounter[$areaKey]['totalnum']++;            //可用來平均數量  
            	$areaCounter[$areaKey]['totalsum']+=$line[25];   
        		}
        		//處理時間的件數
        		/*
            if (!isset($timeCounter[$currentDay])) 
            {
            	$timeCounter[$currentDay] = 1;  //算Total總量
        		} 
        		else 
        		{
            	$timeCounter[$currentDay]++;
        		}
        		*/
        		//處理AreaCounter資料
        		if (!isset($areaCounter[$areaKey]['logs'][$currentDay]))
        		{
        			$areaCounter[$areaKey]['logs'][$currentDay][0]=$currentDay;
        			$areaCounter[$areaKey]['logs'][$currentDay][1]=1;           //案件量
        			$areaCounter[$areaKey]['logs'][$currentDay][2]=$line[25];   //BI總和
        		} 
        		else 
        		{
        			$areaCounter[$areaKey]['logs'][$currentDay][1]++;
        			$areaCounter[$areaKey]['logs'][$currentDay][2]+=$line[25];   
        		}
        }	
	}//end of while($line = fgetcsv($fh, 2048))

$json = array();

foreach ($areaCounter AS $areaKey => $val) {
    $json[$areaKey] = array();
    foreach ($val['logs'] AS $log) {
    	  $temp=array($log[0], intval($log[2]/$log[1]));
        $json[$areaKey][] = $temp;
    }
}
/*
$json['total'] = array();

foreach($timeCounter AS $date => $val) {
    $json['total'][] = array(
        $date,
        intval($val),
    );
}
*/
echo $totalnum;
file_put_contents(dirname(__DIR__) . '/OpenDataBI.json', json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
