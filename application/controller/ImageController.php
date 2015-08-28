<?php

class ImageController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handles what happens when user moves to URL/index/index - or - as this is the default controller, also
     * when user moves to /index or enter your application at base level
     */
    public function user($value)
    {
        if(Session::userIsLoggedIn()) {
            $sanitizedVal = strtolower(trim(strip_tags($value)));

            $a0 = Request::get(0);
            $a1 = Request::get(1);

            $user_has_avatar = null;
            if (isset($a1)) $user_has_avatar = 1;
            else if (isset($a0)) $user_has_avatar = 0;

            $user_id = UserModel::getUserIdByUsername($sanitizedVal);       
            $file = AvatarModel::getPublicAvatarFilePathOfUser($user_id, $user_has_avatar);

            $this->View->renderImage($file, 86400); // 24 hour cache
        } else {
            header("HTTP/1.0 404 Not Found");
            $this->View->render('error/404');
        }
    }

}
