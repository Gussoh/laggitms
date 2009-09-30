<?php
require_once 'functions.php';

if (isset($_GET['videoid'])) {
    echo 'video id: ';
    echo $_GET['videoid'];
    $videoid = mysqlFixSlashes($_GET['videoid']);
    $ip = $_SERVER['REMOTE_ADDR'];

    $result = mysql_fetch_one("SELECT * FROM vote WHERE ip = '$ip' AND videoid = '$videoid'");
    if (count($result)) {
        echo 'already voted';
    } else {
        mysql_query("INSERT INTO vote (videoid, ip, vote) VALUES ('$videoid', '$ip', 1)");
        echo '';
    }
}
?>
