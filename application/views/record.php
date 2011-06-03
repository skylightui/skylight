<div class="record">

    <ul>
        <?php


            if(isset($solr[$thumbnail_field]) && $display_thumbnail) {
                ?><div class="record_thumbnail"> <?php
                foreach($solr[$thumbnail_field] as $thumbnail) {
                    ?> <img src="<?php echo getBitstreamUri($thumbnail); ?>"> <?php
                } ?></div> <?php
            }

            foreach($recorddisplay as $key => $element) {
                if (isset($solr[$element])) {
                    ?>
                    <ul class="metadatafield">
                        <li>
                            <span class="field"><?php echo $key; ?></span>
                            <ul class="metadatavalue"><?php
                                $i = 0;
                                foreach ($solr[$element] as $metadatavalue) {

                                    ?>
                                    <li<?php if($i == 0) { echo ' class="first"'; } ?>>
                                        <?php
                                           // test filter linking
                                           if($element == 'dccontributorauthoren' ||
                                                   $element == 'dccontributorauthoren_US' ||
                                                   $element == 'dccontributorillustratoren_US' ||
                                                   $element == 'dctypeen' ||
                                                   $element == 'dctypeen_US' ||
                                                   $element == 'dcsubjecten' ||
                                                   $element == 'thesisdegreedisciplineen' ||
                                                   $element == 'thesisdegreegrantoren' ||
                                                   $element == 'dcsubjecten_US'
                                           ) {
                                               // quick hack that only works if the filter key
                                               // and recorddisplay key match and the delimiter is :
                                               $orig_filter = preg_replace('/ /','+',$metadatavalue, -1);
                                               $lc_filter = strtolower($orig_filter);
                                               $orig_filter = preg_replace('/,/','%2C',$orig_filter, -1);
                                               $lc_filter = preg_replace('/,/','%2C',$lc_filter, -1);
                                               echo '<a class=\'filter-link\' href=\'./search/*/'.$key.':"'.$lc_filter.'|||'.$orig_filter.'"\'>'.$metadatavalue.'</a>';
                                           }
                                           elseif($element == 'dcdescriptionabstracten') {
                                               $escaped_value = str_replace('"','',$metadatavalue);
                                               $escaped_value = str_replace('&',' and ',$metadatavalue);
                                               echo $metadatavalue;
                                               echo ' (<a class="speaklink" href="http://api.microsofttranslator.com/V2/Http.svc/Speak?appId=131C77D47703820E5E7C20D01BB5EF743C9FD205&language=en&text='.$escaped_value.'">Speak in English</a>)';
                                           }
                                           else {
                                               if(preg_match('#^http://#',$metadatavalue)) {
                                                   echo '<a class="metadata-link" href="'. $metadatavalue . '" target="_blank">'.$metadatavalue.'</a>';
                                               }
                                               else {
                                                echo $metadatavalue;
                                               }

                                            }
                                        ?></li><?php
                                    $i++;
                                }?>
                            </ul>
                        </li>
                    </ul>
            <?php }
            }
        ?>
    </ul>

    <?php if(isset($solr[$bitstream_field]) && $link_bitstream) {
                                ?><div class="record_bitstreams"><h3>Digital Objects</h3><?php
                                foreach($solr[$bitstream_field] as $bitstream) {
                                    ?><p><span class="label"></span><a class="bitstream_link" target="_blank" href="<?php echo getBitstreamUri($bitstream); ?>"><?php echo getBitstreamFilename($bitstream); ?></a>
                                    (<span class="bitstream_size"><?php echo getBitstreamSize($bitstream); ?></span>, <span class="bitstream_mime"><?php echo getBitstreamMimeType($bitstream); ?></span>, <span class="bitstream_description"><?php echo getBitstreamDescription($bitstream); ?></span>)</p>
                            <?php
                                } ?></div> <?php
                    } ?>

    <?php if($sharethis) { ?>
    <div class="social">
        <span class="st_sharethis_button" displayText="Share"></span>
        <span class="st_facebook_button" ></span>
        <span class="st_twitter_button"></span>
        <span class="st_myspace_button" displayText="Share"></span>
        <span class="st_email_button" displayText="Email"></span>

        <script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
        <script type="text/javascript">
                stLight.options({
                        publisher:'91981337-4997-4027-af04-d23ce8fe5c58'
                });
        </script>
    </div>
    <?php } ?>
</div>