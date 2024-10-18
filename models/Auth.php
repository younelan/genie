<?php

#require_once(__DIR__ . "/SimpleAuth.php");

require_once(dirname(__DIR__) .  "/init.php");
require_once "$basedir/data/adminprefs.php";
// require_once(dirname(dirname((__DIR__))) .  "/Plugin.php");
//require_once(__DIR__ . "/adminprefs.php");
// print_r($pref_users);
// print "hi";exit;
class Auth extends AppBase
{

	private $users = [];
	private $simple_auth = null;
	public function __construct($config)
	{
		$this->config = $config;
	}
	function require_login()
	{
		$this->simple_auth->require_login();
	}

	public function set_users($users)
	{
		$this->users = $users;
		if ($this->simple_auth) {
			$this->simple_auth->set_users($users);
		}
	}
	public function on_init_web()
	{
		$paths = $this->config['paths'];
		//print(nl2br(print_r($paths,true)));
		$config = $this->config;
		$users = $this->users ?? $config['users'] ?? [];

		if ($users) {
			$this->set_users($users);
		}
		$vars = $this->config['vars'];
		//print_r($config['translations']);exit;
		//$pref_users = $this->config['users']??[];
		$lang = $config['lang'] ?? 'en';
		if (!isset($config['translations'][$lang])) {
			$lang = 'fr';
		}
		$auth_translations = $config['translations'] ?? [];
		$palette = $config['palette'] ?? 'dark-blue';
		if (!isset($config['palettes'][$palette])) {
			$palette = 'dark-blue';
		}
		$theme = $config['theme'];
		$login_template = $config['login_template'] ?? 'login.tpl';
		$pagestyle = "<style>\n";
		foreach ($config['palettes'][$palette]['css'] as $style) {
			$fname = $paths['frontend'] . "/css/$style";
			//print "$fname\n";
			$pagestyle .= file_get_contents($fname);
		}
		$pagestyle .= "</style>\n";
		$vars['authstyle'] = $pagestyle;
		$config['vars']['authstyle'] = $pagestyle;
		//$simple_auth = new Auth($pref_users, $auth_translations);
		$this->simple_auth = new \Opensitez\Simplicity\SimpleAuth($config);
		$this->simple_auth->set_template("<div>{{content}}</div>");
		$this->simple_auth->set_users($users);
		//$this->simple_auth->set_vars($vars);
		$this->simple_auth->set_template(file_get_contents(__DIR__ . "/templates/$login_template"));
	}
}

// $config['users']=$pref_users;
// //$config['translations'] = $translations;
// $config['vars']=$vars??[];
// $auth = new Auth($config);
// $auth->set_users($pref_users);
// $auth->on_init_web();

// $auth->require_login();

// print "hi";
// Example usage
//$pref_users = ['admin'=>['Password'=>'admin']];
            //$this->users[$user] = password_hash($password, PASSWORD_DEFAULT);


// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     if (isset($_POST['edit_password'])) {
//         $auth->edit_password();
//     } elseif ($auth->login('index.php')) {
//         echo "Logged in successfully!";
//     } else {
//         echo "Login failed.";
//     }
// } else {
//     $auth->show_edit_password_form();
// }

// print_r($_SESSION);
