<?php
// require_once 'Tree.php';

class TreeController extends AppController
{
    private $tree;
    private $userId;

    private $config = [];
    private $templatedir = ".";
    private $translations = [];
    public function __construct($config)
    {
        $this->config = $config;

        $this->tree = new TreeModel($config);
        $this->userId = $config['current-user'];
        $this->basedir = dirname(__DIR__);
    }
    public function getRelationshipTypes()
    {
        $relationshipTypes = $this->memberModel->getRelationshipTypes();
        echo json_encode($relationshipTypes);
        exit;
    }
    public function render_master($data) {
        $master_file = $this->basedir . "/templates/master.tpl";
        $data["app_title"] = $this->config['app_name']??"Genie";
        $data["section"] = $data["section"]??"";

        $content_file = $this->basedir . "/templates/tree_list.tpl";
        $data['content'] = $this->render_file($content_file, $data);

        echo $this->render_file($master_file, $data);

    }

    public function listTrees()
    {
        $data = [
            "trees" => $this->tree->getAllTreesByOwner($this->userId),
            "template" => $this->basedir . "/templates/tree_list.tpl",
            "section" => get_translation("Family Trees")
        ];
        $data["menu"][] = [
            "link"=>"index.php?action=add_tree",
            "text"=>get_translation("New Tree")
        ];
        // $master_data = [
        //     "app_title"=> $this->config['app_name']??"Genie",
        //     "section" => get_translation("Family Trees"),   
        //     "content"=>$content
        // ];

        echo $this->render_master($data);

        //include $this->basedir . "/templates/list_trees.php";
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
        //include $this->basedir . "/templates/add_tree.php";
    }
    public function viewTree()
    {
        $graph = $this->config['graph'];
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
        $limit = $this->config['limits']['members']??70; // Number of members per page
        $update_limit = $this->config['limits']['updates']??40; // Number of members per page
        $offset = ($page - 1) * $limit;

        $members = $this->tree->getMembersByTreeId($treeId, $offset, $limit);
        $lastUpdates = $this->tree->getMembersByTreeId($treeId, 0, $update_limit, $orderby='updated_at DESC');
        $totalMembers = $this->tree->getPersonCount($treeId);
        $countByGender = $this->tree->countMembersByTreeId($treeId);
        $countByGender['Total']=$totalMembers;
        $synonyms = $this->tree->getSynonymsByTreeId($treeId);
        $countByLastname = $this->tree->countTreeMembersByField($treeId,'last_name', $synonyms);
        $countByFirstname = $this->tree->countTreeMembersByField($treeId,'first_name', $synonyms);
        $totalRelationships = $this->tree->countRelationshipsByTreeId($treeId);
        $totalPages = ceil($totalMembers / $limit);
        $stats=[
            'By Gender'=> $countByGender,
            'Relations'=> [
                'Total'=>$totalRelationships,
            ],
            'By First Name'=>$countByFirstname,
            'By Last Name'=>$countByLastname,
        ];
        $events = [];
        $activities = [];
        include $this->basedir . "/templates/list_members.php";
    }
}
