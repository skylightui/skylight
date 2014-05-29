<?php

    $url = $oaipmhbase . 'verb=ListRecords';


    if (!(isset($_GET['metadataPrefix'])))
    {
        $metadataPrefix = '&metadataPrefix=oai_dc';

    }
    else
    {
        $metadataPrefix = '&metadataPrefix='.$_GET['metadataPrefix'];
    }

    if (!(isset($_GET['set'])))
    {
        $set = '&set=' . $oaipmhcollection;

    }
    else
    {
        $set = '&set='.$_GET['set'];
    }


    if (!empty($_GET['resumptionToken'])) {
        $url .= '&resumptionToken=' . $_GET['resumptionToken'];
    } else {
        $url .= $metadataPrefix.$set;
    }



    $response = file_get_contents($url);

    //print_r($response);

    $response = str_replace('<?xml version="1.0" encoding="UTF-8" ?>', '', $response);
    $response = str_replace(substr($oaipmhbase, 0, strlen($oaipmhbase) - 1), htmlentities($base), $response);
    $response = str_replace(' set="' . $oaipmhcollection . '">', '>', $response);
    //$response = str_replace('<identifier>' . $oaipmhid, '<identifier>oai:skylight/' . $id . '/', $response);
    $response = str_replace('<identifier>' . $oaipmhid, '<identifier>oai:skylight/', $response);
    //$response = str_replace('<dc:identifier>' . $oaipmhlink, '<dc:identifier>' . $record_url, $response);
    $response = str_replace($oaipmhlink, $record_url, $response);
    $response = preg_replace("#<dc:identifier>(?:(?!".$record_url.").)*</dc:identifier>#",'', $response);
    $response = str_replace('<setSpec>' . $oaipmhcollection . '</setSpec>', '', $response);
    echo $response;

?>
