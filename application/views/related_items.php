<?php

        $title_field = $recorddisplay['Title'];
        $author_field = $recorddisplay['Author'];
        $artist_field = "dc.contributor.illustrator"; // hardcoded values are OK too

?>

<div class="related_items">

    <h1>Related Items</h1>

    <ul id="search_result_list">

       
    <?php foreach ($related_items as $doc) { ?>
    <li>
        <h3><a href="./record/<?php echo $doc['id']?>"><?php echo $doc[$title_field][0]; ?></a></h3>
        <?php if(array_key_exists($author_field,$doc)) { ?>
        <span class="authors">
            <?php

            $num_authors = 0;
            foreach ($doc[$author_field] as $author) {
               // test author linking
               // quick hack that only works if the filter key
               // and recorddisplay key match and the delimiter is :
               $orig_filter = preg_replace('/ /','+',$author, -1);
               $lc_filter = strtolower($orig_filter);
               $orig_filter = preg_replace('/,/','%2C',$orig_filter, -1);
               $lc_filter = preg_replace('/,/','%2C',$lc_filter, -1);
               echo '<a class=\'filter-link\' href=\'./search/*/Author:"'.$lc_filter.'|||'.$orig_filter.'"\'>'.$author.'</a>';
                $num_authors++;
                if($num_authors < sizeof($doc[$author_field])) {
                    echo '; ';
                }
            }


            ?>
        </span><br/>
            <?php } ?>
        <?php if(array_key_exists($artist_field,$doc)) { ?>
        <span class="artists">
            <?php

            $num_artists = 0;
            foreach ($doc[$artist_field] as $artist) {
               // test author linking
               // quick hack that only works if the filter key
               // and recorddisplay key match and the delimiter is :
               $orig_filter = preg_replace('/ /','+',$artist, -1);
               $lc_filter = strtolower($orig_filter);
               $orig_filter = preg_replace('/,/','%2C',$orig_filter, -1);
               $lc_filter = preg_replace('/,/','%2C',$lc_filter, -1);
               echo '<a class=\'filter-link\' href=\'./search/*/Artist:"'.$lc_filter.'|||'.$orig_filter.'"\'>'.$artist.'</a>';
                $num_artists++;
                if($num_artists < sizeof($doc[$artist_field])) {
                    echo '; ';
                }
            }


            ?>
        </span><br/>
            <?php } ?>
        
        <?php
            if(array_key_exists('dcdescriptionabstracten', $doc)) {
                echo '<p class="abstract">';
                $abstract =  $doc['dcdescriptionabstracten'][0];
                $abstract_words = explode(' ',$abstract);
                $shortened = '';
                $max = 15;
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


    </li>
    <?php } ?>
    </ul>

</div>