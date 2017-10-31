<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class content extends skylight {

    function content() {
        // Initalise the parent
        parent::__construct();
    }

    function index() {
        // Get the URL actually requested
        $url = str_replace("%2B", "+", uri_string());

        // If we are using prefixed URLs then strip off the prefixes.
        $url_prefix = $this->config->item('skylight_url_prefix');
        if (!empty($url_prefix))
        {
            $url = str_replace($url_prefix, '', $url);
        }

        $theme = 'default';
        if($this->config->item('theme') != '' && $this->config->item('theme') != NULL) {
            $theme = $this->config->item('theme');
        }
        $data['theme'] = $theme;

        $data['base_parameters'] = '';

        // Is there a theme override location?
        $local_path = $this->config->item('skylight_local_path');

        // Is this the home page?
        if ($url == '') {
            if($this->config->item('homepage_title') !== '') {
                $data['page_title'] = $this->config->item('skylight_homepage_title');
            }
            else {
                $data['page_title'] = $this->config->item('skylight_fullname');
            }

            // Do we want to include recent items?
            if ($this->config->item('skylight_homepage_recentitems') === TRUE) {
                $recentitems = $this->solr_client->getRecentItems();
                $data['recentitems'] = $recentitems['recent_items'];
                $data['fielddisplay'] = $this->config->item("skylight_searchresult_display");
            }

            // Do we want to include random items?
            if ($this->config->item('skylight_homepage_randomitems') === TRUE) {
                $randomitems = $this->solr_client->getRandomItems();
                $data['randomitems'] = $randomitems['random_items'];
                $data['fielddisplay'] = $this->config->item("skylight_searchresult_display");
            }

            // Get the facet data
            $facet_data = $this->solr_client->getFacets();

            $this->view('header', $data);

            if ($this->config->item('skylight_homepage_fullwidth') === TRUE) {
                $this->view('div_main_full', $data);
            }
            else {
                $this->view('div_main', $data);
            }
            if (file_exists($local_path . '/static/' . $this->config->item('skylight_appname') . '/index.php')) {
                $foreign['load'] = $local_path . '/static/' . $this->config->item('skylight_appname') . '/index.php';
                $this->view('foreign', $foreign);
            } else if (file_exists('./application/views/static/' . $this->config->item('skylight_appname') . '/index.php')) {
                $this->view('static/' . $this->config->item('skylight_appname') . '/index');
            } else {
                $this->view('index', $data);
            }

            if ($this->config->item('skylight_homepage_recentitems') === TRUE) {
                $this->view('recent_items', $data);
            }

            if ($this->config->item('skylight_homepage_randomitems') === TRUE) {
                $this->view('random_items', $data);
            }

            $this->view('div_main_end');
            if ($this->config->item('skylight_homepage_fullwidth') === TRUE) {

            }
            else {
                $this->view('div_sidebar');
                $this->view('search_facets',$facet_data);
                $this->view('div_sidebar_end');
            }
            $this->view('footer');
        } else if (file_exists($local_path . '/static/' . $this->config->item('skylight_appname') . '/' . $url . '.php')) {
            // If there a static file with this name in the local path...
            $title = str_replace('-', ' ', $url);
            $title = str_replace('/', ' ', $title);
            $title = ucwords($title);
            $data['page_title'] = $title;

            $facet_data = $this->solr_client->getFacets();

            $this->view('header', $data);
            if ($this->config->item('skylight_homepage_fullwidth') === TRUE) {
                $this->view('div_main_full', $data);
            }
            else {
                $this->view('div_main', $data);
            }
            $foreign['load'] = $local_path . '/static/' . $this->config->item('skylight_appname') . '/' . $url . '.php';
            $this->view('foreign', $foreign);
            $this->view('div_main_end');
            if ($this->config->item('skylight_homepage_fullwidth') === TRUE) {

            }
            else {
                $this->view('div_sidebar');
                $this->view('search_facets',$facet_data);
                $this->view('div_sidebar_end');
            }
            $this->view('footer');
        } else if (file_exists('./application/views/static/' . $this->config->item('skylight_appname') . '/' . $url . '.php')) {
            // If there a static file with this name...
            $title = str_replace('-', ' ', $url);
            $title = str_replace('/', ' ', $title);
            $title = ucwords($title);
            $data['page_title'] = $title;

            $facet_data = $this->solr_client->getFacets();

            $this->view('header', $data);
            if ($this->config->item('skylight_homepage_fullwidth') === TRUE) {
                $this->view('div_main_full', $data);
            }
            else {
                $this->view('div_main', $data);
            }
            $this->view('static/' . $this->config->item('skylight_appname') . '/' . $url);
            $this->view('div_main_end');
            if ($this->config->item('skylight_homepage_fullwidth') === TRUE) {

            }
            else {
                $this->view('div_sidebar');
                $this->view('search_facets',$facet_data);
                $this->view('div_sidebar_end');
            }
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

            // Get the facet data
            $facet_data = $this->solr_client->getFacets();

            $this->view('search_facets', $facet_data);
            $this->view('div_sidebar_end');
            $this->view('footer');
        }
    }
}
