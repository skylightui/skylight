<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class redirect extends skylight {

    function index() {
        // Redirect to the relevant search page (/search/query) rather than /search/?q=query
        if (!empty($_POST['q'])) {
            redirect('/search/' . $_POST['q']);
        } else {
            redirect('/search/');
        }
    }
}

?>