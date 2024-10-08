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
            echo "\n+++++ Family ID: " . $family->getId() . "\n";
            
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
