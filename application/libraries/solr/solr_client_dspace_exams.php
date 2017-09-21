<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kshe085
 * Date: 27/04/11
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */

class Solr_client_dspace_exams
{

    var $base_url = ''; // Base URL. Typically not overridden in construct params. Get from config.
    var $max_rows = 100; // Default to 100 rows maximum
    var $container = '*'; // Default to all collections
    var $container_field = 'location.comm'; // Default to discovery's DSpace collection field
    var $handle_prefix = '10683';
    var $scope = '';
    var $rows = 10;
    var $recorddisplay = array();
    var $searchresultdisplay = array();
    var $configured_filters = array();
    var $configured_date_filters = array();
    //var $date_field = 'dc.date.year';
    var $delimiter = '';
    var $thumbnail_field = '';
    var $bitstream_field = '';
    var $display_thumbnail = false;
    var $link_bitstream = false;
    var $dictionary = 'default';
    var $fields = array(); //copied from uoa

    /**
     * Constructor
     *
     * @access public
     * @param array initialization parameters
     */
    public function __construct($params = array())
    {
        if (count($params) > 0) {
            $this->initialize($params);
        }

        $CI =& get_instance();
        $this->base_url = $CI->config->item('skylight_solrbase');
        //echo 'BASEURL'.$this->base_url;
        $this->container = $CI->config->item('skylight_container_id');
        $this->container_field = $CI->config->item('skylight_container_field');
        $this->rows = $CI->config->item('skylight_results_per_page');
        $this->recorddisplay = $CI->config->item('skylight_recorddisplay');
        $this->searchresultdisplay = $CI->config->item('skylight_searchresult_display');
        $this->configured_filters = $CI->config->item('skylight_filters');
        $this->configured_date_filters = $CI->config->item('skylight_date_filters');
        $this->delimiter = $CI->config->item('skylight_filter_delimiter');
        $this->bitstream_field = str_replace('.', '', $CI->config->item('skylight_fulltext_field'));
        $this->thumbnail_field = str_replace('.', '', $CI->config->item('skylight_thumbnail_field'));
        $this->dictionary = $CI->config->item('skylight_solr_dictionary');
        //SR 2/12/13 Add highlight_fields to config
        $this->highlight_fields = $CI->config->item('skylight_highlight_fields');
        //echo 'HIGHLIGHTS'.$this->highlight_fields;
        $this->fields = $CI->config->item('skylight_fields'); //copied from uoa
        $this->related_fields = $CI->config->item('skylight_related_fields');
        $this->num_related = $CI->config->item('skylight_related_number');
        $date_fields = $this->configured_date_filters;
        if (count($date_fields) > 0) {
            $this->date_field = array_pop($date_fields);
        }

        log_message('debug', "skylight Solr Client Initialized");
    }

    // --------------------------------------------------------------------

    /**
     * Initialize Preferences
     *
     * @access public
     * @param array initialization parameters
     * @return void
     */
    function initialize($params = array())
    {
        if (count($params) > 0) {
            foreach ($params as $key => $val) {
                if (isset($this->$key)) {
                    $this->$key = $val;
                }
            }
        }
    }

    function solrEscape($in)
    {
        //$in = urldecode($in);

        //$in = urldecode($in);
        $in = preg_replace('/#([^0-9])/', "$1", $in);
        $in = preg_replace('/\(/', "\\\(", $in);
        $in = preg_replace('/\)/', "\\\)", $in);
        $in = preg_replace('/&#40;/', "\\\(", $in);
        $in = preg_replace('/&#41;/', "\\\)", $in);
        $in = preg_replace('/!/', "", $in);
        $in = preg_replace('/@/', "", $in);

        $in = preg_replace('# #', '+', $in);
        $in = preg_replace('#%20#', '+', $in);
        $in = preg_replace('#%2B#', '+', $in);

        return $in;
    }

