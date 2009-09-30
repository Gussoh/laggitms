<?php
require_once 'functions.php';
$videos = mysql_fetch_all("SELECT m.length AS length, m.thumbnail AS thumbnail, m.videoid AS videoid, m.title AS title, m.size as size, SUM(v.vote) AS votes, m.state as status FROM media m, vote v WHERE m.videoid = v.videoid AND m.state NOT LIKE 'playing' GROUP BY v.videoid ORDER BY votes DESC, added ASC");
$playingvideos = mysql_fetch_all("SELECT length, thumbnail, videoid, title, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(changed) AS elapsed FROM media WHERE state LIKE 'playing' GROUP BY videoid ORDER BY added ASC");

echo '<table>';
echo '<tr><td colspan=4><h2>Playing</h2></td></tr>';
$elapsed = 0;
foreach ($playingvideos as $video) {
    $progressRatio = max(min($video['elapsed'] / $video['length'], 1.0), 0.0);
    $progress = $progressRatio * 100;
    $progressBarWidth = 100;
    $progress = round($progress);
    $elapsed = $video['elapsed'];

    echo '<tr>';
    echo '   <td><img src="' . $video['thumbnail'] . '" /></td>';
    echo '   <td><a style="text-decoration: underline" href="http://youtube.com?v=' . $video['videoid'] . '" target="_blank">' . $video['title'] . '</a></td>';
    echo '   <td>' . floor($video['length'] / 60) . ':' . sprintf("%02d", $video['length'] % 60) . '</td>';
    echo '   <td align="center"></td>';
    echo '   <td>playing<br/>
<img style="float:left" src="progress-start.png" height="25"/><div style="float:left;background-image:url(progress-buffered.png);width:' . $progressBarWidth . 'px;height:25px"><div style="float:left;position:relative;left:' . round(($progressBarWidth * $progressRatio - 8)) . 'px; top: 4px"><img src="progress-knob.png" width="16" height="16"/></div><div style="height:25px;width:' . $progress . '%;background-image: url(progress-played.png)"></div></div><img src="progress-end.png" height="25"/>
</td>';
    echo '</tr>';
    echo "\n";
}

echo '<tr><td colspan=4><br /><h2>Enqueued</h2></td></tr>';
echo '<tr><th>Some nice pictures</th><th>Title</th><th>Length</th><th>Votes</th><th>Status</th></tr>';
foreach ($videos as $video) {

    if(!strlen($video['title'])) {
        $video['title'] = 'Waiting for data to be fetched...';
        $video['thumbnail'] = 'loading.png';
    }
    echo '<tr>';
    echo '   <td><img width="120" height="90" src="' . $video['thumbnail'] . '" /></td>';
    echo '   <td width="350">' . $video['title'] . '</td>';
    echo '   <td>' . floor($video['length'] / 60) . ':' . sprintf("%02d", $video['length'] % 60) . '</td>';
    echo '   <td align="center">' . $video['votes'] . ' <a class="green" href="javascript: vote(\'' . $video['videoid'] . '\')"  onclick="">&lt;3</a></td>';

    $file = 'media/' . $video['videoid'] . '.flv';
    if ($video['status'] == 'downloading' && file_exists($file) && intval($video['size']) > 0) {
        $progressRatio = max(min(filesize($file) / $video['size'], 1.0), 0.0);
        $progressBarWidth = 100;
        $progress = $progressRatio * 100;
        $progress = round($progress);
        echo '<td>downloading<br/>
<img style="float:left" src="progress-start.png" height="25"/><div style="float:left;background-image:url(progress-empty.png);width:' . $progressBarWidth . 'px;height:25px"><div style="height:25px;width:' . $progress . '%;background-image: url(progress-buffered.png)"></div></div><img src="progress-end.png" height="25"/>
</td>';
    } else {
        echo '   <td align="center">' . $video['status'] . '</td>';
    }
    echo '</tr>';
}
echo '</table>';



?>
