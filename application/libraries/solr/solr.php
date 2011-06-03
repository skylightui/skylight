<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kshe085
 * Date: 27/04/11
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */

class Filter {

    var $term       = '';
    var $facet      = '';
    var $string     = '';

   /**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 */
	public function __construct($facet, $term)
	{
        $this->facet = $facet;
        $this->term = $term;
        $this->string = constructString($facet, $term);
    }

    function constructString($facet, $term) {
        $string = $facet.':'.$term;
        return $string;
    }
}

class FilterQuery {

    var $filters    = array();
    var $url        = '';

    public function __construct()
	{
        
    }

    public function addFilter($filter) {
        if($filter instanceof Filter) {
            $filters[] = $filter;
        }
        else {
            // nothing
        }
    }

    public function removeFilter($filterString) {
        // parse string, remove appropriate filter
    }

}