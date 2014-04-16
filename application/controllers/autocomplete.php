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
        $field = $this->input->get('field');
        $term_lower = strtolower($term);

        //$solr_xml = file_get_contents($this->config->item('skylight_solrbase') . 'terms?terms=true&terms.fl='.$field.'&terms.prefix='.$term.'&terms.lower.incl=false&terms.regex.flag=case_insensitive&indent=true&wt=json');
        $solr_xml = file_get_contents($this->config->item('skylight_solrbase') . 'terms?terms=true&terms.fl=title_ac&terms.prefix='.$term_lower.'&terms.lower.incl=false&terms.regex.flag=case_insensitive&indent=true&wt=json');

        $ac_json = json_decode($solr_xml);

        echo '[';
        for($i = 0; $i < sizeof($ac_json->terms->{'title_ac'}); $i += 2) {
            echo '"'.$ac_json->terms->{'title_ac'}[$i].'"';
            if($i+2 < sizeof($ac_json->terms->{'title_ac'})) {
                echo ', ';
            }
        }
        echo ']';

	}
}