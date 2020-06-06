<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kshe085
 * Date: 27/04/11
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */

class Solr_client_dspace_6 {

    var $base_url       = 'http://localhost:9136/solr';  // Base URL. Typically not overridden in construct params. Get from config.
    var $max_rows       = 100; // Default to 100 rows maximum
    var $container     = '*'; // Default to all collections
    var $container_field = 'location.coll'; // Default to discovery's DSpace collection field
    var $handle_prefix  = '10683';
    var $scope          = '';
    var $rows           = 10;
    var $recorddisplay  = array();
    var $searchresultdisplay = array();
    var $configured_filters = array();
    var $configured_date_filters = array();
    //SR- 10/12/19-  unsetting
    //var $date_field = 'dc.date.year';
    var $delimiter      = '';
    var $thumbnail_field = '';
    var $bitstream_field = '';
    var $display_thumbnail = false;
    var $link_bitstream = false;
    var $dictionary = '';
    var $fields = array();
    var $facet_limit = 10;


    /**
     * Constructor
     *
     * @access	public
     * @param	array	initialization parameters
     */
    public function __construct($params = array())
    {
        if (count($params) > 0)
        {
            $this->initialize($params);
        }

        $CI =& get_instance();
        $this->base_url = $CI->config->item('skylight_solrbase');
        $this->container = $CI->config->item('skylight_container_id');
        $this->container_field = $CI->config->item('skylight_container_field');
        $this->rows = $CI->config->item('skylight_results_per_page');
        $this->recorddisplay = $CI->config->item('skylight_recorddisplay');
        $this->searchresultdisplay = $CI->config->item('skylight_searchresult_display');
        $this->configured_filters = $CI->config->item('skylight_filters');
        $this->configured_date_filters = $CI->config->item('skylight_date_filters');
        $this->related_fields = $CI->config->item('skylight_related_fields');
        $this->delimiter = $CI->config->item('skylight_filter_delimiter');
        $this->bitstream_field = str_replace('.','',$CI->config->item('skylight_fulltext_field'));
        $this->thumbnail_field = str_replace('.','',$CI->config->item('skylight_thumbnail_field'));
        $this->dictionary = $CI->config->item('skylight_solr_dictionary');
        $this->fields = $CI->config->item('skylight_fields');
        $this->facet_limit = $CI->config->item('skylight_facet_limit');
        $date_fields = $this->configured_date_filters;
        if(count($date_fields) > 0) {
            $this->date_field = array_pop($date_fields);
        }

        log_message('debug', "skylight Solr Client Initialized");
    }

    // --------------------------------------------------------------------

    /**
     * Initialize Preferences
     *
     * @access	public
     * @param	array	initialization parameters
     * @return	void
     */
    function initialize($params = array())
    {
        if (count($params) > 0)
        {
            foreach ($params as $key => $val)
            {
                if (isset($this->$key))
                {
                    $this->$key = $val;
                }
            }
        }
    }

    function solrEscape($in) {
    	//SR - this "comment out" comes directly from v5 library.
    	//Shows that an overall urldecode or encode is often not suitable.
        //$in = urldecode($in);
        $in = preg_replace('/#([^0-9])/',"$1",$in);
        $in = preg_replace('/\(/',"\\\(",$in);
        $in = preg_replace('/\)/',"\\\)",$in);
        $in = preg_replace('/&#40;/',"\\\(",$in);
        $in = preg_replace('/&#41;/',"\\\)",$in);
        $in = preg_replace('/!/',"",$in);
        $in = preg_replace('/@/',"",$in);
        $in = preg_replace('# #', '+', $in);
        $in = preg_replace('#%20#', '+', $in);

        return $in;
    }

    function eventSearch($q = '*:*', $fq = array(), $rows = 1000) {

        $title = $this->recorddisplay['Title'];

        if($q == '*' || $q == '') {
            $q = '*:*';
        }

        $url = $this->base_url . "select?q=" . $this->solrEscape($q);
        if(count($fq) > 0) {
            foreach($fq as $value)
                $url .= '&fq='.$this->solrEscape($value).'';
        }

        // Set up scope
        $url .= '&fq='.$this->container_field.':'.$this->container;
        $url .= '&fq=search.resourcetype:2';
        $url .= '&rows='.$rows;

        $solr_xml = file_get_contents($url);
        $search_xml = @new SimpleXMLElement($solr_xml);

        $docs = array();

        // Build search results from solr response

        foreach ($search_xml->result->doc as $result) {
            $doc = array();
            foreach($result->arr as $multivalue_field) {
                $key = $multivalue_field['name'];
                foreach($multivalue_field->str as $value) {
                    $doc[str_replace('.', '', $key)] = $value;
                }
                foreach($multivalue_field->int as $value) {
                    $doc[str_replace('.', '', $key)] = $value;
                }
                foreach($multivalue_field->date as $value) {
                    $doc[str_replace('.', '', $key)] = $value;
                }

            }

            foreach($result->str as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)]= $value;

            }

