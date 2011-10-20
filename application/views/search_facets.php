 <?php if (isset($facets)) {?>

     <div class="search_filters">
    
     <?php foreach ($facets as $facet) { ?>
        <ul class="search_filter_list"><li class="heading"><strong><?php echo $facet['name']; ?></strong><a class="viewmore" href='./browse/<?php echo $facet['name']; ?>'>MORE ></a></li>

        <?php if(preg_match('/Date/',$base_search) && $facet['name'] == 'Date') {

                $fpattern =  '#\/'.$facet['name'].'.*\]#';
                $fremove = preg_replace($fpattern,'',$base_search, -1);

                ?>

             <li class="active">
                    <a href='<?php echo $fremove;?>'><strong>[X]</strong></a> Remove <?php echo $facet['name']; ?> filters
             </li>

        <?php } ?>

        <?php foreach($facet['terms'] as $term) {

                if($term['active']) {
                    $pattern =  '#\/'.$facet['name'].':%22'.preg_quote($term['name'],-1).'%22#';
                    $remove = preg_replace($pattern,'',$base_search, -1);
                    ?>
                   <li class="active">
                    <a href='<?php echo $remove;?>'><strong>[X]</strong></a> <?php echo $term['display_name'];?> (<?php echo $term['count']; ?>)
                   </li>
                   <?php
                }
                else {
                    ?>
                <li>
                    <a href='<?php echo $base_search; ?>/<?php echo $facet['name']; ?>:"<?php echo $term['name']; ?>"<?php echo $base_parameters ?>'><?php echo $term['display_name'];?> (<?php echo $term['count']; ?>)
                    </a>
                </li>
            <?php
                  }
               }
            foreach($facet['queries'] as $term) {
                $pattern =  '#\/'.$facet['name'].'.*\]#';
                $remove = preg_replace($pattern,'',$base_search, -1);
                if($term['count'] > 0) {
                ?>
                <li>
                    <a href='<?php echo $remove; ?>/<?php echo $facet['name']; ?>:<?php echo $term['name']; ?><?php if(isset($operator)) echo '?operator='.$operator; ?>'><?php echo $term['display_name'];?> (<?php echo $term['count']; ?>)
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
    </div>
<?php } ?>