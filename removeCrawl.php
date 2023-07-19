<?php

error_reporting(E_ERROR);

function rrmdir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
           rrmdir($dir. DIRECTORY_SEPARATOR .$object);
         else
           unlink($dir. DIRECTORY_SEPARATOR .$object);
       }
     }
     rmdir($dir);
   }
 }

function readCrawlData($crawlDir) {
  $data = [];
  $data['crawlDir'] = $crawlDir;
  $crawlData = file_get_contents($crawlDir . "DbSeoSpiderFileKey");
  $lines = explode("\n",$crawlData);
  foreach($lines as $line) {
    $dat = explode("=",$line);
    if (count($dat) > 1) {
      $data[$dat[0]] = stripcslashes($dat[1]);
    };
    if (substr($line,0,1) == '#') {
      $data['date'] = substr($line,1,strlen($line));
    }
  }
  return $data;
}

function findDerbyDir($crawlDir) {
  $str = '';
  foreach(scandir($crawlDir) as $file) if (substr($file,0,8) == 'results_') $str = $crawlDir . $file . "/sql";
  return $str;
}

echo("ScreamingFrog-CrawlRemover\n");
echo("----------------------------\n");
$id = $argv[1];

$frogDir = $_SERVER["HOME"] . '/.ScreamingFrogSEOSpider/ProjectInstanceData/';
$crawlList = scandir($frogDir);
$datList = [];
foreach($crawlList as $crawl) {
  $crawlDir = $frogDir . $crawl . "/";
  $datList[] = readCrawlData($crawlDir);
}
if ($id == "") {
  $c = 0;
  foreach($datList as $crawl) {
    $c++;
    echo($c . " " . $crawl["url"] . " - " . $crawl["date"] . "\n");
  }
  echo("----------------------------\n");
  echo("Crawl-Nummer: ");
  $id = trim(fgets(STDIN));
}
  
if ($id != '') {
  $c = 0;
  foreach($datList as $crawl) {
    $c++;
    if ($c == $id) {
      echo("Crawldaten:\n");
      echo("URL: " . $crawl["url"] . "\n");
      $exportFileName = str_replace('/','',str_replace('.','-',str_replace('://','-',$crawl['url'])))
. '.dbseospider';
      echo("Datum: " . $crawl["date"] . "\n");
      $derbyurl = findDerbyDir($crawl["crawlDir"]);
      echo("ExportFilename: " . $exportFileName . "\n");
      $oldDir = getcwd();
      chdir($crawl['crawlDir']);
      shell_exec('zip -r ' . $oldDir . '/' . $exportFileName . ' *');
      chdir($oldDir);
      rrmdir($crawl["crawlDir"]);
    }
  }
}
