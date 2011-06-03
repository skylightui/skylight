 <?php if (isset($facets)) { ?>
     <div class="search_filters">
    
     <?php foreach ($facets as $facet) { ?>
        <ul class="search_filter_list"><li class="heading"><strong><?php echo $facet['name']; ?></strong></li>

        <?php if(preg_match('/Date/',$base_search) && $facet['name'] == 'Date') {

                $fpattern =  '#\/'.$facet['name'].$delimiter.'.*\]#';
                $fremove = preg_replace($fpattern,'',$base_search, -1);

                ?>

             <li class="active">
                    <a href='<?php echo $fremove;?>'><strong>[X]</strong></a> Remove <?php echo $facet['name']; ?> filters
             </li>

        <?php } ?>

        <?php foreach($facet['terms'] as $term) {

                if($term['active']) {
                    $pattern =  '#\/'.$facet['name'].$delimiter.'%22'.preg_quote(preg_replace('/\|/','%7C',$term['name'],-1)).'%22#';
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
                    <a href='<?php echo $base_search; ?>/<?php echo $facet['name']; ?><?php echo $delimiter?>"<?php echo $term['name']; ?>"<?php echo $base_parameters ?>'><?php echo $term['display_name'];?> (<?php echo $term['count']; ?>)
                    </a>
                </li>
            <?php
                  }
               }
            foreach($facet['queries'] as $term) {
                $pattern =  '#\/'.$facet['name'].$delimiter.'.*\]#';
                $remove = preg_replace($pattern,'',$base_search, -1);
                if($term['count'] > 0) {
                ?>
                <li>
                    <a href='<?php echo $remove; ?>/<?php echo $facet['name']; ?><?php echo $delimiter?><?php echo $term['name']; ?><?php if(isset($operator)) echo '?operator='.$operator; ?>'><?php echo $term['display_name'];?> (<?php echo $term['count']; ?>)
                    </a>
                </li>
              <?php
                 }

               }

                if(empty($facet['terms']) && empty($facet['queries'])) { ?>
                    <li>No matches</li>
               <?php }
                 elseif(!empty($facet['terms'])) {
            ?> <li><a class="viewmore" href='./browse/<?php echo $facet['name']; ?>'>Browse <?php echo $facet['name']; ?>s</a><?php } ?>
            </ul>
        <?php } ?>
    </div>
<?php } ?>