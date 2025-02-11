<?php
class MemberModel extends AppModel
{
    private $db;
    private $config;

    private $person_table = 'individuals';
    private $relation_table = 'other_relationships';
    private $notes_table = 'tags';
    private $tree_table = 'trees';
    private $synonym_table;
    private $families_table = 'families';
    private $children_table = 'family_children';


    public function __construct($config)
    {
        $this->config = $config;
        $this->db = $config['connection'];
        $this->person_table = $config['tables']['person'] ?? 'individuals';
        $this->tree_table = $config['tables']['tree'] ?? 'trees';
        $this->relation_table = $config['tables']['relation'] ?? 'other_relationships';
        $this->synonym_table = $config['tables']['synonyms'] ?? 'synonyms';
        $this->families_table = $config['tables']['families'] ?? 'families';
        $this->children_table = $config['tables']['family_children']??'family_children';
    }


    public function addMember($new_member)
    {
        $treeId = $new_member['treeId'] ?? null;
        $firstName = $new_member['firstName'] ?? null;
        $lastName = $new_member['lastName'] ?? null;
        $dateOfBirth = $new_member['dateOfBirth'] ?? null;
        $placeOfBirth = $new_member['placeOfBirth'] ?? null;
        $dateOfDeath = $new_member['dateOfDeath'] ?? null;  // Add this line
        $placeOfDeath = $new_member['placeOfDeath'] ?? null;  // Add this line
        $gender = $new_member['gender'] ?? null;  // Changed from gender_id to gender
        $alive = $new_member['alive'] ?? 1;

        $query = "INSERT INTO $this->person_table  (tree_id, first_name, last_name, birth_date, birth_place, death_date, death_place, gender, alive, created_at, updated_at) VALUES (:tree_id, :first_name, :last_name, :birth_date, :birth_place, :death_date, :death_place, :gender, :alive, NOW(), NOW())";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            'tree_id' => $treeId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birth_date' => $dateOfBirth ? $dateOfBirth : null,
            'birth_place' => $placeOfBirth ? $placeOfBirth : null,
            'death_date' => $dateOfDeath ? $dateOfDeath : null,  // Add this line
            'death_place' => $placeOfDeath ? $placeOfDeath : null,  // Add this line
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
        // Instead of querying DB, just return formatted config array
        $types = [];
        foreach ($this->config['relationship_types'] as $code => $info) {
            $types[] = [
                'code' => $code,
                'description' => $info['description']
            ];
        }
        return $types;
    }

