<?php

    // CSV serialize a record
    foreach($recorddisplay as $key) {

        $element = $this->skylight_utilities->getField($key);
        if (isset($solr[$element])) {
            echo $key . ', ';
        }

    }
    echo "\r\n";
    foreach($recorddisplay as $key) {

        $element = $this->skylight_utilities->getField($key);
        if(isset($solr[$element])) {
            foreach($solr[$element] as $index => $metadatavalue) {
                // if it's a facet search
                // make it a clickable search link

                echo $metadatavalue;
            }
            if($index < sizeof($solr[$element]) - 1) {
                echo '|| ';
            }
        }
        else{
            echo ', ';
        }
    }


    //TODO Put in digital objects

    //$outstream = fopen("php://temp", 'r+');
    //fputcsv($outstream, $solr, ',', '"');
    //rewind($outstream);
    //$csv = fgets($outstream);
    //fclose($outstream);

    header('Content-type: text/csv');

    //echo $csv;
?>
