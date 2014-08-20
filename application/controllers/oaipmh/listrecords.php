<?php

    $url = $oaipmhbase . 'verb=ListRecords';
    $restricted = false;

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
        // set needs to be defined
        $restricted = true;

    }
    else
    {
        $set = '&set='.$_GET['set'];

        if ($_GET['set'] !== $oaipmhcollection) {
            $restricted = true;
        }
    }

    $url .= $metadataPrefix.$set;

    if (!empty($_GET['resumptionToken'])) {
        $url .= '&resumptionToken=' . $_GET['resumptionToken'];
    }

    // now make sure it's a collection we're allowed to disseminate
    $oaiallowed = $this->config->item('skylight_oaipmhallowed');

    if($oaiallowed && !$restricted) {

        $response = file_get_contents($url);

        //print_r($response);
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
    }
    else { ?>
    <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
        <responseDate><?php echo $now; ?></responseDate>
        <request><?php echo htmlentities($url); ?></request>
        <error code="cannotDisseminateFormat">ListRecords not permitted for this collection</error>
    </OAI-PMH>
    <?php } ?>
