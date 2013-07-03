<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class Recent extends skylight {

    function Recent() {
        // Initalise the parent
        parent::__construct();
    }

    function _remap($query, $params = array()) {

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
        $offset = 0;
		$query = '*';
		
        // Solr query business moved to solr_client library
        $data = $this->solr_client->simpleSearch($query, $offset, $saved_filters, 'AND', 'dc.date.accessioned_dt+desc');

        // Inject query back into results
        $data['query'] = $query;
        $data['delimiter'] = $delimiter;
		
        // Check for zero results
        $result_count = $data['rows'];
        if ($result_count == 0) {
            $data['page_title'] = 'No search results found!';
            $this->view('recent_list_none');
            return;
        }
		
        $data['startrow'] = $offset + 1;
        if($data['startrow'] + ($rows - 1 )  > $result_count) {
            $data['endrow'] = $result_count;
        } else {
            $data['endrow'] = $data['startrow'] + ($rows - 1);
        }

        if(array_key_exists('Author', $recorddisplay)) {
            $data['author_field'] = $recorddisplay['Author'];
        } else {
            $data['author_field'] = 'dccreator';
        }
		
        $data['fielddisplay'] = $this->config->item("skylight_searchresult_display");
        $data['display_thumbnail'] = $display_thumbnail;
        $data['thumbnail_field'] = 'solr_'.str_replace('.','',$thumbnail_field);
        $this->view('recent_list_only', $data);
    }


}