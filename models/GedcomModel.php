<?php

class GedcomModel extends AppModel {
    private $treeModel;
    private $individualModel;
    private $familyModel;
    private $db;
    private $appSource = "GEDCOM";
    private $appName = "Genie";
    private $appCorp = "Opensitez";
    private $appVersion = "5.5";
    private $tree_table = "trees";
    private $individual_table = "individuals";
    private $family_table = "families";
    private $child_table = "family_children";
    private $appEncoding = "UTF-8";
    private $warnings = [];

    public function __construct($config) {
        $this->db = $config['connection'];
        $this->treeModel = new TreeModel($config);
        $this->individualModel = new MemberModel($config);
        $this->familyModel = new FamilyModel($config);
    }

    private function sanitizeGedcomString($string) {
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        $string = str_replace(['@', '/'], ['', ''], $string);
        return trim($string);
    }

    private function convertToGedcomDate($dateString) {
        try {
            $date = new DateTime($dateString);
            return $date->format('d M Y');
        } catch (Exception $e) {
            return 'ABT ' . $dateString;
        }
    }

    function exportGedcom($family_tree_id) {
        $treeData = $this->treeModel->getFamilies($family_tree_id);
        
        if (!$treeData['success']) {
            throw new Exception('Failed to fetch family tree data');
        }

        $individuals = $treeData['data']['individuals'];
        $families = $treeData['data']['families'];
        $children = $treeData['data']['children'];

        // Preprocess family relationships
        $familyRelations = []; // Store FAMC and FAMS for each individual
        foreach ($individuals as $person) {
            $personId = $person['id'];
            $familyRelations[$personId] = [
                'famc' => [], // Family where person is a child
                'fams' => []  // Families where person is a spouse
            ];
        }

        // Process child relationships (FAMC)
        foreach ($children as $child) {
            $childId = $child['child_id'];
            $familyId = $child['family_id'];
            if (isset($familyRelations[$childId])) {
                $familyRelations[$childId]['famc'][] = $familyId;
            }
        }

        // Process spouse relationships (FAMS)
        foreach ($families as $family) {
            if (!empty($family['husband_id'])) {
                $familyRelations[$family['husband_id']]['fams'][] = $family['id'];
            }
            if (!empty($family['wife_id'])) {
                $familyRelations[$family['wife_id']]['fams'][] = $family['id'];
            }
        }

        // Build GEDCOM header
        $gedcom = "0 HEAD\n";
        $gedcom .= "1 SOUR $this->appSource\n";
        $gedcom .= "2 NAME {$this->appName}\n";
        $gedcom .= "2 CORP {$this->appCorp}\n";
        $gedcom .= "1 GEDC\n";
        $gedcom .= "2 VERS $this->appVersion\n";
        $gedcom .= "2 FORM LINEAGE-LINKED\n";
        $gedcom .= "1 CHAR $this->appEncoding\n";
        $gedcom .= "1 SUBM @SUB1@\n";
        $gedcom .= "0 @SUB1@ SUBM\n";
        $gedcom .= "1 NAME Genie Genealogy Export\n";

        // Export individuals with preprocessed family relations
        foreach ($individuals as $individual) {
            $safeId = $this->sanitizeGedcomString($individual['id']);
            $firstName = $this->sanitizeGedcomString($individual['first_name']);
            $lastName = $this->sanitizeGedcomString($individual['last_name']);

            $gedcom .= "0 @I{$safeId}@ INDI\n";
            $gedcom .= "1 NAME {$firstName} /{$lastName}/\n";
            $gedcom .= "2 SURN {$lastName}\n";
            $gedcom .= "2 GIVN {$firstName}\n";

            // Add sex
            $gedcom .= "1 SEX " . ($individual['gender'] === 'M' ? 'M' : 
                                 ($individual['gender'] === 'F' ? 'F' : 'U')) . "\n";

            // Add birth info
            if (!empty($individual['birth_date'])) {
                $birthDate = $this->convertToGedcomDate($individual['birth_date']);
                $gedcom .= "1 BIRT\n";
                $gedcom .= "2 DATE {$birthDate}\n";
                if (!empty($individual['birth_place'])) {
                    $gedcom .= "2 PLAC " . $this->sanitizeGedcomString($individual['birth_place']) . "\n";
                }
            }

            // Add death info if deceased
            if (!empty($individual['death_date'])) {
                $deathDate = $this->convertToGedcomDate($individual['death_date']);
                $gedcom .= "1 DEAT\n";
                $gedcom .= "2 DATE {$deathDate}\n";
                if (!empty($individual['death_place'])) {
                    $gedcom .= "2 PLAC " . $this->sanitizeGedcomString($individual['death_place']) . "\n";
                }
            } elseif (isset($individual['alive']) && !$individual['alive']) {
                $gedcom .= "1 DEAT Y\n";
            }

            // Add FAMC references
            foreach ($familyRelations[$individual['id']]['famc'] as $familyId) {
                $gedcom .= "1 FAMC @F" . $this->sanitizeGedcomString($familyId) . "@\n";
            }

            // Add FAMS references
            foreach ($familyRelations[$individual['id']]['fams'] as $familyId) {
                $gedcom .= "1 FAMS @F" . $this->sanitizeGedcomString($familyId) . "@\n";
            }
        }

        // Export families
        foreach ($families as $family) {
            $safeFamId = $this->sanitizeGedcomString($family['id']);
            $gedcom .= "0 @F{$safeFamId}@ FAM\n";

            // Add spouses
            if (!empty($family['husband_id'])) {
                $gedcom .= "1 HUSB @I" . $this->sanitizeGedcomString($family['husband_id']) . "@\n";
            }
            if (!empty($family['wife_id'])) {
                $gedcom .= "1 WIFE @I" . $this->sanitizeGedcomString($family['wife_id']) . "@\n";
            }

            // Add marriage info
            if (!empty($family['marriage_date'])) {
                $marriageDate = $this->convertToGedcomDate($family['marriage_date']);
                $gedcom .= "1 MARR\n";
                $gedcom .= "2 DATE {$marriageDate}\n";
                if (!empty($family['marriage_place'])) {
                    $gedcom .= "2 PLAC " . $this->sanitizeGedcomString($family['marriage_place']) . "\n";
                }
            }

            // Add divorce info if applicable
            if (!empty($family['divorce_date'])) {
                $divorceDate = $this->convertToGedcomDate($family['divorce_date']);
                $gedcom .= "1 DIV\n";
                $gedcom .= "2 DATE {$divorceDate}\n";
                if (!empty($family['divorce_place'])) {
                    $gedcom .= "2 PLAC " . $this->sanitizeGedcomString($family['divorce_place']) . "\n";
                }
            }

            // Add children
            foreach ($children as $child) {
                if ($child['family_id'] == $family['id']) {
                    $gedcom .= "1 CHIL @I" . $this->sanitizeGedcomString($child['child_id']) . "@\n";
                }
            }
        }

        // Add trailer
        $gedcom .= "0 TRLR\n";

        return $gedcom;
    }

