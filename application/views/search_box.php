<div class="search_box">

    <h3>Basic Search</h3>

    <script type="text/javascript">
     $(document).ready(function() {
        $("input#q").autocomplete({
        source: './autocomplete?field=all_ac',
            max: 5
        });
     });

    </script>

    <form action="./redirect/" method="post">
        <input type="text" name="q" id="q" value="<?php if (isset($searchbox_query)) echo urldecode($searchbox_query); ?>" />
        <input class="button" type="submit" value="Search" />
    </form>

    <a id="advanced-search-link" href="./advanced">Advanced Search</a>

</div>