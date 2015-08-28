<?php

/**
 * Class RegistrationModel
 *
 * Everything registration-related happens here.
 */


class RegistrationModel
{
    /**
     * Handles the entire registration process for DEFAULT users (not for people who register with
     * 3rd party services, like facebook) and creates a new user in the database if everything is fine
     *
     * @return boolean Gives back the success status of the registration
     */

    public static function registerNewUser()
    {
        // clean the input
        $user_name = strtolower(trim(strip_tags(Request::post('user_name'))));
        $user_email = strtolower(trim(strip_tags(Request::post('user_email'))));
        $user_password_new = Request::post('user_password_new');
        $user_password_repeat = Request::post('user_password_repeat');

        $user_firstname = trim(strip_tags(Request::post('user_firstname')));
        $user_lastname = trim(strip_tags(Request::post('user_lastname')));
        $user_dob = trim(strip_tags(Request::post('user_dob')));
        $user_addrline1 = trim(strip_tags(Request::post('user_addrline1')));
        $user_addrline2 = trim(strip_tags(Request::post('user_addrline2')));
        $user_addrline3 = trim(strip_tags(Request::post('user_addrline3')));
        $user_postcode = strtoupper(trim(strip_tags(Request::post('user_postcode'))));
        $user_city = trim(strip_tags(Request::post('user_city')));
        $user_country = trim(strip_tags(Request::post('user_country')));
        $user_telephone = trim(strip_tags(Request::post('user_telephone')));
        $user_mobile = trim(strip_tags(Request::post('user_mobile')));
        $user_business = trim(strip_tags(Request::post('user_business')));

        $user_ref_code = trim(strip_tags(Request::post('user_ref_code')));
        $user_ref_username = strtolower(trim(strip_tags(Request::post('user_ref_username'))));

        // stop registration flow if registrationInputValidation() returns false (= anything breaks the input check rules)
        $validation_result = self::registrationInputValidation(
            Request::post('captcha'), 
            $user_name, 
            $user_email, 
            $user_password_new, 
            $user_password_repeat, 

            $user_firstname,
            $user_lastname,
            $user_dob,
            $user_addrline1,
            $user_addrline2,
            $user_addrline3,
            $user_postcode,
            $user_city,
            $user_country,
            $user_telephone,
            $user_mobile,
            $user_business,

            $user_ref_code,
            $user_ref_username
        );

        if (!$validation_result['result']) return false;

        // crypt the password with the PHP 5.5's password_hash() function, results in a 60 character hash string.
        // @see php.net/manual/en/function.password-hash.php for more, especially for potential options
        $user_password_hash = password_hash($user_password_new, PASSWORD_DEFAULT);

        // generate random hash for email verification (40 char string)
        $user_activation_hash = sha1(uniqid(mt_rand(), true));

        $user_cardno = Self::cardNumGenerate();
        $user_refcode = Self::refCodeGenerate();

        if($user_cardno == 0) {
            Session::add('feedback_negative', 'CardNo failed to generate :(');
            return false;
        }

        if ($user_addrline2 == '') $user_addrline2 = null;
        if ($user_addrline3 == '') $user_addrline3 = null;
        if ($user_telephone == '') $user_telephone = null;
        if ($user_business == '') $user_business = null;

        if(!isset($validation_result['new_data']['user_dob'])) {
            Session::add('feedback_negative', 'Date failed to return :(');
            return false;
        }
        if(!isset($validation_result['new_data']['user_introducer_id'])) {
            Session::add('feedback_negative', 'introducer_id failed to return :(');
            return false;
        } else if($validation_result['new_data']['user_introducer_id'] == null) {
            $validation_result['new_data']['user_introducer_id'] = Config::get('DEFAULT_INTRODUCER_ID');
        }

        // write user data to database
        if (!self::writeNewUserToDatabase(
            $user_name, 
            $user_cardno, 
            $user_password_hash, 
            $user_email, 
            $user_refcode,
            $validation_result['new_data']['user_introducer_id'],
            time(), 
            $user_activation_hash,

            $user_firstname,
            $user_lastname,
            $validation_result['new_data']['user_dob'],
            $user_addrline1,
            $user_addrline2,
            $user_addrline3,
            $user_postcode,
            $user_city,
            $user_country,
            $user_telephone,
            $user_mobile,
            $user_business
        )) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CREATION_FAILED'));
        }

