<?php

$basedir = $basedir ?? __DIR__;
/*
function logapache($foo) {
    if(is_array($foo)) {
        $foo = print_r($foo,true);
    }
    $foo .= "\n";
    file_put_contents('php://stderr', print_r($foo, TRUE)) ;
}
*/


require_once "$basedir/vendor/autoload.php";
require_once "$basedir/AppBase.php";
require_once "$basedir/models/UserModel.php";
require_once "$basedir/models/TreeModel.php";
require_once "$basedir/models/GedcomModel.php";
require_once "$basedir/models/MemberModel.php";
require_once "$basedir/models/TagModel.php";
require_once "$basedir/models/FamilyModel.php";

$config = [];
$vars = [
  "charset" => "utf-8",
   "favicon" => "favicon.png",
   "themepath" => "themes/bootstrap",
   "sitepath" => trim(dirname($_SERVER['PHP_SELF']), "/"),
   "pagebefore" => "",
   "pageafter" => "",
   "contentbefore" => "",
   "contentafter" => "",
   "pagestyle" => "",
   "logoimg" => "images/ControlTower.gif",
   "favicon" => 'images/ControlTowerIcon.png',
   "title" => "Control Tower",
   "footerfg" => "#5d6d8a",
   "footerbg" => "#2a6370",
   "menubg" => "#061e38",
   "menufg" => "white",
   "pagebg" => "#396d89",
   "pagefg" => "black",
   "contentbefore" => "",
   "contentafter" => "",
   "footer" => "(c) 2024 Periscope Server Control",
   "sitename" => "Periscope Server Control",
   "pagestyle" => ""
];

//print "<pre>";
$sitepath = trim(dirname($_SERVER['PHP_SELF']), "/");

if (!$sitepath) {
   $sitepath = '.';
} else {
   $sitepath = "$sitepath";
}
$vars['sitepath'] = $sitepath;

