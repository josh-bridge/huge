<div class="container">
    <h1>Request a password reset</h1>
    <div class="box">

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <!-- request password reset form box -->
        <form method="post" action="<?php echo Config::get('URL'); ?>login/requestPasswordReset_action">
            <label>
                Enter your username or email and you'll get a mail with instructions:
                <p><input type="text" name="user_name_or_email" required />
            </label>
            <input type="submit" value="Send me a password-reset mail" /></p>
        </form>

        <span style="color: red; font-size: 11px;">We do this to make sure it's actually you trying to change the password!</span>

    </div>
</div>
