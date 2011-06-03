<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kshe085
 * Date: 20/05/11
 * Time: 3:30 PM
 * To change this template use File | Settings | File Templates.
 */
 
class Solr_admin {

    var $base_url       = 'http://localhost:8983/solr';  // Base URL. Typically not overridden in construct params. Get from config.
    var $container     = '*'; // Default to all collections
    var $container_field = 'location.coll'; // Default to discovery's DSpace collection field

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

        log_message('debug', "skylight Solr Admin Initialized");

    }


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

}
