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

        $saved_filters = array();
        $url_filters = array();
        if(count($this->uri->segments) > 2) {

            for($i = 3; $i <= count($this->uri->segments); $i++) {

                $test_filter = $this->uri->segments[$i];
                $url_filters[] = $test_filter;
                $filter_segments = preg_split("/$delimiter/",$test_filter, 2);
                if(array_key_exists($filter_segments[0], $configured_filters)) {
                    if(isset($filter_segments[1])) {
                        $saved_filters[] = $configured_filters[$filter_segments[0]].$delimiter.$filter_segments[1];
                    }
                    else {
                        $saved_filters[] = $configured_filters[$filter_segments[0]];
                    }
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

        $decodedField = urldecode($field);

        // Solr query business moved to solr_client library
        $data = $this->solr_client->browseTerms($decodedField, $rows, $offset, $prefix);

        // Determine the page title and heading.
        $page_title_prefix = $this->config->item('skylight_page_title_prefix');
        if( !isset($page_title_prefix) ) {
            $page_title_prefix = "";
        }

        // Check for zero results
        $result_count = $data['rows'];
        $facet_count = $data['facet']['termcount'];

        if ($result_count == 0) {
            $data['page_title'] = $page_title_prefix.'Browse "'. $decodedField . '"';
	        $this->view('header', $data);
            $this->view('div_main');
            $this->view('search_noresults');

            if ($this->config->item('skylight_facets_in_main')) {
                $this->view('div_sidebar');
                $this->view('div_sidebar_end');
                $this->view('div_main_end');
            }
            else {
                $this->view('div_main_end');
                $this->view('div_sidebar');
                $this->view('div_sidebar_end');
            }
    
            $this->view('footer');
            return;
        }

        $browse_url = './browse/'.$field;
        if($prefix !== '') {
            $browse_url .= '?prefix='.$prefix;
        }

        // Set the page title to the record title
        $data['page_title'] = $page_title_prefix.'Browse "'. $decodedField . '"';
	    $data['browse_url'] = $browse_url;
        $data['field'] = $field;
        $data['offset'] = $offset;

        // get the total count
        $total_results = $this->solr_client->countBrowseTerms($decodedField, $prefix);
        $data['total_results'] = $total_results;

        // Load and initialise pagination
        $this->load->library('pagination');
        $config['page_query_string'] = TRUE;
        $config['num_links'] = 4;
        $config['total_rows'] = $total_results;
        $config['per_page'] = $rows;
        $config['base_url'] = $browse_url;
        $config['cur_tag_open'] = '&nbsp;<span class="curpage">';
        $config['cur_tag_close']= '</span>';

        $this->pagination->initialize($config);
        $data['pagelinks'] = $this->pagination->create_links();
        $data['paginationlinks'] = $this->pagination->responsive_links();

        $data['startrow'] = $offset + 1;
        if(($data['startrow'] + ($rows - 1 ))  > $facet_count)
             $data['endrow'] = $offset + $facet_count;
        else
             $data['endrow'] = $data['startrow'] + ($rows - 1);

        //print_r($data);

        $this->view('header', $data);
        $this->view('div_main');
        $this->view('browse_facets', $data);
        
        if ($this->config->item('skylight_facets_in_main')) {
            $this->view('div_sidebar');
            $this->view('search_facets', $data);
            $this->view('div_sidebar_end');
            $this->view('div_main_end');
        }
        else {
            $this->view('div_main_end');
            $this->view('div_sidebar');
            $this->view('search_facets', $data);
            $this->view('div_sidebar_end');
        }

        $this->view('footer');
    }
}
