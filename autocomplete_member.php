<?php
require_once 'Member.php';

$member = new Member($db); // Adjust $db as per your setup

if (isset($_GET['term'])) {
    $term = $_GET['term'];
    $members = $member->searchMembersByName($term);
    $response = [];
    foreach ($members as $member) {
        $response[] = [
            'label' => $member['first_name'] . ' ' . $member['last_name'],
            'value' => $member['first_name'] . ' ' . $member['last_name'],
            'id' => $member['id']
        ];
    }
    echo json_encode($response);
}
?>