        // get user_id of the user that has been created, to keep things clean we DON'T use lastInsertId() here
        $user_id = UserModel::getUserIdByUsername($user_name);

        if (!$user_id) {
            Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
            return false;
        }

        // send verification email
        if (self::sendVerificationEmail($user_id, $user_email, $user_activation_hash)) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_SUCCESSFULLY_CREATED'));
            return true;
        }

        // if verification email sending failed: instantly delete the user
        self::rollbackRegistrationByUserId($user_id);
        Session::add('feedback_negative', Text::get('FEEDBACK_VERIFICATION_MAIL_SENDING_FAILED'));
        return false;
    }

    /**
     * Validates the registration input
     *
     * @param $captcha
     * @param $user_name
     * @param $user_email
     * @param $user_password_new
     * @param $user_password_repeat
     *
     * @param $user_firstname
     * @param $user_lastname
     * @param $user_dob
     * @param $user_addrline1
     * @param $user_addrline2
     * @param $user_addrline3
     * @param $user_postcode
     * @param $user_city
     * @param $user_country
     * @param $user_telephone
     * @param $user_mobile
     * @param $user_business
     * 
     * @return bool
     */
    public static function registrationInputValidation(
        $captcha, 
        $user_name,
        $user_email, 
        $user_password_new, 
        $user_password_repeat,

        $user_firstname,
        $user_lastname,
        $user_dob,
        $user_addrline1,
        $user_addrline2,
        $user_addrline3,
        $user_postcode,
        $user_city,
        $user_country,
        $user_telephone,
        $user_mobile,
        $user_business,

        $user_ref_code,
        $user_ref_username
    ) {
        $result = array();

        // perform all necessary checks
        $validateCaptcha = CaptchaModel::checkCaptcha($captcha);
        $validateUserData = Self::validateUserData($user_name, $user_email, $user_password_new, $user_password_repeat);
        $validateUserDetails = Self::validateUserDetails($user_firstname,$user_lastname,$user_dob,$user_addrline1,$user_addrline2,$user_addrline3,$user_postcode,$user_city,$user_country,$user_telephone,$user_mobile,$user_business);
        $validateRef = Self::validateRef($user_ref_code, $user_ref_username);

        if($validateCaptcha AND $validateUserData['result'] AND $validateUserDetails['result'] AND $validateRef['result']) {
            return Self::validateFeedbackArr(true, array('user_introducer_id' => $validateRef['new_data']['user_introducer_id'], 'user_dob' => $validateUserDetails['new_data']['user_dob']));
        } else {
            if(!$validateUserData['result'] || !$validateUserDetails['result'] || !$validateRef['result']) {
                Session::add('form_feedback_error_captcha', 'formredo');
            } else if (!$validateCaptcha) {
                Session::add('feedback_negative', Text::get('FEEDBACK_CAPTCHA_WRONG'));
                Session::add('form_feedback_error_captcha', 'formerror');
            }

            if(null === Session::get('form_feedback_user_password')) Session::add('form_feedback_user_password', 'formredo');
            if(in_array('reqfieldempty', $validateUserDetails['errors'])) 
                Session::add('feedback_negative', Text::get('FEEDBACK_REQUIRED_FIELDS_EMPTY'));

            return Self::validateFeedbackArr(false);
        }
    }

    /**
     * Validates the user data
     *
     * @param $user_name
     * @param $user_email
     * @param $user_password_new
     * @param $user_password_repeat
     * 
     * @return bool
     */

    public static function validateUserData($user_name, $user_email, $user_password_new, $user_password_repeat) 
    {
        $result = true;
        $valresult = true;

        if (empty($user_name)) {Session::add('form_feedback_user_name', 'formerror'); $result = false;}
            // if username is too short (2), too long (64) or does not fit the pattern (aZ09)
            else if (!preg_match('/^[a-zA-Z][a-zA-Z0-9-_\.]{1,64}$/', $user_name)) {Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_DOES_NOT_FIT_PATTERN')); Session::add('form_feedback_user_name', 'formerror'); $valresult = false;}
            // check if username already exists
            else if (UserModel::doesUsernameAlreadyExist($user_name)) {Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_ALREADY_TAKEN')); Session::add('form_feedback_user_name', 'formerror'); $valresult = false;}
        
        if (empty($user_email)) {Session::add('form_feedback_user_email', 'formerror'); $result = false;}
            // validate the email with PHP's internal filter
            // side-fact: Max length seems to be 254 chars
            // @see http://stackoverflow.com/questions/386294/what-is-the-maximum-length-of-a-valid-email-address
            else if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_DOES_NOT_FIT_PATTERN')); Session::add('form_feedback_user_email', 'formerror'); $valresult = false;}
            // check if email already exists
            else if (UserModel::doesEmailAlreadyExist($user_email)) {Session::add('feedback_negative', Text::get('FEEDBACK_USER_EMAIL_ALREADY_TAKEN')); Session::add('form_feedback_user_email', 'formerror'); $valresult = false;}
       
        if (empty($user_password_new) OR empty($user_password_repeat)) {Session::add('form_feedback_user_password', 'formerror'); $result = false;}
            else if ($user_password_new !== $user_password_repeat) {Session::add('feedback_negative', Text::get('FEEDBACK_PASSWORD_REPEAT_WRONG')); Session::add('form_feedback_user_password', 'formerror'); $valresult = false;}
            else if (strlen($user_password_new) < 6) {Session::add('feedback_negative', Text::get('FEEDBACK_PASSWORD_TOO_SHORT')); Session::add('form_feedback_user_password', 'formerror'); $valresult = false;}

        if (!$result) {
            return Self::validateFeedbackArr(false, false, 'reqfieldempty');
        } else if(!$valresult) {
            return Self::validateFeedbackArr(false);
        } else if($result && $valresult) {
            return Self::validateFeedbackArr(true);
        }

        return Self::validateFeedbackArr(false, false, 'did_not_validate');
    }

    public static function validateUserDetails($user_firstname,$user_lastname,$user_dob,$user_addrline1,$user_addrline2,$user_addrline3,$user_postcode,$user_city,$user_country,$user_telephone,$user_mobile,$user_business)
    {
        $result = true;
        $valresult = true;
        
        if (empty($user_firstname)) {Session::add('form_feedback_user_firstname', 'formerror'); $result = false;}
            else if (!preg_match('/^[a-zA-Z][a-zA-Z- ]{1,64}$/', $user_firstname)) {Session::add('form_feedback_user_firstname', 'formerror'); Session::add('feedback_negative', Text::get('FEEDBACK_FIRSTNAME_DOES_NOT_FIT_PATTERN')); $valresult = false;} 
        if (empty($user_lastname)) {Session::add('form_feedback_user_lastname', 'formerror'); $result = false;}
            else if (!preg_match('/^[a-zA-Z][a-zA-Z- ]{1,64}$/', $user_lastname)) {Session::add('form_feedback_user_lastname', 'formerror'); Session::add('feedback_negative', Text::get('FEEDBACK_LASTNAME_DOES_NOT_FIT_PATTERN')); $valresult = false;} 
        if (empty($user_dob)) {Session::add('form_feedback_user_dob', ' formerror'); $result = false;}
            else if(!Self::validateDOB($user_dob)['result']) {Session::add('form_feedback_user_dob', ' formerror'); $valresult = false;}
        if (empty($user_addrline1)) {Session::add('form_feedback_user_addrline1', 'formerror'); $result = false;}
            else if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9- \(\)]{1,64}$/', $user_addrline1)) {Session::add('form_feedback_user_addrline1', 'formerror'); Session::add('feedback_negative', Text::get('FEEDBACK_ADDRLINE1_DOES_NOT_FIT_PATTERN')); $valresult = false;} 
        if (empty($user_postcode)) {Session::add('form_feedback_user_postcode', 'formerror'); $result = false;}
            else if (!preg_match('/^[A-Za-z]{1,2}[0-9Rr][0-9A-Za-z]? [0-9][ABD-HJLNP-UW-Zabd-hjlnp-uw-z]{2}$/', $user_postcode)) {Session::add('form_feedback_user_postcode', 'formerror'); Session::add('feedback_negative', Text::get('FEEDBACK_POSTCODE_DOES_NOT_FIT_PATTERN')); $valresult = false;} 
        if (empty($user_city)) {Session::add('form_feedback_user_city', 'formerror'); $result = false;}
            else if (!preg_match('/^[a-zA-Z][a-zA-Z- \(\)]{2,64}$/', $user_city)) {Session::add('form_feedback_user_city', 'formerror'); Session::add('feedback_negative', Text::get('FEEDBACK_CITY_DOES_NOT_FIT_PATTERN')); $valresult = false;} 
        if (empty($user_country)) {Session::add('form_feedback_user_country', 'formerror'); $result = false;}
            else if (!preg_match('/^[a-zA-Z][a-zA-Z- \(\)]{2,64}$/', $user_country)) {Session::add('form_feedback_user_country', 'formerror'); Session::add('feedback_negative', Text::get('FEEDBACK_COUNTRY_DOES_NOT_FIT_PATTERN')); $valresult = false;} 
        if (empty($user_mobile)) {Session::add('form_feedback_user_mobile', 'formerror'); $result = false;}
            else if (!preg_match('/^[0-9\+ \(\)]{11,16}$/', $user_mobile)) {Session::add('form_feedback_user_mobile', 'formerror'); Session::add('feedback_negative', Text::get('FEEDBACK_MOBILE_DOES_NOT_FIT_PATTERN')); $valresult = false;} 

        if (!empty($user_addrline2) AND !preg_match('/^[a-zA-Z0-9][a-zA-Z0-9- \(\)]{1,64}$/', $user_addrline2)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ADDRLINE2_DOES_NOT_FIT_PATTERN'));
            Session::add('form_feedback_user_addrline2', 'formerror'); 
            $valresult = false;
        } 

        if (!empty($user_addrline3) AND !preg_match('/^[a-zA-Z0-9][a-zA-Z0-9- \(\)]{1,64}$/', $user_addrline3)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ADDRLINE3_DOES_NOT_FIT_PATTERN')); 
            Session::add('form_feedback_user_addrline3', 'formerror'); 
            $valresult = false;
        } 

        if (!empty($user_telephone) AND !preg_match('/^[0-9\+ \(\)]{11,13}$/', $user_telephone)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_TELEPHONE_DOES_NOT_FIT_PATTERN'));
            Session::add('form_feedback_user_telephone', 'formerror'); 
            $valresult = false;
        } 

        if (!empty($user_business) AND !preg_match('/^[a-zA-Z][a-zA-Z- \(\)]{1,128}$/', $user_business)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_BUSINESS_DOES_NOT_FIT_PATTERN'));
            Session::add('form_feedback_user_business', 'formerror'); 
            $valresult = false;
        }

        if (!$result) {
            return Self::validateFeedbackArr(false, false, 'reqfieldempty');
        } else if(!$valresult) {
            return Self::validateFeedbackArr(false);
        } else if($result && $valresult) {
            return Self::validateFeedbackArr(true, array('user_dob' => $validateDOB['new_data']['user_dob']));
        }

        return Self::validateFeedbackArr(false, false, 'did_not_validate');
    }

    public static function validateDOB($user_dob) {

        $split = array();

        $result = false;
        $formattedDate = $user_dob;

        if(preg_match("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/", $user_dob, $split)) {
            $result = true;
            $formattedDate = $split[3].'-'.$split[2].'-'.$split[1];
        } else if (preg_match("/^([0-9]{4})\/([0-9]{2})\/([0-9]{2})$/", $user_dob, $split)) {
            $result = true;
            $formattedDate = $split[1].'-'.$split[2].'-'.$split[3];
        } else if (preg_match("/^([0-9]{2})-([0-9]{2})-([0-9]{4})$/", $user_dob, $split)) {
            $result = true;
            $formattedDate = $split[3].'-'.$split[2].'-'.$split[1];
        } else if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $user_dob, $split)) {
            $result = true;
        }

        if(!$result) {
            Session::add('feedback_negative', Text::get('FEEDBACK_DOB_DOES_NOT_FIT_PATTERN'));
            return Self::validateFeedbackArr(false, false, 'FEEDBACK_DOB_DOES_NOT_FIT_PATTERN');
        }

        $matched = preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $formattedDate, $split);

        if (!checkdate($split[2],$split[3],$split[1])){
            Session::add('feedback_negative', Text::get('FEEDBACK_DOB_DOES_NOT_EXIST'));
            return Self::validateFeedbackArr(false, false, 'FEEDBACK_DOB_DOES_NOT_EXIST');
        }

        if(strtotime($formattedDate) > strtotime("-18 years", time())) {
            Session::add('feedback_negative', Text::get('FEEDBACK_DOB_AGE_TOO_LOW'));
            return Self::validateFeedbackArr(false, false, 'FEEDBACK_DOB_AGE_TOO_LOW');
        }

        return Self::validateFeedbackArr(true, array('user_dob' => $formattedDate));
    }

    public static function validateRef ($user_ref_code, $user_ref_username) 
    {
        $empty[0] = (empty($user_ref_code) || $user_ref_code == null) ? true : false;
        $empty[1] = (empty($user_ref_username) || $user_ref_username == null) ? true : false;

        $user_ref_username_pattern = preg_match('/^[a-zA-Z][a-zA-Z0-9-_\.]{1,64}$/', $user_ref_username);
        $user_ref_code_pattern = preg_match('/^[a-zA-Z0-9]{3,15}$/', $user_ref_code);

        if($empty[0] && $empty[1]) return Self::validateFeedbackArr(true, array('user_introducer_id' => null), 'empty');
        
        $ref_code_exists = Self::isAlreadyExists('users', 'user_refcode', $user_ref_code);
        $ref_user_name_exists = UserModel::doesUsernameAlreadyExist($user_ref_username);

        if($empty[0]) {
            if(!$ref_user_name_exists) return Self::validateFeedbackArr(false, array('user_introducer_id' => null), 'user_ref_username_invalid');
        } else if($empty[1]) {
            if(!$ref_code_exists) return Self::validateFeedbackArr(false, array('user_introducer_id' => null), 'user_ref_code_invalid');
            Session::add('feedback_negative', 'Ref code invalid');
        } else if(!$user_ref_code_pattern && !$user_ref_username_pattern) {
            return Self::validateFeedbackArr(false, array('user_introducer_id' => null), 'pattern_invalid');
        } else if(!$ref_code_exists && !$ref_user_name_exists) {
            return Self::validateFeedbackArr(false, array('user_introducer_id' => null), 'invalid');
        } else if ($ref_user_name_exists) {
            return Self::validateFeedbackArr(true, array('user_introducer_id' => UserModel::getUserIdByUsername($user_ref_username)));
        } else if ($ref_code_exists) {
            return Self::validateFeedbackArr(true, array('user_introducer_id' => UserModel::getUserIdByRefCode($user_ref_code)));
        }

        return Self::validateFeedbackArr(false, false, 'unknown_ref_error');
    }

    public static function validateFeedbackArr($result, $new_data = null, $errors = null)
    {
        if($new_data === null) $new_data = false;
        if($errors === null) $errors = false;

        if(!is_array($errors) && $errors != false) $errors = array($errors);

        return array('result' => $result, 'new_data' => $new_data, 'errors' => $errors);
    }

    /**
     * Writes the new user's data to the database
     *
     * @param $user_name
     * @param $user_password_hash
     * @param $user_email
     * @param $user_creation_timestamp
     * @param $user_activation_hash
     *
     * @return bool
     */
    public static function writeNewUserToDatabase(
        $user_name, 
        $user_cardno, 
        $user_password_hash, 
        $user_email, 
        $user_refcode,
        $user_introducer_id,
        $user_creation_timestamp, 
        $user_activation_hash,

        $user_firstname,
        $user_lastname,
        $user_dob,
        $user_addrline1,
        $user_addrline2,
        $user_addrline3,
        $user_postcode,
        $user_city,
        $user_country,
        $user_telephone,
        $user_mobile,
        $user_business
    ) {
        $database = DatabaseFactory::getFactory()->getConnection();

        $result[0] = false;
        
        // write new users data into database

        $sql = "INSERT INTO users (user_name, user_cardno, user_password_hash, user_email, user_refcode, user_introducer_id, user_creation_timestamp, user_activation_hash, user_provider_type)
        VALUES (:user_name, :user_cardno, :user_password_hash, :user_email, :user_refcode, :user_introducer_id, :user_creation_timestamp, :user_activation_hash, :user_provider_type)";

        $query = $database->prepare($sql);
        $query->execute(array(
            ':user_name' => $user_name,
            ':user_cardno' => $user_cardno,
            ':user_password_hash' => $user_password_hash,
            ':user_email' => $user_email,
            ':user_refcode' => $user_refcode,
            ':user_introducer_id' => $user_introducer_id,
            ':user_creation_timestamp' => $user_creation_timestamp,
            ':user_activation_hash' => $user_activation_hash,
            ':user_provider_type' => 'DEFAULT'
        ));

        if($query->rowCount() == 1) $result[0] = true;

        $sql2 = "INSERT INTO users_details (
            user_id,
            user_firstname,
            user_lastname,
            user_dob,
            user_addrline1,
            user_addrline2,
            user_addrline3,
            user_postcode,
            user_city,
            user_country,
            user_telephone,
            user_mobile,
            user_business
        ) VALUES (
            :user_id, 
            :user_firstname,
            :user_lastname,
            :user_dob,
            :user_addrline1,
            :user_addrline2,
            :user_addrline3,
            :user_postcode,
            :user_city,
            :user_country,
            :user_telephone,
            :user_mobile,
            :user_business
        )";

        $query2 = $database->prepare($sql2);
        $query2->execute(array(
            ':user_id' => UserModel::getUserIdByUsername($user_name),
            ':user_firstname' => $user_firstname,
            ':user_lastname' => $user_lastname,
            ':user_dob' => $user_dob,
            ':user_addrline1' => $user_addrline1,
            ':user_addrline2' => $user_addrline2,
            ':user_addrline3' => $user_addrline3,
            ':user_postcode' => $user_postcode,
            ':user_city' => $user_city,
            ':user_country' => $user_country,
            ':user_telephone' => $user_telephone,
            ':user_mobile' => $user_mobile,
            ':user_business' => $user_business
        ));

        if($query2->rowCount() == 1) $result[1] = true;

        if($result[0] && $result[1]) return true;

        return false;
    }

    /**
     * Deletes the user from users table. Currently used to rollback a registration when verification mail sending
     * was not successful.
     *
     * @param $user_id
     */
    public static function rollbackRegistrationByUserId($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("DELETE FROM users WHERE user_id = :user_id");
        $query->execute(array(':user_id' => $user_id));
    }

    /**
     * Sends the verification email (to confirm the account).
     * The construction of the mail $body looks weird at first, but it's really just a simple string.
     *
     * @param int $user_id user's id
     * @param string $user_email user's email
     * @param string $user_activation_hash user's mail verification hash string
     *
     * @return boolean gives back true if mail has been sent, gives back false if no mail could been sent
     */
    public static function sendVerificationEmail($user_id, $user_email, $user_activation_hash)
    {
        $body = Config::get('EMAIL_VERIFICATION_CONTENT') . Config::get('URL') . Config::get('EMAIL_VERIFICATION_URL')
                . '/' . urlencode($user_id) . '/' . urlencode($user_activation_hash);

        $mail = new Mail;
        $mail_sent = $mail->sendMail($user_email, Config::get('EMAIL_VERIFICATION_FROM_EMAIL'),
            Config::get('EMAIL_VERIFICATION_FROM_NAME'), Config::get('EMAIL_VERIFICATION_SUBJECT'), $body
        );

        if ($mail_sent) {
            Session::add('feedback_positive', Text::get('FEEDBACK_VERIFICATION_MAIL_SENDING_SUCCESSFUL'));
            return true;
        } else {
            Session::add('feedback_negative', Text::get('FEEDBACK_VERIFICATION_MAIL_SENDING_ERROR') . $mail->getError() );
            return false;
        }
    }

    /**
     * checks the email/verification code combination and set the user's activation status to true in the database
     *
     * @param int $user_id user id
     * @param string $user_activation_verification_code verification token
     *
     * @return bool success status
     */
    public static function verifyNewUser($user_id, $user_activation_verification_code)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE users SET user_active = 1, user_activation_hash = NULL
                WHERE user_id = :user_id AND user_activation_hash = :user_activation_hash LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => $user_id, ':user_activation_hash' => $user_activation_verification_code));

        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_ACTIVATION_SUCCESSFUL'));
            return true;
        }

        Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_ACTIVATION_FAILED'));
        return false;
    }

    /**
     * generates a card number at most even lengths (default 16 digits)
     *
     * @param int $length
     *
     * @return card number
     */
    public static function cardNumGenerate($length = null) {
        $defaultLength = 16;

        if($length == null) $length = $defaultLength;

        if ($length > 4 && $length < 17 && $length % 2 == 0) {
            $genlength = $length/4;
            $halfLeng = $length/2;
            $finished = false;

            do {
                for($i = 0; $i < 2; $i++) {
                    do {
                        $rand[$i] = hexdec(bin2hex(openssl_random_pseudo_bytes($genlength)));
                        if (strlen(strval($rand[$i])) == $halfLeng) $finished = true;
                    } while (!$finished);
                    $finished = false;
                }
            } while (Self::isAlreadyExists('users', 'user_cardno', $rand[0].$rand[1]));
            return $rand[0].$rand[1];
        }

        return 0;
    }

    /**
     * generates a ref code with any length (default 4) with a-zA-z0-9 NO I or l because they look 
     *    too similar (in arial) and the ref code input is case sensitive
     *
     * @param int $length
     *
     * @return ref code
     */
    public static function refCodeGenerate($length = null) {
        if($length == null) $length = 4;

        do {
            $chrList = '0123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ';
            $chrRepeat = $length; // Times to repeat the seed string
            $result = substr(str_shuffle(str_repeat($chrList, $chrRepeat)),0,$length); 
        } while (Self::isAlreadyExists('users', 'user_refcode', $result));

        return $result;
    }

    /**
     * gets user data & details by their ref code
     *
     * @param $refcode
     *
     * @return user data & details on success / bool on failure
     */
    public static function userByRefCode($refCode) {
        if(Self::isAlreadyExists('users', 'user_refcode', $refCode)) {
            $userData = UserModel::getUserDataByRefCode($refCode);
            $user_details = UserModel::getUserDetailsByUserName($userData->user_name);
            if($user_details != false) {
                $user_avatar_file = AvatarModel::getPublicUserAvatarFilePathByUserId($userData->user_id);
                
                $result = array(
                    'user_ref_username' => $userData->user_name,
                    'user_ref_avatar_file' => $user_avatar_file,
                    'user_ref_details' => array(
                        'user_firstname' => $user_details->user_firstname,
                        'user_lastname' => $user_details->user_lastname
                    ),
                    'refResult' => true
                );
            } else {
                return false;
            }
        } else {
            return false;
        }

        return $result;
    }

    /**
     * generic checker to see if a set field already exists in any table/column/row
     *
     * @param $table
     * @param $column
     * @param $value
     *
     * @return bool success status
     */
    public static function isAlreadyExists($table, $column, $value) {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT ".$column." FROM ".$table." WHERE BINARY ".$column." = :value LIMIT 1";
        $query = $database->prepare($sql);

        $query->execute(array(':value' => $value));

        if($query->rowCount() != 0)
            return true;
        
        return false;
    }
}
