#!/usr/bin/php
<?php

$price_per_hour = 70;

$arr = Array();
if (($handle = fopen("data/data.csv", "r")) === false
    || ($header = fgetcsv($handle, 1000, ",")) === false) {
    exit;
}
$i = 0;
while (($data = fgetcsv($handle, 1000, ",")) !== false) {
    $arr[] = Array();
    foreach ($data as $key => $val) {
        $arr[$i][$header[$key]] = $val;
    }

    $arr[$i]['depTime'] = DateTime::createFromFormat("H:i", $arr[$i]['departure'], new DateTimeZone('Europe/Kiev'));
    $arr[$i]['arvTime'] = DateTime::createFromFormat("H:i", $arr[$i]['arrival'], new DateTimeZone('Europe/Kiev'));
    if ($arr[$i]['depTime'] > $arr[$i]['arvTime']) {
        $arr[$i]['arvTime']->setDate(date('Y'), date('m'), date('d', strtotime('+1 day')));
    }
    $arr[$i]['time_in_route'] = ($arr[$i]['arvTime']->getTimestamp() - $arr[$i]['depTime']->getTimestamp()) / 3600;
    $arr[$i]['price'] = $arr[$i]['time_in_route'] * $price_per_hour . '₴';
    $i++;
}

function put_border($minl, $colnumns)
{
    $colnumns -= 1;
    echo '+';
    for ($i = 0; $i < $minl; $i++)
        echo '-';
    echo '+';
    for ($j = 0; $j < $colnumns; $j++) {
        for ($i = 1; $i <= $minl; $i++)
            echo '-';
        echo '+';
    }
    echo PHP_EOL;
}

echo "Напрямки руху поїздів: " . PHP_EOL;
$minl = 15;
put_border($minl + 1, 2);
printf("|%-16s - %17s|" . PHP_EOL
, 'З'
, 'До');
put_border($minl + 1, 2);
foreach ($arr as $key => $val) {
    printf("          %s - %s" . PHP_EOL
    , $val['from']
    , $val['to']);
}
put_border($minl + 1, 2);

function get_input($arr2d_to_search, $str, $key_to_find)
{
    departue:
    echo PHP_EOL . $str;
    $line = fgets(STDIN);
    $line = substr($line, 0, -1); // cuts last \n from string $line
    $found_keys = array_keys(array_column($arr2d_to_search, $key_to_find), $line);
    if (empty($found_keys)) {
        echo "Нажаль ви помилилися. Наступного разу щасти!" . PHP_EOL;
        goto departue;
    }
    return $found_keys;
}

$k1 = get_input($arr, "Введіть, будь ласка, місто відправлення: ", 'from');
$mch = Array();
foreach ($k1 as $key => $val) {
    $mch[] = $arr[$val];
}
$k2 = get_input($mch, "Введіть, будь ласка, місто прибуття: ", 'to');

$minl = 15;
$colnumns = 6;
put_border(${minl}, $colnumns);
printf("|%26s|%26s|%23s|%25s|%23s|%25s|" . PHP_EOL
, 'Номер потяга'
, 'Час відправл.'
, 'Час призн.'
, 'Час у дорозі'
, 'Кіл. віл. місць'
, 'Ціна квитка');
put_border(${minl}, $colnumns);
foreach ($k2 as $key => $val) {
    
    printf("|%${minl}s|%${minl}s|%${minl}s|%${minl}.2f|%${minl}s|%${minl}.2f|" . PHP_EOL
    , $mch[$val]['train_no']
    , date("m/d/ H:i", $mch[$val]['depTime']->getTimestamp())
    , date("m/d/ H:i", $mch[$val]['arvTime']->getTimestamp())
    , $mch[$val]['time_in_route']
    , $mch[$val]['free_seats']
    , $mch[$val]['price']);
}
put_border(${minl}, $colnumns);
$train = get_input($mch, "Оберіть номер потяга із доступною датою, якщо ви згодні з ціною на квиток: ", 'train_no'); // зчитуємо номер потягу
printf("До сплати - %.2f, для підтвердження введіть номер своєї кредитки: ", $mch[$train[0]]['price']);
$line = fgets(STDIN);
echo "Ви успішно замовили квиток!" . PHP_EOL;
