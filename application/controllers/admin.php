<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kshe085
 * Date: 20/05/11
 * Time: 3:26 PM
 * To change this template use File | Settings | File Templates.
 */
 require_once("skylight.php");

class Admin extends skylight {

    function Admin() {
        // Initalise the parent
        parent::__construct();
        $admins = $this->config->item('skylight_administrators');
        if(!in_array($this->input->ip_address(),$admins)) {
            echo $this->input->ip_address().' is not allowed here, sorry. Contact your administrator';
            exit;
        }
    }

	public function index() {
        $config_array = $this->config->config;

        $data['config_array'] = $config_array;
        $data['page_title'] = 'Admin: Dashboard: Configuration';

        $this->view("header", $data);
        $this->view('div_main', $data);
        $this->view("admin_display_config", $data);
        $this->view('div_main_end');
        $this->view("footer");
    }

}