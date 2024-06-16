<?php
require_once 'Member.php';

class MemberController {
    private $member;

    public function __construct($db) {
        $this->member = new Member($db);
    }

    public function oldEeditMember($memberId) {
        $member = $this->member->getMemberById($memberId);
        $relationships = $this->member->getRelationships($memberId);
        $relationshipTypes = $this->member->getRelationshipTypes(); // Fetch relationship types

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle member update logic
            $success = $this->member->updateMember($memberId, $_POST['first_name'], $_POST['last_name'], $_POST['date_of_birth'], $_POST['place_of_birth'], $_POST['date_of_death'], $_POST['place_of_death'], $_POST['gender_id']);
            if ($success) {
                header("Location: index.php?action=view_member&member_id=$memberId");
                exit();
            } else {
                $error = "Failed to update member.";
            }
        }

        include 'edit_member.php';
    }
    public function getMemberById($memberId) {
        $member = $this->member->getMemberById($memberId);
        return $member;
    }
    public function editMember($memberId) {
        $member = $this->member->getMemberById($memberId);
        if (!$member) {
            exit('Member not found.');
        }

        // Fetch member relationships
        $relationships = $this->member->getMemberRelationships($memberId);
        $relationship_types = $this->member->getRelationshipTypes();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle member update logic
            $success = $this->member->updateMember(
                $memberId,
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['date_of_birth'],
                $_POST['place_of_birth'],
                $_POST['date_of_death'],
                $_POST['place_of_death'],
                $_POST['gender_id']
            );
            if ($success) {
                header("Location: index.php?action=edit_member&member_id=$memberId");
                exit();
            } else {
                $error = "Failed to update member.";
            }
        }

        include 'edit_member.php';
    }
    public function addMember($treeId) {
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
        include 'add_member.php';
    }
    public function getRelationshipTypes($treeId) {
        $results = $this->member->getRelationshipTypes( $treeId);
        echo json_encode($results);

    }

    public function autocompleteMember($termId,$memberId,$treeId) {
        $results = $this->member->autocompleteMember($termId, $treeId);
        echo json_encode($results);

    }
    public function getRelationships($memberId) {
        $relationships = $this->member->getMemberRelationships($memberId); // Fetch relationships
        echo json_encode($relationships); // Output relationships as JSON (for AJAX handling)
    }
    // public function addRelationship() {
    //     // Fetch relationship types
    //     $relationshipTypes = $this->member->getRelationshipTypes();
    
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $person1Id = $_POST['member_id']; // Assuming IDs are passed directly
    //         $person2Id = $_POST['member2_id'];
    //         $treeId = $_POST['family_tree_id'];
    //         $relationshipType = $_POST['relationship_type'];
    
    //         $success = $this->member->addRelationship($person1Id, $person2Id, $relationshipType,$treeId);
    //         if ($success) {
    //             echo json_encode(['success' => true]);
    //         } else {
    //             echo json_encode(['success' => false]);
    //         }
    //     } else {
    //         include 'add_relationship_form.php'; // Assuming you have a separate form file
    //     }
    // }
    public function addRelationship() {
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
        apachelog( "--- member $memberId tree $familyTreeId id1 $personId1 id2 $personId2\n");

        if ($personId2) {
             $success = $this->member->addRelationship($personId1, $personId2, $relationshipType,$familyTreeId);

            //$this->member->addRelationship($personId1, $personId2, $relationshipType);
            $response = ['success' => true, 'message' => 'Relationship added successfully.'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to add relationship.'];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }
    public function updateRelationship($postData) {
        // Example: Assuming $_POST contains 'relationship_id', 'member_id', 'member2_id', 'family_tree_id', and 'relationship_type'
        $relationshipId = isset($postData['relationship_id']) ? $postData['relationship_id'] : null;
        $personId1 = isset($postData['member_id']) ? $postData['member_id'] : null;
        $personId2 = isset($postData['member2_id']) ? $postData['member2_id'] : null;
        $familyTreeId = isset($postData['family_tree_id']) ? $postData['family_tree_id'] : null;
        $relationshipType = isset($postData['relationship_type']) ? $postData['relationship_type'] : null;
        apachelog ("--- id $relationshipId p1 $personId1 t $familyTreeId  r $relationshipType");
        if (!$relationshipId || !$personId1 || !$relationshipType) {
            return json_encode(['success' => false, 'message' => 'Missing required parameters']);
        }

        $this->member->updateMemberRelationship($relationshipId, $personId1, $relationshipType);

        // Example: Update relationship in database or storage
        // Example: Replace with actual implementation

        // Simulated response
        $response = ['success' => true, 'message' => 'Relationship updated successfully'];

        // Return JSON response
        return json_encode($response);
    }
    public function deleteMember($memberId) {
        // Implement logic to delete a member from the database or data source
        // Example:

        $success = $this->member->deleteMember($memberId);
        return $success;
    }    
    public function deleteRelationship($relationshipId) {
        $success = $this->member->deleteRelationship($relationshipId);
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete relationship.']);
        }
        exit();
    }

}
?>
