<?php

function getNumberArray($start, $numbers)
{
    $speed=floatval($_GET['speed']);
    //echo $speed;
    $up = [];
    $down = [];
    $now = [];
    $end = $start + ($start * $speed / 24);
    for ($i = 0; $i < $numbers; $i++) {

        if ($i === 0) {
            $now[] = $start;
        } elseif ($i === $numbers - 1) {
            $now[] = $end;
        } else {
            $rand = rand(1, 10) * $_GET['jingdu'];
            if (get_rand([$_GET['rate2'], $_GET['rate1']]) === 1) {
                $now[] = $now[$i - 1] + $rand;
            } else {
                $now[] = $now[$i - 1] - $rand;
            }
        }
    }
    return $now;
}

function get_rand($proArr)
{
    $result = '';
    //概率数组的总概率精度
    $proSum = array_sum($proArr);
    //概率数组循环
    foreach ($proArr as $key => $proCur) {
        $randNum = mt_rand(1, $proSum);
        if ($randNum <= $proCur) {
            $result = $key;
            break;
        } else {
            $proSum -= $proCur;
        }
    }
    unset ($proArr);
    return $result;
}

$rand_start = $_GET['start'];

$numbers = getNumberArray($rand_start, 10);

$obj = [];
$start = $rand_start;

foreach ($numbers as $number) {

//    $open = count($obj) === 0 ? $start : $obj[count($obj) - 1]['close'];
//    $open = sprintf("%.4f", $open);

    $arr = ['open' => count($obj) === 0 ? $start : $obj[count($obj) - 1]['close']];

    $arr['close'] = $number;
    if (count($obj) === 0) {
        $arr['close'] = $number + ((rand(-100, 100)) * $_GET['fudu']);
    }


    $val = max(array_values($arr));
    $minVal = min(array_values($arr));

    $arr['high'] = $val + ((rand(5, 100)) * $_GET['fudu']);
    $arr['low'] = $minVal - ((rand(5, 100)) * $_GET['fudu']);

    array_walk($arr, function (&$val) {
        $val = sprintf('%.6f', $val);
    });
    $obj[] = $arr;

}
//var_dump($obj);
$rsp = [];
$i = 0;
$timess=$_GET['times'];
if($timess<10){
    $timess="0".$timess;
}
foreach ($obj as $v) {
    //strtotime("{$i} minutes")
    $rsp[] = [date('Y-m-d H:i:s', strtotime($_GET['dates']." ".$timess.":00:00")+$i*60), $v['open'], $v['high'], $v['low'], $v['close'], rand(0, 100)];
    //echo strtotime(date('Y-m-d H:i:s', strtotime($_GET['dates']." ".$timess.":00:00")+$i*60)).PHP_EOL;
    $i++;
}
//echo $_GET['dates']." ".$timess.":00:00";
/*
//重新排序
foreach($rsp as $k=>$v){
    $key_arrays[]=$v[0];
}
array_multisort($key_arrays,SORT_ASC,SORT_NUMERIC,$rsp);
*/
///排序 结束

echo json_encode(['data' => $rsp, 'next' => date('Y-m-d', strtotime('+1 days', strtotime($_GET['dates'])))]);
die;
