<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<!--<script src="http://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>-->
<div class="container" style="position: relative">
    <!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); ?>

    <!-- login box on left side -->        

    <form method="post" action="<?php echo Config::get('HTTPS_URL'); ?>login/register_action" onSubmit="saveItems()">
        <div class="login-box" style="width: 50%; display: block;">
            <h2>Register a new account</h2>

            <span class="red-text" style="font-size: 11px;">(*) Indicates Required Field</span></br>

            <!-- register form -->
                <!-- the user name input field uses a HTML5 pattern check -->
                <label for="user_name">Username: <span class="red-text">(*)</span></label>
                    <input id="user_name" class="<?= $this->form_feedback_user_name ?>" type="text" pattern="^[a-zA-Z][a-zA-Z0-9-_\.]{1,64}" name="user_name" maxlength="64" onblur="saveItems()" placeholder="([letters/numbers/_/-/.] only, 2-64 chars)" required />
                <label for="user_email">Email: <span class="red-text">(*)</span></label>
                    <input id="user_email" class="<?= $this->form_feedback_user_email; ?>" type="email" name="user_email" placeholder="(Must be a valid address)" onblur="saveItems()" maxlength="255" required />
                <label for="user_password_new">Password: <span class="red-text">(*)</span></label>
                    <input id="user_password_new" class="<?= $this->form_feedback_user_password ?>" type="password" name="user_password_new" pattern=".{6,}" placeholder="(6+ characters)" onblur="saveItems()" required autocomplete="off" />
                <label for="user_password_repeat">Repeat Password: <span class="red-text">(*)</span></label>
                    <input id="user_password_repeat" class="<?= $this->form_feedback_user_password ?>" type="password" name="user_password_repeat" pattern=".{6,}" onblur="saveItems()" required autocomplete="off" />
                <label for="user_firstname">First Name: <span class="red-text">(*)</span></label>
                    <input id="user_firstname" class="<?= $this->form_feedback_user_firstname ?>" type="text" pattern="^[a-zA-Z][a-zA-Z- ]{1,64}" name="user_firstname" maxlength="64" onblur="saveItems()" required />
                <label for="user_lastname">Last Name: <span class="red-text">(*)</span></label>
                    <input id="user_lastname" class="<?= $this->form_feedback_user_lastname ?>" type="text" pattern="^[a-zA-Z][a-zA-Z- ]{1,64}" name="user_lastname" maxlength="64" onblur="saveItems()" required />
                <label for="user_dob">Date Of Birth (dd/mm/yyyy): <span class="red-text">(*)</span></label>
                    <input id="user_dob" class="<?= $this->form_feedback_user_dob ?>" type="date" name="user_dob" placeholder="dd/mm/yyyy" min="1920-12-31"  max="1999-12-31" onblur="saveItems()" required />
                <label for="user_addrline1">Address line 1: <span class="red-text">(*)</span></label>
                    <input id="user_addrline1" class="<?= $this->form_feedback_user_addrline1 ?>" type="text" pattern="^[a-zA-Z0-9][a-zA-Z0-9- \(\)]{1,64}" name="user_addrline1" onblur="saveItems()" placeholder="" required />
                <label for="user_addrline2">Address line 2:</label>
                    <input id="user_addrline2" class="<?= $this->form_feedback_user_addrline2 ?>" type="text" pattern="^[a-zA-Z0-9][a-zA-Z0-9- \(\)]{1,64}" name="user_addrline2" onblur="saveItems()" placeholder="Optional" />
                <label for="user_addrline3">Address line 3:</label>
                    <input id="user_addrline3" class="<?= $this->form_feedback_user_addrline3 ?>" type="text" pattern="^[a-zA-Z0-9][a-zA-Z0-9- \(\)]{1,64}" name="user_addrline3" onblur="saveItems()" placeholder="Optional" />
                <label for="user_postcode">Postcode: <span class="red-text">(*)</span></label>
                    <input id="user_postcode" class="<?= $this->form_feedback_user_postcode ?>" type="text" pattern="[A-Za-z]{1,2}[0-9Rr][0-9A-Za-z]? [0-9][ABD-HJLNP-UW-Zabd-hjlnp-uw-z]{2}" onblur="saveItems()" maxlength="8" name="user_postcode" placeholder="(Valid UK postcodes only)" required />
                <label for="user_city">City: <span class="red-text">(*)</span></label>
                    <input id="user_city" class="<?= $this->form_feedback_user_city ?>" type="text" pattern="^[a-zA-Z][a-zA-Z- \(\)]{2,64}" name="user_city" onblur="saveItems()" required />
                <label for="user_country">Country: <span class="red-text">(*)</span></label>
                    <input id="user_country" class="<?= $this->form_feedback_user_country ?>" type="text" pattern="^[a-zA-Z][a-zA-Z- \(\)]{2,64}" name="user_country" onblur="saveItems()" value="United Kingdom" required />
                <label for="user_telephone">Home/Office Telephone:</label>
                    <input id="user_telephone" class="<?= $this->form_feedback_user_telephone ?>" type="tel" pattern="[0-9\+ \(\)]{11,13}" name="user_telephone" placeholder="Optional" onblur="saveItems()" />
                <label for="user_mobile">Mobile: <span class="red-text">(*)</span></label>
                    <input id="user_mobile" class="<?= $this->form_feedback_user_mobile ?>" type="tel" pattern="[0-9\+ \(\)]{11,16}" name="user_mobile" placeholder="(International format [UK:+44])" onblur="saveItems()" required />
                <label for="user_business">Business Name:</label>
                    <input id="user_business" class="<?= $this->form_feedback_user_business ?>" type="text" pattern="^[a-zA-Z][a-zA-Z- \(\)]{1,128}" name="user_business" placeholder="Optional" onblur="saveItems()" />

                <!-- show the captcha by calling the login/showCaptcha-method in the src attribute of the img tag -->
                <label for="captcha_text">Are you human? <span class="red-text">(*)</span></label>
                <img id="captcha" src="<?php echo Config::get('HTTPS_URL'); ?>login/showCaptcha" />
                <input type="text" id="captcha_text" class="<?= $this->form_feedback_error_captcha ?>" name="captcha" placeholder="Please enter above characters" required />

                <!-- quick & dirty captcha reloader -->
                <a href="#" style="display: block; font-size: 11px; margin: 5px 0 15px 0; text-align: center"
                   onclick="document.getElementById('captcha').src = '<?php echo Config::get('HTTPS_URL'); ?>login/showCaptcha?' + Math.random(); return false">Reload Captcha</a>
                <input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
                <input type="submit" value="Register" />
        </div>
        <div id="refUser">
                <h2 style="color: black">User Referral</h2>
                <label for="user_refcode">Referral Code (Case Sensitive):</label>
                <input id="user_refcode" onkeyup="search_user_ref()" pattern="^[a-zA-Z0-9]{3,15}" name="user_ref_code" type="text" value="<?php if (isset($this->refCode)) echo $this->refCode; ?>" placeholder="Enter Code" onblur="saveItems()" />
                <input id="user_ref_username" name="user_ref_username" type="hidden" value="<?php if (isset($this->user_ref_username)) echo $this->user_ref_username; ?>" />
                <div id="user_ref_results" style="height: 42px; margin-top: 10px; <?php if(!isset($this->refResult) || !$this->refResult) echo 'display: none;' ?>" />
                    <img id="user_ref_avatar" src="<?php if(isset($this->user_ref_avatar_file)) echo $this->user_ref_avatar_file ?>" height="40" width="40" style="border: 1px solid #A2A2A2; float: left; margin-right: 10px; " />
                    <span style="font-weight: bold; display: block;">Referrer Name: </span>
                        <span id="user_ref_fullname" style="margin-top: 5px; display: block;">
                        <?php if(isset($this->user_ref_details['user_firstname']) && isset($this->user_ref_details['user_lastname']) && $this->user_ref_details['user_firstname'] != null && $this->user_ref_details['user_lastname'] != null) 
                                echo $this->user_ref_details['user_firstname'].' '.$this->user_ref_details['user_lastname'];
                        ?></span>
                </div>

                <div id="user_ref_results_error" class="feedback error" style="display: none; margin-top: 10px;">
                </div>
        </div>        
    </form>

