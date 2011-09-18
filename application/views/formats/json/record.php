<?php

    // JSON serialize a record
    $record = array();

    $keyvalue = array();
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

    $top = array();
    $top['record'] = $record;
    // Display the record
    //print_r($record);
    echo json_encode($top);
?>
