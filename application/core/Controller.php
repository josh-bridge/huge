<?php

/**
 * This is the "base controller class". All other "real" controllers extend this class.
 * Whenever a controller is created, we also
 * 1. initialize a session
 * 2. check if the user is not logged in anymore (session timeout) but has a cookie
 */
class Controller
{
    /** @var View The view object */
    public $View;
    /** @var newController The newController object */
    public $newController;
    /**
     * Construct the (base) controller. This happens when a real controller is constructed, like in
     * the constructor of IndexController when it says: parent::__construct();
     */
    function __construct()
    {
        // always initialize a session
        Session::init();
        // check session concurrency
        Auth::checkSessionConcurrency();
        // user is not logged in but has remember-me-cookie ? then try to login with cookie ("remember me" feature)
        if (!Session::userIsLoggedIn() && Request::cookie('remember_me')) {
            header('location: ' . Config::get('URL') . 'login/loginWithCookie');
        }
        // create a view object to be able to use it inside a controller, like $this->View->render();
        $this->View = new View();
    }
    /**
     * This functions allows you to use controller/methods within each other with a simple static call.
     * The benefit is being able to use methods without the need to copy them into other controllers. Easier on maintainence too.
     * Controller::method('otherController', 'otherMethod');
     */
    public function method($controller, $method)
    {
        $controller = $controller.'Controller';
        require Config::get('PATH_CONTROLLER') . $controller.'.php';
        $this->newController = new $controller();
        $this->newController->$method();
    }
}