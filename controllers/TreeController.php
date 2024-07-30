<?php
// require_once 'Tree.php';

class TreeController
{
    private $tree;
    private $userId;
    private $templatedir = ".";
    private $translations = [];
    public function __construct($db, $userId)
    {
        $this->tree = new TreeModel($db);
        $this->userId = $userId;
        $this->basedir = dirname(__DIR__);
    }
    public function getRelationshipTypes()
    {
        $relationshipTypes = $this->memberModel->getRelationshipTypes();
        echo json_encode($relationshipTypes);
        exit;
    }

    public function listTrees()
    {
        $trees = $this->tree->getAllTreesByOwner($this->userId);
        include $this->basedir . "/templates/list_trees.php";
    }
    public function searchMembers()
    {
        $treeId = $_GET['tree_id'];
        $query = $_GET['query'];
        $members = $this->tree->searchMembers($treeId, $query);
        echo json_encode($members);
    }
    public function addTree()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $success = $this->tree->addTree($this->userId, $name, $description);
            if ($success) {
                header("Location: index.php?action=list_trees");
                exit();
            } else {
                $error = "Failed to add tree.";
            }
        }
        include $this->basedir . "/templates/add_tree.php";
    }
    public function viewTree()
    {
        $familyTreeId = $_GET['tree_id'] ?? $_GET['family_tree_id']; // Get family_tree_id from the request
        include $this->basedir . "/templates/view_tree.php";
    }

    public function getTreeData()
    {
        $familyTreeId = $_GET['family_tree_id']; // Get family_tree_id from the request
        //$treeModel = new Tree();
        $treeData = $this->tree->getTreeData($familyTreeId);
        header('Content-Type: application/json');
        echo json_encode($treeData);
    }
    public function deleteTree()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $treeId = $_POST['tree_id'];
            $success = $this->tree->deleteTree($treeId, $this->userId);
            if ($success) {
                header("Location: index.php?action=list_trees");
                exit();
            } else {
                $error = "Failed to delete tree.";
            }
        }
    }

    public function listMembers($treeId, $page)
    {
        $limit = 70; // Number of members per page
        $offset = ($page - 1) * $limit;
        $members = $this->tree->getMembersByTreeId($treeId, $offset, $limit);
        $lastUpdates = $this->tree->getMembersByTreeId($treeId, 0, 10, $orderby='updated_at DESC');
        $totalMembers = $this->tree->getPersonCount($treeId);
        $countByGender = $this->tree->countMembersByTreeId($treeId);
        $countByGender['Total']=$totalMembers;
        $synonyms = $this->tree->getSynonymsByTreeId($treeId);
        $countByLastname = $this->tree->countTreeMembersByField($treeId,'last_name', $synonyms);
        $countByFirstname = $this->tree->countTreeMembersByField($treeId,'first_name', $synonyms);
        $totalRelationships = $this->tree->countRelationshipsByTreeId($treeId);
        $totalPages = ceil($totalMembers / $limit);
        $stats=[
            'Par Sexe'=> $countByGender,
            'Relations'=> [
                'Total'=>$totalRelationships,
            ],
            'Par Prénom'=>$countByFirstname,
            'Par Nom de famille'=>$countByLastname,
        ];
        $events = [];
        $activities = [];
        include $this->basedir . "/templates/list_members.php";
    }
}
