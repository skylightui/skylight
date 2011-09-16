<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class Pingback extends skylight {

    function Pingback() {
        // Initalise the parent
        parent::__construct();
    }

    function index() {
        // Load the xmlrpc classes
        $this->load->library('xmlrpc');
        $this->load->library('xmlrpcs');

        //$this->xmlrpc->set_debug(true);

        // Configure the pingback call
        $config['functions']['pingback.ping'] = array('function' => 'Pingback._ping');
        $config['object'] = $this;

        // Run...
        $this->xmlrpcs->initialize($config);
        $this->xmlrpcs->serve();
    }

    function _ping($request) {
        // Load the xmlrcp class
        $this->load->library('xmlrpc');
        $parameters = $request->output_parameters();

        // Send the email alert
        $this->load->library('email');

        $email = $this->config->item('skylight_adminemail');
        $emailname = $this->config->item('skylight_fullname');
        $this->email->from($email, $emailname);
        $this->email->to($email);

        $this->email->subject('Email Test');

        $message = "Hi!\n\n";
        $message .= "Someone has blogged or linked to one of your collection pages:\n";
        foreach($parameters as $parameter) {
            $message = $message . "\n - " . $parameter;
        }
        $message .= "\n\nThanks!";

        $this->email->message($message);
        $this->email->send();

        // Say thank you
        $response = array(
                     array('flerror' => array(FALSE, 'boolean'),
                           'message' => "Thanks for the ping!"),
                     'struct');
        return $this->xmlrpc->send_response($response);
    }

    /**
     * Uncomment this method to test, and visit http://yoursite/skylight/pingback/test
     * 
    function test() {
        $this->load->library('xmlrpc');
        $this->xmlrpc->set_debug(true);

        $this->xmlrpc->server('http://localhost/skylight/pingback', 80);
        $this->xmlrpc->method('pingback.ping');

        $request = array('http://blog.example.com/', 'http://localhost/skylight/record/1');
        $this->xmlrpc->request($request);

        if (!$this->xmlrpc->send_request())
        {
            echo $this->xmlrpc->display_error();
        }
    }
    */
}
?>