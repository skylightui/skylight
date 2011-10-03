<?php
if ($suggestion != NULL && $suggestion != "")
{
   ?>

        <div class="suggestion">You searched for "<?php echo urldecode($query) ?>".
    Did you mean <a href="./search/<?php echo $suggestion ?>">
                <?php echo $suggestion ?>
            </a></div>


   <?php
}?>