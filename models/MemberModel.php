<?php
class MemberModel extends AppModel
{
    private $db;
    private $config;

    private $person_table = 'individuals';
    private $relation_table = 'person_relationship';
    private $relation_type_table = 'relationship_type';
    private $people_tag_table = 'tags';
    private $tree_table = 'family_tree';
    private $synonym_table;
    private $families_table = 'families';
    private $family_children_table = 'family_children';


    public function __construct($config)
    {
        $this->config = $config;
        $this->db = $config['connection'];
        $this->person_table = $config['tables']['person'] ?? 'individuals';
        $this->tree_table = $config['tables']['tree'] ?? 'family_tree';
        $this->relation_table = $config['tables']['relation'] ?? 'person_relationship';
        $this->synonym_table = $config['tables']['synonyms'] ?? 'synonyms';
        $this->families_table = $config['tables']['families'] ?? 'families';
        $this->family_children_table = $config['tables']['family_children']??'family_children';
    }


    public function addMember($new_member)
    {
        $treeId = $new_member['treeId'] ?? null;
        $firstName = $new_member['firstName'] ?? null;
        $lastName = $new_member['lastName'] ?? null;
        $dateOfBirth = $new_member['dateOfBirth'] ?? null;
        $placeOfBirth = $new_member['placeOfBirth'] ?? null;
        $gender = $new_member['gender'] ?? null;  // Changed from gender_id to gender
        $alive = $new_member['alive'] ?? 1;

        $query = "INSERT INTO $this->person_table  (tree_id, first_name, last_name, birth_date, birth_place, gender, alive, created_at, updated_at) VALUES (:tree_id, :first_name, :last_name, :birth_date, :birth_place, :gender, :alive, NOW(), NOW())";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            'tree_id' => $treeId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birth_date' => $dateOfBirth ? $dateOfBirth : null,
            'birth_place' => $placeOfBirth ? $placeOfBirth : null,
            'gender' => $gender,
            'alive' => $alive,
        ]);
        return $this->db->lastInsertId();
    }

    public function updateMember($member)
    {
        $memberId = $member['memberId'] ?? "";
        $firstName = $member['firstName'] ?? "";
        $lastName = $member['lastName'];
        $source = $member['source'];
        $alive = intval($member['alive']);
        foreach ($member as $key => $value) {
            if (!$value) {
                $member[$key] = null;
            }
        }
        $dateOfBirth = $member['dateOfBirth'];
        $placeOfBirth = $member['placeOfBirth'];
        $dateOfDeath = $member['dateOfDeath'];
        $placeOfDeath = $member['placeOfDeath'];
        $gender = $member['gender'] ?? null;  // Ensure gender is set

        if ($gender === null) {
            throw new Exception("Gender is required.");
        }

        $query = "UPDATE $this->person_table  SET first_name = :first_name, last_name = :last_name, 
                    birth_date = :birth_date,
                  birth_place = :birth_place, death_date = :death_date, death_place = :death_place,
                  gender = :gender, source = :source, alive = :alive, updated_at = NOW() WHERE id = :id";
        if (!$dateOfDeath) $dateOfDeath = null;
        if (!$dateOfBirth) $dateOfBirth = null;
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':birth_date', $dateOfBirth);
        $stmt->bindParam(':birth_place', $placeOfBirth);
        $stmt->bindParam(':death_date', $dateOfDeath);
        $stmt->bindParam(':death_place', $placeOfDeath);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':id', $memberId);
        $stmt->bindParam(':source', $source);
        $stmt->bindParam(':alive', $alive);
        return $stmt->execute();
    }
    public function getRelationshipTypes($tree_id = 1)
    {
        $query = "SELECT id, description FROM $this->relation_type_table WHERE tree_id = :tree_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['tree_id' => $tree_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMemberById($memberId)
    {
        $query = "SELECT * FROM $this->person_table  WHERE id = :member_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTags($memberId)
    {
        $query = "SELECT * FROM $this->people_tag_table t
        WHERE person_id = :member_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function listTags( $memberId)
    {
        $raw_tags = $this->getTags($memberId);
        $tagList = [];
        foreach ($raw_tags as $tag) {
            if(!in_array($tag['tag'],$tagList)) {
                $tagList[] = $tag['tag'];

            }
        }
        return $tagList;
    }
    public function getTagString($memberId) {
        $raw_concat_tags = $this->listTags( $memberId);
        $concat_tags = implode(",", $raw_concat_tags);
        return $concat_tags;
    }
    public function addTag($newTag) {
        $tree_id = intval($newTag['tree_id'])?? false;
        $person_id = intval($newTag['member_id'])?? false;
        $tag = $newTag['tag']?? false;
        if(!$tree_id || !$person_id || !$tag) {
            return false;
        };
        $query = "INSERT INTO $this->people_tag_table (tag,tree_id,person_id) VALUES (:tag_name,:tree_id,:person_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tag_name', $tag);
        $stmt->bindParam(':person_id', $person_id);
        $stmt->bindParam(':tree_id', $tree_id);
        $result = $stmt->execute();        

        return $this->db->lastInsertId();
    }
    public function deleteTag($delTag) {
        $tree_id = intval($delTag['tree_id'])?? false;
        $member_id = intval($delTag['member_id'])?? false;
        $tag = $delTag['tag']?? false;
        if(!$tree_id || !$member_id || !$tag) {
            return false;
        };
        $query = "DELETE FROM $this->people_tag_table where tag=:tag_name and tree_id=:tree_id and person_id=:member_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tag_name', $tag);
        $stmt->bindParam(':member_id', $member_id);
        $stmt->bindParam(':tree_id', $tree_id);
        $result = $stmt->execute();
        return $result;
    }

    public function autocompleteMember($term, $memberId, $tree_id = 1)
    {
        $query = "SELECT id, first_name, last_name FROM $this->person_table  WHERE 
        (first_name LIKE :term1 OR last_name like :term2) and id != :member_id AND tree_id = :tree_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':tree_id', $tree_id);
        $stmt->bindValue(':term1', '%' . $term . '%');
        $stmt->bindValue(':term2', '%' . $term . '%');
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $labels = [];
        foreach ($results as $result) {
            $labels[] = [
                'label' => $result['first_name'] . ' ' . $result['last_name'],
                'id' => $result['id']
            ];
        }

        return $labels;
    }

    public function deleteMember($memberId)
    {
        $query = "DELETE FROM $this->person_table  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $memberId]);
    }
    public function getMemberRelationships($memberId)
    {
        $query = "SELECT pr.id, p1.first_name AS person1_first_name, p1.last_name AS person1_last_name, 
                         p2.first_name AS person2_first_name, p2.last_name AS person2_last_name, 
                         p1.id as person1_id, p2.id as person2_id, pr.relation_start, pr.relation_end,
                         rt.description AS relationship_description
                  FROM $this->relation_table  pr
                  INNER JOIN $this->person_table p1 ON pr.person_id1 = p1.id
                  INNER JOIN $this->person_table p2 ON pr.person_id2 = p2.id
                  INNER JOIN $this->relation_type_table  rt ON pr.relationship_type_id = rt.id
                  WHERE pr.person_id1 = :memberId OR pr.person_id2 = :memberId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':memberId', $memberId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateMemberRelationship($relationship)
    {
        $relationshipId = $relationship['relationshipId'] ?? null;
        //$personId1= $relationship['relationsType'];
        $relationshipTypeId = $relationship['relationshipTypeId'] ?? null;
        $relationStart = $relationship['relationStart'] ?? null;
        $relationEnd = $relationship['relationEnd'];
        if (!$relationStart) $relationStart = null;
        if (!$relationEnd) $relationEnd = null;

        $query = "UPDATE $this->relation_table  
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
    public function getRelationships($memberId)
    {
        $query = "SELECT pr.id, p1.first_name as person1_name, p2.first_name as person2_name, rt.description, 
                         p1.id as person1_id, p2.id as person2_id
                  FROM $this->relation_table  pr
                  JOIN $this->person_table  p1 ON pr.person_id1 = p1.id
                  JOIN $this->person_table  p2 ON pr.person_id2 = p2.id
                  JOIN $this->relation_type_table  rt ON pr.relationship_type_id = rt.id
                  ORDER BY pr.relationship_type_id 
                  WHERE pr.person_id1 = :memberId OR pr.person_id2 = :memberId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['memberId' => $memberId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function addRelationship($personId1, $personId2, $relationshipTypeId, $treeId)
    {
        $query = "INSERT INTO $this->relation_table  (person_id1, person_id2, relationship_type_id,tree_id,created_at, updated_at) VALUES (:person_id1, :person_id2, :relationship_type_id,:tree_id, NOW(), NOW())";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'person_id1' => $personId1,
            'person_id2' => $personId2,
            'relationship_type_id' => $relationshipTypeId,
            'tree_id' => $treeId
        ]);
    }
      public function swapRelationship($relationshipId)
    {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Fetch the current relationship
            $sql = "SELECT person_id1, person_id2 FROM $this->relation_table  WHERE id = :relationship_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':relationship_id', $relationshipId, PDO::PARAM_INT);
            $stmt->execute();
            $relationship = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($relationship) {
                // Swap the person IDs
                $personId1 = $relationship['person_id1'];
                $personId2 = $relationship['person_id2'];

                // Update the relationship with swapped IDs
                $updateSql = "UPDATE $this->relation_table  SET person_id1 = :person_id2, person_id2 = :person_id1 WHERE id = :relationship_id";
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
    public function deleteRelationship($relationshipId)
    {
        $query = "DELETE FROM $this->relation_table  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $relationshipId]);
    }

    public function getSpouseFamilies($memberId) {
        // First get the families
        $query = "SELECT f.*, concat(h.first_name, ' ', h.last_name) as husband_name,
                         h.first_name as husband_firstname, h.last_name as husband_lastname,
                        concat(w.first_name, ' ', w.last_name) as wife_name,
                         w.first_name as wife_firstname, w.last_name as wife_lastname,
                         f.marriage_date, f.divorce_date,
                         h.id as husband_id, w.id as wife_id
                  FROM $this->person_table p
                  JOIN $this->families_table f ON (f.husband_id = p.id OR f.wife_id = p.id)
                  LEFT JOIN $this->person_table h ON f.husband_id = h.id
                  LEFT JOIN $this->person_table w ON f.wife_id = w.id
                  WHERE p.id = :member_id 
                  AND f.tree_id = p.tree_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
        
        $families = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // For each family, get the children
        foreach ($families as &$family) {
            // if ($family['husband_name'] && $family['husband_lastname']) {
            //     $family['husband_name'] = trim($family['husband_name'] . ' ' . $family['husband_lastname']);
            // }
            // if ($family['wife_name'] && $family['wife_lastname']) {
            //     $family['wife_name'] = trim($family['wife_name'] . ' ' . $family['wife_lastname']);
            // }
            $family['husband_name'] = trim($family['husband_name']);
            $family['wife_name'] = trim($family['wife_name']);

            // Get children for this family
            $childrenQuery = "SELECT c.id, c.first_name, c.last_name, c.birth_date, c.gender
                             FROM $this->family_children_table fc
                             JOIN $this->person_table c ON fc.child_id = c.id
                             WHERE fc.family_id = :family_id
                             ORDER BY c.birth_date";
            
            $childrenStmt = $this->db->prepare($childrenQuery);
            $childrenStmt->bindParam(':family_id', $family['id']);
            $childrenStmt->execute();
            
            $family['children'] = $childrenStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        //apachelog($families);

        return $families;
    }

    public function getChildFamilies($memberId) {
        $query = "SELECT f.*, 
                         h.first_name as husband_name, h.last_name as husband_lastname,
                         w.first_name as wife_name, w.last_name as wife_lastname,
                         h.id as husband_id, w.id as wife_id
                  FROM $this->person_table p
                  JOIN $this->family_children_table fc ON fc.child_id = p.id
                  JOIN $this->families_table f ON fc.family_id = f.id
                  LEFT JOIN $this->person_table h ON f.husband_id = h.id
                  LEFT JOIN $this->person_table w ON f.wife_id = w.id
                  WHERE p.id = :member_id 
                  AND f.tree_id = p.tree_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
        
        $families = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($families as &$family) {
            if ($family['husband_name'] && $family['husband_lastname']) {
                $family['husband_name'] = trim($family['husband_name'] . ' ' . $family['husband_lastname']);
            }
            if ($family['wife_name'] && $family['wife_lastname']) {
                $family['wife_name'] = trim($family['wife_name'] . ' ' . $family['wife_lastname']);
            }
        }
        return $families;
    }

        public function createFamily($familyData)
    {
        try {
            $marriage_date = !empty($familyData['marriage_date']) ? $familyData['marriage_date'] : null;

            $query = "INSERT INTO $this->families_table (tree_id, husband_id, wife_id, marriage_date, created_at, updated_at) 
                    VALUES (:tree_id, :husband_id, :wife_id, :marriage_date, NOW(), NOW())";

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'tree_id' => $familyData['tree_id'],
                'husband_id' => $familyData['husband_id'],
                'wife_id' => $familyData['wife_id'],
                'marriage_date' => $marriage_date
            ]);
            if (!$result) {
                 error_log("Database error: " . print_r($stmt->errorInfo(), true));
             }

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error creating family: " . $e->getMessage());
            return false;
        }
    }
     public function updateFamily($familyData)
    {
          try {
              $query = "UPDATE $this->families_table SET updated_at = NOW() WHERE id = :family_id";
             $stmt = $this->db->prepare($query);
           $result = $stmt->execute([
             'family_id' => $familyData['family_id']
           ]);
             if (!$result) {
                 error_log("Database error: " . print_r($stmt->errorInfo(), true));
            }
            return $result;

          } catch (Exception $e) {
             error_log("Error updating family: " . $e->getMessage());
            return false;
          }

    }
     public function addChildToFamily($familyId, $childId, $treeId)
    {
        $query = "INSERT INTO $this->family_children_table (family_id, child_id, tree_id, created_at, updated_at) 
                  VALUES (:family_id, :child_id, :tree_id, NOW(), NOW())";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'family_id' => $familyId,
            'child_id' => $childId,
            'tree_id' => $treeId
        ]);
    }

    public function removeChildFromFamily($childId, $familyId)
    {
        $query = "DELETE FROM $this->family_children_table WHERE child_id = :child_id AND family_id = :family_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'child_id' => $childId,
            'family_id' => $familyId
        ]);
    }
    public function removeSpouseFromFamily($spouseId, $familyId)
    {
        $this->db->beginTransaction();
        try {
            // Check if family has children
            $query = "SELECT COUNT(*) FROM $this->family_children_table WHERE family_id = :family_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['family_id' => $familyId]);
            $hasChildren = (int)$stmt->fetchColumn() > 0;

             // Update the family to remove the specific spouse
             if ($spouseId) {
                 $query = "UPDATE $this->families_table 
                           SET husband_id = CASE WHEN husband_id = :spouse_id THEN NULL ELSE husband_id END,
                               wife_id = CASE WHEN wife_id = :spouse_id THEN NULL ELSE wife_id END,
                               updated_at=NOW()
                           WHERE id = :family_id";
                 $stmt = $this->db->prepare($query);
                 $stmt->execute([
                     'spouse_id' => $spouseId,
                     'family_id' => $familyId
                 ]);
             }
            // If no children, delete the family
            if (!$hasChildren) {
                $query = "DELETE FROM $this->families_table WHERE id = :family_id";
                $stmt = $this->db->prepare($query);
                $result = $stmt->execute(['family_id' => $familyId]);
             }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error removing spouse from family: " . $e->getMessage());
            return false;
        }
    }
    public function deleteSpouseKeepChildren($spouseId, $familyId)
    {
        $this->db->beginTransaction();
        try {
            // First set spouse to null in family
            $query = "UPDATE $this->families_table 
                      SET husband_id = CASE WHEN husband_id = :spouse_id THEN NULL ELSE husband_id END,
                          wife_id = CASE WHEN wife_id = :spouse_id THEN NULL ELSE wife_id END, updated_at = NOW()
                      WHERE id = :family_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'spouse_id' => $spouseId,
                'family_id' => $familyId
            ]);

            // Delete the spouse if one was specified
            if ($spouseId) {
                $this->deleteMember($spouseId);
            }

            // Check if family has children
            $query = "SELECT COUNT(*) FROM $this->family_children_table WHERE family_id = :family_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['family_id' => $familyId]);
            $hasChildren = (int)$stmt->fetchColumn() > 0;

            // If no children, delete the family
            if (!$hasChildren) {
                $query = "DELETE FROM $this->families_table WHERE id = :family_id";
                $stmt = $this->db->prepare($query);
                $stmt->execute(['family_id' => $familyId]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting spouse: " . $e->getMessage());
            return false;
        }
    }

    public function deleteSpouseAndChildren($spouseId, $familyId)
    {
        $this->db->beginTransaction();
        try {
            // Delete all children first
            $query = "SELECT child_id FROM $this->family_children_table WHERE family_id = :family_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['family_id' => $familyId]);
            $children = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($children as $childId) {
                $this->deleteMember($childId);
            }

            // Delete the spouse if one was specified
            if ($spouseId) {
                $this->deleteMember($spouseId);
            }

            // Delete the family
            $query = "DELETE FROM $this->families_table WHERE id = :family_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['family_id' => $familyId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting spouse and children: " . $e->getMessage());
            return false;
        }
    }
    public function deleteFamilyAndChildren($familyId)
    {
        $this->db->beginTransaction();
        try {
            // Get all children from this family
            $query = "SELECT child_id FROM $this->family_children_table WHERE family_id = :family_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['family_id' => $familyId]);
            $children = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Delete all children
            foreach ($children as $childId) {
                $this->deleteMember($childId);
            }

            // Delete family_children records first
            $query = "DELETE FROM $this->family_children_table WHERE family_id = :family_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['family_id' => $familyId]);

            // Delete the family record
            $query = "DELETE FROM $this->families_table WHERE id = :family_id";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute(['family_id' => $familyId]);
             if (!$result) {
                 error_log("Database error: " . print_r($stmt->errorInfo(), true));
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in deleteFamilyAndChildren: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function updateFamilySpouse($familyId, $spouseId, $memberGender, $marriageDate)
    {
        $this->db->beginTransaction();
        try {

            // First verify the family exists
            $query = "SELECT * FROM $this->families_table WHERE id = :family_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['family_id' => $familyId]);
            $family = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$family) {
                throw new Exception("Family not found");
            }

            // Handle empty marriage date
            $marriageDate = !empty($marriageDate) ? $marriageDate : null;

            // Update the appropriate spouse field based on gender
            $query = "UPDATE $this->families_table 
                      SET " . ($memberGender == 1 ? "wife_id" : "husband_id") . " = :spouse_id,
                          marriage_date = :marriage_date, updated_at = NOW()
                      WHERE id = :family_id";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'spouse_id' => $spouseId,
                'family_id' => $familyId,
                'marriage_date' => $marriageDate  // Will be NULL if empty
            ]);

            if (!$result) {
                error_log("Database error: " . print_r($stmt->errorInfo(), true));
                throw new Exception("Failed to update family");
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating family spouse: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
       public function getExistingFamily($husbandId, $wifeId, $treeId) {
        $query = "SELECT id FROM $this->families_table
        WHERE (husband_id = :husband_id AND wife_id = :wife_id OR husband_id = :wife_id AND wife_id = :husband_id ) and tree_id = :tree_id";
           $stmt = $this->db->prepare($query);
           $stmt->execute([
               'husband_id' => $husbandId,
                'wife_id' => $wifeId,
               'tree_id' => $treeId
           ]);
           $result = $stmt->fetch(PDO::FETCH_ASSOC);
           if ($result) {
             return $result['id'];
           }
           return null;
    }

     public function createFamilyWithChild($familyData, $childId) {
        $this->db->beginTransaction();
        try {
            $familyId = $this->createFamily($familyData);

            if (!$familyId) {
                throw new Exception("Failed to create family");
             }

            $result = $this->addChildToFamily($familyId, $childId, $familyData['tree_id']);

           if (!$result) {
                throw new Exception("Failed to add child to family");
           }

           $this->db->commit();

            return $familyId;
         } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating family with child: " . $e->getMessage());
            return false;
        }
    }
    public function getDescendantsHierarchy($memberId) {
        $person = $this->getMemberById($memberId);
        if (!$person) {
            error_log("getDescendantsHierarchy: Person not found for memberId: " . $memberId);
            return null;
        }

        // Get all families where this person is a spouse
        $spouseFamilies = $this->getSpouseFamilies($memberId);
        if (!$spouseFamilies) {
            error_log("getDescendantsHierarchy: No spouse families found for memberId: " . $memberId);
            return null;
        }

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
                'id' => $family['id'],
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
                    if (!isset($child['id']) || !is_numeric($child['id'])) {
                            error_log("getDescendantsHierarchy: Invalid child ID for memberId: " . $memberId . " and child: " . print_r($child,true));
                            continue;
                     }

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
}