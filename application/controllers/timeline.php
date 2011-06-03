<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class Timeline extends skylight {

    function Timeline() {        // Initalise the parent
        parent::__construct();
    }

    function _remap($query, $params = array()) {
        if (uri_string() == 'timeline/index') {
            $query = 'index';

        } elseif ((empty($query)) || ($query == 'index')) {
            // No record ID, so go home
		    //redirect('/');

            // We can't use * in URL path, so we might need to rethink that one..
            $query = '*';

        }

        $configured_filters = $this->config->item('skylight_filters');
        $configured_date_filters = $this->config->item('skylight_date_filters');
        $delimiter = $this->config->item('skylight_filter_delimiter');

        $saved_filters = array();
        $url_filters = array();
        if(count($this->uri->segments) > 2) {

            for($i = 3; $i <= count($this->uri->segments); $i++) {


                $test_filter = $this->uri->segments[$i];
                $url_filters[] = $test_filter;
                $filter_segments = preg_split("/$delimiter/",$test_filter, 2);
                if(array_key_exists($filter_segments[0], $configured_filters)) {
                    $saved_filters[] = $configured_filters[$filter_segments[0]].$delimiter.$filter_segments[1];
                }
                if(array_key_exists($filter_segments[0], $configured_date_filters)) {
                    $saved_filters[] = $configured_date_filters[$filter_segments[0]].$delimiter.$filter_segments[1];
                }
            }
        }

        $offset = 0;
        if(array_key_exists('offset',$_GET)) {
            $offset = $_GET['offset'];
        }

        // Base search URL
        $base_search = './search/'.$query;
        foreach($url_filters as $url_filter) {
            $base_search .= '/'.$url_filter;
        }

        // Solr query business moved to solr_client library
        $data = $this->solr_client->eventSearch($query,$saved_filters,5000);

        $events = array();
        $eventsarr = array();
        foreach($data['docs'] as $doc) {
            $event = null;
            $title = "";
            $date = "";
            $creator = "";
            foreach($doc as $k => $v) {
                if($k == 'solr_dctitleen') {
                    $title = $v.'';
                }
                elseif($k == 'solr_dcdateissued_dt') {
                    $date = $v.'';
                }
                elseif($k == 'solr_dccontributorauthoren') {
                    $creator = $v.'';
                }
            }
            $event['start'] = $date;
            $event['title'] = $title;
            $event['duration'] = false;
            $eventsarr[] = $event;
        }
        $events['wiki-url'] = "http://www.google.com";
        $events['wiki-section'] = "SDfsff";
        $events['dateTimeFormat'] = "iso8601";
        $events['events'] = $eventsarr;

        echo(json_encode($events));

    }
}