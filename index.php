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
function apachelog($foo) {
    if(is_array($foo)) {
        $foo = print_r($foo,true);
    }
    $foo .= "\n";
    file_put_contents('php://stderr', print_r($foo, TRUE)) ;
}
$action = $_GET['action'] ?? $_POST['action']??'';

$user = new UserModel($config);
$userId = $user->getCurrentUserId();
$config['current-user']=$userId;
if (!$userId) {
    header("Location: login.php");
    exit();
}
$page = $_GET['page'] ?? 1;
$treeController = new TreeController($config);
$memberController = new MemberController($config);
$familyController = new FamilyController($config);

switch ($action) {
    case 'get_spouse_families':
        $memberController->getSpouseFamilies();
        exit;
        break;
    case 'list_trees':
        $treeController->listTrees();
        break;
    case 'add_tree':
        $treeController->addTree();
        break;
    case 'export_tree':
        $treeController->exportTree();
        break;
    case 'migrate_tree':
        $treeController->migrateTree();
        break;
    case 'view_tree':
        $treeController->viewTree();
        break;
    case 'get_tree_data':
        $treeController->getTreeData();
        break;
    case 'delete_tree':
        $treeController->deleteTree();
        break;
    case 'edit_tree':
    case 'list_members':
        $treeId = $_GET['tree_id'];
        $treeController->listMembers($treeId, $page);
        break;

    case 'add_member':
        $treeId = $_GET['tree_id'];
        $memberController->addMember($treeId);
        break;
    case 'add_tag':

        $response =  $memberController->addTag();
        echo $response;
        exit;
        break;

    case 'delete_tag':
        if($memberController->deleteTag()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
        break;

    case 'reload_tags':
        $tags = $memberController->listTags();
        break;
    case 'view_member':
    case 'edit_member':
        $memberId = $_GET['member_id'];
        $memberController->editMember($memberId);
        break;

    case 'visualize_descendants':
        $memberId = $_GET['member_id'];
        $memberController->visualizeDescendants($memberId);
        break;

    case 'get_descendants_data':
        $memberId = $_GET['member_id'];
        $memberController->getDescendantsData($memberId);
        break;

    case 'get_relationship_types':
        $memberId = $_GET['tree_id']??1;
        echo $memberController->getRelationshipTypes($memberId);
        break;
    case 'delete_member':
        // Handle deleting member
        //include 'controllers/MemberController.php';
        //$memberController = new MemberController();
        $memberId = isset($_POST['member_id']) ? $_POST['member_id'] : null;
        $treeId = isset($_POST['treeId']) ? $_POST['treeId'] : null;
        $member = $memberController->getMemberById($memberId);
        $treeId=$member['tree_id']??false;
        if($memberId) {
            $success = $memberController->deleteMember($memberId);
            if($treeId){
                header("Location: index.php?action=list_members&tree_id=$treeId");
            } else {
                header("Location: index.php?action=list_trees");
            }
        }
        $treeController->listMembers($treeId??1,$page);
        // Redirect or display appropriate message after deletion
        break;
    case 'autocomplete_member':
        $term = $_GET['term'];
        $memberId = $_GET['member_id']??1;
        $tree = $_GET['tree_id']??1;
        //apachelog("autocomplete_member $term $memberId $tree");
        $memberController->autocompleteMember($term,$memberId,$tree);
        exit;
        break;
    case 'search_members':
        $treeController->searchMembers();
        break;
    case 'add_relationship':
        $familyController->addRelationship();
        exit;
        break;
    case 'swap_relationship':
        //apachelog($_POST['relationship_id']);
        $memberController->swapRelationshipAction();
        // /apachelog("hello world");
        break;
    case 'old_add_relationship':
        $memberId = $_POST['member_id'] ?? '';
        $personId2 = $_POST['member2_id'] ?? '';
        $relationshipTypeId = $_POST['relationship_type'] ?? '';
        $controller = new MemberController($config);
        //apachelog($_POST);
        $controller->addRelationship($memberId, $personId2, $relationshipTypeId);
        break;
    case 'update_relationship':
        //apachelog($_POST);
        $memberController->updateRelationship($_POST);
        break;    
    case 'get_relationships':
        $memberId = $_GET['member_id'] ?? '';
        $memberController->getRelationships($memberId);
        break;

    case 'delete_relationship':
        $relationshipId = $_POST['relationship_id'] ?? '';
        $controller = new MemberController($config);
        $controller->deleteRelationship($relationshipId);
        break;

    case 'delete_family_member':
        $memberController->deleteFamilyMember();
        break;

    case 'replace_spouse':
        $memberController->replaceSpouse();
        break;

    case 'create_empty_family':
        $memberController->createEmptyFamily();
        break;

    case 'get_families':
        $memberId = $_GET['member_id'] ?? null;
        $memberController->getFamilies($memberId);
        break;

    default:
        header("Location: index.php?action=list_trees");
        exit();
}
