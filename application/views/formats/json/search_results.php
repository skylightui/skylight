<?php

    $records = array();

    foreach ($docs as $doc) {

        // JSON serialize a record
        $record = array();

        foreach($doc as $key => $element) {

                $record[$key] = $element;

        }

        $records[] = array('record' => $record);

    }

    header('Content-type: application/json');
    echo json_encode(array('records' => $records));

?>