<?php

/**
 * User: cknowles
 * ArchivesSpace SOLR 4 Client
 */
class solr_client_archivesspace_1
{
    var $base_url = ''; /** Base URL. Typically not overridden in construct params. Get from config.*/
    var $max_rows = 100; /** Default to 100 rows maximum */
    var $container_default = '*'; /** Default to all collections */
    var $container = array(); /** Default to all collections */
    var $container_field = 'resource'; /** Default to discovery's DSpace collection field */
    var $handle_prefix = '';
    var $scope = '';
    var $rows = 10;
    var $recorddisplay = array();
    var $searchresultdisplay = array();
    var $configured_filters = array();
    var $configured_date_filters = array();
    var $delimiter = '';
    var $thumbnail_field = '';
    var $bitstream_field = '';
    var $resource_field = '';
    var $display_thumbnail = false;
    var $link_bitstream = false;
    var $dictionary = 'default';
    var $fields = array(); //copied from uoa
    var $solr_collection = "collection1"; //TODO move to config
    var $restriction = array();

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
        $this->handle_prefix = $CI->config->item('skylight_handle_prefix');
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
        $this->highlight_fields = $CI->config->item('skylight_highlight_fields');
        $this->related_fields = $CI->config->item('skylight_related_fields');
        $this->num_related = $CI->config->item('skylight_related_number');
        $this->fields = $CI->config->item('skylight_fields'); //copied from uoa
        $this->restriction =$CI->config->item('skylight_query_restriction');
        $date_fields = $this->configured_date_filters;
        if (count($date_fields) > 0) {
            $this->date_field = array_pop($date_fields);
        }

        log_message('debug', "skylight Solr Client Initialized");
        log_message('debug', "handle_prefix " .$this->handle_prefix);
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

