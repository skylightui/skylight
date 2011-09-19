<?php

require_once("skylight.php");

class Admin extends skylight {

    function Admin() {
        // State that this is an authentication class
        skylight::_adminInterface();

        // Initalise the parent
        parent::__construct();
    }

    public function index() {
        $data['page_title'] = 'Admin: Dashboard: Menu';
        $data['theme'] = $this->_get_theme();

        $this->view("header", $data);
        $this->view('div_main', $data);
        $this->view("admin/admin_home", $data);
        $this->view('div_main_end');
        $this->view("footer");
    }

    public function logout() {
        // Unset the admin session variable
        unset($_SESSION['skylight-admin-isadmin-' . base_url()]);

        // Go to the home page
        redirect('/');
    }

	public function displayconfig() {
        $config_array = $this->config->config;

        $data['config_array'] = $config_array;
        $data['page_title'] = 'Admin: Dashboard: Display Configuration';

        $this->view("header", $data);
        $this->view('div_main', $data);
        $this->view("admin/admin_display_config", $data);
        $this->view('div_main_end');
        $this->view("footer");
    }

    public function content() {
        $mode = $this->input->get('mode');
        $content = $this->input->get('file');

        // Check that there are no periods ('.') in the URL - possible hack attack?
        // And ensure that the page exists
        if (!(strpos($content, '.') === False)) {
            $data['page_title'] = 'Admin: Invalid page!';

            $this->view("header", $data);
            $this->view('div_main', $data);
            $this->view('div_main_end');
            $this->view("footer");
        }

        switch ($mode) {
            case 'edit':
                break;
            case 'add':
                break;
            case 'delete':
                echo 'Whoops - you just deleted ' . $content . '<p>Only kidding!';
                break;
            default:
                  $data['page_title'] = 'Admin: Invalid mode!';

                  $this->view("header", $data);
                  $this->view('div_main', $data);
                  $this->view('div_main_end');
                  $this->view("footer");
        }
        //$local_path = $this->config->item('skylight_local_path');
        //$found = false;
    }

}