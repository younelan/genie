<?php
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
   "Search By Name"=>"Chercher Par Nom",
   "Pages"=>"Pages",
   "Last Name"=> "Nom",
   "Gender"=>"Sexe",
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
   "Update Member"=>"Mise A Jour Membre",
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
   "Woman"=>"Femme",
   "Select Existing Member"=>"Choix Membre Existant",
   "Delete Member"=>"Effacer Membre",
   "Add Relation"=>"Ajout Relation",
   "Add Relationship With Existing Member"=>"Ajouter Relation Avec Membre Existant",
   "Add Relationship With New Member"=>"Ajouter Relation Avec Nouveau Member",
   "Relationship Type"=>"Type Relation",
   "Start"=>"Début",
   "End"=>"Fin",
   "Add Relationship"=>"Ajout Relation",
   "Delete Relationship"=>"Effacer Relation",
   "Recent Activity"=>"Activités Récentes",
   "Men"=>"Hommes",
   "Women"=>"Femmes",
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
$config['paths'] = [
   'frontend' => dirname(__DIR__) . "/frontend",
   'base' => dirname(__DIR__),
   'data' => __DIR__ . "/data",
   'backend' => __DIR__,
   'plugins' => dirname(__FILE__) . "/plugins",
];
$config_files = ["default_config.json", "data/config.json"];
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