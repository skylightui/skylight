<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7 ]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel='stylesheet' type='text/css' media='all' href='<?php echo base_url(); ?>theme/default/css/style.css' />
    <!--[if IE]>
    <link rel='stylesheet' type='text/css' media='all' href='<?php echo base_url(); ?>theme/default/css/style-ie.css' />
    <![endif]-->
    <script src="./assets/jquery/jquery-1.6.4.min.js"></script>
    <script src="./assets/plugins/plugins.js"></script>
    <script src="./assets/script/script.js"></script>
    <base href="<?php echo base_url() . index_page(); if (index_page() !== '') { echo '/'; } ?>">

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

<div id="container">

    <header>
        <p class="collection-title"><?php echo $site_title ?></p>
        <a href="http://skylightui.org/" class="logo">Skylight</a>
        <form action="./redirect/" method="post">
            <fieldset class="search">
                <input type="text" name="q" value="<?php if (isset($searchbox_query)) echo urldecode($searchbox_query); ?>" id="q" />
                <input type="submit" name="submit_search" class="btn" value="Search" id="submit_search" />
                <a href="./advanced" class="advanced">Advanced search</a>
            </fieldset>
        </form>
        <nav class="header-links">
            <a href="./">Home</a>
            <a href="./about/">About this site</a>
            <a href="./feedback/" class="last">Feedback</a>
        </nav>
    </header>

    <div id="main" role="main" class="clearfix">