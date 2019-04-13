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
    $last_item="";
    while(! feof($fn))  {
      $result = fgets($fn); # we read line from file
      preg_match('/((\w+)\s*=)?\s*(.*)/', $result, $output_array);
      if ($output_array[2]=="") {
          # so the line does not contain "something = "
          # we append what we foud to last item
          $this[$last_item]=$this[$last_item]."\n".$output_array[3];
      } else {
          # so the line contains "something = "
          $this[$output_array[2]]=$output_array[3];
          $last_item = $output_array[2];
      }
      echo $result;
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
