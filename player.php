#!/usr/bin/php5-cgi -q
<?php

require_once 'functions.php';

while(true) {
    $result = mysql_fetch_one("SELECT m.videoid AS videoid, m.title AS title FROM media m WHERE m.state LIKE 'playing' LIMIT 1");

    if (count($result) == 0) {
        echo "No video has status 'playing'";
        $result = mysql_fetch_one("SELECT m.videoid AS videoid, m.title AS title, SUM(v.vote) AS votes FROM media m, vote v WHERE m.videoid = v.videoid AND m.state LIKE 'idle' GROUP BY v.videoid ORDER BY votes DESC, added ASC LIMIT 1");
    }

    if (count($result) != 0) {
	$updateStr = "UPDATE media SET state = 'playing' WHERE videoid = \"" . $result['videoid'] . "\"";
	//echo $updateStr;
	mysql_query($updateStr) OR die(mysql_error());
        echo("Playing media " . $result['title']);
        //-geometry 1280:60
        system("vlc --no-audio --mouse-hide-timeout 0 -I dummy \"media/" . $result['videoid'] . ".flv\" vlc://quit");
        mysql_query("DELETE FROM vote WHERE videoid = \"" . $result['videoid'] . "\"");
        mysql_query("DELETE FROM media WHERE videoid = \"" . $result['videoid'] . "\"");

		mysql_query("INSERT INTO vote (videoid, ip, vote) VALUES ('" . $result['videoid'] . "', '13.37.13.37', 1)");
        mysql_query("INSERT INTO media (videoid, added) VALUES (\"" . $result['videoid'] . "\", CURRENT_TIMESTAMP)") OR die(mysql_error());
        unlink("media/" . $result['videoid'] . ".flv");
    } else {
        echo(".");
        sleep(5);
    }
}

?>
