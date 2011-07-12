<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class skylight extends CI_Controller {

    // Whether this page is part of the administrative interface
    var $adminInterface = false;

    // The language being used in the user interface
    public $uilang;

    function skylight() {
        // Initalise the parent
        parent::__construct();

        // Load the EasyDeposit config
        $this->config->load('skylight');

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

        // Load the host-specific configuration unless it is overridden in the URL or the session
        $hostname = $_SERVER['HTTP_HOST'];
        $get_config = preg_replace('/[^A-Za-z0-9-_\.]/', '', $this->input->get('config'));
        if (!empty($get_config)) {
            $load_config = $get_config;
        } else if (isset($_SESSION['skylight_config'])) {
            $load_config = $_SESSION['skylight_config'];
        }
        if ((!empty($load_config)) &&
            ($this->config->item('skylight_config_allowoverride') === TRUE) &&
            (file_exists('./application/config/sites/' . $load_config . '.php'))) {
            $this->config->load('sites/' . $load_config);
            $_SESSION['skylight_config'] = $load_config;
        }
        else if (file_exists('./application/config/sites/' . $hostname . '.php')) {
            $this->config->load('sites/' .$hostname);
        } else if (file_exists('./application/config/sites/default.php')) {
            // Load the default config
            $this->config->load('sites/default');
        }
        else {
            show_error('Unknown skylight virtual host: application/config/sites/' . $this->_clean($hostname) .
                       '.php or missing default configuration at application/config/sites.default.php', 500);
            die();
        }

        // Set the language if ?local query string set
        $this->uilang = array();
        $get_lang = $this->input->get('lang');
        if (!empty($get_lang)) {
            if ($this->_is_valid_language($this->input->get('lang'))) {
                $_SESSION['skylight_language'] = $this->input->get('lang');
            }
        }

        // First load the default language file
        $this->_load_lang($this->config->item('skylight_language_default'));

        // If it is set, load the language file from the session
        if (isset($_SESSION['skylight_language'])) {
            $this->_load_lang($_SESSION['skylight_language']);
        } else {
            // TODO: Enable reading of browser locale
        }

        // Decide whether to enable caching, and if so, for how many minutes
        if (is_numeric($this->config->item('skylight_cache'))) {
            //$this->output->cache($this->config->item('skylight_cache'));
        }

        // Load the solr library
        $this->load->library('solr/solr_client');
    }

    function view($view, $data = array()) {
        // Load some globals
        $data['site_title'] = $this->config->item('skylight_fullname');

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

        // Does the theme override this page?
        if (file_exists('./application/views/theme/' . $theme . '/' . $view . '.php')) {
            $this->load->view('theme/' . $theme . '/' . $view, $data);
        }
        elseif (file_exists('./application/views/theme/default/' . $view . '.php')) {
            $this->load->view('theme/default/' . $view, $data);
        }
         else {
            $this->load->view($view, $data);
        }
    }

    function index()
    {
        // Go home, nothing to do here
        redirect('/');
    }

    function _load_lang($lang_code) {
        require_once('./application/language/' . $lang_code . '/skylight_lang.php');
        $this->uilang = array_merge($this->uilang, $text);
    }

    function _is_valid_language($language) {
        return in_array($language, $this->config->item('skylight_language_options'));
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
}