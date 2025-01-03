<?php
$basedir = dirname(__DIR__) ;
require $basedir . '/init.php'; // Ensure you include the autoload file

class GEDCOMImporter
{
    private $pdo;
    private $peopleTable,$treeTable,$synonymTable;
    private $tagsTable;
    private $relationshipsTable;
    private $relationshipTypesTable;
    public function __construct(private $config)
    {
        //$this->config = $config;
        $this->pdo = $config['connection'];
        $this->peopleTable = $config['tables']['person']??'person';
        $this->tagsTable = $config['tables']['people_tags']??'tags';
        $this->treeTable = $config['tables']['tree']??'family_tree';
        $this->relationshipsTable = $config['tables']['relation']??'person_relationship';
        $this->relationshipTypesTable = $config['tables']['relationship_type']??'relationship_types';
        $this->synonymTable = $config['tables']['synonyms']??'synonyms';
        
    }

    public function import($filePath, $familyTreeId)
    {
        $parser = new \Gedcom\Parser();
        $gedcom = $parser->parse($filePath);

        $people = [];
        
        foreach ($gedcom->getIndi() as $individual) {
            // $i = $individual->get
            $names = $individual->getName();
            if (!empty($names)) {
                $name = reset($names); // Get the first name object from the array
                echo $individual->getId() . ': ' . $name->getSurn() . ', ' . $name->getGivn() . PHP_EOL;
                $people[$individual->getId()] = $individual; 
            }

            //$this->saveIndividual($individual, $familyTreeId);

        }
       
        $families = $gedcom->getFam();
        //print_r(array_keys($people));
        foreach ($families as $family) {
            echo "\n\n+++++\nFamily ID: " . $family->getId() . "\n";
            
            // Get spouses and children
            $husband = $family->getHusb();
            $wife = $family->getWife();
            $children = $family->getChil();
            // print "\n----\n";
            // var_dump($husband);
            // print "\n----\n";

            if($wife) {
                $wife_names = $people[$wife]->getName()[0]??"";
                $wife_full=$wife_names->getSurn() . ', ' . $wife_names->getGivn() ;
            } else {
                $wife_full = " -NA- ";
            }
            if($husband) {
                $husband_names = $people[$husband]->getName()[0]??"";
                $husb_full =$husband_names->getSurn() . ', ' . $husband_names->getGivn();    

            } else {
                $husb_full = " -NA- ";
            }
            echo "  Husband $husband: " . ($husband ?  $husb_full : 'N/A') . " ";
            echo " --  Wife $wife: " . ($wife ? $wife_full : 'N/A') . "\n";
        
            foreach ($children as $child) {
                $child_names = $people[$child]->getName()[0]??"";
                if($child) {
                    $child_full =$child_names->getSurn() . ', ' . $child_names->getGivn();
                    echo "    ---Child: " . $child_full  . " $child\n";
                } else {
                    echo "    ---Child: --none--"  . " $child\n";

                }
            }
            $events = $family->getAllEven();
            foreach ($events as $event) {
                if(!is_array($event)) {
                    $event = [$event];
                }
                foreach ($event as $event_row) {
                    echo "  |_ Family Event: " . $event_row->getType() . " on " . $event_row->getDate() . "\n";

                }
            }        
        }

        // foreach ($gedcom->getFam() as $family) {
        //     $events = $family->getAllEven();
        //     if(!empty($events)) {
        //         $name = reset($events); // Get the first name object from the array
        //         echo $events->getId() . ': ' . $name->getSurn() . ', ' . $name->getGivn() . PHP_EOL;

        //     } else {
        //        var_dump($family);
        //     }
        //     //$this->saveFamilyRelationships($family, $familyTreeId);
        // }

    }

    private function saveIndividual($individual, $familyTreeId)
    {
        $id = $individual->getId();
        $name = $individual->getName();
        $birthEvent = $individual->getEvent('BIRT');
        $deathEvent = $individual->getEvent('DEAT');
        $placeOfBirth = $birthEvent ? $birthEvent->getPlace() : '';
        $dateOfBirth = $birthEvent ? $birthEvent->getDate() : null;
        $dateOfDeath = $deathEvent ? $deathEvent->getDate() : null;

        // Prepare and execute the SQL statement
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->peopleTable} 
            (id, tree_id, first_name, last_name, birth_date, birth_place, death_date, created_at, updated_at, alive)
            VALUES (:id, :tree_id, :first_name, :last_name, :birth_date, :birth_place, :death_date, NOW(), NOW(), TRUE)
            ON DUPLICATE KEY UPDATE 
                first_name = VALUES(first_name),
                last_name = VALUES(last_name),
                birth_date = VALUES(birth_date),
                birth_place = VALUES(birth_place),
                death_date = VALUES(death_date)
        ");

        // Split the name into parts
        $nameParts = explode(' ', trim($name));
        $firstName = array_shift($nameParts);
        $lastName = implode(' ', $nameParts);

        $stmt->execute([
            'id' => $id,
            'tree_id' => $familyTreeId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birth_date' => $dateOfBirth,
            'birth_place' => $placeOfBirth,
            'death_date' => $dateOfDeath,
        ]);

        // Save tags associated with the individual
        $this->saveTags($individual, $id);
    }

    private function saveTags($individual, $personId)
    {
        foreach ($individual->getTags() as $tag) {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->tagsTable} (tag, person_id, tree_id) 
                VALUES (:tag, :person_id, :tree_id)
            ");
            $stmt->execute([
                'tag' => $tag,
                'person_id' => $personId,
                'tree_id' => $individual->getFamilyTreeId(), // Assuming individual has a family tree ID
            ]);
        }
    }

    private function saveFamilyRelationships($family, $familyTreeId)
    {
        $husbandId = $family->getHusband() ? $family->getHusband()->getId() : null;
        $wifeId = $family->getWife() ? $family->getWife()->getId() : null;
        $children = $family->getChildren();

        if ($husbandId && $wifeId) {
            $this->saveRelationship($husbandId, $wifeId, $familyTreeId, 1); // 1 for 'married'
        }

        // Handle children relationships
        foreach ($children as $child) {
            $this->saveChildRelationship($husbandId, $wifeId, $child->getId(), $familyTreeId);
        }
    }

    private function saveRelationship($personId1, $personId2, $familyTreeId, $relationshipTypeId)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->relationshipsTable} (tree_id, person_id1, person_id2, relationship_type_id)
            VALUES (:tree_id, :person_id1, :person_id2, :relationship_type_id)
            ON DUPLICATE KEY UPDATE person_id1 = VALUES(person_id1), person_id2 = VALUES(person_id2)
        ");

        $stmt->execute([
            'tree_id' => $familyTreeId,
            'person_id1' => $personId1,
            'person_id2' => $personId2,
            'relationship_type_id' => $relationshipTypeId,
        ]);
    }

    private function saveChildRelationship($husbandId, $wifeId, $childId, $familyTreeId)
    {
        // Save parent-child relationships for both parents
        $this->saveRelationship($husbandId, $childId, $familyTreeId, 2); // 2 for 'parent'
        $this->saveRelationship($wifeId, $childId, $familyTreeId, 2); // 2 for 'parent'
    }
}


// Create an instance of the GEDCOMImporter
$importer = new GEDCOMImporter($config);

// Specify the path to the GEDCOM file
$filePath = 'british.ged';

// Specify the family tree ID you want to import into
$familyTreeId = 1; // Example ID

// Import the GEDCOM content
$importer->import($filePath, $familyTreeId);