            foreach($result->int as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)]= $value;

            }

            foreach($result->date as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)]= $value;

            }


            $raw_hdl = $doc['handle'] . '';

            $handle = preg_split('/\//',$raw_hdl);
            $doc['id'] = $handle[1];
            if(!array_key_exists($title,$doc)) {
                $doc[$title][] = 'No title';
            }
            $docs[] = $doc;
        }

        $data['docs'] = $docs;
        $data['rows'] = $search_xml->result['numFound'];

        return $data;
    }

    function simpleSearch($q = '*:*', $offset = 1, $fq = array(), $operator = 'OR', $sort_by = 'score+desc', $rows = 0)
    {

        if($rows==0) {
            $rows = $this->rows;
        }

        $sort_by = str_replace(' ','+',$sort_by);

        // Returns $data containing search results and facets
        // See search.php controller for example of usage

        $title = 'dctitle';

        if($q == '*' || $q == '') {
            $q = '*:*';
        }
        //SR additional else for brackets
        else{
            $q = str_replace('(', '%28', $q);
            $q = str_replace(')', '%29', $q);
        }

        $url = $this->base_url . "select?q=" . $this->solrEscape($q);


        if(count($fq) > 0) {
            foreach($fq as $value) {
                //SR 10/12/2019 change to append a * to filter- obviously this causes problems if there is a colon in the value
                $value = str_replace(":",":*", $this->solrEscape($value));
                $value = str_replace('\(', '%28', $value);
                $value = str_replace('\)', '%29', $value);
                $url .= '&fq='.$this->solrEscape($value).'*';
            }
        }

        //Date range processing slightly different in DSpace 6.
        /*SR 15/12/2019 - removed.
        $ranges = array();
        if (isset($this->configured_date_filters))
        {
            foreach($this->configured_date_filters as $filter_name => $filter) {
                array_push($ranges,$this->getDateRanges($filter));
            }
        }
        */

        //SR - 15/12/2019 - added from here to...
        foreach($this->configured_date_filters as $filter_name => $filter)
        {
            if (strpos($filter_name, $fq[0]) !== false)
            {
                //$dates = $this->getDateRanges($this->date_field, $q, $fq);
                $dates = $this->getDateRanges($filter_name, $q, $fq);
                $ranges = $dates['ranges'];
                $datefqs = $dates['fq'];
                //this was not in 181- the if statement was
                foreach ($datefqs as $datefq) {
                    $url .= '&fq=' . $datefq;
                }
            }
        }
        //else
        //{
        $ranges = array();
        //}
        //...here.

        //SR- 15/12/2019 the above replaces this.
        /*
        $dates = $this->getDateRanges($this->date_field, $q, $fq);
        $ranges = $dates['ranges'];
        $datefqs = $dates['fq'];

        foreach($datefqs as $datefq) {
            // $url .= '&fq='.$datefq;
        }*/

       // Set up scope

       $url_item = urlencode($this->container);
       $url .= '&fq='.$this->container_field.':'.$url_item;
       $url .= '&fq=search.resourcetype:2';
       $url .= '&sort='.$sort_by;

       //Commented out in v5
       //$url .= '&rows='.$this->rows.'&start='.$offset.'&facet.mincount=1';
       //kshe085 - configurable rows for record searching thingy as per ian 2015-01-23


       $url .= '&rows='.$rows.'&start='.$offset.'&facet.mincount=1';
       $url .= '&facet=true&facet.limit='.$this->facet_limit;
       foreach($this->configured_filters as $filter_name => $filter) {
           $url .= '&facet.field='.$filter;
       }

       foreach($ranges as $range) {
           $url .= '&facet.query='.$range;
       }
       $url .= '&q.op='.$operator;

       //Set up highlighting
       //SR CHANGE to get working (was causing encoding issues)- should reinstate <strong> aspect
       //$url .= '&hl=true&hl.fl=*.en&hl.simple.pre=<strong>&hl.simple.post=</strong>';
       $url .= '&hl=true&hl.fl=*.en&hl.simple.pre=&hl.simple.post=';


       // Set up spellcheck
       if($this->dictionary != '')
       {
           $url .= '&spellcheck=true&spellcheck.collate=true&spellcheck.onlyMorePopular=false&spellcheck.count=5';
           $url .= '&spellcheck.dictionary=' . $this->dictionary;
       }
       else {
           $url .= '&spellcheck=false';
       }

       //SR- 10/12/2019 - more decoding
       $url = str_replace('+%7C%7C%7C+','%0A%7C%7C%7C%0A', $url);
       $url = str_replace('+', '%20', $url);

       $url = str_replace('dc.date.issued.year:[0%20TO%200]', 'dc.date.issued.year:[0+TO+0]', $url);

       //function url_encode($string){
       //   return urlencode(utf8_encode($string));
       // }

        $vars = '';
        $con = curl_init($url);
        curl_setopt($con, CURLOPT_POST, 1);
        curl_setopt($con, CURLOPT_POSTFIELDS, $vars);
        curl_setopt($con, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($con, CURLOPT_HEADER, 0);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, 1);

        $solr_xml = curl_exec($con);

        //$solr_xml = file_get_contents($url);

        //$url_encoded = urlencode(utf8_encode($url));
        //$solr_xml = file_get_contents($url_encoded);

        $search_xml = @new SimpleXMLElement($solr_xml);

        $docs = array();
        $facet = array();
        $facets = array();


        // Build search results from solr response
        foreach ($search_xml->result->doc as $result) {
            $doc = array();
            foreach($result->arr as $multivalue_field) {
                $key = $multivalue_field['name'];
                foreach($multivalue_field->str as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
                foreach($multivalue_field->int as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }

            }

            foreach($result->str as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)]= $value;

            }

            // kshe085 why weren't we doing this for int too? 20150528
            foreach($result->int as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)]= $value;

            }


            $raw_hdl = (string) $doc['handle'];
            $handle = preg_split('/\//',$raw_hdl);

            // Build highlight results from solr response
            if($search_xml->xpath("//lst[@name='highlighting']/lst" !== null)) {
                foreach ($search_xml->xpath("//lst[@name='highlighting']/lst[@name='".$raw_hdl."']/arr/str") as $highlight) {
                    //echo $doc['handle'][0].': '.$highlight.'<br/>';
                    $doc['highlights'][] = $highlight;
                }
            }


            $raw_hdl = $doc['handle'] . '';

            $handle = preg_split('/\//',$raw_hdl);
            $doc['id'] = $handle[1];
            /* kshe085 - temp removal of title force
            if(!array_key_exists($title,$doc)) {
                $doc[$title][] = 'No title';
            }
            kshe085 - end tem removal */
            $docs[] = $doc;
        }


        // get spellcheck collated suggestion
        $suggestion = "";
        $spellcheck = $search_xml->xpath("//lst[@name='spellcheck']/lst[@name='suggestions']/str[@name='collation']");
        if($spellcheck != NULL && sizeof($spellcheck) > 0)
            $suggestion = $spellcheck[0];

        $data['suggestion'] = $suggestion;
        $data['docs'] = $docs;
        $data['rows'] = $search_xml->result['numFound'];



        // Hard coded until I do something better
        foreach($this->configured_filters as $filter_name => $filter) {
            $facet_xml = $search_xml->xpath("//lst[@name='facet_fields']/lst[@name='".$filter."']/int");
            $facet['name'] = $filter_name;
            $terms = array();
            $queries = array();
            // Build facets from solr response
            foreach ($facet_xml as $facet_term) {

                //v5 change
                //$names = preg_split('/\|\|\|/',$facet_term->attributes());

                $term['name'] = urlencode($facet_term->attributes());
                $term['name'] = preg_replace('/%2C/',',',$term['name']);
                $term['name'] = preg_replace('/%28/','&#40;',$term['name']);
                $term['name'] = preg_replace('/%29/','&#41;',$term['name']);
                $term['display_name'] = $facet_term->attributes();
                //$term['norm_name'] = urlencode($names[0]);
                $term['count'] = $facet_term;
                $active_test = $filter.$this->delimiter.'%22'.$term['name'].'%22';

                if (in_array($active_test, $fq)) {
                    $term['active'] = true;
                } else {
                    $term['active'] = false;
                }

                $terms[] = $term;
            }
            $facet['terms'] = $terms;
            $facet['queries'] = array();
            $facets[] = $facet;

        }


        foreach($this->configured_date_filters as $filter_name => $filter) {
            // Date.. needs facet query, not field, since
            // we're on solr 1.4 and can't do nice easy integer ranges
            $facet_xml = $search_xml->xpath("//lst[@name='facet_queries']/int");
            $facet['name'] = $filter_name;
            $queries = array();
            $terms = array();
            foreach($facet_xml as $facet_query) {
                $query_norm_name = '';
                $query_display_name = '';
                $query_name = $facet_query->attributes();
                preg_match_all('#\d{4}#',$query_name, $matches);
                if(count($matches) > 0) {
                    if(count($matches[0]) > 0) {
                        if($matches[0][0] == $matches[0][1]) {
                            $query_display_name = $matches[0][0];
                        }
                        else {
                            $query_display_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#','\1 - \2',$query_name);
                        }
                        $query_norm_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#','[\1+TO+\2]',$query_name);
                    }
                }
                //v5 commment outs
                //$query_display_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#','\1 - \2',$query_name);
                //$query_norm_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#','[\1%20TO%20\2]',$query_name);
                $query['name'] = $query_norm_name;
                $query['display_name'] = $query_display_name;
                $query['norm_name'] = $query_norm_name;
                $query['count'] = $facet_query;
                $active_test = $filter.$this->delimiter.$query_norm_name;
                if(in_array($active_test, $fq)) {
                    $query['active'] = true;
                }
                else {
                    $query['active'] = false;
                }

                $queries[] = $query;

            }
            $facet['queries'] = $queries;
            $facet['terms'] = array();
            $facets[] = $facet;
        }

        $data['facets'] = $facets;

        return $data;

    }

    function getFacets($q = '*:*', $fq = array(), $saved_filters = array())
    {

        $query = $q;
        if($q == '*') {
            $q = '*:*';
        }
        $url = $this->base_url . "select?q=" . $q;
        //echo 'COUNT FQ'.count($fq);
        if(count($fq) > 0) {
            foreach($fq as $value)
                $url .= '&fq='.$value.'';
        }

        /* v5 comment outs
        $ranges = array();
        foreach($this->configured_date_filters as $filter_name => $filter) {
            array_push($ranges,$this->getDateRanges($filter));
        }

         */
        //SR - 15/12/2019 -more date range changes
        //New from here to...
        if (isset($this->date_field))
        {
            $dates = $this->getDateRanges($this->date_field, $q, $fq);
            $ranges = $dates['ranges'];
            $datefqs = $dates['fq'];
            //this was not in 181- the if statement was
            foreach($datefqs as $datefq) {
                $url .= '&fq='.$datefq;
            }
        }
        else
        {
            $ranges = array();
        }

        //...Here.


        //SR- commenting this out and replacing with procedure above
        //$dates = $this->getDateRanges($this->date_field, $q, $fq);
        //$ranges = $dates['ranges'];

        $url .= '&fq='.$this->container_field.':'.$this->container;
        $url .= '&fq=search.resourcetype:2&rows=0&facet.mincount=1';
        $url .= '&facet=true&facet.limit='.$this->facet_limit;

        foreach($this->configured_filters as $filter_name => $filter) {
            $url .= '&facet.field='.$filter;
        }
        foreach($this->configured_date_filters as $filter_name => $filter) {
            $url .= '&facet.field='.$filter;
        }

        //SR commenting this out- it does not work with the v6 solr
        /*foreach($ranges as $range) {
            // $url .= '&facet.query='.$range;
        }
*/
        //SR - some file logging as this cannot appear onscreen
        /*
        $file = fopen("/home/lacddt/logging/log.txt","w");
        echo fwrite($file,"'<!-- THIS IS MY URL'.$url.'-->'");
        fclose($file);
        print_r('<!-- THIS IS MY URL'.$url.'-->');
        */

        $solr_xml = file_get_contents($url);

        // Base search URL
        $base_search = './search/'.$query;

        // Inject query back into results
        $data['base_search'] = $base_search;
        $data['delimiter'] = $this->delimiter;

        // We would construct/pop a new skylight Record model here?
        $search_xml = @new SimpleXMLElement($solr_xml);

        $facets = array();

        // Hard coded until I do something better
        foreach($this->configured_filters as $filter_name => $filter) {
            $facet = array();
            $facet_xml = $search_xml->xpath("//lst[@name='facet_fields']/lst[@name='".$filter."']/int");
            $facet['name'] = $filter_name;
            $terms = array();
            // Build facets from solr response
            foreach ($facet_xml as $facet_term) {
                //v5 comments
                //$names = preg_split('/\|\|\|/',$facet_term->attributes());

                //$term['name'] = urlencode($facet_term->attributes());

                $term['name'] = $facet_term->attributes();
                $term['name'] = preg_replace('/%2C/',',',$term['name']);
                $term['display_name'] = $facet_term->attributes();
                $term['count'] = $facet_term;

                $active_test = $filter.$this->delimiter.'%22'.$term['name'].'%22';

                if(in_array($active_test, $saved_filters)) {
                    $term['active'] = true;
                }
                else {
                    $term['active'] = false;
                }
                $terms[] = $term;
            }
            $facet['terms'] = $terms;
            $facet['queries'] = array();
            $facets[] = $facet;
        }

        foreach($this->configured_date_filters as $filter_name => $filter) {
            // Date.. needs facet query, not field, since
            // we're on solr 1.4 and can't do nice easy integer ranges
            //SR 15/12/2019 change for v6
            //$facet_xml = $search_xml->xpath("//lst[@name='facet_queries']/int");
            $facet_xml = $search_xml->xpath("//lst[@name='facet_fields']/lst[@name='".$filter."']/int");
            $facet['name'] = $filter_name;
            $queries = array();
            $terms = array();
            foreach($facet_xml as $facet_query) {
                $query_name = $facet_query->attributes();
                print_r($query_name);
                $query_norm_name = $query_name;
                $query_display_name = $query_name;
                preg_match_all('#\d{4}#',$query_name, $matches);
                if(count($matches) > 0) {
                    if(count($matches[0]) > 0) {
                        //SR 15/12/2019 change for v6
                        //if($matches[0][0] == $matches[0][1]) {
                        if($matches[0] == $matches[0]) {
                            $query_display_name = $matches[0][0];
                        }
                        else {
                            $query_display_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#','\1 - \2',$query_name);
                        }
                        $query_norm_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#','[\1%20TO%20\2]',$query_name);

                    }
                }
                //v5 comment outs
                //$query_display_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#','\1 - \2',$query_name);
                //$query_norm_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#','[\1%20TO%20\2]',$query_name);
                $fquery['name'] = $query_norm_name;
                $fquery['display_name'] = $query_display_name;
                $fquery['norm_name'] = $query_norm_name;
                $fquery['count'] = $facet_query;
                print_r($fquery['count']);
                $active_test = $filter.$this->delimiter.$query_norm_name;
                if(in_array($active_test, $fq)) {
                    $fquery['active'] = true;
                }
                else {
                    $fquery['active'] = false;
                }
                $queries[] = $fquery;
            }
            $facet['queries'] = $queries;
            $facet['terms'] = array();
            $facets[] = $facet;
        }

        $data['facets'] = $facets;
        return $data;
    }

    function getRecord($id = NULL, $highlight = "")
    {

        $title_field = 'dctitle';
        //Changed line -SR (dcsubject to subject)
        $subject_field = 'subject';
        $handle = $this->handle_prefix . '/' . $id;
        $url = $this->base_url . 'select?q=';
        // all comments in this function inherited v5
        // TODO: Implement highlighting for record pages
        // the below works but only with snippets, not in context
        // of the whole returned doc. Going with javascript now.
        //if($highlight == "") {
        $url .= 'handle:' . $handle;
        //}
        //else {
        //    $url .= $highlight;
        //}
        $url .= '&fq='.$this->container_field.':'.$this->container;
        $url .= '&fq=search.resourcetype:2';
        $url .= '&fq=handle:' . $handle;

        /*if($highlight != "") {
            $url .= '&hl=true&hl.fl=*.en';
        }*/


        $solr_xml = file_get_contents($url);
        // We would construct/pop a new skylight Record model here?
        $search_xml = @new SimpleXMLElement($solr_xml);

        $result_count = $search_xml->result['numFound'];
        $data['result_count'] = $result_count;

        // Check for a valid ID
        if ($result_count == 0) {
            // Return here and let the controller sort it out
            return $data;
        }

        foreach ($search_xml->result->doc[0]->arr as $field) {
            $key = $field['name'];


            foreach ($field->str as $value) {
                $key = str_replace('.', '', $key);
                $solr[$key][] = $value;
            }
            foreach ($field->int as $value) {
                $key = str_replace('.', '', $key);
                $solr[$key][] = $value;
            }
            foreach ($field->date as $value) {
                $key = str_replace('.', '', $key);
                $solr[$key][] = $value;
            }
            // Build highlight results from solr response
            // TODO: Implement this later. For now, highlighting in jquery
            // TODO: on record page because that way, we can do our html bitstreams

            /*
            foreach ($search_xml->xpath("//lst[@name='highlighting']/lst/arr/str") as $highlight) {
                //echo $doc['handle'][0].': '.$highlight.'<br/>';
                $solr['highlights'][] = $highlight;
            }

             */
        }

        // Related Items
        $rels_solr = array();

        foreach ($this->related_fields as $related_field) {
            $key = str_replace('.', '', $related_field);
            if(array_key_exists($key, $solr)) {
                $rels_solr[] = $solr[$key];
            }
        }
        if(count($rels_solr) > 0) {
            $rels_xml = $this->getRelatedItems($rels_solr, $id);
        }
        else {
            $rels_xml = $this->getRelatedItems(array_values($solr), $id);
        }

        $related = @new SimpleXMLElement($rels_xml);

        // Parse like search results. This will be moved somewhere better

        $related_items = array();

        foreach ($related->result->doc as $result) {
            $doc = array();
            foreach($result->arr as $multivalue_field) {
                $key = $multivalue_field['name'];
                foreach($multivalue_field->str as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
                foreach($multivalue_field->int as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
                foreach($multivalue_field->date as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
            }

            foreach($result->str as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)]= $value;

            }
            foreach($result->int as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)]= $value;

            }
            foreach($result->date as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)]= $value;

            }

            $raw_hdl = $doc['handle'] . ' ';

            $handle = preg_split('/\//',$raw_hdl);

            $doc['id'] = $handle[1];
            if(!array_key_exists($title_field,$doc)) {
                $doc[$title_field][] = 'No title';
            }
            $related_items[] = $doc;
        }
        $data['related_items'] = $related_items;

        // End search result parse.

        $data['solr'] = $solr;

        // Set the page title to the record title
        if(!array_key_exists($title_field,$solr)) {
            $solr[$title_field][] = 'No title';
        }

        return $data;

    }

    function getRelatedItems($facets = array(), $id = '')
    {
        $operator = ' OR ';
        $handle = $this->handle_prefix . '/' . $id;
        $counter = 0;
        $query_string = '';
        foreach($facets as $metadatavalue) {
            /*
            $metadatavalue = preg_replace('/:/','',-1);
            */
            //CHANGE LINE -SR to deal if it is an array

            if (is_array($metadatavalue))
            {
                $metadatavalue = $metadatavalue[0];
            }
            /*
            SR 19/12/19 simplify- can go to a urlencode in this case

            $metadatavalue = preg_replace('/:/','',$metadatavalue,-1);
            $metadatavalue = preg_replace('/\[/','\\[',$metadatavalue,-1);
            $metadatavalue = preg_replace('/\]/','\\]',$metadatavalue,-1);
            //   $metadatavalue = preg_replace('/\(/','\\(',$metadatavalue,-1);
            //   $metadatavalue = preg_replace('/\)/','\\)',$metadatavalue,-1);
            $metadatavalue = preg_replace('/\+/','\\+',$metadatavalue,-1);
            //$metadatavalue = preg_replace('/\-/','\\-',$metadatavalue,-1);
            //$metadatavalue = str_replace('-','%2F',$metadatavalue);
            $metadatavalue = str_replace('/','%2F',$metadatavalue);
            $metadatavalue = str_replace('|','%7C',$metadatavalue);
            $metadatavalue = str_replace(',','%2C',$metadatavalue);
            $metadatavalue = str_replace(';','%3B',$metadatavalue);
            $metadatavalue = str_replace('è','%C3%A8',$metadatavalue);
            */
            $metadatavalue = urlencode($metadatavalue);

            $metadatavalue = "\"$metadatavalue\"";

            if($counter == 0) {
                $query_string .= $metadatavalue;
            }
            else {
                $query_string .= $operator . $metadatavalue;
            }
            $counter++;
        }
        $query_string = str_replace('"', '%22', $query_string);

        //$query_string .= ' -handle:\"'.$handle.'\"';
        //SR 16/12/19 slashes don't work in this version- some fairly horrible encoding follows
        $query_string .= ' -handle:%22'.$handle.'%22';

        $query_string = str_replace('(', '%28', $query_string);
        $query_string = str_replace(')', '%29', $query_string);

        //SR 16/12/19 - and this- otherwise gives null string query
        if ($query_string == '')
        {
            $query_string="*:*";
        }

        $url = $this->base_url . 'select?q='.$this->solrEscape($query_string);


        $url .= '&fq='.$this->container_field.':'.$this->container;
        $url .= '&fq=search.resourcetype:2';
        $url .= '&rows=5';

        $solr_xml = file_get_contents($url);
        return $solr_xml;
    }

    function getRecentItems($rows = 5)
    {

        $title_field = $this->fields['Title'];
        $author_field =  $this->fields['Author'];
        $description_field = $this->fields['Abstract'];
        if(isset($this->fields['Subject'])) {
            $subject_field = $this->fields['Subject'];
        }

        $url = $this->base_url . 'select?q=*:*';
        $url .= '&fq='.$this->container_field.':'.$this->container;
        $url .= '&fq=search.resourcetype:2';
        $url .= '&sort=dc.date.accessioned_dt+desc';
        $url .= '&rows=' . $rows;
        $solr_xml = file_get_contents($url);

        $recent_xml = @new SimpleXMLElement($solr_xml);
        $recent_items = array();
        foreach ($recent_xml->result->doc as $result) {
            $doc = array();
            foreach($result->arr as $multivalue_field) {
                $key = $multivalue_field['name'];
                foreach($multivalue_field->str as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
                foreach($multivalue_field->int as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
                foreach($multivalue_field->date as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
            }

            foreach($result->str as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)]= $value;
            }

            foreach($result->date as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)]= $value;
            }

            $raw_hdl = $doc['handle'] . ' ';

            $handle = preg_split('/\//',$raw_hdl);
            $doc['id'] = $handle[1];
            if(!array_key_exists($title_field,$doc)) {
                $doc[$title_field][] = 'No title';
            }

            $recent_items[] = $doc;
        }

        $data['title_field'] = $title_field;
        $data['author_field'] = $author_field;
        $data['description_field'] = $description_field;
        if(isset($subject_field)) {
            $data['subject_field'] = $subject_field;
        }

        $data['recent_items'] = $recent_items;

        return $data;
    }

    function browseTerms($field = 'Author', $rows = 10, $offset = 0, $prefix = '') {

        if( isset($this->configured_filters[$field]) ) {
            $facet_field = $this->configured_filters[$field];
        } else if(isset($this->configured_date_filters[$field])) {
            $facet_field = $this->configured_date_filters[$field];
        }
        if ($offset == '')
        {
            $offset = 0;
        }
        $prefix = $this->solrEscape($prefix);
        $rows++;
        $url = $this->base_url . "select?q=*:*";
        $url .= '&fq='.$this->container_field.':'.$this->container;
        $url .= '&fq=search.resourcetype:2&rows=0&facet.mincount=1';
        $url .= '&facet=true&facet.sort=index&facet.field='.$facet_field.'&facet.limit='.$rows.'&facet.offset='.$offset;
        if($prefix !== '') {
            $url .= '&facet.prefix='.$prefix;
        }

        $solr_xml = file_get_contents($url);

        // Base search URL
        $base_search = './search/*';

        // Inject query back into results
        $data['base_search'] = $base_search;
        $data['delimiter'] = $this->delimiter;

        // We would construct/pop a new skylight Record model here?
        $search_xml = @new SimpleXMLElement($solr_xml);


        $facet_xml = $search_xml->xpath("//lst[@name='facet_fields']/lst[@name='".$facet_field."']/int");
        $facet['name'] = $field;
        $terms = array();
        // Build facets from solr response
        foreach ($facet_xml as $facet_term) {

            //$names = preg_split('/\|\|\|/',$facet_term->attributes());

            $term['name'] = urlencode($facet_term->attributes());
            $term['display_name'] = $facet_term->attributes();
            $term['count'] = $facet_term;

            $terms[] = $term;
        }

        $facet['terms'] = $terms;
        $facet['termcount'] = sizeof($terms);

        $data['facet'] = $facet;
        $data['rows'] = $search_xml->result['numFound'];
        return $data;
    }

    function countBrowseTerms($field = 'Subject', $prefix = '')
    {

        $prefix = $this->solrEscape(strtolower($prefix));

        $url = $this->base_url . "select?q=*:*";
        $url .= '&fq=' . $this->container_field . ':' . $this->container;
        $url .= '&fq=search.resourcetype:2&rows=0&facet.mincount=1';
        if (preg_match("/Date/", $field)) {
            $facetField = $this->configured_date_filters[$field];
        } else {
            $facetField = $this->configured_filters[$field];
        }
        $url .= '&facet=true&facet.sort=index&facet.field=' . $facetField . '&facet.limit=10000';

        if ($prefix !== '') {
            $url .= '&facet.prefix=' . $this->solrEscape($prefix);
        }

        $solr_xml = file_get_contents($url);

        // We would construct/pop a new skylight Record model here?
        $search_xml = @new SimpleXMLElement($solr_xml);

        $facet_xml = $search_xml->xpath("//lst[@name='facet_fields']/lst[@name='" . $facetField . "']/int");

        return count($facet_xml);
    }

    function getDateRanges($field, $q, $fq) {

        $dates = array();

        $lowest_year = $this->getLowerBound($field, $q, $fq);

        //SR 17/12/19  put in this if statement based on relevance
        if ($lowest_year !== '') {
            $lowest_year = floor($lowest_year / 10) * 10;

            $highest_year = $this->getUpperBound($field, $q, $fq);

            $total_gap = $highest_year - $lowest_year;

            $yearcount = 20; // number of ranges to show up as filters
            $gap = 10;
            if ($total_gap < 11) {
                $gap = 1;
            }

            $ranges = array();
            $fq = array();
            if ($gap > 1) {
                for ($i = 0; $i < $yearcount; $i++) {
                    $yr = $lowest_year + (($gap) * $i);
                    $next = $yr + $gap - 1;
                    if ($next > $highest_year) {
                        $next = $highest_year;
                    }
                    $ranges[] = $field . ':[' . $yr . '+TO+' . $next . ']';
                }
            } else {
                for ($i = 0; $i < $total_gap + 1; $i++) {
                    $yr = $lowest_year + $i;
                    $ranges[] = $field . ':[' . $yr . '+TO+' . $yr . ']';
                }
            }
            rsort($ranges);

            $dates['ranges'] = $ranges;
            $dates['fq'] = $fq;

            return $dates;
        }
    }

    function getLowerBound($field, $q, $fq) {

        $value = 1000; // Stupid default to catch problems

        $url = $this->base_url . "select?q=" . $this->solrEscape($q);
        if(count($fq) > 0) {
            foreach($fq as $value)
                $value = urlencode($value);
                $url .= '&fq='.$this->solrEscape($value).'';
        }
        $url .= '&fq='.$this->container_field.':'.$this->container;
        $url .= '&fq=search.resourcetype:2&rows=1';
        $url .= '&sort='.$field.'_sort%20asc';
        $solr_xml = file_get_contents($url);
        $bounds_xml = @new SimpleXMLElement($solr_xml);
        $field_xml = $bounds_xml->xpath("//result/doc/arr[@name='".$field."']/int");

        if(count($field_xml) > 0) {
            $value = $field_xml[0];
        }
        return $value;
    }

    function getUpperBound($field, $q, $fq) {

        $value = 3000; // Stupid default to catch problems

        $url = $this->base_url . "select?q=" . $this->solrEscape($q);
        if(count($fq) > 0) {
            foreach($fq as $value)
                $value = urlencode($value);
                $url .= '&fq='.$this->solrEscape($value).'';
        }
        $url .= '&fq='.$this->container_field.':'.$this->container;
        $url .= '&fq=search.resourcetype:2&rows=1';
        $url .= '&sort='.$field.'_sort%20desc';
        $solr_xml = file_get_contents($url);
        $bounds_xml = @new SimpleXMLElement($solr_xml);
        $field_xml = $bounds_xml->xpath("//result/doc/arr[@name='".$field."']/int");

        if(count($field_xml) > 0) {
            $value = $field_xml[0];
        }
        return $value;
    }

}
