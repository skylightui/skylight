<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class Sitemap extends skylight {

    function Sitemap() {
        // Initalise the parent
        parent::__construct();
    }

    function _remap($query, $params = array()) {

    $container = $this->config->item('skylight_container_field');
    $container_id = $this->config->item('skylight_container_id');

        if($this->config->item('skylight_sitemap_type') == "external") {

            $data = $this->solr_client->getSiteMapURLs($container."%3A%28".$container_id."%29", $container);
            $data["prefixes"] = array();
        }

        // internal sitemap or not set, so default to internal
        else {

            // add in here which collections to include in sitemap for indexing
            // currently
            // 1: CLDS
            // 3: Art
            // 11: MIMEd
            // 15: Calendars
            $data = $this->solr_client->getSiteMapURLs("location.coll%3A%281+OR+3+OR+11+OR+15%29", $container);
            $data["prefixes"] = array("1" => "", "3" => "art/", "11" => "mimed/", "15" => "calendars/");

        }

        // Set the headers
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";

        //print_r($data);

        $this->view("sitemap", $data);

    }

}
