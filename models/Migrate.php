<?php
$basedir = dirname(__DIR__) ;
require $basedir . '/init.php'; // Ensure you include the autoload file

class RelationshipMigrator {
    private $pdo;
    private $parents = [];
    private $families = [];
    private $relations = [];
    private $people = [];
    private $children = [];
    private $spouses = [];
    private $relationships = [];
    private $rel_map = [];
    private $appSource= "GEDCOM";
    private $appName = "Genie";
    private $appCorp = "Opensitez";
    private $appVersion="5.5";

    public function __construct($config) {
        $this->pdo = $config['connection'];
    }
    function getName($id) {
        if(isset($this->people[$id])) {
            $person = $this->people[$id];
            $name = "{$person['first_name']} {$person['last_name']}";

        } else {
            $name = false;
        }

        return $name;

    }
    function exportGedcom() {
        $gedcom = "0 HEAD\n";
        $gedcom .= "1 SOUR $this->appSource\n";
        $gedcom .= "2 VERS $this->appVersion\n";
        $gedcom .= "2 NAME {$this->appName}\n";
        $gedcom .= "2 CORP {$this->appCorp}\n";
        $gedcom .= "0 TRLR\n";
        
        foreach ($this->people as $id=>$individual) {
            $gedcom .= "0 @" . $individual['id'] . " INDI\n";
            $gedcom .= "1 NAME " . $individual['first_name'] . " /" . $individual['last_name'] . "/\n";
            foreach($this->spouses[$id]??[] as $spouseid=>$familyid) {
                //if ($individual['spouse']??false) {
                    $gedcom .= "1 FAMS @" . $familyid . " spouse $spouseid\n";
                //}
    
            }
    
            if ($individual['date_of_death']) {
                $gedcom .= "1 DEAT\n";
                $gedcom .= "2 DATE " . $individual['date_of_death'] . "\n";
                $gedcom .= "2 PLAC " . $individual['place_of_death'] . "\n";
            }
    
            // // Check if both parents are defined
            // if ($individual['parents'][0] && $individual['parents'][1]) {
            //     // Two-parent family
            //     $gedcom .= "1 FAMC @" . $individual['parents'][0]['familyId'] . "\n";
            //     $gedcom .= "1 FAMC @" . $individual['parents'][1]['familyId'] . "\n";
            //     // ... process both parents and their ancestors
            // } elseif ($individual['parents'][0]) {
            //     // Single-parent family (parent 1)
            //     $gedcom .= "1 FAMC @" . $individual['parents'][0]['familyId'] . "\n";
            //     // ... process parent 1 and their ancestors
            // } elseif ($individual['parents'][1]) {
            //     // Single-parent family (parent 2)
            //     $gedcom .= "1 FAMC @" . $individual['parents'][1]['familyId'] . "\n";
            //     // ... process parent 2 and their ancestors
            // }
    
            // // ... other individual details
        }
    
        // ... process families and their relationships
        print $gedcom;  
        return $gedcom;
    }
    public function showPeople() {
        foreach($this->people as $pid=>$person) {
            print "$pid - {$person['first_name']} {$person['last_name']}\n";
            if($this->spouses[$pid]) {
                foreach($this->relationships[$pid] as $rid=>$relation) {
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
    public function fetchChildren($family_tree_id) {
        $sql = "
            SELECT r.person_id1 AS child, r.person_id2 AS parent, r.id AS relation_id, t.code
            FROM person_relationship r
            LEFT JOIN relationship_type t ON r.relationship_type_id = t.id
            WHERE t.code IN ('FATH', 'MOTH','CHLD') 
            AND r.family_tree_id = :family_tree_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':family_tree_id', $family_tree_id, PDO::PARAM_INT);
        $stmt->execute();

        // Store the results and fetch people details
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $child=$this->fetchPersonIfNeeded($row['child']);
            $parent=$this->fetchPersonIfNeeded($row['parent']);
            if(!isset($this->parents[$row['child']])) {
                $this->parents[$row['child']]=[];
            }
            $this->parents[$row['child']][$row['parent']]=$row['parent'];

        }        
        foreach ($this->parents as $child=>$curparents) {

            $name1=$this->getName($child);
            $parentnames=[];
            $numparents=count($curparents); 
            switch($numparents) {
                case 0:
                    print "+++$name1 : No Parents\n";
                    break;
                case 1:
                    if($curparents) {
                        $parentid1=$curparents[array_key_first($curparents)];

                    } else {
                        $parentid1=0;
                    }
                    $spouses = $this->spouses[$parentid1]??[];
                    //print_r($spouses);
                    //print  "+++$name1 : 1 Parent $parentid1\n";
                    switch(count($spouses)) {
                        case 0:
                            $name2 = $this->getName($parentid1);
                            //print "$name1 child of $name2 (no spouse)\n";
                            break;
                        case 1:
                            $name2 = $this->getName($parentid1);
                            $parentid2=array_key_first($spouses);
                            $this->parents[$child][$parentid2]=$parentid2;
                            //print_r($this->parents[$child]);
                            $parentnames = [
                                $parentnames[]=$this->getName($parentid1),
                                $parentnames[]=$this->getName($parentid2)
                            ];                          
                            $name2=implode("," , $parentnames);
                            //print "$name1 $parentid1 child of $name2 $parentid2 (added 1 parent)\n";
                            break;
                        default:
                            $name2 = $this->getName($parentid1);
                            //print "+++$name1 child of $name2 (many parents)\n";

                    }
                    break;
                case 2: 
                    foreach($curparents as $parent) {
                        $parentnames[]=$this->getName($parent) . " $parent";
                    }
                    $name2=implode("," , $parentnames);
                    //print "$name1 $child child of $name2\n";
        
                    break;
            }

        }
    }
    public function migrate($family_tree_id) {
        // Start a transaction
        $this->pdo->beginTransaction();
        
        try {
            // Fetch the family data
            $this->fetchFamilies($family_tree_id);
            $this->fetchChildren($family_tree_id);
            $this->exportGedcom();
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
                $rel_string = min($row['husb'],$row['wife']) . "-" . max($row['husb'],$row['wife']); 
                $this->rel_map[$rel_string]=$row['relation_id'];
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
                $this->spouses[$husb][$wife] = $row['relation_id'];
                $this->spouses[$wife][$husb] = $row['relation_id'];
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
            print "need check already there is spouse";exit;
            $this->spouses[] = [
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
