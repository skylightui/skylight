<div class="feedback_form">

    <h1>Feedback</h1>

    <p>Please contact us with your suggestions or questions below.</p>
    <?php echo validation_errors(); ?>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <form>
        <?php echo form_open($form_prefix.'feedback'); ?>
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" class="form-control" id="name" value="<?php echo set_value('name'); ?>">
        </div>
        <div class="form-group">
            <label for="email">Email address:</label>
            <input type="email" class="form-control" id="email" value="<?php echo set_value('email'); ?>">
        </div>
        <div class="form-group">
            <label for="feedback">Message:</label>
            <textarea type="text" id="feedback" name="feedback" rows="15" cols="80" /><?php echo set_value('feedback'); ?></textarea>
        </div>

        <div class="form-group">
            <div class="g-recaptcha" data-sitekey="6Lftij0UAAAAAKGve8mTK0JDHl5jnazeBYlEc8Sx"></div>
            <!--<div class="g-recaptcha" data-sitekey="6LdICVoUAAAAAJJHzP3XW7Y7yc9PtjeD5IcCUPC_"></div>-->
            <div id="html_element"></div>

            <button type="submit" class="btn btn-custom">Submit</button>
            <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
                    async defer>
            </script>
        </div>
    </form>
    <br>
    <br>

    <h3>Privacy Statement </h3>
    <p>Information about you: how we use it and with whom we share it
    </p>
    <p>
        The information you provide in this form will be used only for purposes of your enquiry. We will not share your personal information with any third party or use it for any other purpose.
        We are using information about you because it is necessary to contact you regarding your enquiry. By providing your personal data when submitting an enquiry to us, consent for your personal data to be used in this way is implied.
    </p>
    <p>
        For digitisation orders, your personal data is necessary for the performance of our contract to provide you with services that charge a fee.
    </p>
    <p>
        We will hold the personal data you provided us for 6 years. We do not use profiling or automated decision-making processes.
    </p>
    <p>
        If you have any questions, please contact: CRC Services Manager is-crc@ed.ac.uk
    </p>
    <p><a href="https://www.ed.ac.uk/records-management/notice" target="_blank">Continued privacy statement</a></p>


</div>