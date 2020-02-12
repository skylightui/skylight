<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');
require_once('./application/libraries/recaptcha-php-1.11/recaptchalib.php');

class Feedback extends skylight {

    function Feedback() {
        // Initalise the parent
        parent::__construct();
    }

    public function index() {
        $data['recaptcha_key_public'] = $this->config->item('skylight_recaptcha_key_public');

        // Determine the page title and heading.
        $page_title_prefix = $this->config->item('skylight_page_title_prefix');
        if( !isset($page_title_prefix) ) {
            $page_title_prefix = "";
        }

        $data['page_title'] = $page_title_prefix.'Feedback';

        $url_prefix = $this->config->item('skylight_url_prefix');
        $form_prefix = "";
        if (!empty($url_prefix))
        {
            $form_prefix = $url_prefix . '/';
        }

        $data['form_prefix'] = $form_prefix;

        $this->view('header', $data);
        if ($this->config->item('skylight_homepage_fullwidth') === TRUE) {
            $this->view('div_main_full', $data);
        }
        else {
            $this->view('div_main', $data);
        }

        // Verify the form inputs
        $this->form_validation->set_rules('name', 'Name', 'trim|_clean|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|_clean|required|valid_email');
        $this->form_validation->set_rules('feedback', 'Feedback', 'trim|_clean|required');
        $this->form_validation->set_rules('recaptcha_response_field', '', 'callback__check_captcha');
        if ($this->form_validation->run() == FALSE)
        {
            // Errors in the form (or first time it has been requested), re-display email form
            $this->view('feedback', $data);
        } else {
            // Send the email
            $this->load->library('email');
            $this->email->from(set_value('email'), set_value('name'));
            $this->email->to($this->config->item('skylight_adminemail'));
            $this->email->subject('Feedback from ' . $this->config->item('skylight_fullname'));
            $this->email->message(set_value('feedback'));
            $this->email->send();
            $this->view('feedbackthanks');
        }

        if ($this->config->item('skylight_facets_in_main')) {
            $this->view('div_sidebar');
            $this->view('div_sidebar_end');
            $this->view('div_main_end');
        }
        else {
            $this->view('div_main_end');
            $this->view('div_sidebar');
            $this->view('div_sidebar_end');
        }

        $this->view('footer');        
    }

    function _check_captcha($input) {
        $recaptcha_key_private = $this->config->item('skylight_recaptcha_key_private');
        $resp = recaptcha_check_answer ($recaptcha_key_private,
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);
        if (!$resp->is_valid) {
            $this->form_validation->set_message('_check_captcha', 'Verification words entered incorrectly.');
            return FALSE;
        } else {
            return TRUE;
        }

    }
}
