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
            "template" => "react_app.tpl",
            "section" => get_translation("Family Trees"),
            "app_title" => $this->config['app_name'] ?? "Genie",
            "app_logo" => $this->config['app_logo'] ?? "/genie/res/images/logo.png",
            "footer_text" => get_translation("Family Tree Manager"),
            "company_name" => $this->config['company_name'] ?? "Genie"
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
            "tree_id" => $_GET['tree_id'] ?? $_GET['tree_id'],
            "graph" => $this->config['graph']
        ];

        $treeId = $_GET['tree_id'] ?? $_GET['tree_id']; // Get tree_id from the request
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
        $familyTreeId = $_GET['tree_id']; // Get tree_id from the request
        //$treeModel = new Tree();
        $treeData = $this->tree->getTreeData($familyTreeId);
        header('Content-Type: application/json');
        echo json_encode($treeData);
    }

    public function getDescendantsData($memberId) {
        header('Content-Type: application/json');
        $descendants = $this->member->getDescendantsHierarchy($memberId);
        echo json_encode($descendants);
        exit;
    }

    public function getDescendantsHierarchy($memberId) {
        $person = $this->getMemberById($memberId);
        if (!$person) {
            return null;
        }

        // Get all families where this person is a spouse
        $spouseFamilies = $this->getSpouseFamilies($memberId);
        
        $result = [
            'id' => $person['id'],
            'name' => trim($person['first_name'] . ' ' . $person['last_name']),
            'data' => [
                'birth' => $person['birth_date'],
                'death' => $person['death_date'],
                'gender' => $person['gender']
            ],
            'marriages' => []
        ];

        // Process each family
        foreach ($spouseFamilies as $family) {
            $spouseId = ($person['gender'] == 'M') ? $family['wife_id'] : $family['husband_id'];
            $spouseName = ($person['gender'] == 'M') ? $family['wife_name'] : $family['husband_name'];
            
            // Add spouse information if exists
            $marriage = [
                'id' => $family['family_id'],
                'spouse' => $spouseId ? [
                    'id' => $spouseId,
                    'name' => $spouseName,
                    'data' => [
                        'gender' => ($person['gender'] == 'M') ? 'F' : 'M', // Set opposite gender
                        'birth' => null,  // Add if available
                        'death' => null   // Add if available
                    ]
                ] : null,
                'children' => []
            ];

            // Add children for this marriage
            if (isset($family['children'])) {
                foreach ($family['children'] as $child) {
                    $childDescendants = $this->getDescendantsHierarchy($child['id']);
                    if ($childDescendants) {
                        $marriage['children'][] = $childDescendants;
                    }
                }
            }
            
            $result['marriages'][] = $marriage;
        }

        return $result;
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
        $data = [
            "template" => "list_members.tpl",
            "section" => get_translation("List Members"),
            "treeId" => $treeId,
            "page" => $page,
            "graph" => $this->config['graph']
        ];

        $treeId = $_GET['tree_id'] ?? $_GET['tree_id']; // Get tree_id from the request
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
