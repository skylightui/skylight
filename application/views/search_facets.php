 <?php if (isset($facets)) {?>
    
     <?php foreach ($facets as $facet) {

         $inactive_terms = array();
         $active_terms = array();

         ?>
        <h4><a href="./browse/<?php echo $facet['name']; ?>"><?php echo $facet['name'] ?></a></h4>

        <?php if(preg_match('/Date/',$base_search) && $facet['name'] == 'Date') {
                $fpattern =  '#\/'.$facet['name'].'.*\]#';
                $fremove = preg_replace($fpattern,'',$base_search, -1);

                ?>
            <ul class="selected">
             <li>
                   Clear <?php echo $facet['name']; ?> filters <a class="deselect" href='<?php echo $fremove;?>'></a>
             </li>
            </ul>
        <?php }

                foreach($facet['terms'] as $term) {

                    if($term['active']) {

                        $active_terms[] = $term;

                    }
                    else {
                        $inactive_terms[] = $term;
                    }

                }


         if(sizeof($active_terms) > 0) { ?>
        <ul class="selected">
            <?php foreach($active_terms as $term) {
               $pattern =  '#\/'.$facet['name'].':%22'.preg_quote($term['name'],-1).'%22#';
               $remove = preg_replace($pattern,'',$base_search, -1);
            ?>
            <li><?php echo $term['display_name'];?> (<?php echo $term['count']; ?>) <a class="deselect" href='<?php echo $remove;?>'></a></li>
        <?php
           }
        ?> </ul> <?php
         }
         ?>
        <ul>
        <?php foreach($inactive_terms as $term) {
                    ?>
                <li>
                    <a href='<?php echo $base_search; ?>/<?php echo $facet['name']; ?>:"<?php echo $term['name']; ?>"<?php echo $base_parameters ?>'><?php echo $term['display_name'];?> (<?php echo $term['count']; ?>)
                    </a>
                </li>
            <?php
        }
               
            foreach($facet['queries'] as $term) {
                $pattern =  '#\/'.$facet['name'].'.*\]#';
                $remove = preg_replace($pattern,'',$base_search, -1);
                if($term['count'] > 0) {
                ?>
                <li>
                    <a class="deselect" href='<?php echo $remove; ?>/<?php echo $facet['name']; ?>:<?php echo $term['name']; ?><?php if(isset($operator)) echo '?operator='.$operator; ?>'><?php echo $term['display_name'];?> (<?php echo $term['count']; ?>)
                    </a>
                </li>
              <?php
                 }

               }

                if(empty($facet['terms']) && empty($facet['queries'])) { ?>
                    <li>No matches</li>
               <?php } ?>
            </ul>
        <?php } ?>

<?php } ?>