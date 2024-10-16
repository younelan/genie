<?php
// require_once 'Member.php';

class MemberController extends AppController
{
    private $member;
    private $config;

    public function __construct($config)
    {
        $db = $config['connection']??null;
        $this->config = $config;
        $this->member = new MemberModel($config);
        $this->basedir = dirname(__DIR__);
    }
    public function getMemberById($memberId)
    {
        $member = $this->member->getMemberById($memberId);
        return $member;
    }
    public function editMember($memberId)
    {
        $member = $this->member->getMemberById($memberId);
        $tagString = $this->member->getTagString($memberId);
        $relationships = $this->member->getMemberRelationships($memberId);
        $relationship_types = $this->member->getRelationshipTypes();
        if (!$member) {
            exit('Member not found.');
        }
        $treeId = $member['family_tree_id']??$_GET['tree_id'] ?? $_GET['family_tree_id']??0; // Get family_tree_id from the request

        $data = [
            "tagString" => $tagString,
            "member" => $member,
            "memberId"=> $member['id']??0,
            "treeId" => $member['family_tree_id'] ?? 0,
            "template" => "edit_member.tpl",
            "relationships" => $relationships,
            "relationship_types" => $relationship_types,
            "section" => get_translation("Update Member"),
            "tree_description" => get_translation("Description"),
            "go_back" => get_translation("Back to List"),            
            "error" => "",
            "tree_id" => $_GET['tree_id'] ?? $_GET['family_tree_id'],
            "graph" => $this->config['graph']
        ];


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

        // Fetch member relationships

        //apachelog("++++++++++++++++" . $_SERVER['REQUEST_METHOD']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //apachelog("--------------");
            $updatedMember = [
                'memberId' => $memberId,
                'firstName' => $_POST['first_name'],
                'middleName' => $_POST['middle_name'],
                'lastName' => $_POST['last_name'],
                'dateOfBirth' => $_POST['date_of_birth'],
                'placeOfBirth' => $_POST['place_of_birth'],
                'dateOfDeath' => $_POST['date_of_death'],
                'placeOfDeath' => $_POST['place_of_death'],
                'genderId' => $_POST['gender_id'],
                'alias1' => $_POST['alias1'],
                'alias2' => $_POST['alias2'],
                'alias3' => $_POST['alias3'],
                'source' => $_POST['source'],
                'body' => $_POST['body'],
                'title' => $_POST['title'],
            ];
            $updatedMember['alive'] = isset($_POST['alive']) ? 1 : 0;  
            ///apachelog("++++++++++++++++");
            //apachelog($updatedMember);
            // Handle member update logic
            $success = $this->member->updateMember($updatedMember);

            // $success = $this->member->updateMember(
            //     $memberId,
            //     $_POST['first_name'],
            //     $_POST['last_name'],
            //     $_POST['date_of_birth'],
            //     $_POST['place_of_birth'],
            //     $_POST['date_of_death'],
            //     $_POST['place_of_death'],
            //     $_POST['gender_id']
            // );
            if ($success) {
                header("Location: index.php?action=edit_member&member_id=$memberId");
                exit();
            } else {
                $error = "Failed to update member.";
                $data["error"] = $error;
            }
        }

