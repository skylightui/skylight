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
            return;
        }

        if ($mode != 'edit') {
            $data['page_title'] = 'Admin: Invalid mode!';

            $this->view("header", $data);
            $this->view('div_main', $data);
            $this->view('div_main_end');
            $this->view("footer");
            return;
        }

        /**
         * Might want this code in the future for deletes.
        if ($mode == 'delete') {
            $local_path = $this->config->item('skylight_local_path');
            if (file_exists($local_path . '/static/' . $this->config->item('skylight_appname') . '/' . $content . '.php')) {
                $load = $local_path . '/static/' . $this->config->item('skylight_appname') . '/' . $content . '.php';
            } else if (file_exists('./application/views/static/' . $this->config->item('skylight_appname') . '/' . $content. '.php')) {
                $load = './application/views/static/' . $this->config->item('skylight_appname') . '/' . $content. '.php';
            }

            unlink($load);
            redirect('/admin/');
            return;
        }
        */

        $data['page_title'] = 'Admin: Edit - ' . $content;
        $data['content'] = $content;

        $local_path = $this->config->item('skylight_local_path');
        if (file_exists($local_path . '/static/' . $this->config->item('skylight_appname') . '/' . $content . '.php')) {
            $load = $local_path . '/static/' . $this->config->item('skylight_appname') . '/' . $content . '.php';
        } else if (file_exists('./application/views/static/' . $this->config->item('skylight_appname') . '/' . $content. '.php')) {
            $load = './application/views/static/' . $this->config->item('skylight_appname') . '/' . $content. '.php';
        }

        $data['html'] = file_get_contents($load);

        $this->view("admin/admin_header", $data);
        $this->view('div_main', $data);
        $this->view('admin/admin_content_editor', $data);
        $this->view('div_main_end');
        $this->view("footer");
    }

    public function savecontent() {
        $content = $_POST['content'];
        $local_path = $this->config->item('skylight_local_path');
        if (file_exists($local_path . '/static/' . $this->config->item('skylight_appname') . '/' . $content . '.php')) {
            $save = $local_path . '/static/' . $this->config->item('skylight_appname') . '/' . $content . '.php';
        } else if (file_exists('./application/views/static/' . $this->config->item('skylight_appname') . '/' . $content. '.php')) {
            $save = './application/views/static/' . $this->config->item('skylight_appname') . '/' . $content. '.php';
        }

        $html = html_entity_decode($_POST['html']);
        // Decode a second time to cope with the XSS protection applied by CodeIgniter
        $html = html_entity_decode($html);
        $html = str_replace('&#39;', "'", $html);
        file_put_contents($save, $html);
        redirect('/admin/');
    }

}