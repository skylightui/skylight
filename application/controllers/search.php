<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class Search extends skylight {



    function Search() {
        // Initalise the parent
        parent::__construct();
    }

    function _remap($query, $params = array()) {

        // Perform content negotiation on the query
        $query = $this->_conneg($query);

        if (uri_string() == 'search/index') {
            $query = 'index';

        } elseif ((empty($query)) || ($query == 'index')) {
            // No record ID, so go home
		    //redirect('/');

            // We can't use * in URL path, so we might need to rethink that one..
            $query = '*';

        }

        $configured_fields = $this->config->item('skylight_fields');
        $configured_filters = $this->config->item('skylight_filters');
        $configured_date_filters = $this->config->item('skylight_date_filters');
        $delimiter = $this->config->item('skylight_filter_delimiter');
        $rows = $this->config->item('skylight_results_per_page');
        $recorddisplay = $this->config->item('skylight_recorddisplay');
        $sort_options = $this->config->item('skylight_sort_fields');
        $display_thumbnail = $this->config->item('skylight_display_thumbnail');
        $thumbnail_field = $this->config->item('skylight_thumbnail_field');

        // TODO: get rid of this, it's bad
        $title = $this->skylight_utilities->getField('Title');

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

        $sort_by = $this->input->get('sort_by');

        // Base search URL
        $base_search = './search/'.$query;
        $event_search = './timeline/'.$query;

        $base_parameters = '';

        foreach($url_filters as $url_filter) {
            $base_search .= '/'.$url_filter;
            $event_search .= '/'.$url_filter;
        }
        //print_r($url_filters);

        if($sort_by != "") {
            $base_parameters .= '?sort_by='.$sort_by;
        }

        // Solr query business moved to solr_client library
        $data = $this->solr_client->simpleSearch($query, $offset, $saved_filters, 'AND', $sort_by);

        // Inject query back into results
        $data['query'] = $query;
        $data['base_search'] = $base_search;
        $data['event_search'] = $event_search;
        $data['base_parameters'] = $base_parameters;
        $data['delimiter'] = $delimiter;

        // Variables to populate the search box
        $data['searchbox_query'] = $query;
        if (($data['searchbox_query'] == '*') || ($data['searchbox_query'] == '*:*')) $data['searchbox_query'] = '';
        $data['searchbox_filters'] = $saved_filters;

        // Check for zero results
        $result_count = $data['rows'];
        if ($result_count == 0) {
            $data['page_title'] = 'No search results found!';
            $this->view('header', $data);
            $this->view('div_main');
            $this->view('search_suggestions', $data);
            $this->view('search_noresults');
            $this->view('div_main_end');
            $this->view('div_sidebar');
            $this->view('div_sidebar_end');
            $this->view('footer');
            return;
        }

        // Load and initialise pagination
        $this->load->library('pagination');
        $config['page_query_string'] = TRUE;
        $config['num_links'] = 2;
        $config['total_rows'] = $result_count;
        $config['per_page'] = $rows;
        $config['base_url'] = $base_search.$base_parameters;
        $this->pagination->initialize($config);


        $data['pagelinks'] = $this->pagination->create_links();

        $data['startrow'] = $offset + 1;
        if($data['startrow'] + ($rows - 1 )  > $result_count)
            $data['endrow'] = $result_count;
        else
            $data['endrow'] = $data['startrow'] + ($rows - 1);

        $data['sort_options'] = $sort_options;

        if(array_key_exists('Author', $recorddisplay)) {
            $data['author_field'] = $recorddisplay['Author'];
        }
        else {
            $data['author_field'] = 'dccreator';
        }

        // Set the page title to the record title
        $data['page_title'] = 'Search results for "'.urldecode($query).'"';
        $data['page_heading'] = 'Search results for "<span class=searched>'.urldecode($query).'</span>"';


        //$data['title_field'] = $title;
        $data['fielddisplay'] = $this->config->item("skylight_searchresult_display");
        // TODO: get rid of this, it's bad
       // $data['author_field'] = $recorddisplay['Author'];
       // $data['artist_field'] = array_key_exists('Artist',$recorddisplay) ? $recorddisplay['Artist'] : 'dccontributorillustratoren';
        $data['display_thumbnail'] = $display_thumbnail;
        $data['thumbnail_field'] = 'solr_'.str_replace('.','',$thumbnail_field);
        $this->view('header', $data);
        $this->view('div_main');
        $this->view('search_suggestions', $data);
        $this->view('search_results', $data);
        $this->view('div_main_end');
        $this->view('div_sidebar');
        $this->view('search_facets', $data);
        $this->view('div_sidebar_end');
        $this->view('footer');
    }


}