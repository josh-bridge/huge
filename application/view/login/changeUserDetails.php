<div class="container">
    <h1>LoginController/changeUserDetails</h1>

    <!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); ?>

    <div class="box">
        <h2>Edit User Details:</h2>
        <!-- new password form box -->
        <div class="login-box" style="width: 50%; display: block;">
        <form method="post" action="<?php echo Config::get('URL'); ?>login/changeUserDetails_action" name="change_user_details_form" onSubmit="saveItems()">
            <label for="user_firstname">First Name:</label>
                <input id="user_firstname" class="<?= $this->form_feedback_user_firstname ?>" value="<?= $this->user_firstname ?>" type="text" pattern="^[a-zA-Z][a-zA-Z- ]{1,64}" name="user_firstname" maxlength="64" required />
            <label for="user_lastname">Last Name:</label>
                <input id="user_lastname" class="<?= $this->form_feedback_user_lastname ?>" value="<?= $this->user_lastname ?>" type="text" pattern="^[a-zA-Z][a-zA-Z- ]{1,64}" name="user_lastname" maxlength="64" required />
            <label for="user_dob">Date Of Birth (dd/mm/yyyy):</label>
                <input id="user_dob" class="<?= $this->form_feedback_user_dob; ?>" value="<?= $this->user_dob ?>" type="date" name="user_dob" placeholder="dd/mm/yyyy" min="1920-12-31"  max="1999-12-31" required />
            <label for="user_addrline1">Address line 1:</label>
                <input id="user_addrline1" class="<?= $this->form_feedback_user_addrline1 ?>" value="<?= $this->user_addrline1 ?>" type="text" pattern="^[a-zA-Z0-9][a-zA-Z0-9- \(\)]{1,64}" name="user_addrline1" placeholder="" required />
            <label for="user_addrline2">Address line 2:</label>
                <input id="user_addrline2" class="<?= $this->form_feedback_user_addrline2 ?>" value="<?= $this->user_addrline2 ?>" type="text" pattern="^[a-zA-Z0-9][a-zA-Z0-9- \(\)]{1,64}" name="user_addrline2" placeholder="Optional" />
            <label for="user_addrline3">Address line 3:</label>
                <input id="user_addrline3" class="<?= $this->form_feedback_user_addrline3 ?>" value="<?= $this->user_addrline3 ?>" type="text" pattern="^[a-zA-Z0-9][a-zA-Z0-9- \(\)]{1,64}" name="user_addrline3" placeholder="Optional" />
            <label for="user_postcode">Postcode:</label>
                <input id="user_postcode" class="<?= $this->form_feedback_user_postcode ?>" value="<?= $this->user_postcode ?>" type="text" pattern="[A-Za-z]{1,2}[0-9Rr][0-9A-Za-z]? [0-9][ABD-HJLNP-UW-Zabd-hjlnp-uw-z]{2}" maxlength="8" name="user_postcode" placeholder="(Valid UK postcodes only)" required />
            <label for="user_city">City:</label>
                <input id="user_city" class="<?= $this->form_feedback_user_city ?>" value="<?= $this->user_city ?>" type="text" pattern="^[a-zA-Z][a-zA-Z- \(\)]{2,64}" name="user_city" required />
            <label for="user_country">Country:</label>
                <input id="user_country" class="<?= $this->form_feedback_user_country ?>" value="<?= $this->user_country ?>" type="text" pattern="^[a-zA-Z][a-zA-Z- \(\)]{2,64}" name="user_country" required />
            <label for="user_telephone">Home/Office Telephone:</label>
                <input id="user_telephone" class="<?= $this->form_feedback_user_telephone ?>" value="<?= $this->user_telephone ?>" type="tel" pattern="[0-9\+ \(\)]{11,13}" name="user_telephone" placeholder="Optional" />
            <label for="user_mobile">Mobile:</label>
                <input id="user_mobile" class="<?= $this->form_feedback_user_mobile ?>" value="<?= $this->user_mobile ?>" type="tel" pattern="[0-9\+ \(\)]{11,16}" name="user_mobile" placeholder="(International format [UK:+44])" required />
            <label for="user_business">Business Name:</label>
                <input id="user_business" class="<?= $this->form_feedback_user_business ?>" value="<?= $this->user_business ?>" type="text" pattern="^[a-zA-Z][a-zA-Z- \(\)]{1,128}" name="user_business" placeholder="Optional" />
            <input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
            <input type="submit"  name="submit_new_details" value="Submit New Details" />
        </form>
        </div>

    </div>
</div>
