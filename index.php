<?php
$my_server="http://0xa.pl/wiktor"
$tmp_dir="tmp" #directory with temporary files
$plugins_dir="plugins" # dir with plugins XMLs
$zips_dir="zips" # dir with plugins zips

require "SimpleXMLElementExtended.php";
require "utils_functions.php";


// Replace XXXXXX_XXXX with the name of the header you need in UPPERCASE (and with '-' replaced by '_')
// $headerStringValue = $_SERVER['HTTP_XXXXXX_XXXX'];
header("Content-type: text/plain");

$event= $_SERVER['HTTP_X_GITHUB_EVENT'];
if ($event!="release"){
  exit
}
$body=file_get_contents('php://input')
$data = json_decode($body,true)

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
$dwn_url = "{$my_server}/{$zips_dir}/{$repo_name}.zip";
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
    $xml_content = simplexml_load_file("{$plugins_dir}/{$file}");
    sxml_append($combined_xml, $xml_content);
  }
}

file_put_contents("plugins.xml", $combined_xml->asXML());
