<?php
class GedcomExportModel extends AppModel
{
    private $config;
    private $db;
    private $memberModel;
    private $person_table;
    private $relation_table;
    private $relation_type_table;
    private $tree_table;

    public function __construct($config)
    {
        $this->config = $config;
        $this->db = $config['connection'];
        $this->memberModel = new MemberModel($config);
        
        // Use table names from config
        $this->person_table = $config['tables']['person'] ?? 'person';
        $this->relation_table = $config['tables']['relation'] ?? 'person_relationship';
        $this->relation_type_table = $config['tables']['relation_type'] ?? 'relationship_type';
        $this->tree_table = $config['tables']['tree'] ?? 'family_tree';
    }

    public function exportGedcom($treeId)
    {
        $gedcom = $this->generateHeader();
        
        // Export individuals
        $individuals = $this->getIndividuals($treeId);
        foreach ($individuals as $individual) {
            $gedcom .= $this->generateIndividualRecord($individual);
        }
        
        // Export relationships
        $relationships = $this->getRelationships($treeId);
        $families = $this->generateFamilies($relationships);
        
        foreach ($families as $familyId => $family) {
            $gedcom .= $this->generateFamilyRecord($familyId, $family);
        }
        
        $gedcom .= "0 TRLR\n";
        
        return $gedcom;
    }

    private function generateHeader()
    {
        return "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n2 FORM LINEAGE-LINKED\n1 CHAR UTF-8\n";
    }

    private function getIndividuals($treeId)
    {
        $query = "SELECT * FROM {$this->person_table} WHERE family_tree_id = :treeId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['treeId' => $treeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function generateIndividualRecord($individual)
    {
        $record = "0 @I{$individual['id']}@ INDI\n";
        $record .= "1 NAME {$individual['first_name']} /{$individual['last_name']}/\n";
        $record .= "1 SEX {$this->mapGender($individual['gender_id'])}\n";
        
        if ($individual['date_of_birth']) {
            $record .= "1 BIRT\n2 DATE {$individual['date_of_birth']}\n";
            if ($individual['place_of_birth']) {
                $record .= "2 PLAC {$individual['place_of_birth']}\n";
            }
        }
        
        if ($individual['date_of_death']) {
            $record .= "1 DEAT\n2 DATE {$individual['date_of_death']}\n";
            if ($individual['place_of_death']) {
                $record .= "2 PLAC {$individual['place_of_death']}\n";
            }
        }

        return $record;
    }

    private function getRelationships($treeId)
    {
        $query = "SELECT pr.*, rt.description as relationship_type 
                  FROM {$this->relation_table} pr
                  JOIN {$this->relation_type_table} rt ON pr.relationship_type_id = rt.id
                  WHERE pr.family_tree_id = :treeId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['treeId' => $treeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function generateFamilies($relationships)
    {
        $families = [];
        $familyCounter = 1;

        foreach ($relationships as $relationship) {
            switch ($relationship['relationship_type']) {
                case 'Marriage':
                case 'Partnership':
                    $familyId = 'F' . $familyCounter++;
                    $families[$familyId] = [
                        'partners' => [$relationship['person_id1'], $relationship['person_id2']],
                        'children' => [],
                        'type' => $relationship['relationship_type']
                    ];
                    break;
                case 'Parent':
                    // Find existing family or create new one
                    $familyId = $this->findOrCreateFamily($families, $relationship['person_id1']);
                    $families[$familyId]['children'][] = $relationship['person_id2'];
                    break;
                // Handle other relationship types as needed
            }
        }

        return $families;
    }

    private function findOrCreateFamily(&$families, $parentId)
    {
        foreach ($families as $familyId => $family) {
            if (in_array($parentId, $family['partners'])) {
                return $familyId;
            }
        }
        // If no existing family found, create a new one
        $familyId = 'F' . (count($families) + 1);
        $families[$familyId] = [
            'partners' => [$parentId],
            'children' => [],
            'type' => 'Single Parent'
        ];
        return $familyId;
    }

    private function generateFamilyRecord($familyId, $family)
    {
        $record = "0 @{$familyId}@ FAM\n";
        
        foreach ($family['partners'] as $index => $partnerId) {
            $tag = ($family['type'] == 'Marriage') ? ($index == 0 ? 'HUSB' : 'WIFE') : 'ASSO';
            $record .= "1 {$tag} @I{$partnerId}@\n";
        }
        
        foreach ($family['children'] as $childId) {
            $record .= "1 CHIL @I{$childId}@\n";
        }
        
        if ($family['type'] == 'Partnership') {
            $record .= "1 RELA Partnership\n";
        }
        
        return $record;
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
