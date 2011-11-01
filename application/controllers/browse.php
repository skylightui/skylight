<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class Browse extends skylight {

    function Browse() {
        // Initalise the parent
        parent::__construct();
    }

    function _remap($field, $params = array()) {
        if ((empty($field)) || ($field == 'index')) {

        }

        $configured_filters = $this->config->item('skylight_filters');
        $delimiter = $this->config->item('skylight_filter_delimiter');
        $rows = 30;
        $recorddisplay = $this->config->item('skylight_recorddisplay');
     //   $title = $recorddisplay['Title'];

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
            }
        }


        $offset = $this->input->get('offset');
        $prefix = $this->input->get('prefix');


        // Base search URL
        $base_search = './search/*';
        foreach($url_filters as $url_filter) {
            $base_search .= '/'.$url_filter;
        }


        // Solr query business moved to solr_client library
        $data = $this->solr_client->browseTerms($field, $rows, $offset, $prefix);

        // Check for zero results
        $result_count = $data['rows'];
        if ($result_count == 0) {
            $data['page_title'] = 'No search results found!';
            $this->view('header', $data);
            $this->view('div_main');
            $this->view('search_noresults');
            $this->view('div_main_end');
            $this->view('div_sidebar');
            $this->view('div_sidebar_end');
            $this->view('footer');
            return;
        }

        $browse_url = './browse/'.$field;
        if($prefix !== '') {
            $browse_url .= '?prefix='.$prefix;
        }

        // Load and initialise pagination
        $this->load->library('solr/pagination');
        $config['page_query_string'] = TRUE;
        $config['num_links'] = 3;
        $config['total_rows'] = $result_count;
        $config['per_page'] = $rows;
        $config['base_url'] = $browse_url;
        $this->pagination->initialize($config);


        $data['pagelinks'] = $this->pagination->create_links();

        $data['startrow'] = $offset + 1;
        if($data['startrow'] + ($rows - 1 )  > $result_count)
            $data['endrow'] = $result_count;
        else
            $data['endrow'] = $data['startrow'] + ($rows - 1);

        // Set the page title to the record title
        $data['page_title'] = 'Browsing '.$field.' terms';
        $data['browse_url'] = $browse_url;
        $data['field'] = $field;
        $data['offset'] = $offset;
        
        $this->view('header', $data);
        $this->view('div_main');
        $this->view('browse_facets', $data);
        $this->view('div_main_end');
        $this->view('div_sidebar');
        $this->view('search_facets', $data);
        $this->view('div_sidebar_end');
        $this->view('footer');
    }
}