#!/usr/bin/env python
# -*- coding: utf-8 -*-

MEDIA_DIR = "media"

import httplib,urllib,urllib2
import time
import sys, os
import commands
import _mysql
from threading import Thread, Semaphore

def getYoutubeVideoInfo(videoID,eurl=None):
  '''
  Return direct URL to video and dictionary containing additional info
  >> url,info = GetYoutubeVideoInfo("tmFbteHdiSw")
  >>
  '''
  if not eurl:
    params = urllib.urlencode({'video_id':videoID})
  else :
    params = urllib.urlencode({'video_id':videoID, 'eurl':eurl})
  conn = httplib.HTTPConnection("www.youtube.com")
  conn.request("GET","/get_video_info?&%s"%params)
  response = conn.getresponse()
  data = response.read()
  video_info = dict((k,urllib.unquote_plus(v)) for k,v in
                               (nvp.split('=') for nvp in data.split('&')))

  if video_info.has_key("errorcode") and video_info['errorcode'] == '150':
    return None,None
  if not video_info.has_key("video_id"):
    return None,None
  conn.request('GET','/get_video?video_id=%s&t=%s' % ( video_info['video_id'],video_info['token']))
  response = conn.getresponse()
  direct_url = response.getheader('location')
  return direct_url,video_info

semaphore = Semaphore(1)

def query(db, query):
  semaphore.acquire()
  db.query(query)
  semaphore.release()

class DownloadThread(Thread):
  def __init__(self, db):
    Thread.__init__(self)
    self.running = True
    self.db = db

  def stop(self):
    self.running = False

  def run(self):
    while self.running:
      query(self.db, "UPDATE media SET state = 'download queue' WHERE state LIKE 'downloading'")
      query(self.db, "SELECT m.videoid AS videoid, m.title AS title, m.length AS length, m.thumbnail AS thumbnail, SUM(v.vote) AS votes, m.state as status FROM media m, vote v WHERE m.videoid = v.videoid AND m.state LIKE 'download queue' GROUP BY v.videoid ORDER BY votes DESC, added ASC LIMIT 1")

      dbResult = self.db.store_result()

      for i in range(dbResult.num_rows()):
        res = dbResult.fetch_row()[0]
  
        print "fetching video info"
        print res[0]
        directUrl, videoInfo = getYoutubeVideoInfo(res[0], "http://chalmers.it")

        filename = os.path.join(MEDIA_DIR, res[0] + ".flv")
        if os.path.exists(filename):
          print "Target file already exists: " + filename + ". removing"
          os.remove(filename)

        query(self.db, "UPDATE media SET state = 'downloading' WHERE videoid = \"%s\"" % (_mysql.escape_string(res[0]),))
        print "downloading video data"
        result = commands.getstatusoutput("wget --user-agent=\"Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 (.NET CLR 3.5.30729)\" \"" + directUrl + "\" -O \"" + filename + "\" 1>&2")
        if result[0] != 0:
          print "wget exited with an error:"
          print result[1]
        else:
          print "Changing %s to idle" % (videoInfo['title'],)
          query(self.db, "UPDATE media SET state = 'idle' WHERE videoid = \"%s\"" % (_mysql.escape_string(res[0]),))

      sys.stdout.write("d")
      sys.stdout.flush()
      time.sleep(1)
    print "Exiting downloading"


class InfoThread(Thread):
  def __init__(self, db):
    Thread.__init__(self)
    self.running = True
    self.db = db

  def stop(self):
    self.running = False

  def run(self):
    while self.running:
      query(self.db, "SELECT videoid FROM media WHERE state LIKE 'processing'")
      dbResult = self.db.store_result()

      for i in range(dbResult.num_rows()):
        res = dbResult.fetch_row()[0]

        print "fetching video info"
        print res[0]
        directUrl, videoInfo = getYoutubeVideoInfo(res[0], "http://chalmers.it")

        if directUrl == None:
          print "unable to embed video, or other error"
          query(self.db, "DELETE FROM media WHERE videoid = \"%s\"" % (res[0]))
          continue
        if int(videoInfo['length_seconds']) > 600:
          print "Too long video, skipping " + videoInfo['title']
          query(self.db, "DELETE FROM media WHERE videoid = \"%s\"" % (res[0]))
          continue

        usock = urllib2.urlopen(directUrl)
        size =  str(usock.info().get('Content-Length'))
        if size is None:
          size = '0'
        usock.close()
        print "size:", size

        print "processing id:%s. title:%s" % (res[0], videoInfo['title'])
        print "inserting info into database"
        query(self.db, "UPDATE media SET title = '%s', length = '%s', thumbnail = '%s', size = '%s', state = 'download queue' WHERE videoid = '%s'" % (_mysql.escape_string(videoInfo['title']), _mysql.escape_string(videoInfo['length_seconds']), _mysql.escape_string(videoInfo['thumbnail_url']), _mysql.escape_string(size), _mysql.escape_string(res[0])))

      sys.stdout.write("i")
      sys.stdout.flush()
      time.sleep(2)
    print "Exiting info"