    public function import($filePath, $options=[]) 
    {
        $treeId = $options['tree_id'] ?? null;
        if (!$treeId) throw new Exception('Tree ID is required');

        try {
            $parser = new \Gedcom\Parser();
            $gedcom = $parser->parse($filePath);
        } catch (Exception $e) {
            error_log("GEDCOM Parse error: " . $e->getMessage());
            throw new Exception("Failed to parse GEDCOM file: " . $e->getMessage());
        }

        $this->db->beginTransaction();

        try {
            $idMap = []; // Maps GEDCOM IDs to new database IDs
            $stats = ['individuals' => 0, 'families' => 0];

            // First pass: Create all individuals
            foreach ($gedcom->getIndi() as $individual) {
                try {
                    $names = $individual->getName();
                    if (empty($names)) {
                        error_log("Skipping individual without name: " . $individual->getId());
                        continue;
                    }

                    $name = reset($names);
                    $events = $individual->getAllEven();
                    
                    $birthDate = null;
                    $birthPlace = null;
                    $deathDate = null;
                    $deathPlace = null;
                    
                    foreach ($events as $event) {
                        if (is_array($event)) $event = reset($event);
                        try {
                            switch ($event->getType()) {
                                case 'BIRT':
                                    $birthDate = $event->getDate();
                                    $plac = $event->getPlac();
                                    $birthPlace = $plac ? $plac->getPlac() : null;
                                    // Convert date to proper format if needed
                                    if ($birthDate) {
                                        $birthDate = date('Y-m-d', strtotime($birthDate));
                                    }
                                    break;
                                case 'DEAT':
                                    $deathDate = $event->getDate();
                                    $plac = $event->getPlac();
                                    $deathPlace = $plac ? $plac->getPlac() : null;
                                    // Convert date to proper format if needed
                                    if ($deathDate) {
                                        $deathDate = date('Y-m-d', strtotime($deathDate));
                                    }
                                    break;
                            }
                        } catch (Exception $e) {
                            error_log("Error processing event for individual {$individual->getId()}: " . $e->getMessage());
                        }
                    }

                    // Create new member using MemberModel
                    $newMember = [
                        'treeId' => $treeId,
                        'firstName' => $name->getGivn() ?: '',
                        'lastName' => $name->getSurn() ?: '',
                        'gender' => $individual->getSex() ?: 'U',
                        'dateOfBirth' => $birthDate,
                        'placeOfBirth' => $birthPlace,
                        'dateOfDeath' => $deathDate,
                        'placeOfDeath' => $deathPlace,
                        'alive' => empty($deathDate) ? 1 : 0,
                        'gedcom_id' => $individual->getId()
                    ];

                    error_log("Adding individual: " . json_encode($newMember));
                    $newId = $this->individualModel->addMember($newMember);
                    
                    if (!$newId) {
                        throw new Exception("Failed to create individual record");
                    }
                    
                    $idMap[$individual->getId()] = $newId;
                    $stats['individuals']++;

                } catch (Exception $e) {
                    error_log("Error processing individual {$individual->getId()}: " . $e->getMessage());
                    throw $e;
                }
            }

            // Second pass: Create families
            foreach ($gedcom->getFam() as $family) {
                try {
                    $husbId = $family->getHusb();
                    $wifeId = $family->getWife();
                    
                    // Get marriage info
                    $marriageDate = null;
                    $events = $family->getAllEven();
                    foreach ($events as $event) {
                        if (is_array($event)) $event = reset($event);
                        if ($event->getType() === 'MARR') {
                            $marriageDate = $event->getDate();
                            if ($marriageDate) {
                                $marriageDate = date('Y-m-d', strtotime($marriageDate));
                            }
                        }
                    }

                    // Create family using FamilyModel
                    $familyData = [
                        'tree_id' => $treeId,
                        'husband_id' => $husbId ? ($idMap[$husbId] ?? null) : null,
                        'wife_id' => $wifeId ? ($idMap[$wifeId] ?? null) : null,
                        'marriage_date' => $marriageDate
                    ];

                    error_log("Creating family: " . json_encode($familyData));
                    $familyId = $this->familyModel->createFamily($familyData);
                    
                    if (!$familyId) {
                        throw new Exception("Failed to create family record");
                    }

                    $stats['families']++;

                    // Add children to family
                    foreach ($family->getChil() as $childId) {
                        if (isset($idMap[$childId])) {
                            $success = $this->familyModel->addChildToFamily(
                                $familyId, 
                                $idMap[$childId],
                                $treeId
                            );
                            if (!$success) {
                                error_log("Failed to add child $childId to family $familyId");
                            }
                        } else {
                            error_log("Child ID $childId not found in ID map");
                        }
                    }

                } catch (Exception $e) {
                    error_log("Error processing family: " . $e->getMessage());
                    throw $e;
                }
            }

            $this->db->commit();
            return $stats;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("GEDCOM Import failed: " . $e->getMessage());
            throw new Exception("GEDCOM Import failed: " . $e->getMessage());
        }
    }

}
