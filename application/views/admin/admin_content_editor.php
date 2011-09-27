<div class="content">

    <form method="post" action="./admin/savecontent">
        <input type="hidden" name="content" value="<?php echo $content; ?>" />
        <p>
                <textarea name="html" cols="90" rows="25"><?php echo $html; ?></textarea>
                <input type="submit" value="Save" />
        </p>
    </form>

</div>