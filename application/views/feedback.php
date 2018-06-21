<?php
$email_address = $this->config->item('skylight_adminemail');
?>
<div class="feedback_form">

    <h1>Feedback</h1>

    <p>Please contact us with your suggestions or questions at <a href="mailto:<?php echo $email_address ?>"><?php echo $email_address ?></a>.</p>


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
        If you have any questions, please contact: <a href="mailto:<?php echo $email_address ?>"><?php echo $email_address ?></a>
    </p>
    <p><a href="https://www.ed.ac.uk/records-management/notice" target="_blank">University privacy statement</a></p>


</div>