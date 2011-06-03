<div id="main">
    <?php
        if(isset($page_heading)) {
            $page_title = $page_heading;
        }
    ?>
    <h1><?php echo $page_title; ?></h1>
    <?php if(isset($message)) { ?>
        <div class="message"> <?php echo $message; ?> </div>
    <?php } ?>