Need a media player for bigscreen on your LAN party?
This is the shit! :D
This system takes youtube URL:s, queues them and plays them on bigscreen. The users can vote for the videos they would like to see.

You'll need a mysql server, a web server with php5, php5-mysql, python and python-mysql to run this system.

To checkout on svn use:
svn checkout http://laggitms.googlecode.com/svn/ laggitms-read-only

# Installation #
1) Install all needed software.

2) Create a user and a database on the mysql server.

3) Use the sql-file in the source directory and run it on the database to create the table structure.

4) Edit the config.php in the source directory.

5) Get the web server running the php-scrits.

6) Start the python daemon in the source directory. Make sure the media-folder is writable by the python script.

7) Browse to player.php with a web browser on the bigscreen, make it fullscreen. To avoid lagg, make sure the bigscreen browser has a quick and high bandwidth connection to the server.

# Usage #
Add links in the web browser on index.php. Use Firefox, other browsers have not been tested at all. They should start to download and the info on the index-page should be updated immediately if the daemon is working correctly.

Any user can use the player.php, it does not affect the system.

Use n{enter} in the daemon to skip a video.


Good luck! We are available in #laggit on irc.chalmers.it for more information!

