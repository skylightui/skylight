<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
<?php if($this->config->item('skylight_sitemap_type') == "internal") { ?>
    <url>
        <loc><?php echo base_url();?></loc>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?php echo base_url() . "about";?></loc>
        <priority>0.8</priority>
    </url>

    <!-- MIMEd -->
    <url>
        <loc><?php echo base_url() . "mimed";?></loc>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?php echo base_url() . "mimed/about";?></loc>
        <priority>0.8</priority>
    </url>

    <!-- Art Collection -->
    <url>
        <loc><?php echo base_url() . "art";?></loc>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?php echo base_url() . "art/about";?></loc>
        <priority>0.8</priority>
    </url>

    <!-- Calendars Collection -->
    <url>
        <loc><?php echo base_url() . "calendars";?></loc>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?php echo base_url() . "calendars/about";?></loc>
        <priority>0.8</priority>
    </url>

<?php } else { ?>
    <url>
        <loc><?php echo base_url();?></loc>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?php echo base_url() . "about";?></loc>
        <priority>0.8</priority>
    </url>


<?php } ?>

    <!-- Record URLs -->
    <?php foreach($docs as $doc) { ?>
        <url>
            <?php
            $prefix = "";
            // check if there's a prefix
            if(array_key_exists($doc['collection'], $prefixes)) {
                // if so, set it
                $prefix = $prefixes[$doc['collection']];
            }
            ?>
            <loc><?php echo base_url().$prefix.$doc['recordURL']?></loc>

            <?php
            // add each image
            if(array_key_exists("imageURL", $doc)) {
                foreach($doc["imageURL"] as $imageURL) { ?>

                    <image:image>
                        <image:loc><?php echo base_url().$prefix.$imageURL; ?></image:loc>
                    </image:image>

                <?php
                }
            }
            ?>
            <priority>0.5</priority>
        </url>
        <?php //check if there's a PDF
        if(array_key_exists("pdfURL", $doc)) {
            foreach($doc["pdfURL"] as $pdfURL) { ?>
                <url>
                    <loc><?php echo base_url().$prefix.str_replace("&", "%26", $pdfURL)?></loc>
                    <priority>0.5</priority>
                </url>
            <?php
            }
        }
        ?>

    <?php } ?>

</urlset>