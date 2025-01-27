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
        /*
    - for now, every relationship is submitted to index.php to which call this add_relationship method
        - the submission should include the relationship type and the data for the relationship
        - the relationship type should be one of the following:
            - spouse which is to be stored in family table
            - child which is to be stored in family_children table
            - parent where parents are to be stored in family table with the child as a child
            - other where the relationship is stored in person_relationships
        */
        $data = $_POST['relationship']['data']??[];
        $assocData = $this->convertToAssociativeArray($data);
        $assocData['tree_id'] = $_POST['tree_id']??null;
        $assocData['type'] = $_POST['relationship']['type']??null;
        $this->data = $assocData;
        apachelog($assocData);

        //$reltype = $_POST['relationship']['type']??$_GET['relationship']['data']['type']??'';
        $reltype = $assocData['type']??'';
        switch($assocData['type']) {
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
                error_log("Invalid relationship type: $reltype");
        }
    }

    public function addSpouse() {
        apachelog("Adding a spouse");
        
        //$formData = $this->data ?? [];
    
        $spouseData = $this->data;
         // Convert array of array to associative array
        // foreach($formData as $item) {
        //     $spouseData[$item['name']] = $item['value'];
        // }
        
        $spouseType = $spouseData['spouse_type'] ?? null;
        $spouseId = $spouseData['spouse_id'] ?? null;
        $treeId = $_POST['tree_id'] ?? null; // we need this from post
        $person1 = $_POST['person1'] ?? null; // we need this from the post

        apachelog("Spouse Type: $spouseType");
    
        if ($spouseType === 'new') {
            
            apachelog("Creating a new spouse");
            $newSpouseData = [
                'first_name' => $spouseData['spouse_first_name'] ?? null,
                'last_name' => $spouseData['spouse_last_name'] ?? null,
                'birth_date' => $spouseData['spouse_birth_date'] ?? null,
                'gender' => $spouseData['spouse_gender'] ?? null,
            ];
            
             apachelog("New Spouse Data:");
             apachelog($newSpouseData);
            
            $marriageDate = $spouseData['marriage_date']??null;
           
           
            $husband_id = null;
            $wife_id = null;
            // Determine husband and wife based on gender
            if($newSpouseData['gender'] == 'F'){
               $husband_id = $person1;
               $wife_id = 'new_person';
             } else {
               $husband_id = 'new_person';
               $wife_id = $person1;
            }
            
             $familyData = [
                'tree_id' => $treeId,
                'husband_id' => $husband_id,
                'wife_id' => $wife_id,
                'marriage_date'=>$marriageDate
            ];
            apachelog("Family Data:");
            apachelog($familyData);
            
        } else {
            if($spouseId){
                 apachelog("Adding existing spouse with id: $spouseId");
                 $marriageDate = $spouseData['marriage_date']??null;
                    
                 $familyData = [
                    'tree_id' => $treeId,
                    'husband_id' => $person1,
                    'wife_id' => $spouseId,
                     'marriage_date'=>$marriageDate
                 ];
                 
                 apachelog("Family Data:");
                 apachelog($familyData);
                 
            } else {
                apachelog("Error: No spouse id provided for an existing spouse");
            }
        }
    }
    public function addChild() {
        
        apachelog("Adding a child");
        //apachelog($_POST);
        $child = $_POST['child']??[];
        //$this->member->addChild($child);
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
        */
        //apachelog($_POST);
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