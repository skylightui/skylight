<div class="col-main-full">
    <?php
        if(isset($page_heading)) {
            $page_title = $page_heading;
        }
    ?>

    <?php if(isset($message)) { ?>
        <div class="message"> <?php echo $message; ?> </div>
    <?php } ?>