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
        
        // Add these lines to fetch family data
        $spouse_families = $this->member->getSpouseFamilies($memberId);
        $child_families = $this->member->getChildFamilies($memberId);
        
        if (!$member) {
            exit('Member not found.');
        }
        $treeId = $member['family_tree_id']??$_GET['tree_id'] ?? $_GET['family_tree_id']??0;

        $data = [
            "tagString" => $tagString,
            "member" => $member,
            "memberId"=> $member['id']??0,
            "treeId" => $member['family_tree_id'] ?? 0,
            "template" => "edit_member.tpl",
            "relationships" => $relationships,
            "relationship_types" => $relationship_types,
            // Add these lines to pass family data to template
            "spouse_families" => $spouse_families,
            "child_families" => $child_families,
            "section" => get_translation("Update Member"),
            "tree_description" => get_translation("Description"),
            "go_back" => get_translation("Back to List"),            
            "error" => "",
            "tree_id" => $treeId,
            "graph" => $this->config['graph']
        ];


        $data["menu"] = [
            [
                "link" => "index.php?action=export_tree&tree_id=$treeId",
                "text" => get_translation("Export Tree"),
            ],
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
                "link" => "index.php?action=export_tree&tree_id=$treeId",
                "text" => get_translation("Export Tree"),
            ],

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
        try {
            $memberId = $_POST['member_id'] ?? null;
            $familyTreeId = $_POST['family_tree_id'] ?? null;
            $relationCategory = $_POST['relation_category'] ?? 'other';

            if (!$memberId || !$familyTreeId) {
                throw new Exception('Missing required member_id or family_tree_id');
            }

            if ($relationCategory === 'parent') {
                return $this->handleAddParents($memberId, $familyTreeId);
            }

            // ... rest of existing addRelationship code ...
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function handleAddParents($memberId, $familyTreeId)
    {
        try {
            // Handle first parent
            $firstParentId = null;
            if ($_POST['first_parent_type'] === 'existing') {
                $firstParentId = $_POST['first_parent_id'] ?? null;
                if (!$firstParentId) {
                    throw new Exception('No first parent selected');
                }
            } else {
                // Create new first parent
                if (empty($_POST['first_parent_first_name']) || empty($_POST['first_parent_last_name'])) {
                    throw new Exception('First name and last name are required for new parent');
                }
                
                $firstParent = [
                    'treeId' => $familyTreeId,
                    'firstName' => $_POST['first_parent_first_name'],
                    'lastName' => $_POST['first_parent_last_name'],
                    'dateOfBirth' => $_POST['first_parent_birth_date'] ?? null,
                    'genderId' => $_POST['first_parent_gender'] ?? null
                ];
                $firstParentId = $this->member->addMember($firstParent);
            }

            // Handle second parent based on type
            $familyId = null;
            switch ($_POST['second_parent_type']) {
                case 'existing_family':
                    $familyId = $_POST['existing_family_id'] ?? null;
                    if (!$familyId) {
                        throw new Exception('No family selected');
                    }
                    break;

                case 'new_person':
                    // Create new second parent and family
                    if (empty($_POST['second_parent_first_name']) || empty($_POST['second_parent_last_name'])) {
                        throw new Exception('First name and last name are required for second parent');
                    }
                    
                    $secondParent = [
                        'treeId' => $familyTreeId,
                        'firstName' => $_POST['second_parent_first_name'],
                        'lastName' => $_POST['second_parent_last_name'],
                        'dateOfBirth' => $_POST['second_parent_birth_date'] ?? null,
                        'genderId' => ($_POST['first_parent_gender'] == 1) ? 2 : 1 // Opposite gender
                    ];
                    $secondParentId = $this->member->addMember($secondParent);
                    
                    // Create new family
                    $familyData = [
                        'tree_id' => $familyTreeId,
                        'husband_id' => $_POST['first_parent_gender'] == 1 ? $firstParentId : $secondParentId,
                        'wife_id' => $_POST['first_parent_gender'] == 2 ? $firstParentId : $secondParentId
                    ];
                    $familyId = $this->member->createFamily($familyData);
                    break;

                case 'existing_person':
                    $secondParentId = $_POST['second_parent_id'] ?? null;
                    if (!$secondParentId) {
                        throw new Exception('No second parent selected');
                    }
                    
                    // Create new family with both parents
                    $familyData = [
                        'tree_id' => $familyTreeId,
                        'husband_id' => $_POST['first_parent_gender'] == 1 ? $firstParentId : $secondParentId,
                        'wife_id' => $_POST['first_parent_gender'] == 2 ? $firstParentId : $secondParentId
                    ];
                    $familyId = $this->member->createFamily($familyData);
                    break;

                default:
                    throw new Exception('Invalid second parent type');
            }

            // Add child to family
            if (!$familyId) {
                throw new Exception('Failed to create or find family');
            }
            
            $success = $this->member->addChildToFamily($familyId, $memberId, $familyTreeId);
            if (!$success) {
                throw new Exception('Failed to add child to family');
            }

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
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
    public function getFamilies($memberId = null)
    {
        if (!$memberId) {
            $memberId = $_GET['member_id'] ?? null;
        }
        
        if (!$memberId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No member ID provided']);
            exit;
        }

        $spouse_families = $this->member->getSpouseFamilies($memberId);
        $child_families = $this->member->getChildFamilies($memberId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'spouse_families' => $spouse_families,
            'child_families' => $child_families
        ]);
        exit;
    }
    public function deleteFamilyMember()
    {
        header('Content-Type: application/json');
        
        try {
            error_log('Delete family member request: ' . print_r($_POST, true));
            
            $familyId = $_POST['family_id'] ?? null;
            $childId = $_POST['child_id'] ?? null;
            $spouseId = $_POST['spouse_id'] ?? null;
            $deleteType = $_POST['delete_type'] ?? null;

            if (!$familyId) {
                throw new Exception('Family ID is required');
            }

            if ($childId) {
                // Handle child deletion
                if ($deleteType === 'remove') {
                    $success = $this->member->removeChildFromFamily($childId, $familyId);
                } else {
                    $success = $this->member->deleteMember($childId);
                }
            } else {
                // Handle spouse deletion - even if spouseId is null
                switch ($deleteType) {
                    case '1': // Remove relationship only
                        $success = $this->member->removeSpouseFromFamily($spouseId, $familyId);
                        break;
                    case '2': // Delete spouse, keep children
                        $success = $spouseId ? 
                            $this->member->deleteSpouseKeepChildren($spouseId, $familyId) :
                            $this->member->removeSpouseFromFamily($spouseId, $familyId);
                        break;
                    case '3': // Delete spouse and children
                        $success = $spouseId ? 
                            $this->member->deleteSpouseAndChildren($spouseId, $familyId) :
                            $this->member->deleteFamilyAndChildren($familyId);
                        break;
                    default:
                        throw new Exception('Invalid delete type');
                }
            }

            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            error_log('Error in deleteFamilyMember: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    public function replaceSpouse()
    {
        header('Content-Type: application/json');
        
        try {
            error_log('Replace spouse request: ' . print_r($_POST, true));
            
            $familyId = $_POST['family_id'] ?? null;
            $spouseType = $_POST['spouse_type'] ?? null;
            $memberGender = $_POST['member_gender'] ?? null;
            $spouseId = $_POST['spouse_id'] ?? null;
            $treeId = $_POST['family_tree_id'] ?? null;

            error_log("Processing replace spouse - Family: $familyId, Spouse: $spouseId, Gender: $memberGender, Tree: $treeId");

            if (!$familyId || !$spouseType || !$memberGender || !$treeId) {
                throw new Exception('Missing required parameters: ' . 
                                  (!$familyId ? 'family_id ' : '') .
                                  (!$spouseType ? 'spouse_type ' : '') .
                                  (!$memberGender ? 'member_gender ' : '') .
                                  (!$treeId ? 'tree_id' : ''));
            }

            if ($spouseType === 'existing') {
                if (!$spouseId) {
                    throw new Exception('No spouse selected');
                }
            } else {
                // Create new spouse
                if (empty($_POST['new_first_name']) || empty($_POST['new_last_name'])) {
                    throw new Exception('First name and last name are required for new spouse');
                }
                
                $new_member = [
                    'treeId' => $treeId,
                    'firstName' => $_POST['new_first_name'],
                    'lastName' => $_POST['new_last_name'],
                    'dateOfBirth' => $_POST['new_birth_date'] ?? null,
                    'genderId' => $memberGender == 1 ? 2 : 1, // Opposite gender
                ];
                $spouseId = $this->member->addMember($new_member);
                if (!$spouseId) {
                    throw new Exception('Failed to create new spouse');
                }
            }

            // Update the family
            $success = $this->member->updateFamilySpouse(
                $familyId, 
                $spouseId, 
                $memberGender, 
                $_POST['marriage_date'] ?? null
            );

            if (!$success) {
                throw new Exception('Failed to update family with new spouse');
            }

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log('Error in replaceSpouse: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    public function visualizeDescendants($memberId) {
        $member = $this->member->getMemberById($memberId);
        if (!$member) {
            exit('Member not found.');
        }

        $data = [
            "member" => $member,
            "template" => "visualize_descendants.tpl",
            "section" => get_translation("Visualize Descendants"),
            "memberId" => $memberId,
            "treeId" => $member['family_tree_id'],
        ];

        echo $this->render_master($data);
    }

    public function getDescendantsData($memberId) {
        header('Content-Type: application/json');
        $descendants = $this->member->getDescendantsHierarchy($memberId);
        echo json_encode($descendants);
        exit;
    }
}
