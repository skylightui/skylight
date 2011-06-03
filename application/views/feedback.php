<div class="feedback_form">
    <?php echo validation_errors(); ?>
    <script type="text/javascript">
     var RecaptchaOptions = {
        theme : 'clean'
     };
     </script>

    <?php echo form_open('feedback'); ?>
        Name: <input type="text" name="name" value="<?php echo set_value('name'); ?>" /><br />
        Email: <input type="text" name="email" value="<?php echo set_value('email'); ?>" /><br />
        Feedback: <textarea type="text" name="feedback" rows="15" cols="80" /><?php echo set_value('feedback'); ?></textarea><br />
        Please enter the following verification words into the box:<?php
            echo recaptcha_get_html($recaptcha_key_public);
        ?>
        <input type="submit" />
    </form>
</div>