        echo $this->render_master($data);
        //include $this->basedir . "/templates/edit_member.php";
    }
    public function addMember($treeId)
    {
        $data = [
            "template" => "add_member.tpl",
            "section" => get_translation("Add Member"),
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['first_name'];
            $lastName = $_POST['last_name'];
            $dateOfBirth = $_POST['date_of_birth'];
            $placeOfBirth = $_POST['place_of_birth'];
            $genderId = $_POST['gender_id'];
            $new_member = [
                'treeId' => $treeId,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'dateOfBirth' => $dateOfBirth,
                'placeOfBirth' => $placeOfBirth,
                'genderId' => $genderId,
                'dateOfDeath' => null,
            ];
            //$treeId, $firstName, $lastName, $dateOfBirth, $placeOfBirth, $genderId
            $success = $this->member->addMember($new_member);
            if ($success) {
                header("Location: index.php?action=list_members&tree_id=$treeId");
                exit();
            } else {
                $error = "Failed to add member.";
            }
        }
        echo $this->render_master($data);

        //include $this->basedir . "/templates/add_member.php";
    }
    public function getRelationshipTypes($treeId)
    {
        $results = $this->member->getRelationshipTypes($treeId);
        echo json_encode($results);
    }

    public function autocompleteMember($termId, $memberId, $treeId)
    {
        $results = $this->member->autocompleteMember($termId, $treeId);
        echo json_encode($results);
    }
    public function getRelationships($memberId)
    {
        $relationships = $this->member->getMemberRelationships($memberId); // Fetch relationships
        echo json_encode($relationships); // Output relationships as JSON (for AJAX handling)
    }
    public function listTags() {
        $treeId = $_POST['tree_id'];
        $memberId = $_POST['member_id'];
        $tagList = $this->member->listTags($treeId, $memberId);

        echo json_encode(['success'=>true,'tags'=>$tagList]);
    }
    public function addTag() {
        $newTag = [
            'tree_id'=>$_POST['tree_id'],
            'member_id'=> $_POST['member_id'],
            'tag'=> $_POST['tag']
        ];
        $success = $this->member->addTag($newTag);
        if ($success) {
            return json_encode(['status'=>'success']);
        } else {
            return json_encode(['status'=>'fail']);
        }
    }
    public function deleteTag() {
        $delTag = [
            'tree_id'=>$_POST['tree_id'],
            'member_id'=> $_POST['member_id'],
            'tag'=> $_POST['tag']
        ];
        $success = $this->member->deleteTag($delTag);
        if ($success) {
            return json_encode(['status'=>'success']);
        } else {
            return json_encode(['status'=>'fail']);
        }
        exit;
    }
    public function addRelationship()
    {
        $memberId = $_POST['member_id'] ?? null;
        $familyTreeId = $_POST['family_tree_id'] ?? null;
        $memberType = $_POST['member_type'] ?? 'existing';
        if ($memberType === 'existing') {
            $personId1 = $_POST['person_id1'];
            $personId2 = $_POST['person_id2'];
            $relationshipType = $_POST['relationship_type_select'];
        } else {
            $firstName = $_POST['new_first_name'];
            $lastName = $_POST['new_last_name'];
            $personId1 = $_POST['person_id1'];
            $relationshipType = $_POST['relationship_type_new'];
            $new_member = [
                'treeId' => $familyTreeId,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'dateOfBirth' => null,
                'placeOfBirth' => null,
                'genderId' => null,
                'dateOfDeath' => null,
            ];
            // Add new member
            //$firstName, $lastName, $familyTreeId
            $personId2 = $this->member->addMember($new_member);
        }

        if ($personId2) {
            $success = $this->member->addRelationship($personId1, $personId2, $relationshipType, $familyTreeId);

            $response = ['success' => true, 'message' => 'Relationship added successfully.'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to add relationship.'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }
    public function updateRelationship($postData)
    {
        // Example: Assuming $_POST contains 'relationship_id', 'member_id', 'member2_id', 'family_tree_id', and 'relationship_type'
        $relationshipId = isset($postData['relationship_id']) ? $postData['relationship_id'] : null;
        $personId1 = isset($postData['member_id']) ? $postData['member_id'] : null;
        $personId2 = isset($postData['member2_id']) ? $postData['member2_id'] : null;
        $familyTreeId = isset($postData['family_tree_id']) ? $postData['family_tree_id'] : null;
        $relationStart = isset($postData['relation_start']) ? $postData['relation_start'] : null;
        $relationEnd = isset($postData['relation_start']) ? $postData['relation_end'] : null;
        $relationshipType = isset($postData['relationship_type']) ? $postData['relationship_type'] : null;
        //apachelog("--- id $relationshipId p1 $personId1 t $familyTreeId  r $relationshipType");
        if (!$relationshipId || !$personId1 || !$relationshipType) {
            return json_encode(['success' => false, 'message' => 'Missing required parameters']);
        }

        $relation = [
            'relationshipId' => $relationshipId,
            'personId1' => $personId1,
            'personId2' => $personId2,
            'relationStart' => $relationStart,
            'relationEnd' => $relationEnd,
            'relationshipTypeId' => $relationshipType,
        ];
        //$relationshipId, $personId1, $relationshipType
        $this->member->updateMemberRelationship($relation);

        // Example: Update relationship in database or storage
        // Example: Replace with actual implementation

        // Simulated response
        $response = ['success' => true, 'message' => 'Relationship updated successfully'];

        // Return JSON response
        return json_encode($response);
    }
    public function swapRelationshipAction()
    {
        $relationshipId = $_POST['relationship_id']; // Or get it from the request in another way

        try {
            $result = $this->member->swapRelationship($relationshipId);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Relationship not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function deleteMember($memberId)
    {
        // Implement logic to delete a member from the database or data source
        // Example:

        $success = $this->member->deleteMember($memberId);
        return $success;
    }
    public function deleteRelationship($relationshipId)
    {
        $success = $this->member->deleteRelationship($relationshipId);
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete relationship.']);
        }
        exit();
    }
}
