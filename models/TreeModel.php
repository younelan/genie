<?php
function getGenderSymbol($gender)
{
    switch ($gender) {
        case 'M':
            return '♂️'; // Male sign
        case 'F':
            return '♀️'; // Female sign
        case 'U':
            return '⚲'; // Neuter (unspecified) sign
        default:
            return '⁉️'; // Unknown or fallback symbol
    }
}
class TreeModel extends AppModel
{
    private $db,$config;
    private $tree_table = 'trees';
    private $relation_type_table = 'relationship_type';
    private $person_table = 'individuals';
    private $family_table = 'families';
    private $children_table = 'family_children';
    private $relation_table = 'other_relationships';
    private $notes_table = 'tags';
    private $synonym_table = 'synonyms';
    private $tree_field = 'tree_id';
    public function __construct($config)
    {
        $this->config = $config;
        $this->db = $config['connection'];
        $this->person_table = $config['tables']['person']??'individuals';
        $this->tree_table = $config['tables']['tree']??'family_tree';
        $this->relation_table = $config['tables']['relation']??'other_relationships';
        $this->synonym_table = $config['tables']['synonyms']??'synonyms';
        $this->notes_table = $config['tables']['notes']??'tags';        
    }

    public function getAllTreesByOwner($ownerId)
    {
        $query = "SELECT * FROM $this->tree_table WHERE owner_id = :owner_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['owner_id' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTree($ownerId, $name, $description)
    {
        $query = "INSERT INTO $this->tree_table (owner_id, name, description, is_public, created_at, updated_at) 
                  VALUES (:owner_id, :name, :description, :is_public, NOW(), NOW())";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            'owner_id' => $ownerId, 
            'name' => $name, 
            'description' => $description,
            'is_public' => 0  // Default to private
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    public function searchMembers($treeId, $query)
    {
        $query = "%$query%";
        $sql = "SELECT * FROM $this->person_table  WHERE $this->tree_field = :tree_id AND (first_name LIKE :query OR last_name LIKE :query)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tree_id' => $treeId, 'query' => $query]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteTree($treeId, $ownerId)
    {
        $query = "DELETE FROM $this->tree_table  WHERE id = :tree_id AND owner_id = :owner_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['tree_id' => $treeId, 'owner_id' => $ownerId]);
    }

    public function getMembersByTreeId($treeId, $offset, $limit, $orderby='')
    {
        if($orderby) {
            $orderby = "ORDER BY $orderby";
        } else {
            $orderby = '';
        }

        $query = "SELECT * FROM $this->person_table  WHERE $this->tree_field = :tree_id $orderby LIMIT :offset, :limit";
        
        //apachelog($query."\nlimit $limit offset $offset\n");
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tree_id', $treeId, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastUpdatesByTreeId($treeId, $offset=0, $limit=15)
    {
        $query = "SELECT * FROM $this->person_table  WHERE $this->tree_field = :tree_id LIMIT :offset, :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tree_id', $treeId, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    
    public function getFamilies($familyTreeId)
    {
        try {
            error_log("Getting families for tree: $familyTreeId");

            // Verify tree exists
            $treeSql = "SELECT * FROM trees WHERE id = :tree_id LIMIT 1";
            $treeStmt = $this->db->prepare($treeSql);
            $treeStmt->execute(['tree_id' => $familyTreeId]);
            if (!$treeStmt->fetch()) {
                throw new Exception('Tree not found');
            }

            // Update individual query to include all relevant fields
            $nodesSql = "SELECT id, first_name, last_name, gender, birth_date, birth_place, 
                               death_date, death_place, alive, data
                        FROM individuals 
                        WHERE tree_id = :tree_id";
            $nodesStmt = $this->db->prepare($nodesSql);
            $nodesStmt->execute(['tree_id' => $familyTreeId]);
            $individuals = $nodesStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($individuals) . " individuals");

            // Update families query to include all relevant fields
            $familiesSql = "SELECT f.id, f.husband_id, f.wife_id, f.marriage_date, 
                                  f.marriage_place_id, f.divorce_date, f.divorce_place_id, f.data,
                                  h.first_name as husband_first_name, h.last_name as husband_last_name,
                                  w.first_name as wife_first_name, w.last_name as wife_last_name
                           FROM families f
                           LEFT JOIN individuals h ON f.husband_id = h.id
                           LEFT JOIN individuals w ON f.wife_id = w.id
                           WHERE f.tree_id = :tree_id";
            $familiesStmt = $this->db->prepare($familiesSql);
            $familiesStmt->execute(['tree_id' => $familyTreeId]);
            $families = $familiesStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($families) . " families");

            // Get all children relationships
            $childrenSql = "SELECT fc.id, fc.family_id, fc.child_id,
                           c.first_name, c.last_name, c.gender, c.birth_date
                           FROM family_children fc
                           INNER JOIN families f ON fc.family_id = f.id
                           INNER JOIN individuals c ON fc.child_id = c.id
                           WHERE f.tree_id = :tree_id";
            $childrenStmt = $this->db->prepare($childrenSql);
            $childrenStmt->execute(['tree_id' => $familyTreeId]);
            $children = $childrenStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($children) . " child relationships");

            return [
                'success' => true,
                'data' => [
                    'individuals' => $individuals,
                    'families' => $families,
                    'children' => $children
                ]
            ];

        } catch (PDOException $e) {
            error_log("Database error in getFamilies: " . $e->getMessage());
            error_log("SQL State: " . $e->errorInfo[0]);
            error_log("Error Code: " . $e->errorInfo[1]);
            error_log("Message: " . $e->errorInfo[2]);
            throw $e;
        }
    }

    private function buildTree(array &$elements, $parentId = null)
    {
        $branch = [];
        foreach ($elements as &$element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
                unset($element);
            }
        }
        return $branch;
    }
    public function countMembersByTreeId($treeId)
    {
        $query = "SELECT gender, count(*) as total FROM `$this->person_table` 
        WHERE $this->tree_field = :tree_id
        GROUP BY gender";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['tree_id' => $treeId]);
        $vals = [];
        $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($counts as $idx => $row) {
            switch ($row['gender']) {
                case 'M':
                    $vals['Men'] = $row['total'];
                    break;
                case 'F':
                    $vals['Women'] = $row['total'];
                    break;
                case null:
                default:
                    $vals['?'] = $row['total'];
            }
        }
        return $vals;
    }
    public function countTreeMembersByField($treeId, $field,$synonyms=null,$limit=15)
    {
        $query = "SELECT $field, count(*) as total FROM `$this->person_table` 
                    WHERE $this->tree_field = ? 
                    GROUP BY $field
                    ORDER BY total desc
                    ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$treeId]);
        $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $vals = [];
        foreach ($counts as $idx => $row) {
            $row_key = $row[$field];
            if ($synonyms && isset( $synonyms[strtolower($row_key)] )) {
                $row_key = $synonyms[strtolower($row_key)];
            }
            if(!isset($vals[$row_key])) {
                $vals[$row_key] = 0;
            }

            $vals[$row_key] += $row['total'];
        }
        arsort($vals);
        if($limit>0) {
            $vals = array_slice($vals, 0, $limit, true);

        }
        return $vals;
    }
    public function getSynonymsByTreeId($treeId) {
        $sql = "SELECT * from $this->synonym_table where $this->tree_field = ? ";
        $result = $this->db->prepare($sql);
        $result->execute([$treeId]);
        $vals = [];
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $vals[strtolower($row['key'])] = $row['value'];
        }
        return $vals;
    }
    public function getPersonCount($treeId)
    {
        $sql = "SELECT count(*) FROM `$this->person_table` WHERE $this->tree_field = ?";
        $result = $this->db->prepare($sql);
        $result->execute([$treeId]);
        return $result->fetchColumn();
    }
    
    public function countRelationshipsByTreeId($treeId)
    {
        $sql = "SELECT count(*) FROM `$this->relation_table` WHERE $this->tree_field = ?";
        $result = $this->db->prepare($sql);
        $result->execute([$treeId]);
        return $result->fetchColumn();
    }

    public function updateTree($treeId, $data, $ownerId)
    {
        $allowedFields = ['name', 'description', 'is_public'];
        $updates = [];
        $params = ['tree_id' => $treeId, 'owner_id' => $ownerId];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $updates[] = "updated_at = NOW()";
        $updateStr = implode(', ', $updates);

        $query = "UPDATE $this->tree_table 
                 SET $updateStr 
                 WHERE id = :tree_id AND owner_id = :owner_id";

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }
    public function getRelationshipTypes($tree_id = 1)
    {
        $query = "SELECT id, description FROM $this->relation_type_table WHERE tree_id = :tree_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['tree_id' => $tree_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function emptyTree($treeId, $ownerId) {
        try {
            $this->db->beginTransaction();

            // Only proceed if user owns the tree
            $stmt = $this->db->prepare("SELECT id FROM $this->tree_table WHERE id = ? AND owner_id = ?");
            $stmt->execute([$treeId, $ownerId]);
            if (!$stmt->fetch()) {
                return false;
            }

            // Delete all tags for this tree
            $stmt = $this->db->prepare("DELETE FROM $this->notes_table WHERE tree_id = ?");
            $stmt->execute([$treeId]);

            // Delete all child relationships
            $stmt = $this->db->prepare("DELETE fc FROM $this->children_table fc
                                      INNER JOIN $this->family_table f ON fc.family_id = f.id
                                      WHERE f.tree_id = ?");
            $stmt->execute([$treeId]);

            // Delete all families
            $stmt = $this->db->prepare("DELETE FROM $this->family_table WHERE tree_id = ?");
            $stmt->execute([$treeId]);

            // Delete all other relationships
            $stmt = $this->db->prepare("DELETE FROM $this->relation_table WHERE tree_id = ?");
            $stmt->execute([$treeId]);

            // Delete all individuals - Fix the syntax error here
            $stmt = $this->db->prepare("DELETE FROM $this->person_table WHERE tree_id = ?");
            $stmt->execute([$treeId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function addSynonym($treeId, $key, $value) {
        $query = "INSERT INTO $this->synonym_table (tree_id, `key`, `value`) 
                  VALUES (:tree_id, :key, :value)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'tree_id' => $treeId,
            'key' => trim($key),
            'value' => trim($value)
        ]);
    }

    public function updateSynonym($synonymId, $treeId, $key, $value) {
        $query = "UPDATE $this->synonym_table 
                  SET `key` = :key, `value` = :value 
                  WHERE syn_id = :id AND tree_id = :tree_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'id' => $synonymId,
            'tree_id' => $treeId,
            'key' => trim($key),
            'value' => trim($value)
        ]);
    }

    public function deleteSynonym($synonymId, $treeId) {
        $query = "DELETE FROM $this->synonym_table 
                  WHERE syn_id = :id AND tree_id = :tree_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'id' => $synonymId,
            'tree_id' => $treeId
        ]);
    }

    public function getAllSynonyms($treeId) {
        $query = "SELECT * FROM $this->synonym_table WHERE tree_id = :tree_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['tree_id' => $treeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
