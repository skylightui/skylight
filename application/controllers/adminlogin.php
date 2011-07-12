<?php

require_once('skylight.php');

class AdminLogin extends skylight {

    function AdminLogin() {
        // Initalise the parent
        parent::__construct();
    }

    function index() {
        // Validate the form
        $this->form_validation->set_rules('username', 'Username', '_clean|required');
        $this->form_validation->set_rules('password', 'Password', '_clean|callback__adminlogin|required');
        if ($this->form_validation->run() == FALSE) {
            // Set the page title
            $data['page_title'] = 'Login to the Administrative Interface';

            $this->view("header", $data);
            $this->view('div_main', $data);
            $this->view("admin/login_form.php", $data);
            $this->view('div_main_end');
            $this->view("footer");
        }
        else {
            // Record the fact they are logged in
            $_SESSION['skylight-admin-isadmin-' . base_url()] = true;

            // Go to the admin home page
            redirect('/admin');
        }
    }

    function _adminlogin($password) {
        // Get the username
        $username = $_POST['username'];

        // Should we authenticate with LDAP?
        $useLDAP = $this->config->item('skylight_adminldap');
        if ((!empty($useLDAP)) &&
            ($useLDAP === TRUE)) {
            // Are they an allowed LDAP user?
            if (!in_array($username, $this->config->item('skylight_adminldap_allowed'))) {
                $this->form_validation->set_message('_adminlogin', 'Unauthorised user');
                    return FALSE;
            }

            try {
                // Bind to the DAP server using the user's credentials
                $password = $_POST['password'];
                $ldaphost = $this->config->item('skylight_adminldap_server');
                $ldapcontext = $this->config->item('skylight_adminldap_context');
                $ldap = ldap_connect($ldaphost);
                ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
                $bind = @ldap_bind($ldap, "cn=$username,$ldapcontext", $password);

                // Search for the user by their netid
                $searchresult = @ldap_search($ldap, $ldapcontext, "cn=$username");
                $items = @ldap_get_entries($ldap, $searchresult);

                // If no items are returned, the login must have been bad
                if ($items['count'] == 0) {
                    $this->form_validation->set_message('_adminlogin', 'Bad username or password');
                    return FALSE;
                }
            }
            catch (Exception $exception) {
                // Something went wrong with connecting to LDAP
                $this->form_validation->set_message('_adminlogin', 'Unable to connect to login server');
                return FALSE;
            }
        } else {
            // Check the username and password are correct
            if (($username != $this->config->item('skylight_adminusername')) ||
                (md5($password) != $this->config->item('skylight_adminpassword'))) {
                $this->form_validation->set_message('_adminlogin', 'Bad username or password');
                return FALSE;
            }
        }

        // Must be OK
        return TRUE;
    }

}

?>