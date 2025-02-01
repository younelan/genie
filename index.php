<?php
session_start();

$basedir = __DIR__;

require_once "$basedir/vendor/autoload.php";
require_once "$basedir/init.php";

//$i18n = new I18n();

function get_translation($i18nstring,$lang=null,$str_translations=null) {
    global $config;

    if(!$lang) {
        $lang = $config['lang']??"fr";
    }
    if(!$str_translations) {
        $str_translations= $config['translations'][$lang];
    }
    
    if(isset($str_translations[$i18nstring])) {
        return $str_translations[$i18nstring];
    } else {
        return $i18nstring;

    }
}
$page = $_GET['page'] ?? 1;
$treeController = new TreeController($config);
$memberController = new MemberController($config);
$familyController = new FamilyController($config);




$data = [
        "template" => "react_app.tpl",
        "section" => get_translation("Family Trees"),
        "app_title" => $config['app_name'] ?? "Genie",
        "app_logo" => $config['app_logo'] ?? "/genie/res/genie.gif",
        "footer_text" => get_translation("Family Tree Manager"),
        "company_name" => $config['company_name'] ?? "Genie"
];
echo $treeController->render($data);
