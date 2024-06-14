<?php
session_start();


require_once 'db.php';
require_once 'User.php';
require_once 'TreeController.php';
require_once 'MemberController.php';
function apachelog($foo) {
    file_put_contents('php://stderr', print_r($foo, TRUE)) ;
}
$action = $_GET['action'] ?? $_POST['action']??'';
// print_r($_POST);
// print_r($_GET);
// exit;
$user = new User($db);
$userId = $user->getCurrentUserId();

if (!$userId) {
    header("Location: login.php");
    exit();
}

$treeController = new TreeController($db, $userId);
$memberController = new MemberController($db);

switch ($action) {
    case 'list_trees':
        $treeController->listTrees();
        break;
    case 'add_tree':
        $treeController->addTree();
        break;
    case 'view_tree':
        //$controller = new TreeController();
        $treeController->viewTree();
        break;
    case 'get_tree_data':
        //$controller = new TreeController();
        $treeController->getTreeData();
        break;
    case 'delete_tree':
        $treeController->deleteTree();
        break;
    case 'edit_tree':
    case 'list_members':
        $treeId = $_GET['tree_id'];
        $page = $_GET['page'] ?? 1;
        $treeController->listMembers($treeId, $page);
        break;

    case 'add_member':
        $treeId = $_GET['tree_id'];
        $memberController->addMember($treeId);
        break;

    case 'view_member':
    case 'edit_member':
        $memberId = $_GET['member_id'];
        $memberController->editMember($memberId);
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
        $treeId=$member['family_tree_id']??false;
        if($memberId) {
            $success = $memberController->deleteMember($memberId);
            if($treeId){
                header("Location: index.php?action=list_members&tree_id=$treeId");
            } else {
                header("Location: index.php?action=list_trees");

            }
    
        }
        $treeController->listMembers($treeId??1);
        // Redirect or display appropriate message after deletion
        break;
    case 'autocomplete_member':
        $term = $_GET['term'];
        $memberId = $_GET['member_id']??1;
        $tree = $_GET['tree_id']??1;
        //apachelog($tree);
        $memberController->autocompleteMember($term,$memberId,$tree);
        exit;
    case 'search_members':
        $treeController->searchMembers();
        break;
    case 'add_relationship':
        $memberId = $_POST['member_id'] ?? '';
        $personId2 = $_POST['member2_id'] ?? '';
        $relationshipTypeId = $_POST['relationship_type'] ?? '';
        $controller = new MemberController($db);
        //apachelog($_POST);
        $controller->addRelationship($memberId, $personId2, $relationshipTypeId);
        break;
    case 'update_relationship':
        apachelog($_POST);
        $memberController->updateRelationship($_POST);
        break;    
    case 'get_relationships':
        $memberId = $_GET['member_id'] ?? '';
        $memberController->getRelationships($memberId);
        break;

    case 'delete_relationship':
        $relationshipId = $_POST['relationship_id'] ?? '';
        $controller = new MemberController($db);
        $controller->deleteRelationship($relationshipId);
        break;

    default:
        header("Location: index.php?action=list_trees");
        exit();
}
?>
