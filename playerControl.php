<?php
require_once 'functions.php';


$video = mysql_fetch_one("SELECT length, thumbnail, videoid, title, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(changed) AS elapsed FROM media WHERE state LIKE 'playing' GROUP BY videoid ORDER BY added ASC LIMIT 1");

$config = getSettings();
$mediaFolder = $config['web.mediafolder'];

if (count($video)) {
echo '
        if ($f().getState() != 3 || $f().getClip().completeUrl.match("' . $video['videoid'] . '.flv$") != "' . $video['videoid'] . '.flv") {
            $f().setClip("' . $mediaFolder . $video['videoid'] . '.flv");
            $f().play();
        }
        if ($f().getState() != 2 && $f().isPlaying() && Math.abs($f().getTime() - ' . $video['elapsed'] . ') > 1 ) {
            $f().seek(' . $video['elapsed'] . ');
        }
    ';
} else {
    echo '
        $f().stop();
    ';
}

?>
