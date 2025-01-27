<?php

class FamilyModel {
    private $db,$config;
    private $family_table = 'families';
    private $children_table = 'family_children';

    private $person_table = 'individuals';
    private $relation_table = 'person_relationship';
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
    public function createFamily($treeId,$husband,$wife,$children=null) {
        // Create a family where treeid is required, either husband or wife is require but one can be null, children is optional

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
}