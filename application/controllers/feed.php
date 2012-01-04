<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class Feed extends skylight {

    function Feed() {
        // Initalise the parent
        parent::__construct();
    }

	public function index() {
        // Setup some other variables
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if (strpos($url, '?') === true) {
            $home = substr($url, 0, strpos($url, '?'));
        } else {
            $home = $url;
        }
        $base = substr($home, 0, strpos($home, 'feed'));

        // Get the variables
        $data['feed_title'] = $this->config->item('skylight_fullname');
        $data['feed_fields'] = $this->config->item('skylight_feed_fields');

        $data['feed_description'] = 'Feed for the ' . $this->config->item('skylight_fullname');
        $data['feed_home'] = $home;
        $data['feed_base'] = $base;
        $data['feed_items'] = $this->solr_client->getRecentItems(20);

        // Set the headers
        header('Content-type: application/rss+xml');
        echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";

        // Show the feed
        $this->view('rss2.php', $data);
    }
}
?>