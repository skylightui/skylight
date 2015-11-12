<?php
$date = new DateTime();

$table = $date->getTimestamp();
$csv = "";

// CSV serialize a search results
foreach($fielddisplay as $key) {
    $csv .= $key . ', ';
}
$csv .=  "\r\n";
foreach ($docs as $index => $doc) {

    foreach ($fielddisplay as $key) {

        $element = $this->skylight_utilities->getField($key);
        //echo var_dump($element);
        if (isset($doc[$element])) {
            foreach ($doc[$element] as $index => $metadatavalue) {
                $csv .= '"' . $metadatavalue;
                if ($index < sizeof($doc[$element]) - 1) {
                    $csv .= '|| ';
                }
            }
            $csv .= '"';
        }
        $csv .= ',';
    }
    $csv .= "\r\n";
}


//OUPUT HEADERS
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$table.csv\";" );
header("Content-Transfer-Encoding: binary");

echo($csv);
?>