    function eventSearch($q = '*:*', $fq = array(), $rows = 1000)
    {

        $title = $this->recorddisplay['Title'];

        if ($q == '*' || $q == '') {
            $q = '*:*';
        }
        $url = $this->base_url . "select?q=" . $this->solrEscape($q);
        if (count($fq) > 0) {
            foreach ($fq as $value)
                $url .= '&fq=' . $this->solrEscape($value) . '';
        }

        // Set up scope
        $url .= '&fq=' . $this->container_field . ':' . $this->container;
        $url .= '&fq=search.resourcetype:2';

        $url .= '&rows=' . $rows;

        //print_r('event search' . $url);

        $solr_xml = file_get_contents($url);
        $search_xml = @new SimpleXMLElement($solr_xml);

        $docs = array();

        // Build search results from solr response

        foreach ($search_xml->result->doc as $result) {
            $doc = array();
            foreach ($result->arr as $multivalue_field) {
                $key = $multivalue_field['name'];
                foreach ($multivalue_field->str as $value) {
                    $doc[str_replace('.', '', $key)] = $value;
                }
                foreach ($multivalue_field->int as $value) {
                    $doc[str_replace('.', '', $key)] = $value;
                }
                foreach ($multivalue_field->date as $value) {
                    $doc[str_replace('.', '', $key)] = $value;
                }

            }

            foreach ($result->str as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)] = $value;

            }

