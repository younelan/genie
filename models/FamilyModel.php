<?php

class FamilyModel {
    private $db,$config;
    private $family_table = 'families';
    private $children_table = 'family_children';

    private $person_table = 'individuals';
    private $relation_table = 'person_relationship';
    private $family_children_table = 'family_children';

    private $synonym_table = 'synonyms';
    private $family_field = 'family_id';
    public function __construct($config)
    {
        $this->config = $config;
        $this->db = $config['connection'];
        $this->person_table = $config['tables']['person']??'individuals';
        $this->family_table = $config['tables']['family']??'families';
        $this->relation_table = $config['tables']['relation']??'person_relationship';
        $this->synonym_table = $config['tables']['synonyms']??'synonyms';
        
    }
    public function addIndividual($new_member)
    {
        apachelog($new_member);
        $treeId = $new_member['treeId'] ?? null;
        apachelog($treeId);
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
    public function createFamily(array $familyData): int
    {
        $sql = "INSERT INTO families (tree_id, husband_id, wife_id, marriage_date, created_at, updated_at) VALUES (:tree_id, :husband_id, :wife_id, :marriage_date, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);
        $marriageDate = $familyData['marriage_date'] ?? null; // Get date or null
        $created_at = $familyData['created_at'] ?? date('Y-m-d H:i:s');
        $updated_at = $familyData['updated_at'] ?? date('Y-m-d H:i:s');
       if(empty($marriageDate)){
           $marriageDate = null;
       }

        $stmt->execute([
            ':tree_id' => $familyData['tree_id'],
            ':husband_id' => $familyData['husband_id'] ?? null,
            ':wife_id' => $familyData['wife_id'] ?? null,
            ':marriage_date' => $marriageDate,
            ':created_at' => $created_at,
            ':updated_at' => $updated_at,
        ]);
        return $this->db->lastInsertId();
    }

    public function addChildToFamily($familyId, $childId, $treeId)
    {
        $query = "INSERT INTO $this->family_children_table (family_id, child_id, created_at, updated_at) 
                  VALUES (:family_id, :child_id, NOW(), NOW())";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'family_id' => $familyId,
            'child_id' => $childId,
        ]);
    }
    public function addSpouse($spouse)
    {
        $query = "INSERT INTO $this->family_table  (owner_id, name, description) VALUES (:owner_id, :name, :description)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute(['owner_id' => $ownerId, 'name' => $name, 'description' => $description]);
        return $this->db->lastInsertId();

    }
    public function addChild($child)
    {
        $query = "INSERT INTO $this->family_table  (owner_id, name, description) VALUES (:owner_id, :name, :description)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute(['owner_id' => $ownerId, 'name' => $name, 'description' => $description]);
        return $this->db->lastInsertId();

    }
    public function addParents($parent)
    {

        $query = "INSERT INTO $this->family_table  (owner_id, name, description) VALUES (:owner_id, :name, :description)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute(['owner_id' => $ownerId, 'name' => $name, 'description' => $description]);
        return $this->db->lastInsertId();

    }
    public function addOther($other)
    {
    }

    public function getFamilyById($familyId) {
        $query = "SELECT f.*, 
                         h.first_name as husband_name, h.gender as husband_gender,
                         w.first_name as wife_name, w.gender as wife_gender
                  FROM $this->family_table f
                  LEFT JOIN $this->person_table h ON f.husband_id = h.id
                  LEFT JOIN $this->person_table w ON f.wife_id = w.id
                  WHERE f.id = :family_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['family_id' => $familyId]);
        
        $family = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($family) {
            // Add children to family data
            $childrenQuery = "SELECT c.*, p.first_name, p.last_name, p.gender, p.birth_date 
                            FROM $this->children_table c
                            JOIN $this->person_table p ON c.child_id = p.id
                            WHERE c.family_id = :family_id";
            
            $childStmt = $this->db->prepare($childrenQuery);
            $childStmt->execute(['family_id' => $familyId]);
            $family['children'] = $childStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $family;
    }
}