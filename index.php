<?php
require "UtilFunctions.php";

$tmp_dir="tmp"; #directory with temporary files
$plugins_dir="plugins"; # dir with plugins XMLs
$zips_dir="zips"; # dir with plugins zips

$githubhook_secret=NULL; # might be some password !!!!

$method = $_SERVER['REQUEST_METHOD'];
$event  = $_SERVER['HTTP_X_GITHUB_EVENT'];
$sign   = $_SERVER['HTTP_X_HUB_SIGNATURE'];

$body = file_get_contents('php://input');

if ($method=='GET'){
  header("Content-type: text/xml");
  echo file_get_contents("plugins.xml"); # this file shall be present
}
elseif ( $method=='POST' &&
         $event=='release' &&
         (
           $githubhook_secret==NULL ||
           $sign=="sha1=".hash_hmac('sha1',$body,$githubhook_secret)
         )){
  header("Content-type: text/plain");

  # it will work for  somenting like http://example.com/someadress/
  $my_server = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

  $data = json_decode($body,true);

  # create direcory for dowloaded data
  if (!is_dir($tmp_dir)) {
    mkdir($tmp_dir);
  }
  # this directory should not be deleted (contains current plugins)
  if (!is_dir($plugins_dir)) {
    mkdir($plugins_dir);
  }
  if (!is_dir($zips_dir)) {
    mkdir($zips_dir);
  }

  $repo_name = $data["repository"]["name"];
  $repo_version = $data["release"]["tag_name"];
  $author = $data["repository"]["owner"]["login"];
  $link = "https://github.com/{$author}/{$repo_name}/archive/{$repo_version}.zip";

  #  we dowload the released zip archive
  $opts = array (
  	'https' => array (
  		'method' => "GET",
  		'user_agent' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1"
  		),
  	'http' => array (
  		'method' => "GET",
  		'user_agent' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1"
  		)
  );
  $context = stream_context_create($opts);

  file_put_contents(
      "{$tmp_dir}/{$repo_name}{$repo_version}.zip",
      fopen("{$link}",'r'),
      false,
      $context);

  # The zip archive contains
  $zip = new ZipArchive;
  $res = $zip->open("{$tmp_dir}/{$repo_name}{$repo_version}.zip");
  if ($res === TRUE) {
    $zip->extractTo($tmp_dir);
    $zip->close();
  } else {
    die('Could not extract the file');
  }

  # lets get what is inside metadata.txt
  $metadata_file = file_get_contents("{$tmp_dir}/{$repo_name}-{$repo_version}/metadata.txt");
  # we need new name without tailing repo_version
  rename("{$tmp_dir}/{$repo_name}-{$repo_version}","{$tmp_dir}/{$repo_name}");
  zippity("{$repo_name}.zip","{$tmp_dir}","{$repo_name}","{$zips_dir}");
  $dwn_url = "{$my_server}{$zips_dir}/{$repo_name}.zip";
  $replaced_metadata = preg_replace('/\n\s+/',' ',$metadata_file);
  file_put_contents("{$tmp_dir}/metadatka.txt",$replaced_metadata);
  $metadata = parse_ini_file("{$tmp_dir}/metadatka.txt");
  $id = hexdec(substr(md5("{$repo_name}"),0,3));

  $xml = new SimpleXMLElementExtended("<pyqgis_plugin></pyqgis_plugin>");
  $xml->addAttribute("name",$metadata["name"]);
  $xml->addAttribute("version",$metadata["version"]);
  $xml->addAttribute("plugin_id",$id);
  $xml->addChildWithCDATA("description",$metadata["description"]);
  $xml->addChildWithCDATA("author_name",$metadata["author"]);
  $xml->addChildWithCDATA("about",$metadata["about"]);
  $xml->addChild("qgis_minimum_version",$metadata["qgisMinimumVersion"]);
  $xml->addChildWithCDATA("homepage",$metadata["homepage"]);
  $xml->addChildWithCDATA("file_name","{$repo_name}");
  $xml->addChild("download_url",$dwn_url);
  $xml->addChildWithCDATA("repository",$metadata["repository"]);
  $xml->addChildWithCDATA("tracker",$metadata["tracker"]);
  if($metadata["experimental"]){
    $xml->addChildWithCDATA("experimental",$metadata["experimental"]);
  }else{
    $xml->addChild("experimental",NULL);
  }

  file_put_contents("{$plugins_dir}/{$repo_name}.xml",$xml->asXML());

  $combined_xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><plugins></plugins>');

  foreach(glob("{$plugins_dir}/*") as $file){
    if(!is_dir($file)){
      $xml_content = simplexml_load_file("{$file}");
      sxml_append($combined_xml, $xml_content);
    }
  }

  file_put_contents("plugins.xml", $combined_xml->asXML());
  deleteDirectory($tmp_dir);
  echo "OK!";
} else {
  header("Content-type: text/plain");
  echo "Not implemented";
}

?>
