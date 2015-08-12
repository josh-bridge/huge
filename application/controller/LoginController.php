<?php

/**
 * LoginController
 * Controls everything that is authentication-related
 */
class LoginController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class. The parent::__construct thing is necessary to
     * put checkAuthentication in here to make an entire controller only usable for logged-in users (for sure not
     * needed in the LoginController).
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Index, default action (shows the login form), when you do login/index
     */
    public function index()
    {
        // if user is logged in redirect to main-page, if not show the view
        if (LoginModel::isUserLoggedIn()) {
            Redirect::home();
        } else {
            $data = array('redirect' => Request::get('redirect') ? Request::get('redirect') : NULL);
            $this->View->render('login/index', $data);
        }
    }

    /**
     * The login action, when you do login/login
     */
    public function login()
    {
         // check if csrf token is valid
        if (!Csrf::isTokenValid()) {
            self::logout();
        }

        // perform the login method, put result (true or false) into $login_successful
        $login_successful = LoginModel::login(
            Request::post('user_name'), Request::post('user_password'), Request::post('set_remember_me_cookie')
        );

        // check login status: if true, then redirect user login/showProfile, if false, then to login form again
        if ($login_successful) {
            if (Request::post('redirect')) {
                Redirect::to(ltrim(urldecode(Request::post('redirect')), '/'));
            } else {
                Redirect::to('dashboard/index');
            }
        } else {
            Redirect::to('login/index');
        }
    }

    /**
     * The logout action
     * Perform logout, redirect user to main-page
     */
    public function logout()
    {
        LoginModel::logout();
        Redirect::home();
        exit();
    }

    /**
     * Login with cookie
     */
    public function loginWithCookie()
    {
        // run the loginWithCookie() method in the login-model, put the result in $login_successful (true or false)
         $login_successful = LoginModel::loginWithCookie(Request::cookie('remember_me'));

        // if login successful, redirect to dashboard/index ...
        if ($login_successful) {
            Redirect::to('dashboard/index');
        } else {
            // if not, delete cookie (outdated? attack?) and route user to login form to prevent infinite login loops
            LoginModel::deleteCookie();
            Redirect::to('login/index');
        }
    }

    /**
     * Show user's PRIVATE profile
     * Auth::checkAuthentication() makes sure that only logged in users can use this action and see this page
     */
    public function showProfile()
    {
        Auth::checkAuthentication();

        $user_main_details = array(
            'user_name' => Session::get('user_name'),
            'user_email' => Session::get('user_email'),
            'user_gravatar_image_url' => Session::get('user_gravatar_image_url'),
            'user_avatar_file' => Session::get('user_avatar_file'),
            'user_account_type' => Session::get('user_account_type')
        );

        $this->View->render('login/showProfile', array_merge($user_main_details, Session::get('user_details')));
    }

    /**
     * Show edit-my-username page
     * Auth::checkAuthentication() makes sure that only logged in users can use this action and see this page
     */
    public function editUsername()
    {
        Auth::checkAuthentication();
        $this->View->render('login/editUsername');
    }

    /**
     * Edit user name (perform the real action after form has been submitted)
     * Auth::checkAuthentication() makes sure that only logged in users can use this action
     */
    public function editUsername_action()
    {
        Auth::checkAuthentication();

        // check if csrf token is valid
        if (!Csrf::isTokenValid()) {
            self::logout();
        }

        UserModel::editUserName(Request::post('user_name'));
        Redirect::to('login/index');
    }

    /**
     * Show edit-my-user-email page
     * Auth::checkAuthentication() makes sure that only logged in users can use this action and see this page
     */
    public function editUserEmail()
    {
        Auth::checkAuthentication();
        $this->View->render('login/editUserEmail');
    }

    /**
     * Edit user email (perform the real action after form has been submitted)
     * Auth::checkAuthentication() makes sure that only logged in users can use this action and see this page
     */
    // make this POST
    public function editUserEmail_action()
    {
        Auth::checkAuthentication();

        // check if csrf token is valid
        if (!Csrf::isTokenValid()) {
            self::logout();
        }

        UserModel::editUserEmail(Request::post('user_email'));
        Redirect::to('login/editUserEmail');
    }

    /**
     * Edit avatar
     * Auth::checkAuthentication() makes sure that only logged in users can use this action and see this page
     */
    public function editAvatar()
    {
        Auth::checkAuthentication();
        $this->View->render('login/editAvatar', array(
            'avatar_file_path' => AvatarModel::getPublicUserAvatarFilePathByUserId(Session::get('user_id'))
        ));
    }

    /**
     * Perform the upload of the avatar
     * Auth::checkAuthentication() makes sure that only logged in users can use this action and see this page
     * POST-request
     */
    public function uploadAvatar_action()
    {
        Auth::checkAuthentication();
        AvatarModel::createAvatar();
        Redirect::to('login/editAvatar');
    }

    /**
     * Delete the current user's avatar
     * Auth::checkAuthentication() makes sure that only logged in users can use this action and see this page
     */
    public function deleteAvatar_action()
    {
        Auth::checkAuthentication();
        AvatarModel::deleteAvatar(Session::get("user_id"));
        Redirect::to('login/editAvatar');
    }

    /**
     * Show the change-account-type page
     * Auth::checkAuthentication() makes sure that only logged in users can use this action and see this page
     */
    public function changeUserRole()
    {
        Auth::checkAuthentication();
        $this->View->render('login/changeUserRole');
    }

    /**
     * Perform the account-type changing
     * Auth::checkAuthentication() makes sure that only logged in users can use this action
     * POST-request
     */
    public function changeUserRole_action()
    {
        Auth::checkAuthentication();

        if (Request::post('user_account_upgrade')) {
            // "2" is quick & dirty account type 2, something like "premium user" maybe. you got the idea :)
            UserRoleModel::changeUserRole(2);
        }

        if (Request::post('user_account_downgrade')) {
            // "1" is quick & dirty account type 1, something like "basic user" maybe.
            UserRoleModel::changeUserRole(1);
        }

        Redirect::to('login/changeUserRole');
    }

    /**
     * Register page
     * Show the register form, but redirect to main-page if user is already logged-in
     */
    public function register($ref = null, $refCode = null)
    {
        if (LoginModel::isUserLoggedIn()) {
            Redirect::home();
        } else {
            $array = LoginModel::form_feedback_array();
            $input_data = [];
            
            if(isset($ref) && $ref == 'ref' && isset($refCode) && preg_match('/^[a-zA-Z0-9]{3,15}$/', $refCode)) {
                $input_data = RegistrationModel::userByRefCode($refCode);

                if(!$input_data) {
                    $input_data = array('refCode' => $refCode, 'refResult' => false);
                } else
                    $input_data['refCode'] = $refCode;

            } else if (!preg_match('/^[a-zA-Z0-9]{3,15}$/', $refCode)) {
                $input_data = array('refCode' => $refCode, 'refResult' => false);
            }

            $array = array_merge($input_data, LoginModel::form_feedback_array());
            $this->View->render('login/register', $array);
        }
    }

    /*
    public function genRefCode() {
        $this->View->renderWithoutHeaderAndFooter('login/genRefCode', array('refCode' => RegistrationModel::refCodeGenerate()));
    }
    */

    /*
    public function genCardNum() {
        $this->View->renderWithoutHeaderAndFooter('login/genCardNum', array('cardNum' => RegistrationModel::cardNumGenerate()));
    }
    */

    /**
     * Register page action
     * POST-request after form submit
     */
    public function register_action()
    {
        $registration_successful = RegistrationModel::registerNewUser();

        if ($registration_successful) {
            Redirect::to('login/index');
        } else {
            Redirect::to('login/register');
        }
    }

    /**
     * Verify user after activation mail link opened
     * @param int $user_id user's id
     * @param string $user_activation_verification_code user's verification token
     */
    public function verify($user_id, $user_activation_verification_code)
    {
        if (isset($user_id) && isset($user_activation_verification_code)) {
            RegistrationModel::verifyNewUser($user_id, $user_activation_verification_code);
            $this->View->render('login/verify');
        } else {
            Redirect::to('login/index');
        }
    }

    /**
     * Show the request-password-reset page
     */
    public function requestPasswordReset()
    {
        $this->View->render('login/requestPasswordReset');
    }

    /**
     * The request-password-reset action
     * POST-request after form submit
     */
    public function requestPasswordReset_action()
    {
        PasswordResetModel::requestPasswordReset(Request::post('user_name_or_email'));
        Redirect::to('login/index');
    }

    /**
     * Verify the verification token of that user (to show the user the password editing view or not)
     * @param string $user_name username
     * @param string $verification_code password reset verification token
     */
    public function verifyPasswordReset($user_name, $verification_code)
    {
        // check if this the provided verification code fits the user's verification code
        if (PasswordResetModel::verifyPasswordReset($user_name, $verification_code)) {
            // pass URL-provided variable to view to display them
            $this->View->render('login/resetPassword', array(
                'user_name' => $user_name,
                'user_password_reset_hash' => $verification_code
            ));
        } else {
            Redirect::to('login/index');
        }
    }

    /**
     * Set the new password
     * Please note that this happens while the user is not logged in. The user identifies via the data provided by the
     * password reset link from the email, automatically filled into the <form> fields. See verifyPasswordReset()
     * for more. Then (regardless of result) route user to index page (user will get success/error via feedback message)
     * POST request !
     * TODO this is an _action
     */
    public function setNewPassword()
    {
        PasswordResetModel::setNewPassword(
            Request::post('user_name'), Request::post('user_password_reset_hash'),
            Request::post('user_password_new'), Request::post('user_password_repeat')
        );
        Redirect::to('login/index');
    }

    /**
     * Password Change Page
     * Show the password form if user is logged in, otherwise redirect to login page
     */
    public function changePassword()
    {
        Auth::checkAuthentication();
        $this->View->render('login/changePassword');
    }

    /**
     * Password Change Action
     * Submit form, if retured positive redirect to index, otherwise show the changePassword page again
     */
    public function changePassword_action()
    {
        
        Auth::checkAuthentication();

        // check if csrf token is valid
        if (!Csrf::isTokenValid()) {
            self::logout();
        }

        $result = PasswordResetModel::changePassword(
            Session::get('user_name'), Request::post('user_password_current'),
            Request::post('user_password_new'), Request::post('user_password_repeat')
        );

        if($result)
            Redirect::to('dashboard/index');
        else
            Redirect::to('login/changePassword');
    }


    public function changeUserDetails() {
        Auth::checkAuthentication();

        $this->View->render('login/changeUserDetails', array_merge(Session::get('user_details'), LoginModel::form_feedback_array()));
    }

    public function changeUserDetails_action() {
        Auth::checkAuthentication();

        // check if csrf token is valid
        if (!Csrf::isTokenValid()) {
            self::logout();
        }

        $result = UserModel::changeUserDetails(
            Session::get('user_id'),
            trim(strip_tags(Request::post('user_firstname'))), 
            trim(strip_tags(Request::post('user_lastname'))),
            trim(strip_tags(Request::post('user_dob'))), 
            trim(strip_tags(Request::post('user_addrline1'))), 
            trim(strip_tags(Request::post('user_addrline2'))),
            trim(strip_tags(Request::post('user_addrline3'))), 
            trim(strip_tags(Request::post('user_postcode'))),
            trim(strip_tags(Request::post('user_city'))), 
            trim(strip_tags(Request::post('user_country'))), 
            trim(strip_tags(Request::post('user_telephone'))),
            trim(strip_tags(Request::post('user_mobile'))), 
            trim(strip_tags(Request::post('user_business')))
        );

        if($result)
            Redirect::to('login/showProfile');
        else
            Redirect::to('login/changeUserDetails');
    }

    public function userSearchByName() {
        $searchTerm = Request::post('searchTerm') ? trim(strip_tags(Request::post('searchTerm'))) : NULL;
        $results = Request::post('results') ? intval(trim(strip_tags(Request::post('results')))) : NULL;

        if($searchTerm != null) {$this->View->renderJSON(UserModel::searchUsersByName($searchTerm, $results));}
    }

    public function userSearchByRefCode() {
        $refCode = Request::post('refCode') ? trim(strip_tags(Request::post('refCode'))) : NULL;
        $result = RegistrationModel::userByRefCode($refCode);

        if(!$result)
            $result = array('result' => false);
        
        $this->View->renderJSON($result);
    }

    /**
     * Generate a captcha, write the characters into $_SESSION['captcha'] and returns a real image which will be used
     * like this: <img src="......./login/showCaptcha" />
     * IMPORTANT: As this action is called via <img ...> AFTER the real application has finished executing (!), the
     * SESSION["captcha"] has no content when the application is loaded. The SESSION["captcha"] gets filled at the
     * moment the end-user requests the <img .. >
     * Maybe refactor this sometime.
     */
    public function showCaptcha()
    {
        CaptchaModel::generateAndShowCaptcha();
    }
}


