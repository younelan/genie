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
    private $person_table = 'individuals';
    private $relation_table = 'person_relationship';
    private $synonym_table = 'synonyms';
    private $tree_field = 'tree_id';
    public function __construct($config)
    {
        $this->config = $config;
        $this->db = $config['connection'];
        $this->person_table = $config['tables']['person']??'individuals';
        $this->tree_table = $config['tables']['tree']??'family_tree';
        $this->relation_table = $config['tables']['relation']??'person_relationship';
        $this->synonym_table = $config['tables']['synonyms']??'synonyms';
        
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
        $query = "INSERT INTO $this->tree_table  (owner_id, name, description) VALUES (:owner_id, :name, :description)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute(['owner_id' => $ownerId, 'name' => $name, 'description' => $description]);
        return $this->db->lastInsertId();

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
    public function getTreeData($familyTreeId)
    {
        // Fetching nodes
        $nodesSql = "SELECT id, first_name, last_name FROM $this->person_table  WHERE $this->tree_field = :tree_id";
        $nodesStmt = $this->db->prepare($nodesSql);
        $nodesStmt->bindParam(':tree_id', $familyTreeId, PDO::PARAM_INT);
        $nodesStmt->execute();
        $nodes = $nodesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetching links
        $linksSql = "SELECT person_id1 AS source, person_id2 AS target FROM person_relationship WHERE $this->tree_field = :tree_id";
        $linksStmt = $this->db->prepare($linksSql);
        $linksStmt->bindParam(':tree_id', $familyTreeId, PDO::PARAM_INT);
        $linksStmt->execute();
        $links = $linksStmt->fetchAll(PDO::FETCH_ASSOC);

        return ['nodes' => $nodes, 'links' => $links];
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
}
