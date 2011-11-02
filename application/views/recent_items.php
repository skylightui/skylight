    <h3>Recently added items</h3>

    <ul class="listing">

    <?php foreach ($docs as $index => $doc) {

        $type = 'Unknown';

        if(isset($doc[$type_field])) {
                    $type = "media-" . strtolower(str_replace(' ','-',$doc[$type_field][0]));
                }
        ?>

    <li<?php if($index == 0) { echo ' class="first"'; } elseif($index == sizeof($docs) - 1) { echo ' class="last"'; } ?>>
        <span class="icon <?php echo $type ?>"></span>
        <h3><a href="./record/<?php echo $doc['id']?>?highlight=<?php echo $query ?>"><?php echo $doc[$title_field][0]; ?></a></h3>
        <div class="tags">


        <?php if(array_key_exists($author_field,$doc)) { ?>

            <?php

            $num_authors = 0;
            foreach ($doc[$author_field] as $author) {
               // test author linking
               // quick hack that only works if the filter key
               // and recorddisplay key match and the delimiter is :
               $orig_filter = preg_replace('/ /','+',$author, -1);
               $orig_filter = preg_replace('/,/','%2C',$orig_filter, -1);
               echo '<a href=\'./search/*/Author:"'.$orig_filter.'"\'>'.$author.'</a>';
                $num_authors++;
                if($num_authors < sizeof($doc[$author_field])) {
                    echo ' ';
                }
            }


            ?>

            <?php } ?>

       <?php if(array_key_exists($date_field, $doc)) { ?>
            <span>
                <?php
                echo '(' . $doc[$date_field][0] . ')';
          }
                    elseif(array_key_exists('dateIssuedyear', $doc)) {
                        echo '( ' . $doc['dateIssuedyear'][0] . ')';
                    }

                ?>
                </span>



        <?php
        // TODO: Make highlighting configurable

        if(array_key_exists('highlights',$doc)) {
            ?> <p><?php
            foreach($doc['highlights'] as $highlight) {
                echo "...".$highlight."...".'<br/>';
            }
            ?></p><?php
        }
        else {
            if(array_key_exists('dcdescriptionabstract', $doc)) {
                echo '<p>';
                $abstract =  $doc['dcdescriptionabstract'][0];
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
        }

        ?>

        </div> <!-- close tags div -->

    </li>
        <?php } ?>
    </ul>