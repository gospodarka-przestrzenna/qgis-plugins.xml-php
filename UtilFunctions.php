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


Class ReadMetadata extends ArrayObject {
  public function __construct($file){
    parent::__construct();

    $fn = fopen($file,"r");
    $prev_item="";
    while(! feof($fn))  {
      $result = fgets($fn); # we read line from file
      preg_match('/^(((\w+)\s*=\s*)|(\s*))(.+)$/', $result, $output_array);
      if (sizeof($output_array)>0){
        if ($output_array[3]=="") {
          # so the line does not contain "something = "
          # we append what we foud to last item
          $this[$prev_item]=$this[$prev_item]."\n".$output_array[5];
        } else {
          # so the line contains "something = "
          $this[$output_array[3]]=$output_array[5];
          $prev_item = $output_array[3];
        }
      }
    }
    fclose($fn);
  }
}

Class SimpleXMLElementExtended extends SimpleXMLElement {

  /**
   * Adds a child with $value inside CDATA
   * @param unknown $name
   * @param unknown $value
   */
  public function addChildWithCDATA($name, $value = NULL) {
    $new_child = $this->addChild($name);

    if ($new_child !== NULL) {
      $node = dom_import_simplexml($new_child);
      $no   = $node->ownerDocument;
      $node->appendChild($no->createCDATASection($value));
    }

    return $new_child;
  }
}
?>