</div>
<script type="text/javascript">
function search_user_ref_invalid_code(ErrorCode) {
    document.getElementById('user_ref_results').style.display = 'none';
    document.getElementById('user_ref_username').value = '';
    if(typeof ErrorCode !== 'undefined') {
        document.getElementById('user_ref_results_error').style.display = 'block';
        document.getElementById('user_ref_results_error').innerHTML = ErrorCode;
    } else {
        document.getElementById('user_ref_results_error').style.display = 'none';        
    }
    document.getElementById('user_ref_fullname').innerHTML = ''; 
    document.getElementById('user_ref_avatar').src = '';
}

function search_user_ref() {
    if(document.getElementById('user_refcode').value.length >= 4) {
        $.ajax({
            url  : '<?= str_replace('public', '', dirname($_SERVER['SCRIPT_NAME'])); ?>login/userSearchByRefCode',
            data : {refCode: document.getElementById('user_refcode').value},
            type : 'POST' ,
            success : function( output ) {
                if(output['refResult'] == true) {
                    document.getElementById('user_ref_results').style.display = 'block';
                    document.getElementById('user_ref_results_error').style.display = 'none';
                    if(output['user_ref_details']['user_firstname'] != null && output['user_ref_details']['user_lastname'] != null) {
                        document.getElementById('user_ref_fullname').innerHTML = output['user_ref_details']['user_firstname'] + ' ' + output['user_ref_details']['user_lastname']; 
                    }
                    if(output['user_ref_username'] != null) {document.getElementById('user_ref_username').value = output['user_ref_username']}
                    document.getElementById('user_ref_avatar').src = output['user_ref_avatar_file'];
                } else {
                    search_user_ref_invalid_code("Referral Code Invalid");
                }
            }, error: function (error) {
                search_user_ref_invalid_code("Retrieval Error");
            }
        });
    } else if(document.getElementById('user_refcode').value == '') {
        search_user_ref_invalid_code();
    } else {
        search_user_ref_invalid_code("Referral Code Invalid");
    }
} 
</script>
