<?php
#please put in auth before using it anywhere that's not localhost and locked down from the outside
// class UserModel extends AppModel
// {
//     public static function getCurrentUserId()
//     {
//         return $_SESSION['user_id'] ?? 1;
//     }
// }

class UserModel extends AppModel
{
    public function __construct(private $config = null) {
        parent::__construct($config);
        $this->config = $config;
        //$this->auth = new Auth();
    }
    public static function getCurrentUserId()
    {
        return $_SESSION['user_id'] ?? 1;
    }
    public static function requireLogin() {
        $auth->set_users($pref_users);
        $auth->on_init_web();
        if ($auth) {
          $auth->require_login();
        } else {
          die("failed to load auth");
        }
        
    }
}