    public function getMemberById($memberId)
    {
        $query = "SELECT * FROM $this->person_table  WHERE id = :member_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

    public function deleteMember($memberId, $isNestedCall = false)
    {
        if (!$isNestedCall) {
            $this->db->beginTransaction();
        }
        
        try {
            // First delete any family_children relationships
            $query = "DELETE FROM $this->children_table WHERE child_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $memberId]);

            // Delete from families table where this person is a spouse
            $query = "DELETE FROM $this->families_table WHERE husband_id = :id OR wife_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $memberId]);

            // Finally delete the person
            $query = "DELETE FROM $this->person_table WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute(['id' => $memberId]);

            if (!$isNestedCall) {
                $this->db->commit();
            }
            return $result;
        } catch (Exception $e) {
            if (!$isNestedCall) {
                $this->db->rollBack();
            }
            error_log("Error in deleteMember: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMemberRelationships($memberId) {
        $query = "SELECT r.id, 
                         r.person_id1, r.person_id2,
                         p1.first_name as person1_first_name, 
                         p1.last_name as person1_last_name,
                         p2.first_name as person2_first_name, 
                         p2.last_name as person2_last_name,
                         r.relcode,
                         r.relation_start,
                         r.relation_end
                  FROM $this->relation_table r
                  JOIN $this->person_table p1 ON r.person_id1 = p1.id
                  JOIN $this->person_table p2 ON r.person_id2 = p2.id
                  WHERE r.person_id1 = :memberId 
                  OR r.person_id2 = :memberId";
    
        $stmt = $this->db->prepare($query);
        $stmt->execute(['memberId' => $memberId]);
        $relationships = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        error_log("Available relationship types: " . print_r($this->config['relationship_types'], true));
        
        foreach ($relationships as &$rel) {
            error_log("Processing relationship with relcode: " . $rel['relcode']);
            if (isset($this->config['relationship_types'][$rel['relcode']])) {
                $rel['description'] = $this->config['relationship_types'][$rel['relcode']]['description'];
                error_log("Found description: " . $rel['description']);
            } else {
                error_log("No description found for relcode: " . $rel['relcode']);
                $rel['description'] = 'Unknown';
            }
        }
    
        return $relationships;
    }

    public function updateMemberRelationship($relationship)
    {
        $relationshipId = $relationship['relationshipId'] ?? null;
        $relcode = $relationship['relcode'] ?? null;
        $relationStart = $relationship['relationStart'] ?? null;
        $relationEnd = $relationship['relationEnd'] ?? null;

        if (!$relationshipId || !$relcode) {
            return false;
        }

        // Convert empty strings to null
        $relationStart = $relationStart ?: null;
        $relationEnd = $relationEnd ?: null;

        $query = "UPDATE $this->relation_table 
                  SET relcode = :relcode, 
                      relation_start = :relation_start, 
                      relation_end = :relation_end,
                      updated_at = NOW()
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':relcode', $relcode);
        $stmt->bindParam(':relation_start', $relationStart);
        $stmt->bindParam(':relation_end', $relationEnd);
        $stmt->bindParam(':id', $relationshipId);

        return $stmt->execute();
    }

    public function getRelationships($memberId)
    {
        $query = "SELECT pr.id, p1.first_name as person1_name, p2.first_name as person2_name, pr.relcode,
                         p1.id as person1_id, p2.id as person2_id
                  FROM $this->relation_table pr
                  JOIN $this->person_table p1 ON pr.person_id1 = p1.id
                  JOIN $this->person_table p2 ON pr.person_id2 = p2.id
                  ORDER BY pr.relcode
                  WHERE pr.person_id1 = :memberId OR pr.person_id2 = :memberId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['memberId' => $memberId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Add relationship description from config lookup
        foreach ($rows as &$row) {
            $row['description'] = $this->config['relationship_types'][$row['relcode']]['description'] ?? 'Unknown';
        }
        return $rows;
    }

    public function addRelationship($personId1, $personId2, $relcode, $treeId)
    {
        // Validate relcode exists in config
        if (!isset($this->config['relationship_types'][$relcode])) {
            throw new Exception('Invalid relationship code');
        }

        $query = "INSERT INTO $this->relation_table (person_id1, person_id2, relcode, tree_id, created_at, updated_at) 
                  VALUES (:person_id1, :person_id2, :relcode, :tree_id, NOW(), NOW())";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'person_id1' => $personId1,
            'person_id2' => $personId2,
            'relcode' => $relcode,
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
    $query = "SELECT 
        f.*,
        CASE 
            WHEN p.id = f.husband_id THEN 
                COALESCE(CONCAT(w.first_name, ' ', w.last_name), 'Unknown Spouse')
            ELSE 
                COALESCE(CONCAT(h.first_name, ' ', h.last_name), 'Unknown Spouse')
        END as spouse_name,
        CASE 
            WHEN p.id = f.husband_id THEN w.id
            ELSE h.id
        END as spouse_id,
        CASE 
            WHEN p.id = f.husband_id THEN 'F'
            ELSE 'M'
        END as spouse_gender,
        f.marriage_date, 
        f.divorce_date
    FROM $this->person_table p
    JOIN $this->families_table f ON (f.husband_id = p.id OR f.wife_id = p.id)
    LEFT JOIN $this->person_table h ON f.husband_id = h.id
    LEFT JOIN $this->person_table w ON f.wife_id = w.id
    WHERE p.id = :member_id 
    AND f.tree_id = p.tree_id";
    
    $stmt = $query = "SELECT 
        f.*,
        CASE 
            WHEN p.id = f.husband_id THEN 
                COALESCE(CONCAT(w.first_name, ' ', w.last_name), 'Unknown Spouse')
            ELSE 
                COALESCE(CONCAT(h.first_name, ' ', h.last_name), 'Unknown Spouse')
        END as spouse_name,
        CASE 
            WHEN p.id = f.husband_id THEN w.id
            ELSE h.id
        END as spouse_id,
        CASE 
            WHEN p.id = f.husband_id THEN 'F'
            ELSE 'M'
        END as spouse_gender,
        f.marriage_date, 
        f.divorce_date
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
        // Get children for this family
        $childrenQuery = "SELECT c.id, c.first_name, c.last_name, c.birth_date, c.gender
                         FROM $this->children_table fc
                         JOIN $this->person_table c ON fc.child_id = c.id
                         WHERE fc.family_id = :family_id
                         ORDER BY c.birth_date";
        
        $childrenStmt = $this->db->prepare($childrenQuery);
        $childrenStmt->bindParam(':family_id', $family['id']);
        $childrenStmt->execute();
        
        $family['children'] = $childrenStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add flags for UI logic
        $family['has_spouse'] = ($family['spouse_id'] !== null);
        $family['is_single_parent'] = ($family['husband_id'] === null || $family['wife_id'] === null);
    }

    return $families;
}

    public function getChildFamilies($memberId) {
        $query = "SELECT f.*, 
                         h.first_name as husband_name, h.last_name as husband_lastname,
                         w.first_name as wife_name, w.last_name as wife_lastname,
                         h.id as husband_id, w.id as wife_id
                  FROM $this->person_table p
                  JOIN $this->children_table fc ON fc.child_id = p.id
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
        $currentTime = date('Y-m-d H:i:s');

        $query = "INSERT INTO $this->families_table (
            tree_id, 
            husband_id, 
            wife_id, 
            marriage_date, 
            created_at, 
            updated_at,
            active
        ) VALUES (
            :tree_id, 
            :husband_id, 
            :wife_id, 
            :marriage_date, 
            :created_at, 
            :updated_at,
            1
        )";

        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            'tree_id' => $familyData['tree_id'],
            'husband_id' => $familyData['husband_id'],
            'wife_id' => $familyData['wife_id'],
            'marriage_date' => $marriage_date,
            'created_at' => $currentTime,
            'updated_at' => $currentTime
        ]);

