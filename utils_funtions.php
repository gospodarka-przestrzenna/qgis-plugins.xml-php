<?php

function sxml_append(SimpleXMLElement $to, SimpleXMLElement $from) {
    $toDom = dom_import_simplexml($to);
    $fromDom = dom_import_simplexml($from);
    $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
}

function deleteDirectory($dir) {
    system('rm -rf ' . escapeshellarg($dir), $retval);
    return $retval == 0; // UNIX commands return zero on success
}

function zippity($zipname, $startdir, $dir, $moveto){
    system("pushd ".escapeshellarg($startdir)." && zip -r ".escapeshellarg($zipname)." ".escapeshellarg($dir)."; popd && mv ".escapeshellarg($startdir)."/".escapeshellarg($zipname)." ".escapeshellarg($moveto).";", $retval);
    return $retval ==0;
}

?>
