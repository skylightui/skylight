<?php

/*
    $url = $oaipmhbase . 'verb=GetRecord';


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


    $identifier = $_GET['identifier'];

    $identifier = str_replace('oai:skylight/' . $id, substr($oaipmhid, 0, strlen($oaipmhid) - 1), $identifier);

   $url .= '&identifier=' . $identifier . $metadataPrefix;


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
    $response = str_replace($oaipmhbitstream, $record_url, $response);
    echo $response;
*/

/* We are not allowing GetRecord to stop exam paper records being displayed */

?>
    <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
             xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
        <responseDate><?php echo $now; ?></responseDate>
        <request><?php echo htmlentities($url); ?></request>
        <error code="cannotDisseminateFormat">GetRecord not permitted for this collection</error>
    </OAI-PMH>