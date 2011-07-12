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

}