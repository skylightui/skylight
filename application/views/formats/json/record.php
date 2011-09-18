<?php

    // JSON serialize a record
    $record = array();

    foreach($recorddisplay as $key => $element) {
        if (isset($solr[$element])) {
            $key = array();
            foreach ($solr[$element] as $metadatavalue) {
                $key[] = "" . $metadatavalue;
            }
            $record[$element] = $key;
        }
    }

    //TODO Put in digital objects

    header('Content-type: application/json');
    echo json_encode(array('record' => $record));
?>
