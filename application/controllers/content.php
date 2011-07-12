<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class content extends skylight {

    function content() {
        // Initalise the parent
        parent::__construct();
    }

	function index() {
        // Get the URL actually requested
        $url = uri_string();
        
        $theme = 'default';
        if($this->config->item('theme') != '' && $this->config->item('theme') != NULL) {
            $theme = $this->config->item('theme');
        }
        $data['theme'] = $theme;

        $data['base_parameters'] = '';

        // Is this the home page?
        if ($url == '') {
            if($this->config->item('homepage_title') !== '') {
                $data['page_title'] = $this->config->item('skylight_homepage_title');
            }
            else {
                $data['page_title'] = $this->config->item('skylight_fullname');
            }

            //$recent_item_data = $this->solr_client->getRecentItems(5);
            $facet_data = $this->solr_client->getFacets();

            $this->view('header', $data);
            $this->view('div_main', $data);
            if (file_exists('./application/views/static/' . $this->config->item('skylight_appname') . '/index.php')) {
                $this->view('static/' . $this->config->item('skylight_appname') . '/index');
            } else {
                $this->view('index', $data);
            }
            $this->view('div_main_end');
            $this->view('div_sidebar');
            $this->view('search_box');
            $this->view('search_facets',$facet_data);
            $this->view('div_sidebar_end');
            $this->view('footer');
        } else if (file_exists('./application/views/static/' . $this->config->item('skylight_appname') . '/' . $url . '.php')) {
            // If there a static file with this name...
            $data['page_title'] = ucfirst($url);
            $facet_data = $this->solr_client->getFacets();

            $this->view('header', $data);
            $this->view('div_main', $data);
            $this->view('static/' . $this->config->item('skylight_appname') . '/' . $url);
            $this->view('div_main_end');
            $this->view('div_sidebar');
            $this->view('search_box');
            $this->view('search_facets', $facet_data);
            $this->view('div_sidebar_end');
            $this->view('footer');
        } else if (file_exists('./application/views/theme/' . $this->config->item('skylight_appname') . '/404.php')) {
            // Is there a custom 404 in this theme...
            $this->output->set_status_header('404');
            $data['page_title'] = $this->uilang['skylight_content_notfound'];
            $this->view('header', $data);
            $this->view('div_main', $data);
            $this->view('theme/' . $this->config->item('skylight_appname') . '/404');
            $this->view('div_main_end');
            $this->view('div_sidebar');
            $this->view('search_box');
            $this->view('div_sidebar_end');
            $this->view('footer');
        } else {
            // Show a normal 404
            $this->output->set_status_header('404');
            $data['page_title'] = $this->uilang['skylight_content_notfound'];
            $this->view('header', $data);
            $this->view('div_main', $data);
            $this->view('404');
            $this->view('div_main_end');
            $this->view('div_sidebar');
            $this->view('search_box');
            $this->view('div_sidebar_end');
            $this->view('footer');
        }
    }
}
