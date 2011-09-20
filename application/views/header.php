<html>
    <head profile="http://dublincore.org/documents/dcq-html/">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <base href="<?php echo base_url() . index_page(); if (index_page() !== '') { echo '/'; } ?>">
        <title><?php echo $site_title . ': ' . $page_title; ?></title>
        <link rel="alternate" type="application/rss+xml" title='<?php echo $site_title; ?> RSS Feed' href='./feed/' />
        <link rel="SHORTCUT ICON" href="favicon.ico">

        <!-- Load theme CSS -->
        <link rel='stylesheet' type='text/css' media='all' href='<?php echo base_url(); ?>theme/default/css/style.css' />
        <!--[if IE]>
            <link rel='stylesheet' type='text/css' media='all' href='<?php echo base_url(); ?>theme/default/css/style-ie.css' />
        <![endif]-->
        
        <!-- Load JQuery -->
        <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
        <script type='text/javascript' src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
        <script type='text/javascript' src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>

        <!-- Load Pingback -->
        <link rel="pingback" href="<?php echo base_url() . index_page(); if (index_page() !== '') { echo '/'; } echo 'pingback'; ?>" />

        <!-- Load Colorbox -->
        <script type='text/javascript' src='<?php echo base_url(); ?>theme/default/js/jquery.colorbox-min.js'></script>
        <link media="screen" rel="stylesheet" href="<?php echo base_url(); ?>theme/default/css/colorbox.css">

        <?php if (isset($solr)) { ?><link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />
        <link rel="schema.DCTERMS" href="http://purl.org/dc/terms/" />

        <?php

            foreach($metafields as $label => $element) {
                $field = "";
                if(isset($recorddisplay[$label])) {
                    $field = $recorddisplay[$label];
                    if(isset($solr[$field])) {
                        $values = $solr[$field];
                        foreach($values as $value) {
                            ?>  <meta name="<?php echo $element; ?>" content="<?php echo $value; ?>"> <?php
                        }
                    }
                }
            }

        } ?>

    </head>
    <body>
        <div id="header">
            <div id="banner"><h1 class="site-title"><?php echo $site_title ?></h1></div>
            <div id="trail">
                <ul class="trail-links">
                    <li><a href="http://example.com/">Your organisation</a></li>
                    <li><a href="./"><?php echo $site_title ?></a></li>
                </ul>
            </div>
        </div>
        <div id="content">