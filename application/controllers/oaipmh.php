<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class OAIPMH extends skylight {

    function OAIPMH() {
        // Initalise the parent
        parent::__construct();
    }

	public function index() {
        // What verb is being requested?
        if (!empty($_GET['verb'])) {
            $verb = $_GET['verb'];
        } else {
            $verb = '';
        }

        // Setup the date timezone
        date_default_timezone_set('Etc/Zulu');
        $now = date('Y-m-d\TH:i:s\Z');

        // Setup some other variables
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if (strpos($url, '?') === true) {
            $base = substr($url, 0, strpos($url, '?'));
        } else {
            $base = $url;
        }
        $home = substr($base, 0, strpos($base, 'oaipmh'));

        // Set the headers
        if (true) {
            header('Content-type: text/xml');
            echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
            //echo '<?xml-stylesheet type="text/xsl" href="' . $home . 'xsl/oai.xsl" ? >' . "\n";
        }

        // Load some configuration variables
        $id = $this->config->item('skylight_appname');
        $name = $this->config->item('skylight_fullname');
        $email = $this->config->item('skylight_adminemail');
        $site_url = base_url() . index_page();
        if (index_page() !== '') $site_url .= '/';
        $record_url = $site_url . 'record/';
        $oaipmhbase = $this->config->item('skylight_oaipmhbase');
        $oaipmhid = $this->config->item('skylight_oaipmhid');
        $oaipmhlink = $this->config->item('skylight_oaipmhlink');
        $oaipmhcollection = $this->config->item('skylight_oaipmhcollection');

        // Process the query
        switch ($verb) {
            case "GetRecord":
                require_once('oaipmh/getrecord.php');
                break;
            case "Identify":
                require_once('oaipmh/identify.php');
                break;
            case "ListIdentifiers":
                require_once('oaipmh/listidentifiers.php');
                break;
            case "ListMetadataFormats":
                require_once('oaipmh/listmetadataformats.php');
                break;
            case "ListRecords":
                require_once('oaipmh/listrecords.php');
                break;
            case "ListSets":
                require_once('oaipmh/listsets.php');
                break;
            default:
                require_once('oaipmh/illegalverb.php');
                break;
        }
    }
}

?>
