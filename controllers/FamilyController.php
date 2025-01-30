<?php

class FamilyController extends AppController
{
    private $member;
    private $config;
    private $data;
    private $family;
    private $familyIdField = 'id'; // Class variable for family ID field

    public function __construct($config)
    {
        $this->config = $config;
        $this->family = new FamilyModel($config);
    }
    private function convertToAssociativeArray($dataArray) {
        $assocArray = [];
            if(is_array($dataArray)){
                foreach ($dataArray as $item) {
                    if(isset($item['name']) && isset($item['value'])){
                         $assocArray[$item['name']] = $item['value'];
                    }
                }
            }
        return $assocArray;
    }    
    public function addRelationship() {
        header('Content-Type: application/json');
        error_log("Received POST data: " . print_r($_POST, true)); // Debug log
        
        try {
            if (!isset($_POST['type']) || !isset($_POST['relationship_type'])) {
                error_log("Missing type in POST data"); // Debug log
                throw new Exception('Missing relationship type');
            }

            $type = $_POST['type'];
            $this->data = $_POST;
            
            switch ($type) {
                case 'spouse':
                    $this->addSpouse();
                    break;
                case 'child':
                    $this->addChild();
                    break;
                case 'parent':
                    $this->addParents();
                    break;
                case 'other':
                    $this->addOther();
                    break;
                default:
                    error_log("Invalid relationship type: " . $type); // Debug log
                    throw new Exception('Invalid relationship type: ' . $type);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Relationship added successfully'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    public function addSpouse() {
        apachelog("Adding a spouse");
        
        $alive = $this->data['alive'] ?? 1;
        $marriageDate = $this->data['marriage_date'] ?? null;
        $memberId = $this->data['member_id'];
        $treeId = $this->data['tree_id'];

        try {
            if ($this->data['spouse_type'] == 'new') {
                $newSpouseData = [
                    'firstName' => $this->data['spouse_first_name'] ?? null,
                    'lastName' => $this->data['spouse_last_name'] ?? null,
                    'treeId' => $treeId,
                    'gender' => $this->data['spouse_gender'] ?? null,
                    'dateOfBirth' => $this->data['spouse_birth_date'] ?? null,
                    'alive' => $alive,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $spouseId = $this->family->addIndividual($newSpouseData);
                $spouseGender = $newSpouseData['gender'];
            } else {
                $spouseId = $this->data['spouse_id'];
                $spouseGender = $this->family->getMemberGender($spouseId);
            }

            // Determine husband/wife based on gender
            if ($spouseGender == 'F') {
                $husband = $memberId;
                $wife = $spouseId;
            } else {
                $husband = $spouseId;
                $wife = $memberId;
            }

            $familyData = [
                'tree_id' => $treeId,
                'husband_id' => $husband,
                'wife_id' => $wife,
                'marriage_date' => $marriageDate,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            apachelog("Creating family with data:");
            apachelog($familyData);
            return $this->family->createFamily($familyData);

        } catch (Exception $e) {
            apachelog("Error in addSpouse: " . $e->getMessage());
            throw $e;
        }
    }

    public function addChild() {
        $alive = $this->data['alive']??1;
        if($this->data['child_type'] == 'new')
        {
                $person = [
                    'firstName' => $this->data['child_first_name'] ?? null,
                    'lastName' => $this->data['child_last_name'] ?? null,
                    'treeId' => $this->data['tree_id'] ?? null,
                    'gender' => $this->data['child_gender'] ?? null,
                    'dateOfBirth' => $this->data['child_birth_date'] ?? null,
                    'alive' => $alive,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
            ];
            $childId = $this->family->addIndividual($person);
        } else {
            $childId = $this->data['child_id'];
        }
        if($this->data['family_id']=='new') {
            $familyData = [
                'tree_id' => $this->data['tree_id'],
                'husband_id' => $this->data['member_id'],
                'wife_id' => null,
                'marriage_date' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $familyId = $this->family->createFamily($familyData);
        } else {
            $familyId = $this->data['family_id'];
        }
        $this->family->addChildToFamily($familyId, $childId, $this->data['tree_id']);

    }
    public function addOther() {
        apachelog("Adding other relationship");
        /*
        - Add Other Relationship
        - Options should be Cousin, Sibling, Aunt, Nephew, Grandparent, Grandchild, Uncle, Niece,
        - Either select from existing with autocomplete or create a new person
        - If the person exists, use the existing person
        - If the person doesn't exist, create a new person
        - create the relationship in the person_relationships table with the relationship type */        
    }
    public function addParents() {
        apachelog("Adding parents");
        /*
        - Add Parents, either single parent or two parents
        - if you need to create a person
            - create a person in the individuals table. 
            - Make sure that you have a first name, last_name, and specify an updated_at, created_at and alive (by default alive)
        - you should select a first parent with autocomplete or create a new parent
        - if the first new parent exists and is selected
            - you should be able to either select from their existing families like now
            - you should also be able to create a second parent for that person and the form
        - if the first new parent doesn't exist
            - either allow to create a second parent too and show the new person form
            - or if there is no second parent allow to create a single parent relationship
        Array
        (
            [parent1_type] => new
            [parent1_id] => 
            [tree_id] => 9
            [parent1_first_name] => Bob
            [parent1_last_name] => Gargamel
            [parent1_birth_date] => 
            [parent1_gender] => M
            [second_parent_option] => new
            [parent2_first_name] => Sylvia
            [parent2_last_name] => Bloom
            [parent2_birth_date] => 
            [parent2_gender] => M
            [member_id] => 1851
            [member_gender] => M
            [type] => parent
        )

        */
        $alive1 = $this->data['parent1_alive']??1;
        $alive2 = $this->data['parent2_alive']??1;
        $childId = $this->data['member_id'];
        if($this->data['parent1_type'] == 'new'){
            $parent1 = [
                'firstName' => $this->data['parent1_first_name'] ?? null,
                'lastName' => $this->data['parent1_last_name'] ?? null,
                'treeId' => $this->data['tree_id'] ?? null,
                'birth_date' => $this->data['parent1_birth_date'] ?? null,
                'gender' => $this->data['parent1_gender'] ?? null,
                'alive' => $alive1,
            ];
            $parent1Id = $this->family->addIndividual($parent1);
            $parent1Gender = $this->$parent1['gender'];

        } else {
            $parent1Id = $this->data['parent1_id'];
            $parent1Gender=null;
        }
        if($this->data['second_parent_option'] == 'new'){
            $parent2 = [
                'firstName' => $this->data['parent2_first_name'] ?? null,
                'lastName' => $this->data['parent2_last_name'] ?? null,
                'treeId' => $this->data['tree_id'] ?? null,
                'birth_date' => $this->data['parent2_birth_date'] ?? null,
                'gender' => $this->data['parent1_gender'] ?? null,
                'alive' => $alive1,
            ];
            $parent2Id = $this->family->addIndividual($parent2);
            $parent2Gender = $parent2['gender'];
        } else {
            $parent2Id = $this->data['parent2_id'];
            $parent2Gender=null;
        }
        if($parent1Gender == 'F' || $parent2Gender == 'M'){
            $wife = $parent1Id;
            $husband = $parent2Id;
        } elseif($parent2Gender == 'M'|| $parent1Gender == 'F'){ 
            $husband = $parent1Id;
            $wife = $parent2Id;
        } else {
            $husband = $parent1Id;
            $wife = $parent2Id; 
        }
        $treeId = $this->data['tree_id'];
 
        $familyData = [
            'tree_id' => $treeId,
            'husband_id' => $husband,
            'wife_id' => $wife,
            'marriage_date' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $familyId = $this->family->createFamily($familyData);
        $this->family->addChildToFamily($familyId, $childId, $treeId);

    }


}

/*

although right now we are focusing on the frontend and how it submits to index.php, I am provided more logic
however, right now, we are focusing on the frontend and how it submits to index.php
    - for now, all should be submitted to index.php to add_relationship method
        - the submission should include the relationship type and the data for the relationship
        - the relationship type should be one of the following:
            - spouse which is to be stored in family table
            - child which is to be stored in family_children table
            - parent where parents are to be stored in family table with the child as a child
            - other where the relationship is stored in person_relationships
            - right now, the form should submit to index.php with the relationship type and the data for the relationship and it should be outputted in error_log
-On level one, you should be able to select what type of relationship, similar to what is happening now
    - Add Spouse which creates a new family with a husband and wife
    - Add Child which creates a new child relationship with a family of the current individual and a spouse if selected
    - Add Parents which creates a new family in families table with parents which are provided in the form and the current individual as a child
    - Add Other Relationship which creates a new relationship with the current individual and another individual in person_relationships
- When that first radio is selected, a subform should be created for the relationship type, similar to what is happening now

    - Add Parents
        - you should select a first parent with autocomplete or create a new parent
        - if the first new parent exists and is selected
            - you should be able to either select from their existing families like now
            - you should also be able to create a second parent for that person and the form
        - if the first new parent doesn't exist
            - either allow to create a second parent too and show the new person form
            - or if there is no second parent allow to create a single parent relationship
    - Add Child
        - with [listbox of existing spouses]
        - an option for no spouse should be there should be an option to create a family with no spouse

- if you need to create a person
    create a person in the individuals table. 
    Make sure that you have a first name, last_name, and specify an updated_at, created_at and alive (by default alive)
- if you need are creating a spouse, 
    - the family is created in the families table. 
    - If one of them is male, make it the husband
    - If it is a single parent relationship, make the parent the father if it is a male, the mother if it is female
    - if you are creating a child relationship, 
        - create a child-family link in the family_children table
    - if you are creating a parent relationship
        - if the parents don't exist, create them in the individuals table first
        - if the parent is a single parent
            - create a new family in the families table with the parent as the father if it is a male, wife if the Parent is female
        - if it is a two-parent relationship, 
            - create a new family in the families table
            - add the current member as a child in the family_children table
- Everything whether relationships, families or individuals should be created with created_at, updated_at
- it should be a transaction so that if one thing fails, everything fails (creating people and relationships)
- assign husband/wife based on gender
- Most of the form used to work but it is not working now, likely because database fields have changed and attempts to fix things have messed up the relationships
- Make sure you follow the whole chain as the relationships are created without reloading the page asynchronously
- use the gender field in individuals M or F for gender
Methods needed (they might exist in the model already with a different name):
addMember() - creates new person, make sure to add created_at, updated_at, and alive
createFamily() - creates new family, assigns husband/wife based on gender
addChildToFamily() - links child to existing family
getExistingFamily() - checks if family exists for spouse pair
createFamilyWithChild() - creates family and adds child link
Tables used:
individuals - for new person creation
families - for family units
family_children - for child relationships
person_relationships - for relationships between individuals that are not parent-child
relationship_types - for the types of relationships with code and description for non parent-child/family relationships




Array
(
    [type] => spouse
    [data] => Array
        (
            [0] => Array
                (
                    [name] => spouse_type
                    [value] => new
                )

            [1] => Array
                (
                    [name] => spouse_id
                    [value] => 
                )

            [2] => Array
                (
                    [name] => spouse_first_name
                    [value] => Bimbam
                )

            [3] => Array
                (
                    [name] => spouse_last_name
                    [value] => tes
                )

            [4] => Array
                (
                    [name] => spouse_birth_date
                    [value] => 
                )

            [5] => Array
                (
                    [name] => spouse_gender
                    [value] => F
                )

            [6] => Array
                (
                    [name] => marriage_date
                    [value] => 
                )

        )

)



*/