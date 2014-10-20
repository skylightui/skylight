<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class redirect extends skylight {

    function index() {

        $url_prefix = $this->config->item('skylight_url_prefix');
        if (!empty($url_prefix))
        {
            $url_prefix = '/'.$url_prefix;
        }

        // Redirect to the relevant search page (/search/query) rather than /search/?q=query
        if (!empty($_REQUEST['q'])) {
            redirect($url_prefix.'/search/' . urlencode($_REQUEST['q']));
        } else {
            redirect($url_prefix.'/search/');
        }
    }
}

?>