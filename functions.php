<?php

function getSettings() {
    $settingsFile = file('config');
    $settings = array();
    foreach($settingsFile as $settingsRow) {
        if (!strlen(trim($settingsRow)) || $settingsRow{0} == '#') {
            continue;
        }
        $arr = explode('=', $settingsRow, 2);
        if (count($arr) == 2) {
            $settings[$arr[0]] = trim($arr[1]);
        }
    }

    return $settings;
}

function mysqlFixSlashes($input) {
    if(get_magic_quotes_gpc() || get_magic_quotes_runtime()) {
        $input = stripslashes($input);
    }
    return mysql_real_escape_string($input);
}

function mysql_fetch_all($query, $debug = false) {
    if($debug)
        echo $query . '<br />';
    $r = mysql_query($query) OR die(mysql_error());
    $result = array();
    if(mysql_num_rows($r))
        while($row = mysql_fetch_array($r))
            $result[] = $row;

    return $result;
}

function mysql_fetch_one($query, $debug = false) {
    if($debug)
        echo $query . '<br />';
    $r = mysql_query($query) OR die(mysql_error());
    if(mysql_num_rows($r) == 1)
        return mysql_fetch_array($r);
    elseif(mysql_num_rows($r) > 1)
        die('More than one result returned where only one was expected from MYSQL database on query: ' . $query);
    else
        return array();
}

function videoIdExists($videoid) {
    return count(mysql_fetch_one("SELECT * FROM media WHERE videoid = '$videoid'"));
}

$settings = getSettings();
mysql_connect($settings['db.host'], $settings['db.user'], $settings['db.pass']) or die(mysql_error());
mysql_select_db($settings['db.db']) or die(mysql_error());
?>
