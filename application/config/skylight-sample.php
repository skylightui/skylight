<?php

    // The URL of the parent solr server
    $config['skylight_solrbase'] = 'http://your.repository.org:8080/solr/search/';

    // The platform and version of your repository.
    // Currently DSpace 1.7.1+ is the only supported repository
    $config['skylight_repository_type'] = 'dspace'; // default 'dspace'
    $config['skylight_repository_version'] = '171'; // default '171'

    // The local path for theme and configuration overrides (if required)
    // $config['skylight_local_path'] = '/superindex';

    // The main username and password (by default admin:admin)
    $config['skylight_adminusername'] = 'admin';
    $config['skylight_adminpassword'] = '21232f297a57a5a743894a0e4a801fc3';

    // Whether to use LDAP for admin authentication
    $config['skylight_adminldap'] = False;
    $config['skylight_adminldap_server'] = "ldaps://ldap.example.com:636";
    $config['skylight_adminldap_context'] = "ou=users,dc=example,dc=com";
    $config['skylight_adminldap_allowed'] = array('id1', 'id2');

    // The OAI-PMH base for the parent server
    $config['skylight_oaipmhbase'] = 'http://your.repository.org:8080/oai/request?';

    // The OAI-PMH identifier to replace in OAI-PMH responses
    $config['skylight_oaipmhid'] = 'your.repository.org:123456789/';

    // The link in OAI-PMH responses to replace with the skylight record URL
    $config['skylight_oaipmhlink'] = 'http://hdl.handle.net/123456789/';

    // The URL base for where digital objects can be proxied from
    $config['skylight_objectproxy_url'] = 'http://your.repository.org:8080/bitstream/123456789/';

    // The URL base for where digital objects can be proxied from
    $config['skylight_objectproxy_url'] = 'http://your.repository.org:8080/bitstream/123456789/';

    // Set to the number of minutes to cache pages for. Set to false for no caching.
    // This can be overridden in site-specific configuration files.
    $config['skylight_cache'] = false;

    // Keys required for the recapthca system
    $config['skylight_recaptcha_key_public'] = 'ENTER-HERE';
    $config['skylight_recaptcha_key_private'] = 'ENTER-HERE';

    // Digital object management
    $config['skylight_bitstream_field'] = 'superindex.bitstreams.original.en';
    $config['skylight_thumbnail_field'] = 'superindex.bitstreams.thumbnail.en';
    $config['skylight_display_thumbnail'] = false;
    $config['skylight_link_bitstream'] = false;

    // Other options
    $config['skylight_homepage_recentitems'] = false;

    // Spellchecking / Spelling suggestions
    // Dictionaries must be set up in your local solr configuration
    $config['skylight_solr_dictionary'] = 'default';


    /**
     * Debug / development options.
     *
     * We recommend that these are disabled (or commented out) for production systems
     */

    // Set to true to enable debugging / profiling information
    // $config['skylight_debug'] = false;

    // Can configuration files be overwritten by the user ?config={vhostname}
    // $config['skylight_config_allowoverride'] = false;

    // Can themes be overridden by the user using ?theme={themename}
    // $config['skylight_theme_allowoverride'] = false;

?>