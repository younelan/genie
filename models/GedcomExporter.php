<?php 
$basedir = dirname(__DIR__) ;
require $basedir . '/init.php'; // Ensure you include the autoload file

//use Liberu\Genealogy\Gedcom;

class GEDCOMExporter
{
    private $pdo;
    private $peopleTable;
    private $tagsTable,$treeTable,$synonymTable;
    private $relationshipsTable;
    private $relationshipTypesTable;

    // public function __construct(array $config)
    // {
    //     $this->pdo = $config['connection'];
    //     $this->peopleTable = $config['tables']['people'];
    //     $this->tagsTable = $config['tables']['people_tags'];
    //     $this->relationshipsTable = $config['tables']['person_relationship'];
    //     $this->relationshipTypesTable = $config['tables']['relationship_type'];
    // }
    public function __construct(private $config)
    {
        //$this->config = $config;
        $this->pdo = $config['connection'];
        $this->peopleTable = $config['tables']['person']??'person';
        $this->tagsTable = $config['tables']['people_tags']??'tags';
        $this->treeTable = $config['tables']['tree']??'family_tree';
        $this->relationshipsTable = $config['tables']['relation']??'person_relationship';
        $this->synonymTable = $config['tables']['synonyms']??'synonyms';
        
    }

    public function export($familyTreeId)
    {
        $gedcom = new \Gedcom\Writer();

        // Add header
        $gedcom->addHeader();

        // Fetch people for the specified family tree
        $people = $this->fetchPeople($familyTreeId);
        $individuals = $this->createIndividuals($gedcom, $people);

        // Fetch relationships and create family links
        $this->createRelationships($individuals, $familyTreeId);

        // Add footer
        $gedcom->addTrailer();

        return $gedcom->toString();
    }

    private function fetchPeople($familyTreeId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->peopleTable} WHERE tree_id = :tree_id");
        $stmt->execute(['tree_id' => $familyTreeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function createIndividuals(Gedcom $gedcom, array $people)
    {
        $individuals = [];
        foreach ($people as $person) {
            $individual = $gedcom->createIndividual($person['id']);
            $fullName = trim("{$person['first_name']}  {$person['last_name']}");
            $individual->setName($fullName);

            // Add birth and death events with places
            if ($person['birth_date']) {
                $individual->addEvent('BIRT', ['DATE' => $person['birth_date'], 'PLAC' => $person['birth_place']]);
            }
            if ($person['death_date']) {
                $individual->addEvent('DEAT', ['DATE' => $person['death_date'], 'PLAC' => $person['death_place']]);
            }

            // Add aliases if they exist
            $this->addAliases($individual, $person);
            /*
            // Add body text if it exists... probably should be tags
            if (!empty($person['body'])) {
                $individual->addNote($person['body']);
            }
            */

            // Optionally include created_at timestamp
            if (!empty($person['created_at'])) {
                $individual->addNote("Created at: " . $person['created_at']);
            }

            // Handle optional fields (tags)
            $this->addTags($individual, $person['id']);

            $individuals[$person['id']] = $individual;
            $gedcom->addIndividual($individual);
        }
        return $individuals;
    }

    private function addAliases($individual, $person)
    {
        $aliases = array_filter([$person['alias1'], $person['alias2'], $person['alias3']]);
        foreach ($aliases as $alias) {
            if (!empty($alias)) {
                $individual->addAlias($alias);
            }
        }
    }

    private function addTags($individual, $personId)
    {
        $stmt = $this->pdo->prepare("SELECT tag FROM {$this->tagsTable} WHERE person_id = :person_id");
        $stmt->execute(['person_id' => $personId]);
        $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tags as $tag) {
            $individual->addTag($tag);
        }
    }

    private function createRelationships(array $individuals, $familyTreeId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->relationshipsTable} WHERE tree_id = :tree_id");
        $stmt->execute(['tree_id' => $familyTreeId]);
        $relationships = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($relationships as $relation) {
            if (isset($individuals[$relation['person_id1']]) && isset($individuals[$relation['person_id2']])) {
                $individual = $individuals[$relation['person_id1']];
                $relatedIndividual = $individuals[$relation['person_id2']];
                
                // Fetch relationship type
                $relationshipType = $this->fetchRelationshipType($relation['relationship_type_id'], $familyTreeId);

                if ($relationshipType) {
                    $individual->addFamily($relatedIndividual, ['TYPE' => $relationshipType]);
                }
            }
        }
    }

    private function fetchRelationshipType($relationshipTypeId, $familyTreeId)
    {
        $stmt = $this->pdo->prepare("SELECT description FROM {$this->relationshipTypesTable} WHERE id = :id AND tree_id = :tree_id");
        $stmt->execute(['id' => $relationshipTypeId, 'tree_id' => $familyTreeId]);
        return $stmt->fetchColumn();
    }
}

// // Create a PDO connection


// Create an instance of the GEDCOMExporter
$exporter = new GEDCOMExporter($config);
die("ok");
// Specify the family tree ID you want to export
$familyTreeId = 1; // Example ID

// Generate the GEDCOM output
$gedcomContent = $exporter->export($familyTreeId);

// Set headers and output the GEDCOM file
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="family_tree.ged"');
echo $gedcomContent;
