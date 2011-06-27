<?php

    $url = $oaipmhbase . 'verb=ListIdentifiers';

    if (!empty($_GET['resumptionToken'])) {
        $url .= '&resumptionToken=' . $_GET['resumptionToken'];
    } else {
        $url .= '&set=' . $oaipmhcollection . '&metadataPrefix=oai_dc';
    }

    $response = file_get_contents($url);

    $response = str_replace('<?xml version="1.0" encoding="UTF-8" ?>', '', $response);
    $response = str_replace(substr($oaipmhbase, 0, strlen($oaipmhbase) - 1), htmlentities($base), $response);
    $response = str_replace(' set="' . $oaipmhcollection . '">', '>', $response);
    $response = str_replace('<identifier>' . $oaipmhid, '<identifier>oai:skylight/' . $id . '/', $response);
    $response = str_replace('<dc:identifier>' . $oaipmhlink, '<dc:identifier>' . $record_url, $response);
    $response = preg_replace("#<dc:identifier>(?:(?!".$record_url.").)*</dc:identifier>#",'', $response);
    $response = str_replace('<setSpec>' . $oaipmhcollection . '</setSpec>', '', $response);
    echo $response;

?>
