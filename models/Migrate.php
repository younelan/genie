<?php
$basedir = dirname(__DIR__) ;
require $basedir . '/init.php'; // Ensure you include the autoload file

class RelationshipMigrator {
    private $pdo;
    private $families = [];
    private $relations = [];
    private $people = [];
    private $relationships = [];

    public function __construct($config) {
        $this->pdo = $config['connection'];
    }
    public function showPeople() {
        foreach($this->people as $pid=>$person) {
            print "$pid - {$person['first_name']} {$person['last_name']}\n";
            if($this->relationships[$pid]) {
                foreach($this->relationships[$pid] as $rid=>$relation) {
                    print_r($relation);
                    exit;
                    $relid1 = $relation['person_id1']??0;
                    $relid2 = $relation['person_id2']??0;

                    if($relid1 && $this->people[$relid1]) {
                        $rel1 = $this->people[$relid1]??false;
                        $name1 = "{$rel1['first_name']} {$rel1['last_name']}";

                    } else {
                        $rel1=false;
                        $name1="((undefined $relid1))";
                    }
                    if($relid2 && $this->people[$relid2]) {
                        $rel2 = $this->people[$relid2]??false;
                        $name2 = "{$rel2['first_name']} {$rel2['last_name']}";
 
                    } else {
                        $rel2 = false;
                        $name2 = " ((undefined $relid2)) ";
                    }

                    // if($rel1) {
                    //     $name1 = "{$rel1['first_name']} {$rel1['last_name']}";
                    // } else {
                    //     $name = "((undefined))";
                    // }
                    // if($rel2) {
                    //     $name2 = "{$rel2['first_name']} {$rel2['last_name']}";
                    // } else {
                    //     $name2 = "((undefined))";
                    // }

                    print "    - $rid - {$name1} {$name2}\n";

                }

            }
        } 
    }
    public function migrate($family_tree_id) {
        // Start a transaction
        $this->pdo->beginTransaction();
        
        try {
            // Fetch the family data
            $this->fetchFamilies($family_tree_id);
            $this->showPeople();
            print "fetched:\n";
            //print_r($this->relationships);
            exit;
            // Insert the families into the database
            $this->insertFamilies();


            // Commit the transaction
            $this->pdo->commit();
            echo "Migration for family tree ID $family_tree_id completed successfully.";
        } catch (Exception $e) {
            // Rollback the transaction if something went wrong
            $this->pdo->rollBack();
            echo "Migration failed: " . $e->getMessage();
        }
    }
    private function fetchPersonIfNeeded($person_id) {
        // Check if person is already fetched
        if (isset($this->people[$person_id])) {
            return $this->people[$person_id];
        }

        // Fetch the person from the database
        $person = $this->fetchPersonById($person_id);
        
        if ($person) {
            $this->people[intval($person_id)] = $person; // Store the person record
            return $person; // Return the fetched person
        }

        return null; // Return null if not found
    }

    private function fetchPersonById($person_id) {
        try {
            $sql = "
                SELECT *
                FROM person
                WHERE id = :person_id
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':person_id', $person_id, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch the person details
            $person = $stmt->fetch(PDO::FETCH_ASSOC);

            // Return the person details or null if not found
            return $person !== false ? $person : null;
        } catch (Exception $e) {
            // Handle error (optional: log the error)
            return null; // Return null on error
        }
    }
    private function fetchFamilies($family_tree_id) {
        // SQL to select husbands and wives
        $sql = "
            SELECT r.person_id1 AS husb, r.person_id2 AS wife, r.id AS relation_id, t.code
            FROM person_relationship r
            LEFT JOIN relationship_type t ON r.relationship_type_id = t.id
            WHERE t.code IN ('HUSB', 'WIFE','DIV') 
            AND r.family_tree_id = :family_tree_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':family_tree_id', $family_tree_id, PDO::PARAM_INT);
        $stmt->execute();

        // Store the results and fetch people details
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Fetch people if not already done
            $id1=$this->fetchPersonIfNeeded($row['husb']);
            $id2=$this->fetchPersonIfNeeded($row['wife']);
            if($id2['gender_id']==1) {
                $husb=intval($row['wife']);
                $wife=intval($row['husb']);
            } else {
                $husb=$row['husb'];
                $wife=$row['wife'];
            }

            // Store the family data with full person records
            //if ($husb && $wife) {
                $this->families[$row['relation_id']] = [
                    'husb' => $husb,
                    'husb_name'=>$this->people[$husb]['first_name'] . " " . $this->people[$husb]['last_name'] ,
                    'wife_name'=>$this->people[$wife]['first_name'] . " " . $this->people[$wife]['last_name'] ,
                    'wife' => $wife,
                    'relation_id' => $row['relation_id'],
                    'code' => $row['code'],
                ];
                // var_dump($husb);
                // var_dump($wife);
                // var_dump($this->families);
                // var_dump($this->people);
                // Store the relation ID for both husband and wife
                $this->relationships[$husb][] = $row['relation_id'];
                $this->relationships[$wife][] = $row['relation_id'];
            //}
            // exit;
        }
    }


    private function insertFamilies() {
        foreach ($this->families as $family) {
            $sql = "
                INSERT INTO families (husband_id, wife_id, created_at, updated_at)
                VALUES (:husb, :wife, NOW(), NOW());
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':husb', $family['husb'], PDO::PARAM_INT);
            $stmt->bindParam(':wife', $family['wife'], PDO::PARAM_INT);
            $stmt->execute();
            
            // Store the family relationship
            $this->relationships[] = [
                'husb' => $family['husb'],
                'wife' => $family['wife'],
                'relation_id' => $family['relation_id'],
            ];
        }
    }
}

// Create an instance of the GEDCOMImporter
$migrator = new RelationshipMigrator($config);


// Specify the family tree ID you want to import into
$familyTreeId = 1; // Example ID

// Import the GEDCOM content
$migrator->migrate($familyTreeId);
