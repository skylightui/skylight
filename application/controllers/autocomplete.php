<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('skylight.php');

class Autocomplete extends skylight {

    function Autocomplete() {
        // Initalise the parent
        parent::__construct();
    }

	public function index() {

        $term = $this->input->get('term');
        $field = $this->input->get('field');

        $solr_xml = file_get_contents($this->config->item('skylight_solrbase') . 'terms?terms=true&terms.fl='.$field.'&terms.prefix='.$term.'&terms.lower.incl=false&terms.regex.flag=case_insensitive&indent=true&wt=json');

        $ac_json = json_decode($solr_xml);

        echo '[';
        for($i = 0; $i < sizeof($ac_json->terms[1]); $i += 2) {
            echo '"'.$ac_json->terms[1][$i].'"';
            if($i+2 < sizeof($ac_json->terms[1])) {
                echo ', ';
            }
        }
        echo ']';

	}
}