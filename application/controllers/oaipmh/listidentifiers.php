<?php

    $url = $oaipmhbase . 'verb=ListIdentifiers';

    //Scott Renton 6/3/14- Allow alternative prefixes and sets. Default to oai_dc and current $oaipmhcollection.

    if (!(isset($_GET['metadataPrefix'])))
    {
        $metadataPrefix = '&metadataPrefix=oai_dc';

    }
    else
    {
        $metadataPrefix = '&metadataPrefix='.$_GET['metadataPrefix'];
    }

switch ($_GET['metadataPrefix'])
{
    case "lido":
        $config = "mimed";
        break;
    case "pndsdc":
        $config = "art";
        break;
    default:
        $config = "";
        break;
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
    $record_url = str_replace("/record/","/".$config."/record/", $record_url );
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
