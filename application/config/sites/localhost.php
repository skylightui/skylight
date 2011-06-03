<?php

    $config['skylight_appname'] = 'nzais';

    $config['skylight_theme'] = 'nzais';

    $config['skylight_fullname'] = 'New Zealand Asia Institute Information Service';

    $config['skylight_adminemail'] = 'bic.library@auckland.ac.nz';

    $config['skylight_oaipmhcollection'] = 'hdl_123456789_3';


    // Container ID and the field used in solr index to store this ID. Used for restricting search/browse scope.
    $config['skylight_container_id'] = '9';
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

    $config['skylight_language_default'] = 'en';
    $config['skylight_language_options'] = array('en', 'ko', 'jp');


?>