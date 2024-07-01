<?php
class TreeModel {
    private $db;
    private $tree_table = 'family_tree';
    private $person_table = 'person';
    private $relation_table = 'person_relationship';

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllTreesByOwner($ownerId) {
        $query = "SELECT * FROM $this->tree_table WHERE owner_id = :owner_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['owner_id' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTree($ownerId, $name, $description) {
        $query = "INSERT INTO $this->tree_table  (owner_id, name, description) VALUES (:owner_id, :name, :description)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['owner_id' => $ownerId, 'name' => $name, 'description' => $description]);
    }
    public function searchMembers($treeId, $query) {
        $query = "%$query%";
        $sql = "SELECT * FROM $this->person_table  WHERE family_tree_id = :tree_id AND (first_name LIKE :query OR last_name LIKE :query)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tree_id' => $treeId, 'query' => $query]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function deleteTree($treeId, $ownerId) {
        $query = "DELETE FROM $this->tree_table  WHERE id = :tree_id AND owner_id = :owner_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['tree_id' => $treeId, 'owner_id' => $ownerId]);
    }

    public function getMembersByTreeId($treeId, $offset, $limit) {
        $query = "SELECT * FROM $this->person_table  WHERE family_tree_id = :tree_id LIMIT :offset, :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tree_id', $treeId, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTreeData($familyTreeId) {
        // Fetching nodes
        $nodesSql = "SELECT id, first_name, last_name FROM $this->person_table  WHERE family_tree_id = :tree_id";
        $nodesStmt = $this->db->prepare($nodesSql);
        $nodesStmt->bindParam(':tree_id', $familyTreeId, PDO::PARAM_INT);
        $nodesStmt->execute();
        $nodes = $nodesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetching links
        $linksSql = "SELECT person_id1 AS source, person_id2 AS target FROM person_relationship WHERE family_tree_id = :tree_id";
        $linksStmt = $this->db->prepare($linksSql);
        $linksStmt->bindParam(':tree_id', $familyTreeId, PDO::PARAM_INT);
        $linksStmt->execute();
        $links = $linksStmt->fetchAll(PDO::FETCH_ASSOC);

        return ['nodes' => $nodes, 'links' => $links];
    }


    private function buildTree(array &$elements, $parentId = null) {
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
    public function countMembersByTreeId($treeId) {
        $query = "SELECT COUNT(*) FROM $this->person_table  WHERE family_tree_id = :tree_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['tree_id' => $treeId]);
        return $stmt->fetchColumn();
    }
    // public function getPersonCount($treeId) {
    //     $sql = "SELECT count(*) FROM `$this->person_table` WHERE tree_id = ?"; 
    //     $result = $con->prepare($sql); 
    //     $result->execute([$treeId]); 
    //     return $result->fetchColumn();           
    // }
    public function countRelationshipsByTreeId($treeId) {
        $sql = "SELECT count(*) FROM `$this->relation_table` WHERE family_tree_id = ?"; 
        $result = $this->db->prepare($sql); 
        $result->execute([$treeId]); 
        return $result->fetchColumn();           
    }

}
