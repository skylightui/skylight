<?php

    $config['skylight_appname'] = 'Demo';

    $config['skylight_theme'] = 'default';

    $config['skylight_fullname'] = 'Name of your collection';

    $config['skylight_adminemail'] = 'example@example.com';

    $config['skylight_oaipmhcollection'] = 'hdl_123456789_1';


    // Container ID and the field used in solr index to store this ID. Used for restricting search/browse scope.
    $config['skylight_container_id'] = '1';
    $config['skylight_container_field'] = 'location.coll';

    $config['skylight_date_filters'] = array('Date' => 'dc.date.issued.year');

    $config['skylight_filters'] = array('Subject' => 'dc.subject_filter', 'Type' => 'dc.type_filter', 'Author' => 'dc.contributor.author_filter');
    $config['skylight_filter_delimiter'] = ':';

    $config['skylight_meta_fields'] = array('Title' => 'dc.title',
                                              'Author' => 'dc.creator',
                                              'Abstract' => 'dc.description.abstract',
                                              'Subject' => 'dc.subject',
                                              'Date' => 'dc.date.issued',
                                              'Type' => 'dc.type');

    $config['skylight_recorddisplay'] = array('Title' => 'dctitleen',
                                                'Author' => 'dccontributorauthoren',
                                                'Subject' => 'dcsubjecten',
                                                'Type' => 'dctypeen',
                                                'Abstract' => 'dcdescriptionabstracten'
                                                );

    $config['skylight_search_fields'] = array('Keywords' => 'text',
                                                  'Subject' => 'dc.subject',
                                                  'Type' => 'dc.type',
                                                  'Author' => 'dc.contributor.author'
                                                  );

    $config['skylight_results_per_page'] = 10;
    $config['skylight_share_buttons'] = false;

    // Set to the number of minutes to cache pages for. Set to false for no caching.
    // This overrides the setting in skylight.php so is commented by default
    $config['skylight_cache'] = false;

    // Digital object management
    $config['skylight_display_thumbnail'] = true;
    $config['skylight_link_bitstream'] = true;

    // Display common image formats in "light box" gallery?
    $config['skylight_lightbox'] = true;
    $config['skylight_lightbox_mimes'] = array('image/jpeg', 'image/gif', 'image/png');

    // Language and locale settings
    $config['skylight_language_default'] = 'en';
    $config['skylight_language_options'] = array('en', 'ko', 'jp');

?>