        if (!$result) {
            error_log("Database error: " . print_r($stmt->errorInfo(), true));
        }

        return $result ? $this->db->lastInsertId() : false;
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
        $query = "INSERT INTO $this->children_table (family_id, child_id, tree_id, created_at, updated_at) 
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
        $this->db->beginTransaction();
        try {
            // Only remove the relationship in family_children table
            $query = "DELETE FROM $this->children_table 
                      WHERE child_id = :child_id 
                      AND family_id = :family_id";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'child_id' => $childId,
                'family_id' => $familyId
            ]);

            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error removing child from family: " . $e->getMessage());
            return false;
        }
    }

    public function removeSpouseFromFamily($spouseId, $familyId)
    {
        $this->db->beginTransaction();
        try {
            // Check if family has children
            $query = "SELECT COUNT(*) FROM $this->children_table WHERE family_id = :family_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['family_id' => $familyId]);
            $hasChildren = (int)$stmt->fetchColumn() > 0;

            if ($hasChildren) {
                // If there are children, just set the spouse to NULL
                $query = "UPDATE $this->families_table 
                         SET husband_id = CASE WHEN husband_id = :spouse_id THEN NULL ELSE husband_id END,
                             wife_id = CASE WHEN wife_id = :spouse_id THEN NULL ELSE wife_id END,
                             updated_at = NOW()
                         WHERE id = :family_id";
                $stmt = $this->db->prepare($query);
                $result = $stmt->execute([
                    'spouse_id' => $spouseId,
                    'family_id' => $familyId
                ]);
            } else {
                // If no children, delete the entire family record
                $query = "DELETE FROM $this->families_table WHERE id = :family_id";
                $stmt = $this->db->prepare($query);
                $result = $stmt->execute(['family_id' => $familyId]);
            }

            $this->db->commit();
            return $result;
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
            // First update the family to remove the spouse reference
            $query = "UPDATE $this->families_table 
                     SET husband_id = CASE WHEN husband_id = :spouse_id THEN NULL ELSE husband_id END,
                         wife_id = CASE WHEN wife_id = :spouse_id THEN NULL ELSE wife_id END,
                         updated_at = NOW()
                     WHERE id = :family_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'spouse_id' => $spouseId,
                'family_id' => $familyId
            ]);

            // Then delete the spouse person record if specified
            if ($spouseId) {
                $this->deleteMember($spouseId);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in deleteSpouseKeepChildren: " . $e->getMessage());
            return false;
        }
    }

    public function deleteSpouseAndChildren($spouseId, $familyId)
    {
        $this->db->beginTransaction();
        try {
            // Check if family has any children first
            $query = "SELECT EXISTS(SELECT 1 FROM $this->children_table WHERE family_id = :family_id)";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['family_id' => $familyId]);
            $hasChildren = (bool)$stmt->fetchColumn();

            if ($hasChildren) {
                // If there are children, delete them first
                $query = "SELECT child_id FROM $this->children_table WHERE family_id = :family_id";
                $stmt = $this->db->prepare($query);
                $stmt->execute(['family_id' => $familyId]);
                $children = $stmt->fetchAll(PDO::FETCH_COLUMN);

                // Delete the family_children records
                $query = "DELETE FROM $this->children_table WHERE family_id = :family_id";
                $stmt = $this->db->prepare($query);
                $stmt->execute(['family_id' => $familyId]);

                // Delete each child
                foreach ($children as $childId) {
                    $this->deleteMember($childId, true);
                }
            }

            // Delete the spouse if specified
            if ($spouseId) {
                $this->deleteMember($spouseId, true);
            }

            // Finally delete the family record
            $query = "DELETE FROM $this->families_table WHERE id = :family_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['family_id' => $familyId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in deleteSpouseAndChildren: " . $e->getMessage());
            return false;
        }
    }

    public function deleteFamilyAndChildren($familyId)
    {
        $this->db->beginTransaction();
        try {
            // First get all children IDs
            $query = "SELECT child_id FROM $this->children_table WHERE family_id = :family_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['family_id' => $familyId]);
            $children = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Delete children records - pass true to indicate nested call
            foreach ($children as $childId) {
                $this->deleteMember($childId, true);
            }

            // Delete the family record
            $query = "DELETE FROM $this->families_table WHERE id = :family_id";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute(['family_id' => $familyId]);

            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in deleteFamilyAndChildren: " . $e->getMessage());
            throw $e;
        }
    }