//navigationmenu
$translations["fr"] = [
   "Family Members"=>"Membres de la Famille",
   "New Tree"=>"Nouvel Arbre",
   "Search By Name"=>"Chercher Par Nom",
   "Search By Name..."=>"Chercher Par Nom",
   "Pages"=>"Pages",
   "Settings"=>"Configuration",
   "Birth"=>"Naissance",
   "Death"=>"Décès",
   "Last Name"=> "Nom",
   "Gender"=>"Sexe",
   "Spouse"=>"Epoux",
   "Child"=>"Enfant",
   "Single Parent"=>"Parent Unique",
   "First Parent"=>"Premier Parent",
   "Second Parent"=>"2eme Parent",
   "Select Existing Parent"=>"Choix Parent Existant",
   "Select Existing Parent1"=>"Choix Parent Existant",
   "Select Existing Other"=>"Choix Autre Relation Existante",
   "Select Existing"=>"Existant",
   "Parent"=>"Parent",
   "Parents"=>"Parents",
   "Unspecified"=>"Non Spécifié",
   "New Spouse Details"=>"Info Nouvel Epoux",
   "View Spouse"=>"Voir Epoux",
   "Change Spouse"=>"Modifier Epoux",
   "Replace Spouse"=>"Remplace Epoux",
   "Save Spouse"=>"Enregistrer Epoux",
   "First Name"=>"Prénom",
   "Middle Name"=>"2eme Prénom",
   "Edit Member"=>"Edition Membre",
   "Actions"=>"Actions",
   "Person 1"=>"Personne 1",
   "Member Details"=>"Détails Membre",
   "Person 2"=>"Personne 2",
   "Warning, This Can Not Be Undone"=>"Attention, Ceci ne peut etre annulé",
   "More Fields"=>"Plus De Champs",
   "Less Fields"=>"Moins De Champs",
   "Alive"=>"En Vie",
   "Source"=>"Source",
   "Family Trees"=> "Arbres Généalogiques",
   "Update Member"=>"Edition Membre",
   "More"=>"Plus",
   "Existing Relations"=>"Relations Existantes",
   "Date of Birth"=>"Date De Naissance",
   "Place of Birth"=>"Né à",
   "Date of Death"=>"Date de Décès",
   "Type"=>"Type",
   "Title"=>"Titre",
   "Changes"=>"Changements",
   "Alias 1"=>"Alias 1",
   "Alias 2"=>"Alias 2",
   "Alias 3"=>"Alias 3",
   "Place of Death"=>"Endroit de Décès",
   "Man"=>"Homme",
   "Male"=>"Homme",
   "Woman"=>"Femme",
   "New"=>"Nouveau",
   "Details"=>"Details",
   "Female"=>"Femme",
   "Add New Member"=> "Rajouter Nouveau Membre",
   "Select Existing Member"=>"Choix Membre Existant",
   "Delete Member"=>"Effacer Membre",
   "Delete Tree"=>"Effacer Tree",
   "User"=>"Utilisateur",
   "User Menu"=>"Menu Utilisateur",
   "Add Relation"=>"Ajout Relation",
   "List Members"=>"Liste de Membres",
   "Add Relationship With Existing Member"=>"Ajouter Relation Avec Membre Existant",
   "Add Relationship With New Member"=>"Ajouter Relation Avec Nouveau Member",
   "Relationship Type"=>"Type Relation",
   "Start"=>"Début",
   "End"=>"Fin",
   "Copy"=>"Copier",
   "Add Relationship"=>"Ajout Relation",
   "Delete Relationship"=>"Effacer Relation",
   "Recent Activity"=>"Activités Récentes",
   "Men"=>"Hommes",
   "Sibling"=>"Fraternel",
   "Father"=>"Père",
   "Mother"=>"Mère",
   "Half Sibling"=> "Demi-Fraternel",
   "Husband"=>"Mari",
   "Wife"=>"Femme",
   "Unknown"=>"Inconnu",
   "Fiance"=>"Fiancé(e)",
   "Adopted"=>"Adopté(e)",
   "Illegitimate"=>"Non Légitime",
   "1st Cousin"=>"1er Cousin",
   "2nd Cousin"=>"2ème Cousin",
   "3rd Cousin"=>"3ème Cousin",
   "Gone Cousin"=>"Cousin Eloigné",
   "Birth Date"=>"Date de Naissance",
   "Name"=>"Nom",
   "Save"=>"Enregistrer",
   "Close"=>"Fermer",
   "Cancel"=>"Annuler",
   "Other Relationship"=>"Autre Relation",
   "Parents"=>"Parents",
   "Delete Spouse"=>"Effacer Conjoint",
   "Add Child"=>"Ajouter Enfant",
   "Add Spouse"=>"Ajouter Conjoint",
   "Marriage Date"=>"Date de Mariage",
   "Marriage Place"=>"Lieu de Mariage",
   "Divorce Date"=>"Date de Divorce",
   "Divorce Place"=>"Lieu de Divorce",
   "Spouse"=>"Conjoint",
   "Children"=>"Enfants",
   "Delete Child"=>"Effacer Enfant",
   "Delete Parent"=>"Effacer Parent",
   "Add Parents"=>"Ajouter Parents",
   "Families"=>"Familles",
   "Marriage Details"=>"Détails Mariage",
   "Existing Person"=>"Personne Existante",
   "Existing Child"=>"Enfant Existant",
   "New Child"=>"Nouvel Enfant",
   "Existing Parent"=>"Parent Existant",
   "New Parent"=>"Nouveau Parent",
   "New Parent"=>"Nouveau Parent1",
   "New Family"=>"Nouvelle Famille",
   "Delete Family"=>"Effacer Famille",
   "No Spouse"=>"Pas D'epoux",
   "Existing Family"=>"Famille Existante",
   "Gender"=>"Sexe",
   "New Person"=>"Nouvelle Personne",
   "Select Existing Spouse"=>"Choisir Epoux Existant",
   "Select Existing Person"=>"Choisir Personne Existante",
   "Visualize Descendants"=>"Visualiser Descendants",
   "Family Relationships"=>"Relations Familiales",
   "Select Existing Parent"=>"Choisir Parent Existant",
   "Select Existing Child"=>"Choisir Enfant Existant",
   "Add Other Relationship"=>"Ajouter Autre Relation",
   "Once Removed Cousin"=>"Cousin Eloigné 2ème Génération",
   "Twice Removed Cousin"=>"Cousin Eloigné 3ème Génération",
   "Fraternel"=>"Fraternal",
   "Parent"=>"Parent",
   "Uncle/Aunt" => "Oncle/Tante",
   "Nephew/Niece"=>"Neveu/Nièce",
   "Child"=>"Enfant",
   "Ex Spouse"=>"Ex Èpoux",
   "Partner"=>"Partenaire",
   "Friend"=>"Ami",
   "Cousin"=>"Cousin(e)",
   "Women"=>"Femmes",
   "Back to Home"=>"Page D'accueuil",
   "Back to List"=> "Retour à La liste",
   "Add New Family Tree"=> "Ajouter Nouvel Arbre",
   "Add Tree"=>"Ajouter Arbre",
   "Tree Name"=>"Nom Arbre",
   "Description"=>"Description",
   "By First Name"=>"Par Prénom",
   "By Last Name"=>"Par Nom",
   "By Gender"=>"Par Sexe",
   "Relations"=> "Relations",
   "Events"=>"Evènements",
   "Other"=>"Autres",
   "Statistics"=>"Statistiques",
   "Edit Relationship"=>"Editer Relation",
   "Trees"=>"Arbres",
   "Visualize"=>"Visualiser",
   "New Member"=>"Nouveau Membre",
   "View Tree" => "Voir Arbre",
   "Ok" > "Ok",
   "Fail" => "Echoué",
   "Collapse" => "Réduire",
   "Expand" => "Montrer",
   "Failed Login" => "Echec Connection",
   "Invalid CSRF token" => "Echec Verif Code CSRF",
   "info" => "Info",
   "Time" => "Heure",
   "Amount" => "Qté",
   "Sender" => "Expediteur",
   "Recipient" => "Destinataire",
   "Network" => "Réseau",
   "Network Hosts" => "Machines sur le Réseau",
   "Load" => "Proc",
   "Uptime" => "Dispo",
   "Device Details" => "Détails Machine",
   "Ops Wellness" => "Santé Opérationnelle",
   "Queue is Empty, Back to " => "Queue Vide, Retour Vers ",
   "Mac Address" => "Adresse Mac",
   "Host Name" => "Nom Machine",
   "Details" => "Détails",
   "Vendor" => "Marque",
   "Security" => "Sécurité",
   "Logon" => "Connecter",
   "Connect" => "Connecter",
   "Logoff" => "Déconnecter",
   "Delete" => "Effacer",
   "All" => "Tout",
   "None" => "Aucun",
   "Select" => "Selection",
   "Drag a field to Move" => "Glisser un champ pour Réorganiser",
   "Click Hide/Show to Hide/Show Fields" => "Montrer/Cacher affiche/cache Champ",
   "Click a field to Sort that Column" => "Clic sur entête trie la Colonne",
   "Labels" => "L&eacute;gende",
   "Mail Queue Contents" => "Queue Postfix",
   'Password' => 'Mot de passe',
   'Login' => 'Identifiant',
   'Current Password' => 'Mot de passe Actuel',
   'Save Changes' => 'Enregistrer',
   'Marriage Date' => 'Date De Marriage',
   'Marriage Date:' => 'Date De Marriage',
   "No parents added"=>"Pas de Parents",
   
   'Other Relationships' => 'Autres Relations',
   'No other relationships found'=>"Pas d'autres Relations",
   "Loading..."=>"Chargement En cours...",
   'New Password' => 'New Password',
   'Confirm Password' => 'Confirmer Mot de Passe',
   'One Digit' => 'Password must contain at leat one Digit',
   'One Uppercase' => 'Password must contain at leat one Uppercase',
   'One Lowercase' => 'Password must contain at leat one Lowercase',
   'One Special' => 'Password must contain at leat one Special Character',
   'Min Length' => 'Password must be at least 8 characters long',
   'Edit Password' => 'Changer Mot de passe',
   'Change Password' => 'Changer Mot de Passe',
   'Password Changed' => 'Mot de passe Changé',
   'Password Change Failed' => 'Changement de Mot de passe échoué',
   'Current Password Incorrect' => 'Mot de passe Actuel Incorrect',
   'Please Login' => 'Veuillez Connecter',
   'Please Enter Password' => "Entrer Mot de Passe",
   "Update Password" => "Mettre à Jour Mot de passe",
   'Login Required' => "Mot de Passe Requis",
   "Tree" => "Arbre",
   "User" => "Utilisateur",
   "Tree Menu" => "Menu Arbre",
   "User Menu" => "Menu Utilisateur",
   "Create New Family Tree" => "Créer Nouvel Arbre",
   "Create Tree" => "Créer Arbre",
   "Import GEDCOM" => "Importer GEDCOM",
   "Are you sure you want to delete this tree?" => "Êtes-vous sûr de vouloir supprimer cet arbre?",
   "Are you sure you want to empty this tree? All members and relationships will be deleted. This cannot be undone." => "Êtes-vous sûr de vouloir vider cet arbre? Tous les membres et relations seront supprimés. Cette action ne peut pas être annulée.",
   "Public" => "Public",
   "Unknown Spouse" => "Conjoint Inconnu",
   "With" => "Avec",
   "Create Empty Family" => "Créer Famille Vide",
   "Back to Trees" => "Retour aux Arbres",
   "Original Term" => "Terme Original",
   "Replacement Term" => "Terme de Remplacement",
   "Add Synonym" => "Ajouter Synonyme",
   "Manage Synonyms" => "Gérer les Synonymes",
   "Save" => "Enregistrer",
   "Edit" => "Éditer", 
   "Recent Updates" => "Mises à Jour Récentes",
   "Family Trees" => "Arbres Généalogiques",
   "Make this tree public" => "Rendre cet arbre public",
   "Empty Tree" => "Vider l'Arbre",
   "Export GEDCOM" => "Exporter GEDCOM",
   "Tree Settings" => "Paramètres de l'Arbre",
   "View Members" => "Voir les Membres",
   "Are you sure you want to delete this synonym?" => "Êtes-vous sûr de vouloir supprimer ce synonyme?",
   // Tabs
   "Members" => "Membres",
   "Updates" => "Mises à jour",
   
   // Tree Menu Items
   "List Members" => "Liste des Membres",
   "Add Member" => "Ajouter un Membre",
   "Visualize Tree" => "Visualiser l'Arbre", 
   "Manage Synonyms" => "Gérer les Synonymes",
   "Tree Settings" => "Paramètres de l'Arbre",

   // User Menu Items
   "Profile" => "Profil",
   "Settings" => "Paramètres",
   "Logout" => "Déconnexion",

   // Statistics Categories
   "By Gender" => "Par Genre",
   "By Age" => "Par Âge",
   "By Status" => "Par Statut",
   "By Location" => "Par Lieu",
   
   // Member List
   "Search by name..." => "Rechercher par nom...",
   "No results found" => "Aucun résultat trouvé",
   "Page" => "Page",
   
   // Common Actions
   "Add" => "Ajouter",
   "Edit" => "Modifier",
   "Delete" => "Supprimer",
   "Cancel" => "Annuler",
   "Save" => "Enregistrer",
   "Create" => "Créer",
   "Import" => "Importer",
   
   // Modal Labels
   "Create New Tree" => "Créer Nouvel Arbre",
   "Import GEDCOM File" => "Importer Fichier GEDCOM",
   "Tree Name" => "Nom de l'Arbre",
   "Description" => "Description",
   
   // Stats
   "Total Members" => "Total des Membres",
   "Active Members" => "Membres Actifs",
   "Recent Activity" => "Activité Récente",
   "Last Updated" => "Dernière Mise à Jour",
   
   // Navigation
   "Back" => "Retour",
   "Next" => "Suivant",
   "Previous" => "Précédent",
   // Navigation titles
   "Visualize" => "Visualisation",
   "Member Details" => "Détails du Membre",
   "Add Member" => "Ajouter un Membre",
   "View Members" => "Liste des Membres",
   "Manage Synonyms" => "Gestion des Synonymes",
   "Tree Settings" => "Configuration de l'Arbre",
   "Edit Tree" => "Modifier l'Arbre",
   // User menu items rewrite for clarity
   "Profile" => "Mon Profil", 
   "Settings" => "Configuration",
   "Logout" => "Déconnexion",
   // Page titles
   "Family Trees" => "Arbres Généalogiques",
   // User label
   "User" => "Utilisateur",
   // Member Details
   "Loading..." => "Chargement...",
   "Return to Members List" => "Retour à la Liste des Membres",
   "Member not found" => "Membre non trouvé",
   "Member Details" => "Détails du Membre",
   "Select Gender" => "Sélectionner le Genre",
   "Visualize Descendants" => "Visualiser les Descendants",
   "Add Relationship" => "Ajouter une Relation",
   "Delete Member" => "Supprimer le Membre",
   "Are you sure you want to delete this member?" => "Êtes-vous sûr de vouloir supprimer ce membre?",
   
   // Tag Input
   "Tags" => "Étiquettes",
   "Copy" => "Copier",
   "Type and press Enter to add tags" => "Tapez et appuyez sur Entrée pour ajouter des étiquettes",
   
   // Relationship Modal
   "Add Relationship" => "Ajouter une Relation",
   "Close" => "Fermer",
   "Save" => "Enregistrer",
   "Create Empty Family" => "Créer une Famille Vide",
   "Existing Person" => "Personne Existante",
   "New Person" => "Nouvelle Personne",
   "Select Existing" => "Sélectionner Existant",
   "First Name" => "Prénom",
   "Last Name" => "Nom",
   "Marriage Date" => "Date de Mariage",
   "Marriage Place" => "Lieu de Mariage",
   "Divorce Date" => "Date de Divorce",
   "Birth Date" => "Date de Naissance",
   "First Parent" => "Premier Parent",
   "Second Parent" => "Deuxième Parent",
   "Single Parent" => "Parent Unique",
   "Family:" => "Famille:",
   "With" => "Avec",
   "Unknown Spouse" => "Conjoint Inconnu",
   "New Family (No Spouse)" => "Nouvelle Famille (Sans Conjoint)",
   
   // Edit Other Relationship
   "Edit Relationship" => "Modifier la Relation",
   "Person 1:" => "Personne 1:",
   "Person 2:" => "Personne 2:",
   "Relationship Type:" => "Type de Relation:",
   "Start Date:" => "Date de Début:",
   "End Date:" => "Date de Fin:",
   "Swap Persons" => "Échanger les Personnes",
   "Are you sure you want to remove this parent relationship?" => "Êtes-vous sûr de vouloir supprimer cette relation parentale?",
   "Are you sure you want to remove the spouse from this family?" => "Êtes-vous sûr de vouloir supprimer le conjoint de cette famille?",
   "Are you sure you want to remove this child?" => "Êtes-vous sûr de vouloir supprimer cet enfant?",
   "No parents added" => "Aucun parent ajouté",
   "Please select a spouse" => "Veuillez sélectionner un conjoint",
   "Please select first parent" => "Veuillez sélectionner le premier parent",
   "Please select second parent" => "Veuillez sélectionner le deuxième parent",
   "Please select a relationship type" => "Veuillez sélectionner un type de relation",
   "Please select a person" => "Veuillez sélectionner une personne",
   "Child first and last name are required" => "Le prénom et le nom de l'enfant sont requis",
   "Parent first and last name are required" => "Le prénom et le nom du parent sont requis",
   "Second parent first and last name are required" => "Le prénom et le nom du deuxième parent sont requis",
   "Person first and last name are required" => "Le prénom et le nom de la personne sont requis",
   // Relationship Modal Tabs
   "Spouse" => "Conjoint(e)",
   "Child" => "Enfant",
   "Parent" => "Parent",
   "Other" => "Autre",
   // Relationship search placeholders
   "Search for spouse..." => "Rechercher un conjoint...",
   "Search for child..." => "Rechercher un enfant...",
   "Search for other..." => "Rechercher une personne...",
   "Search for parent1..." => "Rechercher parent 1...",
   "Search for parent2..." => "Rechercher parent 2...",
   "Search for first parent..." => "Rechercher le premier parent...",
   "Search for second parent..." => "Rechercher le deuxième parent...",
   "Search for person..." => "Rechercher une personne...",
   
   // Relationship types descriptions (for dropdown)
   "Sibling" => "Frère/Soeur",
   "Father" => "Père",
   "Mother" => "Mère",
   "Half Sibling" => "Demi-Frère/Soeur",
   "Husband" => "Mari",
   "Wife" => "Femme",
   "Partner" => "Partenaire",
   "Friend" => "Ami(e)",
   "Cousin" => "Cousin(e)",
   "Uncle" => "Oncle",
   "Aunt" => "Tante",
   "Nephew" => "Neveu",
   "Niece" => "Nièce",
   "Step Sibling" => "Demi-Frère/Soeur",
];