        return $in;
    }

    function eventSearch($q = '*:*', $fq = array(), $rows = 1000)
    {
        $title = $this->recorddisplay['Title'];

        if ($q == '*' || $q == '') {
            $q = '*:*';
        }
        $url = $this->base_url . "#/" . $this->solr_collection . "/query?q=" . $this->solrEscape($q);
        if (count($fq) > 0) {
            foreach ($fq as $value)
                $url .= '&fq=' . $this->solrEscape($value) . '';
        }

        // Set up scope
        //TODO if container is an array
        //check if an empty array if true use default
        //else loop through the array and add to fq with + between
        if (empty($this->container)) {
            $url .= '&fq=' . $this->container_field . ':' . $this->container_default;
        } else {
            $ind = 0;
            $url .= '&fq=';
            foreach ($this->container as $container_id) {
                $ind++;
                $url .= $this->container_field . ':' . $container_id;
                $count = count($this->container);
                if ($count > 1 && $ind != $count) {
                    $url .= '+';
                }
            }
        }
        foreach ($this->restriction as $restrict_field => $restrict_by)
        {
            $url .= '&fq=' . $restrict_field. ':' . $restrict_by;
        }

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

            $handle = preg_split('/\//', $doc['id']);
            $doc['id'] = $handle[4];
            if (!array_key_exists($title, $doc)) {
                $doc[$title][] = 'No title';
            }
            $docs[] = $doc;
        }

        $data['docs'] = $docs;
        $data['rows'] = $search_xml->result['numFound'];

        return $data;
    }

    function simpleSearch($q = '*:*', $offset = 1, $fq = array(), $operator = 'OR', $sort_by = 'score+desc')
    {

        $sort_by = str_replace(' ', '+', $sort_by);

        // Returns $data containing search results and facets
        // See search.php controller for example of usage

        $title = $this->recorddisplay[0]; //changed to index

        $url = $this->base_url . $this->solr_collection . "/select?";

        if (isset($this->date_field)) {
            $dates = $this->getDateRanges($this->date_field, $q, $fq);
            $ranges = $dates['ranges'];
        } else {
            $ranges = array();
        }

        // Set up scope
        // print_r("$q = " .$q. " END ");
        if ($q != NULL && $q != '*') {
            $url .= "q=" . $this->solrEscape($q);
            $url .= "&df=fullrecord";
        } else {

            $url .= 'q=*:*';
        }
        //$url .= '&fq=' . $this->container_field . ':' . $this->container;
        if (empty($this->container))
        {
            $url .= '&fq=' . $this->container_field . ':' . $this->container_default;
        }
        else {
            $ind = 0;
            $url .= '&fq=';
            foreach ($this->container as $container_id){
                $ind++;
                $url .= $this->container_field . ':' . $container_id;
                $count = count($this->container);
                if ($count > 1 && $ind != $count)
                {
                    $url .= '+';
                }
            }
        }
        $url .= '&fq=types:"archival_object"+types:"resource"';
        if (count($fq) > 0) {
            foreach ($fq as $value)
                $url .= '&fq=' . $this->solrEscape($value) . '';
        }
        if ($sort_by == null) {
            $sort_by = "title_sort+asc";
        }

        foreach ($this->restriction as $restrict_field => $restrict_by)
        {
            $url .= '&fq=' . $restrict_field. ':' . $restrict_by;
        }

        $url .= '&sort=' . $sort_by;

        $url .= '&rows=' . $this->rows . '&start=' . $offset . '&facet.mincount=1';
        $url .= '&facet=true&facet.limit=10';
        foreach ($this->configured_filters as $filter_name => $filter) {
            $url .= '&facet.field=' . $filter;
        }

        foreach ($ranges as $range) {
            $url .= '&facet.query=' . $range;
        }

        $url .= '&spellcheck=true&spellcheck.collate=true&spellcheck.onlyMorePopular=false&spellcheck.count=5';
        $url .= '&spellcheck.dictionary=' . $this->dictionary;
       // print_r('simple search '. $url);

        $solr_xml = file_get_contents($url);
        $search_xml = @new SimpleXMLElement($solr_xml);

        $facet = array();
        $facets = array();

        // Build search results from solr response
        $docs = $this->getResultsFromSolr($search_xml, $title);


        // get spellcheck collated suggestion
        $suggestion = "";
        $spellcheck = $search_xml->xpath("//lst[@name='spellcheck']/lst[@name='suggestions']/str[@name='collation']");
        if ($spellcheck != NULL && sizeof($spellcheck) > 0)
            $suggestion = $spellcheck[0];

        $data['suggestion'] = $suggestion;
        $data['docs'] = $docs;
        $data['rows'] = $search_xml->result['numFound'];


        // Hard coded until I do something better
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
        return $data;

    }

    function getFacets($q = '*:*', $fq = array(), $saved_filters = array())
    {

        $query = $q;
        $url = $this->base_url . $this->solr_collection ."/select?";
        if (count($fq) > 0) {
            foreach ($fq as $value)
                $url .= '&fq=' . $value . '';
        }


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

        $url .= 'q=*:*';
        if (empty($this->container))
        {
            $url .= '&fq=' . $this->container_field . ':' . $this->container_default;
        }
        else {
            $ind = 0;
            $url .= '&fq=';
            foreach ($this->container as $container_id){
                $ind++;
                $url .= $this->container_field . ':' . $container_id;
                $count = count($this->container);
                if ($count > 1 && $ind != $count)
                {
                    $url .= '+';
                }
            }
        }
        $url .= '&fq=types:"archival_object"+types:"resource"';
        foreach ($this->restriction as $restrict_field => $restrict_by)
        {
            $url .= '&fq=' . $restrict_field. ':' . $restrict_by;
        }

        $url .= '&wt=xml';
        $url .= '&rows=0';
        $url .= '&facet.mincount=1';
        $url .= '&facet=true';
        $url .= '&facet.limit=10';

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
        return $data;
    }

    function getRecord($id = NULL, $params)
    {
        //TODO remove hardcoding
        $title_field = 'title';
        //todo better way to pass on type to query - hacktastic
        $type = $params[0] . 's'; //todo need item type

        $url = $this->base_url . '' . $this->solr_collection .'/select?q=';

        //TODO replace get Handle prefix as depends on collection

        $id = "\"". $this->handle_prefix . $type . "/" .$id . "\"";
        $url .= 'id:' . $id;
        foreach ($this->restriction as $restrict_field => $restrict_by)
        {
            $url .= '&fq=' . $restrict_field. ':' . $restrict_by;
        }

        $url .= '&wt=xml';

        //print(" get record url "  . $url . " ");
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

        //print_r ($search_xml->result->doc[0]);
        //TODO what about the data not in arrays?
        //get the json???
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

        }

        //todo loop throught the $search_xml->result->doc[0] looking for the record display items?
        //for Strings
        $related_item_type = null;
        $related_item_value = null;
        foreach ($search_xml->result->doc[0]->str as $field)
        {
            $key = "" . $field['name'];
            if ($field['name'] == 'json') {

                $json_obj = json_decode($field, TRUE);
                if(!empty($json_obj['dates'])) {
                    $solr['dates'][] = $json_obj['dates'][0]['expression'];
                }
                if (!empty($json_obj['extents'])) {
                    $solr['extents'][] = $json_obj['extents'][0]['number'] . " " . $json_obj['extents'][0]['extent_type'];
                }

                //todo multipart notes
                foreach ($json_obj['notes'] as $note) {
                    if ($note['jsonmodel_type'] == 'note_multipart') {
                        $solr[$note['type']][] = $note['subnotes'][0]['content'];
                    }
                    elseif($note['jsonmodel_type'] == 'note_bibliography')
                    {
                        $solr['note_bibliography'][] = $note['content'][0];
                    }
                    else{
                        $solr[$note['type']][] = $note['content'][0];
                    }
                }
                if (!empty($json_obj['component_id'])) {
                    $solr['component_id'][] = $json_obj['component_id'];
                }
                if(!empty($json_obj['parent'])) {
                    $parent = $json_obj['parent']['ref'];
                    $parent_pieces = explode("/", $parent);
                    //print_r("***********" . $parent . " " . count($parent_pieces));
                    $parent_id = $parent_pieces[4];
                    $parent_type = $parent_pieces[3];
                    $solr['parent'][] = $parent;
                    $solr['parent_id'][] = $parent_id;
                    $solr['parent_type'][] = substr($parent_type, 0, strlen($parent_type)-1);
                }
            }
            else
            {
                $value = $field;
                $solr[$key][] = $value;
            }
        }

        // Related Items
        $rels_solr = array();

        foreach ($this->related_fields as $related_field) {
            $key = str_replace('.', '', $related_field);
            //print_r("key " . $key . " field " . $related_field);
            if(array_key_exists($key, $solr)) {
                $rels_solr[$key] = $solr[$key];
            }
        }

        $rels_xml = $this->getRelatedItems($rels_solr, $id);

        $related = @new SimpleXMLElement($rels_xml);

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
                //print_r(" " . $key . "  ");

                if ($key == 'json') {
                    $json_obj = json_decode($unique_field, TRUE);
                    if(!empty($json_obj['dates'])) {
                        $doc['dates'] = $json_obj['dates'][0]['expression'];
                    }

                    if (!empty($json_obj['component_id'])) {
                        $doc['component_id'] = $json_obj['component_id'];
                    }

                }
                else
                {
                    $value = $unique_field;
                    $doc[str_replace('.', '', $key)] = $value;
                }

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


            //TODO replace handle here
            $handle = preg_split('/\//', $doc['id']);
            $doc['id'] = $handle[4];
            if (!array_key_exists($title_field, $doc)) {
                $doc[$title_field][] = 'No title';
            }
            else{
            }
            $related_items[] = $doc;
        }
        //print_r($related_items);

        $data['related_items'] = $related_items;

        // End search result parse.

        $data['solr'] = $solr;

        // Set the page title to the record title
        if (!array_key_exists($title_field, $solr)) {
            $solr[$title_field][] = 'No title';
        }

        return $data;

    }

    function getRelatedItems($related = array(), $id = '')
    {
        $operator = ' OR ';
        $counter = 0;
        $query_string = '';
        foreach ($related as $metadatavalue) {

            if (is_array($metadatavalue)) {
                $md = $metadatavalue;
                $metadatavalue = '';
                $metadatavalue .= $md[0] . ' ';
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
                $query_string .=  '"' . $metadatavalue . '"';
            } else {
                $query_string .= $operator . '"' . $metadatavalue . '"';
            }
            $counter++;
        }

        //print_r("related items query_string " . $query_string . " ");
        $url = $this->base_url . '' . $this->solr_collection .'/select?';
        $url .= 'q=*:*';
        if (empty($this->container))
        {
            $url .= '&fq=' . $this->container_field . ':' . $this->container_default;
        }
        else {
            $ind = 0;
            $url .= '&fq=';
            foreach ($this->container as $container_id){
                $ind++;
                $url .= $this->container_field . ':' . $container_id;
                $count = count($this->container);
                if ($count > 1 && $ind != $count)
                {
                    $url .= '+';
                }
            }
        }
        $url .= '&fq=' . $this->solrEscape($query_string) ;
        $url .= '&fq=-id:' .$id;
        foreach ($this->restriction as $restrict_field => $restrict_by)
        {
            $url .= '&fq=' . $restrict_field. ':' . $restrict_by;
        }

        $url .= '&df=fullrecord';
        $url .= '&rows=' . $this->num_related;
        //print_r("related items url " . $url . " ");

        $solr_xml = file_get_contents($url);

        return $solr_xml;
    }

    function getRecentItems($rows = 5)
    {
        $title = $this->recorddisplay[0]; //changed to index
        $url = $this->base_url . $this->solr_collection ."/select?";
        $url .= 'q=' . $this->container_field . ':' . $this->container;
        $url .= '&fq=types:"archival_object"+types:"resource"';
        $url .= '&rows=' . $rows;
        $url .= '&wt=xml';

        //print_r("recent items " . $url);
        $solr_xml = file_get_contents($url);

        $recent_xml = @new SimpleXMLElement($solr_xml);

        // Build search results from solr response
        $recent_items = $this->getResultsFromSolr($recent_xml, $title);

        $data['recent_items'] = $recent_items;

        return $data;
    }

    function getRandomItems($rows = 10)
    {
        $title = $this->recorddisplay[0]; //changed to index
        $url = $this->base_url . 'select?';
        $url .= 'q=*:*';
        if (empty($this->container))
        {
            $url .= '&fq=' . $this->container_field . ':' . $this->container_default;
        }
        else {
            $ind = 0;
            $url .= '&fq=';
            foreach ($this->container as $container_id){
                $ind++;
                $url .= $this->container_field . ':' . $container_id;
                $count = count($this->container);
                if ($count > 1 && $ind != $count)
                {
                    $url .= '+';
                }
            }
        }
        $url .= '&fq=types:"archival_object"+types:"resource"';
        foreach ($this->restriction as $restrict_field => $restrict_by)
        {
            $url .= '&fq=' . $restrict_field. ':' . $restrict_by;
        }

        $url .= '&sort=random_'. mt_rand(1, 10000).'%20desc'; //change
        $url .= '&rows=' . $rows;
        $url .= '&wt=xml';
        //print_r("random " . $url);
        $solr_xml = file_get_contents($url);
        $random_xml = @new SimpleXMLElement($solr_xml);
        $random_items = $this->getResultsFromSolr($random_xml, $title);
        $data['random_items'] = $random_items;
        return $data;
    }

    function browseTerms($field = 'Subject', $rows = 10, $offset = 0, $prefix = '')
    {
        $prefix = $this->solrEscape(strtolower($prefix));
        $rows++;
        $url = $this->base_url . $this->solr_collection ."/select?";
        $url .= 'q=*:*';
        if (empty($this->container))
        {
            $url .= '&fq=' . $this->container_field . ':' . $this->container_default;
        }
        else {
            $ind = 0;
            $url .= '&fq=';
            foreach ($this->container as $container_id){
                $ind++;
                $url .= $this->container_field . ':' . $container_id;
                $count = count($this->container);
                if ($count > 1 && $ind != $count)
                {
                    $url .= '+';
                }
            }
        }
        foreach ($this->restriction as $restrict_field => $restrict_by)
        {
            $url .= '&fq=' . $restrict_field. ':' . $restrict_by;
        }

        $url .= '&rows=0&facet.mincount=1';
        if (preg_match("/Date/", $field)) {
            $facetField = $this->configured_date_filters[$field];
        } else {
            $facetField = $this->configured_filters[$field];
        }
        //$url .= '&facet=true&facet.sort=index&facet.field=' . $facetField . '&facet.limit=' . $rows . '&facet.offset=' . $offset;
        $url .= '&facet=true&facet.sort=index&facet.field=' . $facetField . '&facet.limit=' . $rows;
        if (isset($offset) && $offset > 0) {
            $url .= '&facet.offset=' . $offset;
        }

        if ($prefix !== '') {
            $url .= '&facet.prefix=' . $this->solrEscape($prefix);
        }
        //print_r($url);

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

        $url = $this->base_url . $this->solr_collection ."/select?";
        $url .= 'q=*:*';
        if (empty($this->container))
        {
            $url .= '&fq=' . $this->container_field . ':' . $this->container_default;
        }
        else {
            $ind = 0;
            $url .= '&fq=';
            foreach ($this->container as $container_id){
                $ind++;
                $url .= $this->container_field . ':' . $container_id;
                $count = count($this->container);
                if ($count > 1 && $ind != $count)
                {
                    $url .= '+';
                }
            }
        }
        foreach ($this->restriction as $restrict_field => $restrict_by)
        {
            $url .= '&fq=' . $restrict_field. ':' . $restrict_by;
        }

        $url .= '&rows=0&facet.mincount=1';
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
        $lowest_year = floor($lowest_year / 10) * 10;

        $highest_year = $this->getUpperBound($field, $q, $fq);
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

        $url = $this->base_url . "#/" . $this->solr_collection ."/query?q=" . $this->solrEscape($q);
        if (count($fq) > 0) {
            foreach ($fq as $value)
                $url .= '&fq=' . $this->solrEscape($value) . '';
        }
        //$url .= '&fq=' . $this->container_field . ':' . $this->container;
        //$url .= '&fq=search.resourcetype:2';
        $url .= '&rows=1';
        $url .= '&sort=' . $field . '%20asc';

        $solr_xml = file_get_contents($url);
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

        $url = $this->base_url . "#/" . $this->solr_collection ."/query?q=" . $this->solrEscape($q);
        if (count($fq) > 0) {
            foreach ($fq as $value)
                $url .= '&fq=' . $this->solrEscape($value) . '';
        }
       //$url .= '&fq=' . $this->container_field . ':' . $this->container;
        //$url .= '&fq=search.resourcetype:2
        $url .= '&rows=1';
        $url .= '&sort='.$field.'%20desc';
        //$url .= '&sort=' . $field . '_sort%20desc'; //copied from uoa

        $solr_xml = file_get_contents($url);
        $bounds_xml = @new SimpleXMLElement($solr_xml);
        $field_xml = $bounds_xml->xpath("//result/doc/str[@name='" . $field . "']");

        if (count($field_xml) > 0) {
            $value = $field_xml[0];
        }
        return $value;
    }

    // function to fetch handles and images
    // for sitemap generation
    function getSiteMapURLs($q)
    {

        $url = $this->base_url . "select?indent=on&version=2.2&q=";
        $url .= $q . "&fq=&start=0&rows=10000";

        $solr_xml = file_get_contents($url);
        $result_xml = @new SimpleXMLElement($solr_xml);

        $docs = array();

        $data['docs'] = $docs;
        $data['rows'] = $result_xml->result['numFound'];

        return $data;

    }

    /**
     * @param $recent_xml
     * @param $title
     * @param $recent_items
     * @return array
     */
    public function getResultsFromSolr($recent_xml, $title)
    {
        $items = array();

        foreach ($recent_xml->result->doc as $result) {
            $doc = array();
            foreach ($result->arr as $multivalue_field) {

                $key = $multivalue_field['name'];
                foreach ($multivalue_field->str as $value) {
                    $doc[str_replace('.', '', $key)][] = $value;
                }
            }

            foreach ($result->str as $unique_field) {

                $key = $unique_field['name'];

                if ($key == 'json') {

                    $json_obj = json_decode($unique_field, TRUE);
                    if(!empty($json_obj['dates'])) {
                        $doc['dates'] = $json_obj['dates'][0];
                    }

                    if (!empty($json_obj['component_id'])) {
                        $doc['component_id'] = $json_obj['component_id'];
                    }

                }
                else {
                    $value = $unique_field;
                    $doc[str_replace('.', '', $key)] = $value;
                }
            }
            $handle = preg_split('/\//', $doc['id']);
            //todo top level does not have an id in this format!
            if (count($handle) > 3 && $handle[4] != NULL) {
                $doc['id'] = $handle[4];
            }

            if (!array_key_exists($title, $doc)) {
                $doc[$title][] = 'No title';
            }


            $items[] = $doc;

        }
        return $items;
    }


}



