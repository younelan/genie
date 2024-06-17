<?php
class Member {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addMember($new_member) {
        //$treeId, $firstName, $lastName, $dateOfBirth, $placeOfBirth, $genderId
        $treeId = $new_member['treeId']??null;
        $firstName = $new_member['firstName']??null;
        $lastName = $new_member['lastName']??null;
        $dateOfBirth = $new_member['dateOfBirth']??null;
        $placeOfBirth = $new_member['placeOfBirth']??null;
        $genderId = $new_member['genderId']??null;

        $query = "INSERT INTO person (family_tree_id, first_name, last_name, date_of_birth, place_of_birth, gender_id) VALUES (:family_tree_id, :first_name, :last_name, :date_of_birth, :place_of_birth, :gender_id)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            'family_tree_id' => $treeId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'date_of_birth' => $dateOfBirth ? $dateOfBirth : null,
            'place_of_birth' => $placeOfBirth ? $placeOfBirth : null,
            'gender_id' => $genderId
        ]);
        //apachelog("Inserted member " . $this->db->lastInsertId());
        return $this->db->lastInsertId();
    }
    // Fetch relationship types from the database
    public function getRelationshipTypes($tree_id=1) {
        $query = "SELECT id, description FROM relationship_type";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMemberById($memberId) {
        $query = "SELECT * FROM person WHERE id = :member_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function autocompleteMember($term, $memberId, $tree_id=1) {
        $query = "SELECT id, first_name, last_name FROM person WHERE 
        (first_name LIKE :term1 OR last_name like :term2) and id != :member_id";
        $query2 = str_replace(":tree_id", $tree_id, $query);
        $query2 = str_replace(":term1", '%' . $term . '%', $query2);
        $query2 = str_replace(":term2", '%'. $term . '%', $query2);
        $query2 = str_replace(":member_id", $memberId, $query2);
        //print($query2);
        $stmt = $this->db->prepare($query);
        //$stmt->bindValue(':tree_id', '%' . $tree_id . '%');
        $stmt->bindValue(':term1', '%' . $term . '%');
        $stmt->bindValue(':term2', '%' . $term . '%');
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //apachelog($results);
        $labels = [];
        foreach($results as $result) {
            $labels[] = [   
                    'label' => $result['first_name'] . ' ' . $result['last_name'],
                    'id' => $result['id']
            ];

        }

        return $labels;


    }
    // public function deleteMember($memberId) {
    //     $query = "DELETE FROM person WHERE id = :member_id";
    //     $stmt = $this->db->prepare($query);
    //     $stmt->bindParam(':member_id', $memberId);
    //     $status = $stmt->execute();
    //     if($status) {
    //         print "failed";
    //     } else {
    //         print "success";
    //     }
    // }

    public function deleteMember($memberId) {
        $query = "DELETE FROM person WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $memberId]);
    }
    public function getMemberRelationships($memberId) {
        $query = "SELECT pr.id, p1.first_name AS person1_first_name, p1.last_name AS person1_last_name, 
                         p2.first_name AS person2_first_name, p2.last_name AS person2_last_name, 
                         p1.id as person1_id, p2.id as person2_id, pr.relation_start, pr.relation_end,
                         rt.description AS relationship_description
                  FROM person_relationship pr
                  INNER JOIN person p1 ON pr.person_id1 = p1.id
                  INNER JOIN person p2 ON pr.person_id2 = p2.id
                  INNER JOIN relationship_type rt ON pr.relationship_type_id = rt.id
                  WHERE pr.person_id1 = :memberId OR pr.person_id2 = :memberId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':memberId', $memberId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateMemberRelationship($relationship) {
        $relationshipId=$relationship['relationshipId']??null;
        //$personId1= $relationship['relationsType'];
        $relationshipTypeId = $relationship['relationshipTypeId']??null;
        $relationStart = $relationship['relationStart']??null;
        $relationEnd = $relationship['relationEnd'];
        if(!$relationStart) $relationStart=null;
        if(!$relationEnd) $relationEnd=null;
        
        $query = "UPDATE person_relationship 
                  SET relation_start = :relation_start, 
                  relation_end = :relation_end,
                  relationship_type_id = :relationship_type_id WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':relation_start', $relationStart);
        $stmt->bindParam(':relation_end', $relationEnd);
        $stmt->bindParam(':relationship_type_id', $relationshipTypeId);
        $stmt->bindParam(':id', $relationshipId);
        return $stmt->execute();
    }

    public function updateMember($member) {
        //$memberId, $firstName, $lastName, $dateOfBirth, $placeOfBirth, $dateOfDeath, $placeOfDeath, $genderId
        $memberId= $member['memberId']??"";
        $firstName= $member['firstName']??"";
        $middleName = $member['middleName']??"";
        $lastName = $member ['lastName'];
        $alias1 = $member['alias1'];
        $alias2 = $member['alias2'];
        $alias3 = $member['alias3'];
        foreach($member as $key=>$value) {
            if(!$value) {
                $member[$key]=null;
            }
        }
        $dateOfBirth = $member['dateOfBirth'];
        $placeOfBirth= $member['placeOfBirth'];
        $dateOfDeath = $member['dateOfDeath'];
        $placeOfDeath= $member['placeOfDeath'];
        $memberId = $member['memberId'];
        $genderId = $member['genderId'];
        $body = $member['body'];
        $title= $member['title'];

        $query = "UPDATE person SET first_name = :first_name, last_name = :last_name, 
                    middle_name = :middle_name, date_of_birth = :date_of_birth,
                  alias1 = :alias1, alias2 = :alias2, alias3 = :alias3, title = :title, body = :body,
                  place_of_birth = :place_of_birth, date_of_death = :date_of_death, place_of_death = :place_of_death,
                  gender_id = :gender_id WHERE id = :id";
        if(!$dateOfDeath) $dateOfDeath=null;
        if(!$dateOfBirth) $dateOfBirth=null;
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':middle_name', $middleName);
        $stmt->bindParam(':date_of_birth', $dateOfBirth);
        $stmt->bindParam(':place_of_birth', $placeOfBirth);
        $stmt->bindParam(':date_of_death', $dateOfDeath);
        $stmt->bindParam(':place_of_death', $placeOfDeath);
        $stmt->bindParam(':gender_id', $genderId);
        $stmt->bindParam(':id', $memberId);
        $stmt->bindParam(':alias1', $alias1);
        $stmt->bindParam(':alias2', $alias2);
        $stmt->bindParam(':alias3', $alias3);
        $stmt->bindParam(':body', $body);
        $stmt->bindParam(':title', $title);
        return $stmt->execute();
    }

    public function getRelationships($memberId) {
        $query = "SELECT pr.id, p1.first_name as person1_name, p2.first_name as person2_name, rt.description, 
                         p1.id as person1_id, p2.id as person2_id
                  FROM person_relationship pr
                  JOIN person p1 ON pr.person_id1 = p1.id
                  JOIN person p2 ON pr.person_id2 = p2.id
                  JOIN relationship_type rt ON pr.relationship_type_id = rt.id
                  WHERE pr.person_id1 = :memberId OR pr.person_id2 = :memberId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['memberId' => $memberId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addRelationship($personId1, $personId2, $relationshipTypeId, $treeId) {
        $query = "INSERT INTO person_relationship (person_id1, person_id2, relationship_type_id,family_tree_id) VALUES (:person_id1, :person_id2, :relationship_type_id,:family_tree_id)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'person_id1' => $personId1,
            'person_id2' => $personId2,
            'relationship_type_id' => $relationshipTypeId,
            'family_tree_id'=>$treeId
        ]);
    }
    public function swapRelationship($relationshipId) {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Fetch the current relationship
            $sql = "SELECT person_id1, person_id2 FROM person_relationship WHERE id = :relationship_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':relationship_id', $relationshipId, PDO::PARAM_INT);
            $stmt->execute();
            $relationship = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($relationship) {
                // Swap the person IDs
                $personId1 = $relationship['person_id1'];
                $personId2 = $relationship['person_id2'];

                // Update the relationship with swapped IDs
                $updateSql = "UPDATE person_relationship SET person_id1 = :person_id2, person_id2 = :person_id1 WHERE id = :relationship_id";
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->bindParam(':person_id1', $personId1, PDO::PARAM_INT);
                $updateStmt->bindParam(':person_id2', $personId2, PDO::PARAM_INT);
                $updateStmt->bindParam(':relationship_id', $relationshipId, PDO::PARAM_INT);
                $updateStmt->execute();

                // Commit transaction
                $this->db->commit();

                return true; // Indicate success
            } else {
                // Rollback transaction if relationship not found
                $this->db->rollBack();
                return false; // Indicate failure
            }
        } catch (Exception $e) {
            // Rollback transaction in case of error
            $this->db->rollBack();
            throw $e; // Re-throw exception
        }
    }   
    public function deleteRelationship($relationshipId) {
        $query = "DELETE FROM person_relationship WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $relationshipId]);
    }
}
?>
