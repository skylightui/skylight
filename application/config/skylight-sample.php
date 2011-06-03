<?php

    // The URL of the parent solr server
    $config['skylight_solrbase'] = 'http://your.repository.org:8080/solr/search/';

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