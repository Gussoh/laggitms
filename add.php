<?php
require_once 'functions.php';

if (isset($_GET['url'])) {

            $count = preg_match("/v=([a-zA-Z0-9-_]+)/", $_GET['url'], &$videoid);
            if ($count) {
                $videoid = $videoid[1];
                $ip = $_SERVER['REMOTE_ADDR'];
                mysql_query("INSERT INTO vote (videoid, ip, vote) VALUES ('$videoid', '$ip', 1)");
                mysql_query("INSERT INTO media (videoid, added) VALUES (\"" . $videoid . "\", CURRENT_TIMESTAMP)") OR die(mysql_error());
                $command = "python youtubedownloader.py $videoid media/" . $videoid . ".flv >> log2.txt 2>&1 &";
                //exec($command);
                echo 'Your video is submitted, please wait for it to download...';
            } else {
                echo 'This is not a valid YouTube URL.';
            }
        }
?>
