<?php

    $config['skylight_appname'] = 'Demo';

    $config['skylight_theme'] = 'default';

    $config['skylight_fullname'] = 'Skylight';

    $config['skylight_adminemail'] = 'example@example.com';

    $config['skylight_oaipmhcollection'] = 'hdl_123456789_1';

    // Container ID and the field used in solr index to store this ID. Used for restricting search/browse scope.
    // skylight_container_field can be either location.coll (with the id set to the Collection ID) or
    // location.comm (with the id set to the Community ID). To view items from all colelctions, set the
    // skylight_container_id to '*'.
    $config['skylight_container_field'] = 'location.coll';
    $config['skylight_container_id'] = '*';

    $config['skylight_fields'] = array('Title' => 'dc.title',
                                        'Author' => 'dc.creator',
                                        'Subject' => 'dc.subject',
                                        'Type' => 'dc.type',
                                        'Abstract' => 'dc.description.abstract',
                                        'Date' => 'dc.date.issued',
                                        'Accession Date' => 'dc.date.accessioned_dt'
                                        );

    $config['skylight_date_filters'] = array('Date' => 'dateissued.year_sort');
    $config['skylight_filters'] = array('Author' => 'author_superfilter', 'Subject' => 'subject_superfilter', 'Type' => 'dc.type');
    $config['skylight_filter_delimiter'] = ':';

    $config['skylight_meta_fields'] = array('Title' => 'dc.title',
                                              'Author' => 'dc.creator',
                                              'Abstract' => 'dc.description.abstract',
                                              'Subject' => 'dc.subject',
                                              'Date' => 'dc.date.issued',
                                              'Type' => 'dc.type');

    $config['skylight_recorddisplay'] = array('Title','Author','Subject','Type','Abstract');

    $config['skylight_searchresult_display'] = array('Title','Author','Subject','Type','Abstract');

    $config['skylight_search_fields'] = array('Keywords' => 'text',
                                                  'Subject' => 'dc.subject',
                                                  'Type' => 'dc.type',
                                                  'Author' => 'dc.creator'
                                                  );

    $config['skylight_sort_fields'] = array('Title' => 'dc.title',
                                                  'Date' => 'dc.date.issued',
                                                  'Author' => 'dc.creator'
                                                  );

    $config['skylight_feed_fields'] = array('Title' => 'Title',
                                            'Author' => 'Author',
                                            'Subject' => 'Subject',
                                            'Description' => 'Abstract',
                                            'Date' => 'Date');

    $config['skylight_results_per_page'] = 10;
    $config['skylight_share_buttons'] = false;

    $config['skylight_homepage_recentitems'] = true;
    $config['skylight_homepage_fullwidth'] = false;

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

    // Common title prefix
    $config['skylight_page_title_prefix'] = "Skylight: ";

    // SR 2/12/13 Add highlighting config
    $config['skylight_highlight_fields'] = 'dc.title.en,dc.contributor.author,dc.subject.en,lido.country.en,dc.description.en,dc.relation.ispartof.en';

?>