<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class Search extends skylight {



    function Search() {
        // Initalise the parent
        parent::__construct();
    }

    function _remap($query, $params = array()) {

        // Perform content negotiation on the query
        //TODO change where content negotiation takes place add param type?
        $format = $this->input->get('format');
        $this->_conneg($format);
        //$query = $this->_conneg($query);

        if (uri_string() == 'search/index') {
            $query = 'index';

        }
        elseif ((empty($query)) || ($query == 'index')) {
            // No record ID, so go home
		    //redirect('/');

            // We can't use * in URL path, so we might need to rethink that one..
            $query = '*';

        }

        $configured_fields = $this->config->item('skylight_fields');
        $configured_filters = $this->config->item('skylight_filters');
        $configured_additional_fields = $this->config->item('skylight_filters_additional');
        $configured_date_filters = $this->config->item('skylight_date_filters');
        $delimiter = $this->config->item('skylight_filter_delimiter');
        $rows = $this->config->item('skylight_results_per_page');
        $recorddisplay = $this->config->item('skylight_recorddisplay');
        $sort_options = $this->config->item('skylight_sort_fields');
        $display_thumbnail = $this->config->item('skylight_display_thumbnail');
        $thumbnail_field = $this->config->item('skylight_thumbnail_field');
        $link_bitstream = $this->config->item('skylight_link_bitstream');
        $bitstream_field = str_replace('.','',$this->config->item('skylight_bitstream_field'));

        $search_header = $this->config->item('skylight_search_header');

        // TODO: get rid of this, it's bad
        $title = $this->skylight_utilities->getField('Title');

        if(!isset($configured_additional_fields) || !is_array($configured_additional_fields)) {
            $configured_additional_fields = array();
        }

        $saved_filters = array();
        $url_filters = array();
        if(count($this->uri->rsegments) > 2) {

            for($i = 3; $i <= count($this->uri->rsegments); $i++) {

                $test_filter = $this->uri->rsegments[$i];
                $url_filters[] = $test_filter;
                $filter_segments = preg_split("/$delimiter/",$test_filter, 2);
                $filter_segments[0] = urldecode($filter_segments[0]);

                if(array_key_exists($filter_segments[0], $configured_filters)) {
                    $corrected_filter = str_replace("%2B", "+", $filter_segments[1]);
                    $corrected_filter = str_replace("|", "%7C", $corrected_filter);
                    $saved_filters[] = $configured_filters[$filter_segments[0]].$delimiter.$corrected_filter;
                } else if(array_key_exists($filter_segments[0], $configured_additional_fields)) {
                    $saved_filters[] = $configured_additional_fields[$filter_segments[0]].$delimiter.$filter_segments[1];
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

        $num_results = $this->input->get('num_results');

        if($num_results != "") {
            $rows = $num_results;
        }

        // Base search URL
        $base_search = './search/'.$query;
        $event_search = './timeline/'.$query;

        $base_parameters = '';

        foreach($url_filters as $url_filter) {
            $base_search .= '/'.$url_filter;
            $event_search .= '/'.$url_filter;
        }

        if($sort_by != "") {
            $base_parameters .= '?sort_by='.$sort_by;
        }

        // Solr query business moved to solr_client library
        $data = $this->solr_client->simpleSearch($query, $offset, $saved_filters, 'AND', $sort_by, $rows);

        // Inject query back into results
        $data['search_url'] = str_replace("%2B", "+", uri_string());
        $data['query'] = $query;
        $data['base_search'] = $base_search;
        $data['event_search'] = $event_search;
        $data['base_parameters'] = $base_parameters;
        $data['delimiter'] = $delimiter;

        // Variables to populate the search box
        $data['searchbox_query'] = $query;
        if (($data['searchbox_query'] == '*') || ($data['searchbox_query'] == '*:*')) $data['searchbox_query'] = '';
        $data['searchbox_filters'] = $saved_filters;


        // Obtain the common page title prefix.
        $page_title_prefix = $this->config->item('skylight_page_title_prefix');
        if( !isset($page_title_prefix) ) {
            $page_title_prefix = "";
        }

        if( urldecode($query) != "*:*" && urldecode($query) != "*" ) {
            $data['page_title'] = $page_title_prefix.'Search results for "'.urldecode($query).'"';
            $data['page_heading'] = 'Search results for "<span class=searched>'.urldecode($query).'</span>"';
        } else {
            $data['page_title'] = $page_title_prefix.'Search Results';
            $data['page_heading'] = 'Search Results"';
        }

	    
        // Check for zero results
        $result_count = $data['rows'];
        if ($result_count == 0) {
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
        $config['num_links'] = 4;
        $config['total_rows'] = $result_count;
        $config['per_page'] = $rows;
        $config['cur_tag_open'] = '&nbsp;<span class="curpage">';
        $config['cur_tag_close']= '</span>';
        $config['base_url'] = $base_search.$base_parameters;
        $this->pagination->initialize($config);

        $data['pagelinks'] = $this->pagination->create_links();
        $data['paginationlinks'] = $this->pagination->responsive_links();

        $data['startrow'] = $offset + 1;
        if($data['startrow'] + ($rows - 1 )  > $result_count)
            $data['endrow'] = $result_count;
        else
            $data['endrow'] = $data['startrow'] + ($rows - 1);

        $data['sort_options'] = $sort_options;

        $data['num_results'] = $rows;

        if(array_key_exists('Author', $recorddisplay)) {
            $data['author_field'] = $recorddisplay['Author'];
        }
        else {
            $data['author_field'] = 'dccreator';
        }

        $data['fielddisplay'] = $this->config->item("skylight_searchresult_display");
        $data['display_thumbnail'] = $display_thumbnail;
        $data['thumbnail_field'] = 'solr_'.str_replace('.','',$thumbnail_field);

        $data['link_bitstream'] = $link_bitstream;
        $data['bitstream_field'] = $bitstream_field;

        // Currently only used to restrict access to Physics material, but available for use elsewhere.
        $data['isAuthorised'] =  $this->_isAuthorised();

        $this->view('header', $data);
        $this->view('div_main');
        $this->view('search_suggestions', $data);
        if ($search_header == true)
        {
            $this->view('result_type_header', $data);

        }
        $this->view('search_results', $data);
        $this->view('div_main_end');
        $this->view('div_sidebar');
        $this->view('search_facets', $data);
        $this->view('div_sidebar_end');
        $this->view('footer');
    }


}
