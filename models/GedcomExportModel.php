<?php
class GedcomExportModel extends AppModel
{
    private $db;
    private $memberModel;

    public function __construct($config)
    {
        $this->db = $config['connection'];
        $this->memberModel = new MemberModel($config);
    }

    public function exportGedcom($treeId)
    {
        $gedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n2 FORM LINEAGE-LINKED\n1 CHAR UTF-8\n";
        
        // Export individuals
        $query = "SELECT * FROM person WHERE family_tree_id = :treeId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['treeId' => $treeId]);
        $individuals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($individuals as $individual) {
            $gedcom .= "0 @I{$individual['id']}@ INDI\n";
            $gedcom .= "1 NAME {$individual['first_name']} /{$individual['last_name']}/\n";
            $gedcom .= "1 SEX {$this->mapGender($individual['gender_id'])}\n";
            
            if ($individual['date_of_birth']) {
                $gedcom .= "1 BIRT\n2 DATE {$individual['date_of_birth']}\n";
                if ($individual['place_of_birth']) {
                    $gedcom .= "2 PLAC {$individual['place_of_birth']}\n";
                }
            }
            
            if ($individual['date_of_death']) {
                $gedcom .= "1 DEAT\n2 DATE {$individual['date_of_death']}\n";
                if ($individual['place_of_death']) {
                    $gedcom .= "2 PLAC {$individual['place_of_death']}\n";
                }
            }
        }
        
        // Export families
        $query = "SELECT * FROM person_relationship WHERE family_tree_id = :treeId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['treeId' => $treeId]);
        $relationships = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $families = [];
        foreach ($relationships as $relationship) {
            if ($relationship['relationship_type_id'] == 5) { // Assuming 5 is the ID for marriage relationship
                $familyId = 'F' . $relationship['id'];
                $gedcom .= "0 @{$familyId}@ FAM\n";
                $gedcom .= "1 HUSB @I{$relationship['person_id1']}@\n";
                $gedcom .= "1 WIFE @I{$relationship['person_id2']}@\n";
                $families[$familyId] = ['husband' => $relationship['person_id1'], 'wife' => $relationship['person_id2']];
            } elseif ($relationship['relationship_type_id'] == 2) { // Assuming 2 is the ID for parent relationship
                foreach ($families as $familyId => $family) {
                    if ($family['husband'] == $relationship['person_id1'] || $family['wife'] == $relationship['person_id1']) {
                        $gedcom .= "1 CHIL @I{$relationship['person_id2']}@\n";
                        break;
                    }
                }
            }
        }
        
        $gedcom .= "0 TRLR\n";
        
        return $gedcom;
    }
    
    private function mapGender($genderId)
    {
        switch ($genderId) {
            case 1: // Assuming 1 is the ID for male in your gender table
                return 'M';
            case 2: // Assuming 2 is the ID for female in your gender table
                return 'F';
            default:
                return 'U';
        }
    }
}
?>