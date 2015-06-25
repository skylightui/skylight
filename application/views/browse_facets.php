
    <div class="pagination browse_pagination">
        <span class="no-results">
        <strong><?php echo $startrow ?>-<?php echo $endrow ?></strong> of
        <strong><?php echo $total_results ?></strong> results </span>
        <?php echo $pagelinks ?>
    </div>
    <br />
    <div class="browse_results">
        <div class="term_search">
            <form method="get" action="./browse/<?php echo $field; ?>">
                <label for="prefix">Starts with: (case sensitive) </label>
                <input name="prefix" id="prefix" value=""/>
                <input type="submit"/>
            </form>
        </div>
        <br />
        <div class="browse_facets">
            <ul class="browse_facet_list">

                <?php foreach($facet['terms'] as $term) { ?>
                    <li>
                        <a href='<?php echo $base_search; ?>/<?php echo $facet['name']; ?><?php echo $delimiter?>"<?php echo $term['name']; ?>"<?php if(isset($operator)) echo '?operator='.$operator; ?>'><?php echo $term['display_name'];?> (<?php echo $term['count']; ?>)
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>