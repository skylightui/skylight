<?php

    // CSV serialize a record
    $record = array();

    foreach($recorddisplay as $key => $element) {
        if (isset($solr[$element])) {
            $key = '';
            foreach ($solr[$element] as $metadatavalue) {
                $key .= '|||' . $metadatavalue;
            }
            $record[] = substr($key, 3);
        }
    }

    //TODO Put in digital objects

    $outstream = fopen("php://temp", 'r+');
    fputcsv($outstream, $record, ',', '"');
    rewind($outstream);
    $csv = fgets($outstream);
    fclose($outstream);

    header('Content-type: text/csv');
    echo $csv;
?>
