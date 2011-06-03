<div class="search_results">

    <?php

        // Set up some variables to easily refer to particular fields you've configured
        // in $config['skylight_searchresult_display']

        $title_field = $fielddisplay['Title'];
        $author_field = $fielddisplay['Author'];
        $artist_field = "dc.contributor.illustrator"; // hardcoded values are OK too

        $base_parameters = preg_replace("/[?&]sort_by=[_a-zA-Z+%20. ]+/","",$base_parameters);
        if($base_parameters == "") {
            $sort = '?sort_by=';
        }
        else {
            $sort = '&sort_by=';
        }
    ?>
    <div class="sort_options" style="position: relative; clear: both; float: left; display: block; width: 100%">
        <p style="display: inline; float: left"><strong>Sort by:</strong></p><ul style="list-style: none;display: inline; float: left; clear: none;">

    <?php foreach($sort_options as $label => $field) { ?>
        <li><strong><?php echo $label ?></strong> ( <a href="<?php echo $base_search.$base_parameters.$sort.$field.'+desc' ?>">descending</a> |
        <a href="<?php echo $base_search.$base_parameters.$sort.$field.'+asc' ?>">ascending</a> )</li>
    <?php } ?>
        </ul>
    </div>

    <div class="pagination">
        Results <strong><?php echo $startrow.' - '.$endrow ?></strong> of <strong><?php echo $rows; ?></strong><br/>
        <?php echo $pagelinks ?>
    </div>

    <ul id="search_result_list">

       
    <?php foreach ($docs as $doc) { ?>

            <?php
                $image = '';
                $type = 'Unknown';

                if(array_key_exists('solr_dctypeen', $doc)) {
                        $type = implode(" ",$doc['solr_dctypeen']);
                }

                if($display_thumbnail) {
                    if(array_key_exists($thumbnail_field, $doc)) {

                        $image = getBitstreamUri($doc[$thumbnail_field][0]);
                    }
                }
                else {
                       $image = './theme/default/images/'.strtolower($type).'.png';
                }

            ?>

    <li class="<?php echo $type; ?>" <?php if($image !== '') { echo ' style="background-image: url(\''.$image.'\');"'; } ?>>
        <h3><a href="./record/<?php echo $doc['id']?>"><?php echo $doc['solr_'.$title_field][0]; ?></a></h3>
        <?php if(array_key_exists('solr_'.$author_field,$doc)) { ?>
        <span class="authors">
            <?php

            $num_authors = 0;
            foreach ($doc['solr_'.$author_field] as $author) {
               // test author linking
               // quick hack that only works if the filter key
               // and recorddisplay key match and the delimiter is :
               $orig_filter = preg_replace('/ /','+',$author, -1);
               $lc_filter = strtolower($orig_filter);
               $orig_filter = preg_replace('/,/','%2C',$orig_filter, -1);
               $lc_filter = preg_replace('/,/','%2C',$lc_filter, -1);
               echo '<a class=\'filter-link\' href=\'./search/*/Author:"'.$lc_filter.'|||'.$orig_filter.'"\'>'.$author.'</a>';
                $num_authors++;
                if($num_authors < sizeof($doc['solr_'.$author_field])) {
                    echo '; ';
                }
            }


            ?>
        </span><br/>
            <?php } ?>
        <?php if(array_key_exists('solr_'.$artist_field,$doc)) { ?>
        <span class="artists">
            <?php

            $num_artists = 0;
            foreach ($doc['solr_'.$artist_field] as $artist) {
               // test author linking
               // quick hack that only works if the filter key
               // and recorddisplay key match and the delimiter is :
               $orig_filter = preg_replace('/ /','+',$artist, -1);
               $lc_filter = strtolower($orig_filter);
               $orig_filter = preg_replace('/,/','%2C',$orig_filter, -1);
               $lc_filter = preg_replace('/,/','%2C',$lc_filter, -1);
               echo '<a class=\'filter-link\' href=\'./search/*/Artist:"'.$lc_filter.'|||'.$orig_filter.'"\'>'.$artist.'</a>';
                $num_artists++;
                if($num_artists < sizeof($doc['solr_'.$artist_field])) {
                    echo '; ';
                }
            }


            ?>
        </span><br/>
            <?php } ?>
        <em>
       <?php if(array_key_exists('solr_superindexnzaisdate', $doc)) { ?>
            <span class="date">
                <?php
                echo 'Published: ' . $doc['solr_superindexnzaisdate'][0];
          }
                    elseif(array_key_exists('solr_dcdateissuedyear', $doc)) {
                        echo 'Published: ' . $doc['solr_superindexnzaisdate'][0];
                    }

                ?>
                </span>
        </em>
        


        <?php
            if(array_key_exists('solr_dcdescriptionabstracten', $doc)) {
                echo '<p class="abstract">';
                $abstract =  $doc['solr_dcdescriptionabstracten'][0];
                $abstract_words = explode(' ',$abstract);
                $shortened = '';
                $max = 40;
                $suffix = '...';
                if($max > sizeof($abstract_words)) {
                    $max = sizeof($abstract_words);
                    $suffix = '';
                }
                for ($i=0 ; $i<$max ; $i++){
                    $shortened .= $abstract_words[$i] . ' ';
                }
                echo $shortened.$suffix;
                echo '</p>';
            }
        ?>



        <p class="read_item"><a class="record_list_links"  href="./record/<?php echo $doc['id']?>">Read more...</a></p>
    </li>
    <?php

        if(array_key_exists('solr_exifgpscoordinates', $doc)) {
            $coordinates[$doc['id']] = $doc['solr_exifgpscoordinates'];
        }

    } ?>
    </ul>

    <div class="pagination">
       <?php echo $pagelinks ?>
    </div>

</div>