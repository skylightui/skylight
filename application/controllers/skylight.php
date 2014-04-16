<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class skylight extends CI_Controller {

    // Whether this page is part of the administrative interface
    var $adminInterface = false;

    // The language being used in the user interface
    public $uilang;

    // The output type - normally html, but could be RDF / ATOM / JSON etc
    public $output_type = 'html';

    function skylight() {
        // Initalise the parent
        parent::__construct();

        // See if there is a skylight.php in the default 'local' location
        if (file_exists('../skylight-local/config/skylight.php')) {
            $this->_load_config('../skylight-local/config/skylight.php');
        } else {
            // Load the normal skylight config
            $this->config->load('skylight');
        }

        // Is there a skylight-local setup, and does it have a master skylight.php config?
        $local_path = $this->config->item('skylight_local_path');
        if (!empty($local_path)) {
            if (file_exists($local_path . '/config/skylight.php')) {
                $this->_load_config($local_path . '/config/skylight.php');
            }
        }

        // Decide whether to enable debug / profiling mode or not
        if ($this->config->item('skylight_debug') === TRUE) {
            $this->output->enable_profiler(TRUE);
        }

        // Start the sessions
        session_start();

        // Do we need to clear the session?
        $clear_session = $this->input->get('reset');
        if (!empty($clear_session)) {
            session_destroy();
            session_start();
        }

        // Load some helpers
        $this->load->helper(array('form', 'url'));
        $this->load->helper('skylight_bitstream_helper');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error"><ul><li>', '</li></ul></div>');

        // Check the user is logged in, else redirect them to the first step
        if ($this->adminInterface)
        {
            // Check the user is logged in as an admin
            if (empty($_SESSION['skylight-admin-isadmin-' . base_url()]))
            {
                redirect('/adminlogin');
                die();
            }
        }

        // Load the correct site configuration file
        $this->_load_site_config();

        // Load any language files needed
        $this->_load_languages();

        // Decide whether to enable caching, and if so, for how many minutes
        if (is_numeric($this->config->item('skylight_cache'))) {
            //$this->output->cache($this->config->item('skylight_cache'));
        }

        // Load the solr library
        // Check for repo type and version, load accordingly
        $repository_type = $this->config->item('skylight_repository_type');
        $repository_version = $this->config->item('skylight_repository_version');
        $this->load->library('solr/solr_client'.'_'.$repository_type.'_'.$repository_version, '', 'solr_client');

        // Load the Skylight Utilities library
        $this->load->library('skylight_utilities');
    }

    function view($view, $data = array()) {
        // Load some globals
        $data['site_title'] = $this->config->item('skylight_fullname');
        $data['ga_code'] = $this->config->item('skylight_ga_code');

        // Which output type to use?
        switch ($this->output_type) {
            case 'json':
                if (file_exists('./application/views/formats/json/' . $view . '.php')) {
                    $this->load->view('formats/json/' . $view, $data);
                }
                break;
            case 'csv':
                if (file_exists('./application/views/formats/csv/' . $view . '.php')) {
                    $this->load->view('formats/csv/' . $view, $data);
                }
                break;
            case 'xml':
                if (file_exists('./application/views/formats/xml/' . $view . '.php')) {
                    $this->load->view('formats/xml/' . $view, $data);
                }
                break;
            default:
                // Get the theme
                $theme = $this->_get_theme();

                // Does the theme override this page?
                $local_path = $this->config->item('skylight_local_path');
                if ((!empty($local_path)) &&
                    (file_exists($local_path . '/theme/' . $theme . '/views/' . $view . '.php'))) {
                    $data['load'] = $local_path . '/theme/' . $theme . '/views/' . $view . '.php';
                    $this->view('foreign' , $data);
                }
                else if (file_exists('./application/views/theme/' . $theme . '/' . $view . '.php')) {
                    $this->load->view('theme/' . $theme . '/' . $view, $data);
                }
                else if (file_exists('./application/views/theme/default/' . $view . '.php')) {
                    $this->load->view('theme/default/' . $view, $data);
                }
                 else {
                    $this->load->view($view, $data);
                }
        }
    }

    function _get_theme() {
        // The site's theme name
        $theme = $this->config->item('skylight_theme');

        // Has the user requested to override the theme?
        $get_theme = $this->input->get('theme');
        if ((!empty($get_theme)) && ($this->config->item('skylight_theme_allowoverride') === TRUE)) {
            $theme = preg_replace('/[^A-Za-z0-9]/', '', $this->input->get('theme'));
            $_SESSION['skylight_theme'] = $theme;
        } else if (isset($_SESSION['skylight_theme'])) {
            $theme = $_SESSION['skylight_theme'];
        }

        // Return the theme
        return $theme;
    }

    function index() {
        // Go home, nothing to do here
        redirect('/');
    }

    /**
     * Load the host-specific configuration unless it is overridden in the URL or the session
     */
    function _load_site_config() {
        // Load the correct config file - usually looked up using the hostname
        //$hostname = $_SERVER['HTTP_HOST'];

        // Our URLs will be of the form collections.ed.ac.uk/thing where thing will match the site config file. Robin.
        $url_segments = explode( "/", $_SERVER['PHP_SELF'] );
        $hostname = $url_segments[1];

        // Trim the techically-legal but config-breaking trailing dot that a hostname may contain
        $hostname = trim($hostname, ".");

        // Has a config file been specified using a query string parameter or in the session?
        if ($this->config->item('skylight_config_allowoverride') === TRUE) {
            $get_config = preg_replace('/[^A-Za-z0-9-_\.]/', '', $this->input->get('config'));
            if (!empty($get_config)) {
                $hostname = $get_config;
                $_SESSION['skylight_config'] = $hostname;
            } else if (isset($_SESSION['skylight_config'])) {
                $hostname = $_SESSION['skylight_config'];
            }
        }

        // Is there a specified config file to load?
        // - First check the override directory
        $local_path = $this->config->item('skylight_local_path');
        if ((!empty($local_path)) &&
            (file_exists($local_path . '/config/' . $hostname . '.php'))) {
            $this->_load_config($local_path . '/config/' . $hostname . '.php');
        }
        // - Next check the application/sites directory
        else if (file_exists('./application/config/sites/' . $hostname . '.php')) {
            $this->config->load('sites/' .$hostname);
        }
        // - Try the default site configuration file
        else if (file_exists('./application/config/sites/default.php')) {
            // Load the default config
            $this->config->load('sites/default');
        }
        // - For some reason the default site config was missing
        else {
            show_error('Unknown skylight virtual host: application/config/sites/' . $this->_clean($hostname) .
                       '.php or missing default configuration at application/config/sites.default.php', 500);
            die();
        }
    }

    /**
     * Load a configuration file - we can't use the standard CodeIgniter function here
     * as this method is called when they exist outside of the normal application/config
     * directory structure
     */
    function _load_config($filename) {
        include($filename);
        foreach ($config as $key => $value) {
            $this->config->set_item($key, $value);
        }
    }

    /**
     * Load the correct language files for the interface
     */
    function _load_languages() {
        // Set the language if ?locale query string set
        $this->uilang = array();
        $get_lang = $this->input->get('lang');
        if (!empty($get_lang)) {
            if ($this->_is_valid_language($this->input->get('lang'))) {
                $_SESSION['skylight_language'] = $this->input->get('lang');
            }
        }

        // First load the default language file
        $this->_load_lang($this->config->item('skylight_language_default'));

        // If it is set (and not already loaded), load the language file from the session
        if ((isset($_SESSION['skylight_language'])) &&
            ($this->config->item('skylight_language_default') != $_SESSION['skylight_language'])) {
            $this->_load_lang($_SESSION['skylight_language']);
        } else {
            // TODO: Enable reading of browser locale
        }
    }

    function _load_lang($lang_code) {
        $local_path = $this->config->item('skylight_local_path');
        $theme = $this->_get_theme();
        if(!empty($local_path) && file_exists($local_path.'/language/'.$theme.'/'.$lang_code.'/skylight_lang.php'))
            require_once($local_path.'/language/'.$theme.'/'.$lang_code.'/skylight_lang.php');
        else
            require_once('./application/language/'.$lang_code.'/skylight_lang.php');
        $this->uilang = array_merge($this->uilang, $text);

    }

    function _is_valid_language($language) {
        return in_array($language, $this->config->item('skylight_language_options'));
    }

    function _conneg($in) {
        if ($this->_endswith($in, '.json')) {
            $this->output_type = 'json';
            return substr($in, 0, strlen($in) - 5);
        } else if ($this->_endswith($in, '.csv')) {
            $this->output_type = 'csv';
            return substr($in, 0, strlen($in) - 4);
        } else if ($this->_endswith($in, '.xml')) {
            $this->output_type = 'xml';
            return substr($in, 0, strlen($in) - 4);
        }

        //TODO True content negotiation based on 'Accepts' header

        return $in;
    }

    function _clean($in) {
        // Clean up any input
        $in = strip_tags($in);
        $in = htmlentities($in);
        $in = trim($in);
        return $in;
    }

    function _escape($in) {
        // Simple string escaping for post keys, form ids, etc.
        // (not full urlencode)
        $in = preg_replace('# #','_',$in,-1);
        return $in;
    }

    function _unescape($in) {
        // Simple string escaping for post keys, form ids, etc.
        // (not full urlencode)
        $in = preg_replace('#_#',' ',$in,-1);
        return $in;
    }

    function _adminInterface() {
        // Set this for admin pages than need authenticating
        $this->adminInterface = true;
    }

    function _endswith($haystack, $needle) {
        return strrpos($haystack, $needle) === strlen($haystack) - strlen($needle);
    }
}