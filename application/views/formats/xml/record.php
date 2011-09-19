<?php

    // JSON serialize a record
    $record = array();

    foreach($recorddisplay as $key => $element) {
        if (isset($solr[$element])) {
            $key = array();
            foreach ($solr[$element] as $metadatavalue) {
                $key["" . $metadatavalue] = $element;
            }
            $record[$element] = $key;
        }
    }

    //TODO Put in digital objects

    header('Content-type: application/xml');
    $xml = new SimpleXMLElement('<record/>');
    array_walk_recursive($record, array ($xml, 'addChild'));
    print $xml->asXML();
?>