            foreach ($result->int as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)] = $value;

            }

            foreach ($result->date as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)] = $value;

            }


            $handle = preg_split('/\//', $doc['handle']);
            $doc['id'] = $handle[1];
            if (!array_key_exists($title, $doc)) {
                $doc[$title][] = 'No title';
            }
            $docs[] = $doc;
        }

        $data['docs'] = $docs;
        $data['rows'] = $search_xml->result['numFound'];

        return $data;
    }

    function simpleSearch($q = '*:*', $offset = 1, $fq = array(), $operator = 'OR', $sort_by = 'score+desc', $num_results = "")
    {

        if($sort_by == "score+desc" || $sort_by == "" || !isset($sort_by)) {
            $sort_by = 'dc.coverage.temporal_sort+desc,score+desc,dc.title_sort+asc';
        }
        else {
            $sort_by = str_replace(' ', '+', $sort_by);
            $sort_by .= ',dc.coverage.temporal_sort+desc';
        }
        
        if($num_results != "") {
            $this->rows = $num_results;
        }

        // Returns $data containing search results and facets
        // See search.php controller for example of usage

        $title = $this->recorddisplay[0]; //changed to index
        if ($q == '*' || $q == '') {
            $q = '*:*';
        }

        // treat search box query as phrase
        if ($q != '*:*') {
            $q = '"' . $q . '"';
        }

        $url = $this->base_url . 'select?q=' . $this->solrEscape($q);
        if (count($fq) > 0) {
            foreach ($fq as $value)
               $url .= '&fq=' . $this->solrEscape($value) . '';
        }

        $url .= '&qf=dc.title^20.0';

        if (isset($this->date_field))
        {
            $dates = $this->getDateRanges($this->date_field, $q, $fq);
            $ranges = $dates['ranges'];
            $datefqs = $dates['fq'];
        }
        else
        {
            $ranges = array();
        }


        // Set up scope
        $url .= '&fq=' . $this->container_field . ':' . $this->container;
        $url .= '&fq=search.resourcetype:2';
        $url .= '&sort=' . $sort_by;

        $url .= '&rows=' . $this->rows . '&start=' . $offset . '&facet.mincount=1';
        //$url .= '&rows=20&facet.mincount=1';
        $url .= '&facet=true&facet.limit=-1&facet.sort=index';
        foreach ($this->configured_filters as $filter_name => $filter) {
            $url .= '&facet.field=' . $filter;
        }


        foreach ($ranges as $range) {
            $url .= '&facet.query=' . $range;
        }
        $url .= '&q.op=' . $operator;

        // Set up highlighting
        // SR 2/12/13 change *.en to $this->highlight_fields. Things like bitstream don't look good here!
        $url .= '&hl=true&hl.fl='.$this->highlight_fields.'&hl.simple.pre=<strong>&hl.simple.post=</strong>';

        // Set up spellcheck

        $url .= '&spellcheck=true&spellcheck.collate=true&spellcheck.onlyMorePopular=false&spellcheck.count=5';
        $url .= '&spellcheck.dictionary=' . $this->dictionary;

        // Call Solr!
        $solr_xml = @file_get_contents($url);
        $search_xml = @new SimpleXMLElement($solr_xml);

        $docs = array();
        $facet = array();
        $facets = array();
        $last_active_facet = 'None';

        // Build search results from solr response
        foreach ($search_xml->result->doc as $result) {
            $doc = array();
            foreach ($result->arr as $multivalue_field) {
                $key = $multivalue_field['name'];
                foreach ($multivalue_field->str as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }

            }

            foreach ($result->str as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)] = $value;

            }

            // Check for the existence of highlighted fields before trying to loop round them
            $highlights = $search_xml->xpath("//lst[@name='highlighting']/lst[@name='" . $doc['handle'] . "']/arr/str");
            if ($highlights) {
               // Build highlight results from solr response
               foreach ($highlights as $highlight) {
               //echo $doc['handle'][0].': '.$highlight.'<br/>';
               $doc['highlights'][] = $highlight;
               }
            }


            $handle = preg_split('/\//', $doc['handle']);
            $doc['id'] = $handle[1];
            if (!array_key_exists($title, $doc)) {
                $doc[$title][] = 'No title';
            }
            $docs[] = $doc;
        }

        // get spellcheck collated suggestion
        $suggestion = "";
        $spellcheck = $search_xml->xpath("//lst[@name='spellcheck']/lst[@name='suggestions']/str[@name='collation']");
        if ($spellcheck != NULL && sizeof($spellcheck) > 0)
            $suggestion = $spellcheck[0];

        $data['suggestion'] = $suggestion;
        $data['docs'] = $docs;
        $data['rows'] = $search_xml->result['numFound'];


        // First do the non-date filters
        foreach ($this->configured_filters as $filter_name => $filter) {
            $facet_xml = $search_xml->xpath("//lst[@name='facet_fields']/lst[@name='" . $filter . "']/int");
            $facet['name'] = $filter_name;
            $terms = array();
            $queries = array();
            // Build facets from solr response
            foreach ($facet_xml as $facet_term) {
                $names = preg_split('/\|\|\|/', $facet_term->attributes());
                $term['name'] = urlencode($facet_term->attributes());
                if (count($names) > 1) {
                    $term['display_name'] = $names[1];
                } else {
                    $term['display_name'] = $names[0];
                }
                $term['norm_name'] = urlencode($names[0]);
                $term['count'] = $facet_term;
                $active_test = $filter . $this->delimiter . '%22' . $term['name'] . '%22';

                if (in_array($active_test, $fq)) {
                    $term['active'] = true;
                    $last_active_facet = $facet['name'];
                } else {
                    $term['active'] = false;
                }

                $terms[] = $term;
            }
            $facet['terms'] = $terms;
            $facet['queries'] = array();
            $facets[] = $facet;
        }

        // Now do the date filters
        foreach ($this->configured_date_filters as $filter_name => $filter) {
            // Date.. needs facet query, not field, since
            // we're on solr 1.4 and can't do nice easy integer ranges
            $facet_xml = $search_xml->xpath("//lst[@name='facet_queries']/int");
            $facet['name'] = $filter_name;
            $queries = array();
            $terms = array();
            foreach ($facet_xml as $facet_query) {
                $query_norm_name = '';
                $query_display_name = '';
                $query_name = $facet_query->attributes();
                preg_match_all('#\d{4}#', $query_name, $matches);
                if (count($matches) > 0) {
                    if (count($matches[0]) > 0) {
                        if ($matches[0][0] == $matches[0][1]) {
                            $query_display_name = $matches[0][0];
                        } else {
                            $query_display_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#', '\1 - \2', $query_name);
                        }
                        $query_norm_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#', '[\1%20TO%20\2]', $query_name);
                    }
                }
                //$query_display_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#','\1 - \2',$query_name);
                //$query_norm_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#','[\1%20TO%20\2]',$query_name);
                $query['name'] = $query_norm_name;
                $query['display_name'] = $query_display_name;
                $query['norm_name'] = $query_norm_name;
                $query['count'] = $facet_query;
                $active_test = $filter . $this->delimiter . $query_norm_name;
                if (in_array($active_test, $fq)) {
                    $query['active'] = true;
                } else {
                    $query['active'] = false;
                }

                $queries[] = $query;

            }
            $facet['queries'] = $queries;
            $facet['terms'] = array();
            $facets[] = $facet;
        }


        $data['facets'] = $facets;
        $data['last_facet_display'] = $this->setLastFacetDisplay($last_active_facet);

        return $data;

    }

    function getFacets($q = '*:*', $fq = array(), $saved_filters = array())
    {
        $query = $q;
        if ($q == '*') {
            $q = '*:*';
        }
        $url = $this->base_url . "select?q=" . $q;
        if (count($fq) > 0) {
            foreach ($fq as $value)
                $url .= '&fq=' . $value . '';
        }


//        $ranges = array();
//        foreach($this->configured_date_filters as $filter_name => $filter) {
//            array_push($ranges,$this->getDateRanges($filter, $q, $fq));
//        }


        //$dates = $this->getDateRanges($this->date_field, $q, $fq);
        //$ranges = $dates['ranges'];

        if (isset($this->date_field))
        {
            $dates = $this->getDateRanges($this->date_field, $q, $fq);
            $ranges = $dates['ranges'];
            //$datefqs = $dates['fq'];
        }
        else
        {
            $ranges = array();
        }


        $url .= '&fq=' . $this->container_field . ':' . $this->container;
        $url .= '&fq=search.resourcetype:2&rows=0&facet.mincount=1';
        $url .= '&facet=true&facet.limit=-1&facet.sort=index';

        foreach ($this->configured_filters as $filter_name => $filter) {
            $url .= '&facet.field=' . $filter;
        }

        foreach ($ranges as $range) {
            $url .= '&facet.query=' . $range;
        }

        //print_r('facets ' . $url);

        $solr_xml = file_get_contents($url);

        // Base search URL
        $base_search = './search/' . $query;

        // Inject query back into results
        $data['base_search'] = $base_search;
        $data['delimiter'] = $this->delimiter;

        // We would construct/pop a new skylight Record model here?
        $search_xml = @new SimpleXMLElement($solr_xml);

        $facets = array();
        $last_active_facet = 'None';

        // Hard coded until I do something better
        foreach ($this->configured_filters as $filter_name => $filter) {
            $facet = array();
            $facet_xml = $search_xml->xpath("//lst[@name='facet_fields']/lst[@name='" . $filter . "']/int");
            $facet['name'] = $filter_name;
            $terms = array();
            // Build facets from solr response
            foreach ($facet_xml as $facet_term) {

                //print_r($facet_term);
                $names = preg_split('/\|\|\|/', $facet_term->attributes());
                $term['name'] = urlencode($facet_term->attributes());
                //print_r('no of names:');
                //print_r(count($names));
                if (count($names) > 1) {
                    $term['display_name'] = $names[1];
                } else {
                    $term['display_name'] = $names[0];
                }
                $term['norm_name'] = urlencode($names[0]);
                $term['count'] = $facet_term;
                $active_test = $filter . $this->delimiter . '%22' . $term['name'] . '%22';

                if (in_array($active_test, $saved_filters)) {
                    $term['active'] = true;
                    $last_active_facet = $facet['name'];
                } else {
                    $term['active'] = false;
                }

                $terms[] = $term;
            }
            $facet['terms'] = $terms;
            $facet['queries'] = array();
            $facets[] = $facet;
        }

        foreach ($this->configured_date_filters as $filter_name => $filter) {
            // Date.. needs facet query, not field, since
            // we're on solr 1.4 and can't do nice easy integer ranges
            $facet_xml = $search_xml->xpath("//lst[@name='facet_queries']/int");
            $facet['name'] = $filter_name;
            $queries = array();
            $terms = array();
            foreach ($facet_xml as $facet_query) {
                //print_r($facet_query);
                $query_name = $facet_query->attributes();
                $query_norm_name = $query_name;
                $query_display_name = $query_name;
                preg_match_all('#\d{4}#', $query_name, $matches);
                if (count($matches) > 0) {
                    if (count($matches[0]) > 0) {
                        if ($matches[0][0] == $matches[0][1]) {
                            $query_display_name = $matches[0][0];
                        } else {
                            $query_display_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#', '\1 - \2', $query_name);
                        }
                        $query_norm_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#', '[\1%20TO%20\2]', $query_name);

                    }
                }
                //$query_display_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#','\1 - \2',$query_name);
                //$query_norm_name = preg_replace('#^.*\[(\d+) TO (\d+).*$#','[\1%20TO%20\2]',$query_name);
                $fquery['name'] = $query_norm_name;
                $fquery['display_name'] = $query_display_name;
                $fquery['norm_name'] = $query_norm_name;
                $fquery['count'] = $facet_query;
                $active_test = $filter . $this->delimiter . $query_norm_name;
                if (in_array($active_test, $fq)) {
                    $fquery['active'] = true;
                } else {
                    $fquery['active'] = false;
                }
                $queries[] = $fquery;
            }
            $facet['queries'] = $queries;
            $facet['terms'] = array();
            $facets[] = $facet;
        }

        $data['facets'] = $facets;
        $data['last_facet_display'] = $this->setLastFacetDisplay($last_active_facet);

        return $data;
    }

    function getRecord($id = NULL, $highlight = "")
    {
        $title_field = "Title";

        $handle = $this->handle_prefix . '/' . $id;
        $url = $this->base_url . 'select?q=';
        // TODO: Implement highlighting for record pages
        // the below works but only with snippets, not in context
        // of the whole returned doc. Going with javascript now.

        $url .= 'handle:' . $handle;
        $url .= '&fq=' . $this->container_field . ':' . $this->container;
        $url .= '&fq=search.resourcetype:2';
        $url .= '&fq=handle:' . $handle;

        //print_r('record '.$url);
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
            foreach ($result->arr as $multivalue_field) {
                $key = $multivalue_field['name'];
                foreach ($multivalue_field->str as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
                foreach ($multivalue_field->int as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
                foreach ($multivalue_field->date as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
            }

            foreach ($result->str as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)] = $value;

            }
            foreach ($result->int as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)] = $value;

            }
            foreach ($result->date as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)] = $value;

            }

            $handle = preg_split('/\//', $doc['handle']);
            $doc['id'] = $handle[1];
            if (!array_key_exists($title_field, $doc)) {
                $doc[$title_field][] = 'No title';
            }
            $related_items[] = $doc;
        }
        $data['related_items'] = $related_items;

        // End search result parse.

        $data['solr'] = $solr;

        // Set the page title to the record title
        if (!array_key_exists($title_field, $solr)) {
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
        foreach ($facets as $metadatavalue) {
            if (is_array($metadatavalue)) {
                $md = $metadatavalue;
                $metadatavalue = '';
                // limit to the first 200 characters
                $metadatavalue .= substr($md[0],0,200) . ' ';

            }
            $metadatavalue = preg_replace('/\[/', '\\[', $metadatavalue, -1);
            $metadatavalue = preg_replace('/\]/', '\\]', $metadatavalue, -1);
            $metadatavalue = preg_replace('/\(/', '\\(', $metadatavalue, -1);
            $metadatavalue = preg_replace('/\)/', '\\)', $metadatavalue, -1);
            $metadatavalue = preg_replace('/\+/', '\\+', $metadatavalue, -1);
            $metadatavalue = preg_replace('/\-/', '\\-', $metadatavalue, -1);
            $metadatavalue = preg_replace('/\:/', '\\:', $metadatavalue, -1);
            $metadatavalue = preg_replace('/\)/', '', $metadatavalue, -1);
            $metadatavalue = preg_replace('/\(/', '', $metadatavalue, -1);
            $metadatavalue = preg_replace('/\}/', '', $metadatavalue, -1);
            $metadatavalue = preg_replace('/\{/', '', $metadatavalue, -1);
            $metadatavalue = preg_replace('/\]/', '', $metadatavalue, -1);
            $metadatavalue = preg_replace('/\[/', '', $metadatavalue, -1);
            $metadatavalue = preg_replace('/\"/', '', $metadatavalue, -1);
            $metadatavalue = preg_replace("/\'/", '', $metadatavalue, -1);
            $metadatavalue = preg_replace('/%/', '', $metadatavalue, -1);

            if ($counter == 0) {
                $query_string .= $metadatavalue;
            } else {
                $query_string .= $operator . $metadatavalue;
            }
            $counter++;
        }
        $query_string .= ' -handle:"' . $handle . '"';
        $url = $this->base_url . 'select?q=' . $this->solrEscape($query_string);
        $url .= '&fq=' . $this->container_field . ':' . $this->container;
        $url .= '&fq=search.resourcetype:2';
        $url .= '&rows=' . $this->num_related;

        //print_r('related '. $url);
        $solr_xml = file_get_contents($url);

        return $solr_xml;
    }

    function getRecentItems($rows = 5)
    {
        $title_field = $this->searchresultdisplay[0]; //'Title'];
        $author_field = $this->searchresultdisplay[1]; //'Author'];
        $subject_field = $this->searchresultdisplay[2]; //'Subject'];
        $description_field = $this->searchresultdisplay[4]; //'Abstract'];

        $url = $this->base_url . 'select?q=*:*';
        $url .= '&fq=' . $this->container_field . ':' . $this->container;
        $url .= '&fq=search.resourcetype:2';
        $url .= '&sort=dc.date.accessioned_dt+desc';
        $url .= '&rows=' . $rows;

        //print_r('recent '. $url);
        $solr_xml = file_get_contents($url);

        $recent_xml = @new SimpleXMLElement($solr_xml);
        $recent_items = array();
        foreach ($recent_xml->result->doc as $result) {
            $doc = array();
            foreach ($result->arr as $multivalue_field) {
                $key = $multivalue_field['name'];
                foreach ($multivalue_field->str as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
                foreach ($multivalue_field->int as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
                foreach ($multivalue_field->date as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
            }

            foreach ($result->str as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)] = $value;
            }

            $handle = preg_split('/\//', $doc['handle']);
            $doc['id'] = $handle[1];
            if (!array_key_exists($title_field, $doc)) {
                $doc[$title_field][] = 'No title';
            }

            $recent_items[] = $doc;
        }

        $data['title_field'] = $title_field;
        $data['author_field'] = $author_field;
        $data['subject_field'] = $subject_field;
        $data['description_field'] = $description_field;

        $data['recent_items'] = $recent_items;

        return $data;
    }

    function getRandomItems($rows = 5)
    {
        $title_field = $this->searchresultdisplay[0]; //'Title'];
        $author_field = $this->searchresultdisplay[1]; //'Author'];
        $subject_field = $this->searchresultdisplay[2]; //'Subject'];
        $description_field = $this->searchresultdisplay[4]; //'Abstract'];

        $url = $this->base_url . 'select?q=*:*';
        $url .= '&fq=' . $this->container_field . ':' . $this->container;
        $url .= '&fq=search.resourcetype:2';
        $url .= '&sort=random_'. mt_rand(1, 10000).'%20desc';
        $url .= '&rows=' . $rows;
        $solr_xml = file_get_contents($url);

        $recent_xml = @new SimpleXMLElement($solr_xml);
        $random_items = array();
        foreach ($recent_xml->result->doc as $result) {
            $doc = array();
            foreach ($result->arr as $multivalue_field) {
                $key = $multivalue_field['name'];
                foreach ($multivalue_field->str as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
                foreach ($multivalue_field->int as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
                foreach ($multivalue_field->date as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
            }

            foreach ($result->str as $unique_field) {
                $key = $unique_field['name'];
                $value = $unique_field;
                $doc[str_replace('.', '', $key)] = $value;
            }

            $handle = preg_split('/\//', $doc['handle']);
            $doc['id'] = $handle[1];
            if (!array_key_exists($title_field, $doc)) {
                $doc[$title_field][] = 'No title';
            }

            $random_items[] = $doc;
        }

        $data['title_field'] = $title_field;
        $data['author_field'] = $author_field;
        $data['subject_field'] = $subject_field;
        $data['description_field'] = $description_field;

        $data['random_items'] = $random_items;

        return $data;
    }

    function browseTerms($field = 'Subject', $rows = 10, $offset = 0, $prefix = '')
    {

        $prefix = $this->solrEscape(strtolower($prefix));
        $rows++;
        $url = $this->base_url . "select?q=*:*";
        $url .= '&fq=' . $this->container_field . ':' . $this->container;
        $url .= '&fq=search.resourcetype:2&rows=0&facet.mincount=1';
        if (preg_match("/Date/", $field)) {
            $facetField = $this->configured_date_filters[$field];
        } else {
            $facetField = $this->configured_filters[$field];
        }
        $url .= '&facet=true&facet.sort=index&facet.field=' . $facetField . '&facet.limit=' . $rows . '&facet.offset=' . $offset;

        if ($prefix !== '') {
            $url .= '&facet.prefix=' . $this->solrEscape($prefix);
        }

        //print_r('browseTerms '.$url);

        $solr_xml = file_get_contents($url);

        // Base search URL
        $base_search = './search/*';

        // Inject query back into results
        $data['base_search'] = $base_search;
        $data['delimiter'] = $this->delimiter;

        // We would construct/pop a new skylight Record model here?
        $search_xml = @new SimpleXMLElement($solr_xml);
        //print_r('field' . $facetField);

        $facet_xml = $search_xml->xpath("//lst[@name='facet_fields']/lst[@name='" . $facetField . "']/int");
        $facet['name'] = $field;
        $terms = array();
        // Build facets from solr response
        foreach ($facet_xml as $facet_term) {

            $names = preg_split('/\|\|\|/', $facet_term->attributes());
            //print_r($names);
            $term['name'] = urlencode($facet_term->attributes());
            if (count($names) == 1)
            {
                $term['display_name'] = $names[0];
            }
            else
            {
                $term['display_name'] = $names[1];
            }
            $term['norm_name'] = urlencode($names[0]);
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

        //print_r($url);

        $solr_xml = file_get_contents($url);

        // We would construct/pop a new skylight Record model here?
        $search_xml = @new SimpleXMLElement($solr_xml);

        $facet_xml = $search_xml->xpath("//lst[@name='facet_fields']/lst[@name='" . $facetField . "']/int");

        return count($facet_xml);
    }

    function getDateRanges($field, $q, $fq)
    {
        $dates = array();
        $lowest_year = $this->getLowerBound($field, $q, $fq);
        //print_r($lowest_year);
        $lowest_year = floor($lowest_year / 10) * 10;

        $highest_year = $this->getUpperBound($field, $q, $fq);
        //print_r($highest_year);
        $total_gap = $highest_year - $lowest_year;
        $yearcount = 20; // number of ranges to show up as filters
        $gap = 5;
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

    function getLowerBound($field, $q, $fq)
    {

        $value = 1000; // Stupid default to catch problems

        $url = $this->base_url . "select/?q=" . $this->solrEscape($q);
        if (count($fq) > 0) {
            foreach ($fq as $value) {
                //print_r(' Lower value is '.$value);
                $url .= '&fq=' . $this->solrEscape($value) . '';
            }
        }
        $url .= '&fq=' . $this->container_field . ':' . $this->container;
        $url .= '&fq=search.resourcetype:2&rows=1';
        $url .= '&sort=' . $field . '%20asc';

        //print_r('lower bound='. $url);
        //print_r('field='. $field);
        $solr_xml = file_get_contents($url);
        //print_r('URL'.$url);
      // print_r('XML'.$solr_xml);
        $bounds_xml = @new SimpleXMLElement($solr_xml);
        $field_xml = $bounds_xml->xpath("//result/doc/str[@name='" . $field . "']");

        if (count($field_xml) > 0) {
            $value = $field_xml[0];
        }
        return $value;
    }

    function getUpperBound($field, $q, $fq)
    {

        $value = 3000; // Stupid default to catch problems

        $url = $this->base_url . "select?q=" . $this->solrEscape($q);
        if (count($fq) > 0) {
            foreach ($fq as $value) {
                //print_r(' Upper value is '.$value);
                $url .= '&fq=' . $this->solrEscape($value) . '';
            }
        }
        $url .= '&fq=' . $this->container_field . ':' . $this->container;
        $url .= '&fq=search.resourcetype:2&rows=1';
        $url .= '&sort='.$field.'%20desc';
        //$url .= '&sort=' . $field . '_sort%20desc'; //copied from uoa

        //print_r('upper bound='. $url);
        $solr_xml = file_get_contents($url);
        $bounds_xml = @new SimpleXMLElement($solr_xml);
        $field_xml = $bounds_xml->xpath("//result/doc/str[@name='" . $field . "']");

        if (count($field_xml) > 0) {
            $value = $field_xml[0];
        }
        return $value;
    }

    function setLastFacetDisplay($last_active_facet)
    {
        switch($last_active_facet) {
            case 'None';
                return 'School';
            case 'School';
                return 'Subject';
            case 'Subject';
                return 'Title';
            case 'Academic Year';
                return 'Title';
            default;
                return 'Title';
        }
    }

}



