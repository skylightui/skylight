<!--<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
    <responseDate><?php echo $now; ?></responseDate>
    <request verb="ListMetadataFormats"><?php echo htmlentities($base); ?></request>
    <ListMetadataFormats>
        <metadataFormat>
            <metadataPrefix>oai_dc</metadataPrefix>
            <schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>
            <metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>
        </metadataFormat>
    </ListMetadataFormats>
</OAI-PMH>-->

<?php
$url = $oaipmhbase . 'verb=ListMetadataFormats';
$response = file_get_contents($url);
$response = str_replace('<?xml version="1.0" encoding="UTF-8" ?>', '', $response);
$response = str_replace(substr($oaipmhbase, 0, strlen($oaipmhbase) - 1), htmlentities($base), $response);
$response = str_replace(' set="' . $oaipmhcollection . '">', '>', $response);
//$response = str_replace('<identifier>' . $oaipmhid, '<identifier>oai:skylight/' . $id . '/', $response);
$response = str_replace('<identifier>' . $oaipmhid, '<identifier>oai:skylight/', $response);
$response = str_replace('<dc:identifier>' . $oaipmhlink, '<dc:identifier>' . $record_url, $response);
$response = preg_replace("#<dc:identifier>(?:(?!".$record_url.").)*</dc:identifier>#",'', $response);
$response = str_replace('<setSpec>' . $oaipmhcollection . '</setSpec>', '', $response);
echo $response;
?>
