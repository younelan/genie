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

        $content_file = $this->basedir . "/templates/" . $data["template"];
        $data['content'] = $this->render_file($content_file, $data);

        return $this->render_file($master_file, $data);

    }

    public function listTrees()
    {
        $data = [
            "trees" => $this->tree->getAllTreesByOwner($this->userId),
            "template" => "tree_list.tpl",
            "section" => get_translation("Family Trees")
        ];
        $data["menu"][] = [
            "link"=>"index.php?action=add_tree",
            "text"=>get_translation("New Tree")
        ];

        echo $this->render_master($data);

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
        $data = [
            "template" => "add_tree.tpl",
            "section" => get_translation("Add New Family Tree"),
            "tree_name" => get_translation("Tree Name"),
            "tree_description" => get_translation("Description"),
            "go_back" => get_translation("Back to List"),            
            "error" => ""
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $success = $this->tree->addTree($this->userId, $name, $description);
            if ($success) {
                header("Location: index.php?action=list_trees");
                exit();
            } else {
                // $data['template']=$this->basedir . "/templates/error.tpl";
                $data['error'] = "Failed to add tree.";
            }
        }
        echo $this->render_master($data);
        //include $this->basedir . "/templates/add_tree.php";
    }
    public function exportTree() {
        $migrator = new RelationshipMigrator($this->config);
        //print "hi";
        $familyTreeId = $_GET['tree_id']??0;
        if(!$familyTreeId) {
            print "Invalid tree";
            exit;
        }
        // Specify the family tree ID you want to import into
        //$familyTreeId = 1; // Example ID
        header('Content-Type: text/gedcom');
        header('Content-Disposition: attachment; filename="family_tree.ged"');
                
        // Import the GEDCOM content
        $gedcom = $migrator->exportGedcom($familyTreeId);
        print $gedcom;
        exit;
               
    }
    public function migrateTree() {
        $migrator = new RelationshipMigrator($this->config);

        $familyTreeId = $_GET['tree_id']??0;
        if(!$familyTreeId) {
            print "Invalid tree";
            exit;
        }                
        // Import the GEDCOM content
        $migration = $migrator->migrate($familyTreeId);
        if($migration) {
            echo "Migration for family tree ID $familyTreeId completed successfully.";

        } else {
            echo "Migration for family tree ID $familyTreeId failed.";
        }
        //print $gedcom;
        exit;
               
    }

    public function viewTree()
    {
        $data = [
            "template" => "view_tree.tpl",
            "section" => get_translation("View Tree"),
            "tree_description" => get_translation("Description"),
            "go_back" => get_translation("Back to List"),            
            "error" => "",
            "tree_id" => $_GET['tree_id'] ?? $_GET['family_tree_id'],
            "graph" => $this->config['graph']
        ];

        $treeId = $_GET['tree_id'] ?? $_GET['family_tree_id']; // Get family_tree_id from the request
        $data["menu"] = [
            [
                "link" => "index.php?action=add_member&tree_id=$treeId",
                "text" => get_translation("New Member"),
            ],
            [
                "link" => "index.php?action=edit_tree&tree_id=$treeId",
                "text" => get_translation("List Members"),
            ],
            [
                "link" => "index.php?action=list_trees",
                "text" =>  get_translation("Trees"),
            ]
        
        ];        
         echo $this->render_master($data);

        //include $this->basedir . "/templates/view_tree.php";
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
        $data = [
            "template" => "list_members.tpl",
            "members"=>$members,
            "lastUpdates"=> $this->tree->getMembersByTreeId($treeId, 0, $update_limit, $orderby='updated_at DESC'),
            "section" => get_translation("List Members"),
            "str_family_members" => get_translation("Family Members"),
            "str_pages" =>get_translation("Pages"),
            "totalPages" => ceil($totalMembers / $limit),            
            "totalMembers" => $this->tree->getPersonCount($treeId),
            "countByGender" => $this->tree->countMembersByTreeId($treeId),
            "synonyms" => $this->tree->getSynonymsByTreeId($treeId),
            "countByLastname" => $this->tree->countTreeMembersByField($treeId,'last_name', $synonyms),
            "countByFirstname" => $this->tree->countTreeMembersByField($treeId,'first_name', $synonyms),
            "totalRelationships" => $this->tree->countRelationshipsByTreeId($treeId),
            "go_back" => get_translation("Back to List"),
            "error" => "",
            "stats" => $stats,
            "events"=> $events,
            "activities" => $activities,
            "treeId" => $_GET['tree_id'] ?? $_GET['family_tree_id'],
            "graph" => $this->config['graph']
        ];
        $data["countByGender"]['Total']=$totalMembers;
        $treeId = $_GET['tree_id'] ?? $_GET['family_tree_id']; // Get family_tree_id from the request
        $data["menu"] = [
            [
                "link" => "index.php?action=add_member&tree_id=$treeId",
                "text" => get_translation("New Member"),
            ],
            [
                "link" => "index.php?action=migrate_tree&tree_id=$treeId",
                "text" => get_translation("Migrate Tree"),
            ],
            [
                "link" => "index.php?action=export_tree&tree_id=$treeId",
                "text" => get_translation("Export Tree"),
            ],
            [
                "link" => "index.php?action=view_tree&tree_id=$treeId",
                "text" => get_translation("Visualize"),
            ],
            [
                "link" => "index.php?action=list_trees",
                "text" =>  get_translation("Trees"),
            ]
        ];

        echo $this->render_master($data);

        //include $this->basedir . "/templates/list_members.php";
    }
}