public function updateFamilySpouse($data)
{
    $this->db->beginTransaction();
    try {
        $familyId = $data['family_id'];
        $spouseId = $data['spouse_id'];
        $position = $data['position']; // 'husband' or 'wife'
        $marriageDate = !empty($data['marriage_date']) ? $data['marriage_date'] : null;
        $currentTime = date('Y-m-d H:i:s');

        $query = "UPDATE $this->families_table 
                  SET " . ($position === 'husband' ? 'husband_id' : 'wife_id') . " = :spouse_id,
                      marriage_date = :marriage_date,
                      updated_at = :updated_at
                  WHERE id = :family_id";
        
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            'spouse_id' => $spouseId,
            'family_id' => $familyId,
            'marriage_date' => $marriageDate,
            'updated_at' => $currentTime
        ]);

        if (!$result) {
            throw new Exception("Failed to update family");
        }

        $this->db->commit();
        return true;
    } catch (Exception $e) {
        $this->db->rollBack();
        error_log("Error updating family spouse: " . $e->getMessage());
        throw $e;
    }
}

    public function getFamilyById($familyId)
    {
        $query = "SELECT * FROM $this->families_table WHERE id = :family_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['family_id' => $familyId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

    public function getFamilyChildren($familyId)
    {
        $query = "SELECT c.id, c.first_name, c.last_name, c.birth_date, c.gender 
                  FROM $this->children_table fc
                  JOIN $this->person_table c ON fc.child_id = c.id
                  WHERE fc.family_id = :family_id
                  ORDER BY c.birth_date";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['family_id' => $familyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRelationship($data) {
        error_log("Updating relationship with data: " . print_r($data, true));
        
        if (empty($data['id']) || empty($data['relcode'])) {
            error_log("Missing required data for update");
            return false;
        }
    
        $query = "UPDATE $this->relation_table 
                  SET relcode = :relcode,
                      relation_start = :start_date,
                      relation_end = :end_date
                  WHERE id = :id";
    
        $params = [
            'id' => $data['id'],
            'relcode' => $data['relcode'],
            'start_date' => $data['relation_start'],
            'end_date' => $data['relation_end']
        ];
    
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute($params);
        
        error_log("Update result: " . ($result ? 'success' : 'failure'));
        
        return $result;
    }

    public function removeFamilySpouse($familyId, $currentMemberId) {
        $this->db->beginTransaction();
        try {
            // Get the family first to identify which spouse to remove
            $family = $this->getFamilyById($familyId);
            if (!$family) {
                throw new Exception('Family not found');
            }

            // Set the other spouse to NULL but keep the current member
            $query = "UPDATE $this->families_table 
                     SET husband_id = CASE 
                            WHEN husband_id = :current_id THEN husband_id 
                            ELSE NULL 
                         END,
                         wife_id = CASE 
                            WHEN wife_id = :current_id THEN wife_id 
                            ELSE NULL 
                         END,
                         updated_at = NOW()
                     WHERE id = :family_id";

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'family_id' => $familyId,
                'current_id' => $currentMemberId
            ]);
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error removing spouse from family: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteFamily($familyId) {
        $this->db->beginTransaction();
        try {
            // First remove all children from the family
            $query = "DELETE FROM $this->children_table 
                     WHERE family_id = :family_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['family_id' => $familyId]);

            // Then delete the family
            $query = "DELETE FROM $this->families_table 
                     WHERE id = :family_id";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute(['family_id' => $familyId]);

            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting family: " . $e->getMessage());
            throw $e;
        }
    }

    public function getRelationshipDetail($relationshipId) {
        $stmt = $this->db->prepare("SELECT relcode, relation_start, relation_end, person1_first_name, person1_last_name, person2_first_name, person2_last_name FROM relationships WHERE id = ?");
        $stmt->execute([$relationshipId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        // Use relcode from row for lookup. If not available, fall back if needed.
        $relcode = $row['relcode'] ?? null;
        $relationshipInfo = $this->config['relationship_types'][$relcode] ?? ['description' => 'Unknown'];
    
        return [
            'relcode'        => $relcode,
            'description'    => $relationshipInfo['description'],
            'relation_start' => $row['relation_start'],
            'relation_end'   => $row['relation_end'],
            'person1'        => trim($row['person1_first_name'] . ' ' . $row['person1_last_name']),
            'person2'        => trim($row['person2_first_name'] . ' ' . $row['person2_last_name'])
        ];
    }

    protected function convertRelationshipRecord($row) {
        $relcode = $row['relcode'] ?? null;
        $relationshipInfo = $this->config['relationship_types'][$relcode] ?? ['description' => 'Unknown'];
        return [
            'id'             => $row['id'],
            'relcode'        => $relcode,
            'description'    => $relationshipInfo['description'],
            'relation_start' => $row['relation_start'],
            'relation_end'   => $row['relation_end'],
            'person1_first_name' => $row['person1_first_name'],
            'person1_last_name'  => $row['person1_last_name'],
            'person2_first_name' => $row['person2_first_name'],
            'person2_last_name'  => $row['person2_last_name']
        ];
    }
}