$keys = array_keys($translations['fr']);

$translations['en'] = array_combine($keys, $keys);

$config['translations'] = $translations;

// Add relationship types configuration
$config['relationship_types'] = [
    'SIBL' => ['id' => 1,  'description' => 'Sibling'],
    'FATH' => ['id' => 2,  'description' => 'Father'],
    'MOTH' => ['id' => 3,  'description' => 'Mother'],
    'HALF' => ['id' => 4,  'description' => 'Half Sibling'],
    'HUSB' => ['id' => 5,  'description' => 'Husband'],
    'WIFE' => ['id' => 6,  'description' => 'Wife'],
    'CHLD' => ['id' => 7,  'description' => 'Enfant'],
    'CUSN' => ['id' => 8,  'description' => 'Cousin'],
    'DIV'  => ['id' => 9,  'description' => 'Ex Epoux'],
    'PART' => ['id' => 10, 'description' => 'Partner'],
    'FIAN' => ['id' => 11, 'description' => 'Fiance'],
    'ADOP' => ['id' => 12, 'description' => 'Adopted'],
    'ILLE' => ['id' => 13, 'description' => 'Illegitimate'],
    '1ST'  => ['id' => 14, 'description' => '1st Cousin'],
    '2ND'  => ['id' => 15, 'description' => '2nd Cousin'],
    '3RD'  => ['id' => 16, 'description' => 'Third Cousin'],
    'GONE' => ['id' => 17, 'description' => 'Gone Cousin'],
    'ONCE' => ['id' => 18, 'description' => 'Once Removed Cousin'],
    'STEP' => ['id' => 19, 'description' => 'Step Sibling'],
    'UNKN' => ['id' => 20, 'description' => 'Unknown']
];

$root_dir = __DIR__;
$config['paths'] = [
   'frontend' => $root_dir . "/frontend",
   'base' => $root_dir ,
   'data' => $root_dir . "/data",
   'backend' => $root_dir ,

];
$config_files = ["data/default.json", "data/config.json"];
foreach ($config_files as $fname) {
   if (file_exists(__DIR__ . "/$fname")) {
      $cfg = file_get_contents(__DIR__ . "/$fname");
      $json_config = json_decode($cfg, true);
      if ($json_config) {
         $config = array_replace_recursive($config, $json_config);
         // foreach($json_config as $key=>$value) {
         //     $config[$key]=$value;
         // }
      }
   }
}

$dbHost = $config['db']['host'] ?? 'localhost';
$dbName = $config['db']['name'] ?? 'genealogy';
$dbUser = $config['db']['user'] ?? 'root';
$dbPass = $config['db']['pass']??'';

try {
    $connection = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $config['connection'] = $connection;
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}


$user = new UserModel($config);
$userId = $user->getCurrentUserId();
$config['current-user']=$userId;
if (!$userId) {
    header("Location: login.php");
    exit();
}