def vlcPlay(f):
  params = urllib.urlencode({'command':"pl_empty"})
  try:
    pass
    conn = httplib.HTTPConnection("localhost", 42923)
    conn.request("GET","/requests?%s"%params)
  except:
    vlcPlayer = VlcThread()
    vlcPlayer.start()
    time.sleep(3)
  print "params:",params
  conn = httplib.HTTPConnection("localhost", 42923)
  conn.request("GET","/requests/status.xml?%s" % params)
  response = conn.getresponse()
  data = response.read()
  print "Vlc pl_empty:", data

  params = urllib.urlencode({'command':"in_play", 'input':f})
  conn = httplib.HTTPConnection("localhost", 42923)
  conn.request("GET","/requests/status.xml?%s" % params)
  response = conn.getresponse()
  data = response.read()
  print "Vlc in_play:", data


class PlayerThread(Thread):
  def __init__(self, db):
    Thread.__init__(self)
    self.db = db
    self.running = True

  def stop(self):
    self.running = False

  def run(self):
    while self.running:
      query(self.db, "SELECT videoid, length, 0 AS votes FROM media WHERE state LIKE 'playing' LIMIT 1")
      dbResult = self.db.store_result()
      if dbResult.num_rows() == 0:
        query(self.db, "SELECT m.videoid AS videoid, m.length as length, SUM(v.vote) AS votes FROM media m, vote v WHERE m.videoid = v.videoid AND m.state LIKE 'idle' GROUP BY v.videoid ORDER BY votes DESC, added ASC LIMIT 1")
        dbResult = self.db.store_result()
      
      if dbResult.num_rows() == 0:
        sys.stdout.write("v")
        sys.stdout.flush()
        time.sleep(2)
        continue

      res = dbResult.fetch_row()[0]
      vID = res[0]
      length = int(res[1])

      query(self.db, "UPDATE media SET state = 'playing' WHERE videoid = '%s'" % vID)      
      vlcPlay("http://flippy/lms/media/%s.flv" % vID)
      time.sleep(length-1)
      query(self.db, "DELETE FROM vote WHERE videoid = '%s'" % vID)
      query(self.db, "DELETE FROM media WHERE videoid = '%s'" % vID)

      query(self.db, "INSERT INTO vote (videoid, ip, vote) VALUES ('%s', '13.37.13.37', 1)" % vID);
      query(self.db, "INSERT INTO media (videoid, added) VALUES ('%s', CURRENT_TIMESTAMP)" % vID);

class VlcThread(Thread):
  def __init__(self):
    Thread.__init__(self)

  def run(self):
    print "booting vlc"
    result = commands.getstatusoutput("vlc http://flippy/lms/media/jqyljvTvxQ0.flv -I http --http-host 127.0.0.1:42923 --sout \"#transcode{vcodec=h264,vb=800,scale=1,acodec=mp4a,ab=128,channels=2,samplerate=44100}:std{access=http,mux=ts,dst=0.0.0.0:8080}\" --sout-all --sout-keep")
    print "vlc exited. output:", result

if __name__ == "__main__":
  hostname = ""
  username = ""
  password = ""
  database = ""

  conf = open("config.php", 'r')
  for line in conf:
    line = line.replace("\n", "")
    if len(line) == 0 or line.startswith("#"):
      continue
    parts = line.split("=", 1)
    var = parts[0]
    value = parts[1]
    if var == "db.host":
      hostname = value
    if var == "db.user":
      username = value
    if var == "db.pass":
      password = value
    if var == "db.db":
      database = value

  db = _mysql.connect(hostname, username, password, database)

  dt = DownloadThread(db)
  di = InfoThread(db)
  player = PlayerThread(db)

  dt.start()
  di.start()
  player.start()

  while True:
    try:
      time.sleep(1)
    except (KeyboardInterrupt, SystemExit):
      print "Exiting"
      break

  dt.stop()
  di.stop()
  player.stop()

  dt.join()
  di.join()
  player.join()

print "bye!"

