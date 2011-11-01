
<h1>Advanced Search</h1>

<script type="text/javascript">
     $(document).ready(function() {
        $("input#Subject").autocomplete({
        source: './autocomplete?field=dc.subject_ac',
            max: 5
        });
         $("input#Author").autocomplete({
        source: './autocomplete?field=dc.contributor.author_ac',
            max: 5
        });
         $("input#Artist").autocomplete({
        source: './autocomplete?field=dc.contributor.illustrator_ac',
            max: 5
        });
         $("input#Type").autocomplete({
        source: './autocomplete?field=dc.type_ac',
            max: 5
        });
      });

</script>

<?php if($formhidden) {
    // We're hiding the form in search results
    ?>
    <p><strong><a href="#" id="showform">Change Advanced Search options</a></strong></p>
<?php } ?>

<div class="searchform" style="display:<?php echo $formhidden == true ? 'none' : 'block'; ?>">
    <p><strong>Hint: </strong> To match an exact phrase, try using quotation marks, eg. <em>"a search phrase"</em></p>
<?php    echo $form;
    ?>

</div>

<script>
    $("#showform").click(function() {
        $(".searchform").show();
        $(this).hide();
        $(".message").hide();
      <?php
        if(isset($saved_search)) {

        foreach($saved_search as $key => $val) {
            ?>
                $("input#<?php echo preg_replace('# #','_',$key,-1); ?>").val('<?php echo urldecode($val); ?>');
            <?php

        }
        } ?>

        return false;
    });
</script>
