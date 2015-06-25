<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class Autocomplete extends skylight {

    function Autocomplete() {
        // Initalise the parent
        parent::__construct();
    }

	public function index() {

        log_message('debug', "search autocomplete initialized");

        $term = $this->input->get('term');
        $term = rawurlencode($term);
        $term_lower = strtolower($term);

        //$solr_xml = file_get_contents($this->config->item('skylight_solrbase') . 'terms?terms=true&terms.fl='.$field.'&terms.prefix='.$term.'&terms.lower.incl=false&terms.regex.flag=case_insensitive&indent=true&wt=json');
        $solr_xml = file_get_contents($this->config->item('skylight_solrbase') . 'terms?terms=true&terms.fl=search_ac&terms.regex=^'.$term_lower.'.*&terms.lower.incl=false&terms.regex.flag=case_insensitive&indent=true&wt=json');

        $ac_json = json_decode($solr_xml);

        echo '[';
        for($i = 0; $i < sizeof($ac_json->terms->{'search_ac'}); $i += 2) {
            if($ac_json->terms->{'search_ac'}[$i+1] > 5) {
                echo '"'.$ac_json->terms->{'search_ac'}[$i].'"';
                if($i+2 < sizeof($ac_json->terms->{'search_ac'})) {
                    if($ac_json->terms->{'search_ac'}[$i+3] > 5) {
                        echo ', ';
                    }
                }
            }
        }
        echo ']';

	}
}