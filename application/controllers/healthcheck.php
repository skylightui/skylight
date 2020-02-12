<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class HealthCheck extends skylight {

    function HealthCheck() {
        // Initalise the parent
        parent::__construct();
    }

    public function index() {

        $errorMsg = "";

        try {
            $data = $this->solr_client->simpleSearch('*:*', 1, array(), 'OR', 'score+desc');
        }
        catch(Exception $e) {
            $errorMsg = 'Caught exception: ' . $e->getMessage();
        }

        // Inject query back into results
        $data['query'] = '*:*';
        $data['base_search'] = './search/*:*';
        $data['event_search'] = './timeline//*:*';
        $data['base_parameters'] = '?sort_by=score+desc';
        $data['delimiter'] = $this->config->item('skylight_filter_delimiter');
        $data['page_title'] = $this->config->item('skylight_page_title_prefix') . ' Health Check';
        $data['page_heading'] = 'Health Check';
        $data['error_message'] = $errorMsg;

        $this->view('header', $data);
        $this->view('div_main');
        $this->view('healthcheck', $data);

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
