<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <title>LaggIT Media Service</title>
        <script src="default.js" type="text/javascript" ></script>
        <script type="text/javascript" src="flowplayer-3.1.4.min.js"></script>

        <style>
            body {
                background-color: black;
                color: #aaa;
                font-family: verdana, arial, helvetica, sans-serif;
            }
            a {
                color: #ddd;
                text-decoration: none;
            }

            a.green {
                color: green;
            }
            a.red {
                color: red;
            }
            input {
                border: 1px solid red;
                color: white;
                background-color: black;
                margin: 5px;
                padding: 5px;
            }
        </style>
    </head>
    <body onLoad="start()">

        <img src="youtube.png" style="position:absolute; left:150px; top:65px;z-index:-11" />
        <img src="xzibit.jpg" style="position:absolute; bottom:0px; right:65px;z-index:-11" />

        <form onsubmit="add(this.url)">
            YouTube URL: <input type="text" name="url" value="http://www.youtube.com/watch?v=9FtQvLrGUak" size="70" />
            <input type="submit" value="Add to playlist" />
        </form><br />

        <a
            style="display:block;width:400px;height:250px;position:absolute;right:20px;top:60px;z-index:-10"
            id="player">
        </a>

        <div id ="playlist" style="z-index: 10"/>
    </body>
</html>