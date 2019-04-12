<?php
require "SimpleXMLElementExtended.php";

function sxml_append(SimpleXMLElement $to, SimpleXMLElement $from) {
    $toDom = dom_import_simplexml($to);
    $fromDom = dom_import_simplexml($from);
    $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
}

function deleteDirectory($dir) {
    system('rm -rf ' . escapeshellarg($dir), $retval);
    return $retval == 0; // UNIX commands return zero on success
}

function zippity($zipname, $dir){
    system('zip -r '.escapeshellarg($zipname).' '.escapeshellarg($dir), $retval);
    return $retval ==0;
}

mkdir("Downloads/tmp");
$data = json_decode(file_get_contents('php://input'),true);
//print_r($data);

$repo_name = $data["repository"]["name"];
$repo_version = $data["release"]["tag_name"];
$author = $data["release"]["author"]["login"];
$link = "https://github.com/{$author}/{$repo_name}/archive/{$repo_version}.zip";

$opts = array (
	'https' => array (
		'method' => "GET",
		'header' => 'bipbop',
		'user_agent' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1"
		),
	'http' => array (
		'method' => "GET",
		'header' => 'bipbop',
		'user_agent' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1"
		)
);
$context = stream_context_create($opts);
file_put_contents("Downloads/tmp/{$repo_name}{$repo_version}.zip", fopen("{$link}",'r'),false,$context);

//sleep(30);
$zip = new ZipArchive;
$res = $zip->open("Downloads/tmp/{$repo_name}{$repo_version}.zip");
if ($res === TRUE) {
  $zip->extractTo("Downloads/tmp");
  $zip->close();
} else {
  die('Could not extract the file');
}


$file = file_get_contents("Downloads/tmp/{$repo_name}-{$repo_version}/metadata.txt");

rename("Downloads/tmp/{$repo_name}-{$repo_version}","Downloads/tmp/{$repo_name}");
zippity("zips/{$repo_name}.zip","Downloads/tmp/{$repo_name}");
$dwn_url = "http://0xa.p/wiktor/zips/{$repo_name}.zip";
$replaced = preg_replace('/\n\s+/',' ',$file);
file_put_contents("Downloads/tmp/metadatka.txt",$replaced);
$directory = parse_ini_file("Downloads/tmp/metadatka.txt");
$id = hexdec(substr(md5("{$repo_name}"),0,3));

$xml = new SimpleXMLElementExtended("<pyqgis_plugin></pyqgis_plugin>");
$xml->addAttribute("name",$directory["name"]);
$xml->addAttribute("version",$directory["version"]);
$xml->addAttribute("plugin_id",$id);
$xml->addChildWithCDATA("description",$directory["description"]);
$xml->addChildWithCDATA("author_name",$directory["author"]);
$xml->addChildWithCDATA("about",$directory["about"]);
$xml->addChildWithCDATA("homepage",$directory["homepage"]);
$xml->addChildWithCDATA("file_name","{$repo_name}");
$xml->addChild("download_url",$dwn_url);
$xml->addChildWithCDATA("repository",$directory["repository"]);
$xml->addChildWithCDATA("tracker",$directory["tracker"]);
if($directory["experimental"]==True){
  $xml->addChildWithCDATA("experimental",$directory["experimental"]);
}else{
  $xml->addChild("experimental",NULL);
}


file_put_contents("xmls/plugins/{$repo_name}.xml",$xml->asXML());

header("Content-type: text/plain");
$sxml = simplexml_load_string("<plugins></plugins>");

$dir = "xmls/plugins/";
foreach(glob($dir."*") as $file){
  if(!is_dir($file)){
    $n1 = simplexml_load_file($dir.basename($file));
    sxml_append($sxml, $n1);
  }
}

file_put_contents("xmls/plugins.xml", $sxml->asXML());
deleteDirectory("Downloads/tmp");
?>
