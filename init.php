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
//require_once "$basedir/models/Auth.php";
require_once "$basedir/models/UserModel.php";
require_once "$basedir/models/TreeModel.php";
require_once "$basedir/models/GedcomModel.php";
require_once "$basedir/models/MemberModel.php";
require_once "$basedir/models/TagModel.php";
require_once "$basedir/models/FamilyModel.php";
#require_once "$basedir/controllers/TreeController.php";
#require_once "$basedir/controllers/MemberController.php";
#require_once "$basedir/controllers/FamilyController.php";
require_once "$basedir/models/Migrate.php";

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
   "Pages"=>"Pages",
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
