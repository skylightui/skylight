<div class="search_results">

    <h1>Recently Added</h1>

    <ul id="search_result_list">

       
    <?php
        $date_field = $recorddisplay['Date'];
        foreach ($recent_items as $doc) { ?>

            <?php
                $image = '';
                $type = 'Unknown';

                if(array_key_exists('dctypeen', $doc)) {
                        $type = implode(" ",$doc['dctypeen']);
                }

                if($display_thumbnail && array_key_exists($thumbnail_field, $doc)) {

                        $image = getBitstreamUri($doc[$thumbnail_field][0]);
                }
                else if (file_exists('./assets/images/'.strtolower($type).'.png')) {
                       $image = './assets/images/'.strtolower($type).'.png';
                }
                else {
                    $image = './assets/images/unknown.png';
                }

            ?>

    <li class="<?php echo $type; ?>" <?php if($image !== '') { echo ' style="background-image: url(\''.$image.'\'); background-repeat: no-repeat";'; } ?>>
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
       
        <em>
       <?php if(array_key_exists($date_field, $doc)) { ?>
            <span class="date">
                <?php
                echo '(' . $doc[$date_field][0] . ')';
          }
                    elseif(array_key_exists('dateIssuedyear', $doc)) {
                        echo '( ' . $doc['dateIssuedyear'][0] . ')';
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



        <p class="read_item">Added to index at <?php echo $doc['solr_dcdateaccessioned_dt'][0]?></p>

    </li>
    <?php } ?>
    </ul>

</div>