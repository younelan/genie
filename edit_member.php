<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Member</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="style.css"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>
    <h1>Edit Member</h1>
    <a href='?action=edit_tree&tree_id=<?php echo htmlspecialchars($member['family_tree_id']) ?>'>Back to tree</a>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form id="edit-member-form" method="post" action="">
        <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($member['id']); ?>">

        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($member['first_name']); ?>" required><br>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($member['last_name']); ?>"><br>

        <label for="date_of_birth">Date of Birth:</label>
        <input type="date" name="date_of_birth" id="date_of_birth" value="<?php echo htmlspecialchars($member['date_of_birth']); ?>"><br>

        <label for="place_of_birth">Place of Birth:</label>
        <input type="text" name="place_of_birth" id="place_of_birth" value="<?php echo htmlspecialchars($member['place_of_birth']); ?>"><br>

        <label for="date_of_death">Date of Death:</label>
        <input type="date" name="date_of_death" id="date_of_death" value="<?php echo htmlspecialchars($member['date_of_death']); ?>"><br>

        <label for="place_of_death">Place of Death:</label>
        <input type="text" name="place_of_death" id="place_of_death" value="<?php echo htmlspecialchars($member['place_of_death']); ?>"><br>

        <label for="gender_id">Gender:</label>
        <select name="gender_id" id="gender_id">
            <option value="1" <?php if ($member['gender_id'] == 1) echo 'selected'; ?>>Male</option>
            <option value="2" <?php if ($member['gender_id'] == 2) echo 'selected'; ?>>Female</option>
            <!-- Add more options as needed -->
        </select><br><br>

        <button type="submit">Update Member</button>
    </form>

    <!-- Display Relationships -->
    <h2>Relationships</h2>
    <div id="relationships">
        <table class="relationship-table">
            <tr>
                <th>Person 1</th>
                <th>Person 2</th>
                <th>Relationship Type</th>
                <th>Actions</th>
            </tr>
            <tbody id="relationships-table-body">
                <!-- Relationships will be dynamically filled via JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Form to add new relationship -->
    <h2>Add Relationship</h2>
    <form id="add-relationship-form">
        <input type="hidden" id="member_id" name="member_id" value="<?php echo htmlspecialchars($member['id']); ?>">
        <input type="hidden" name="member2_id" value="<?php echo htmlspecialchars($member['id']); ?>">
        <input type="hidden" name="family_tree_id" value="<?php echo htmlspecialchars($member['family_tree_id']); ?>">

        <label for="autocomplete_member">Person:</label>
        <input type="text" id="autocomplete_member" name="autocomplete_member" autocomplete="off" required><br>

        <label for="relationship_type">Relationship Type:</label>
        <select name="relationship_type" id="relationship_type">
            <!-- Options will be dynamically filled via AJAX -->
        </select><br>

        <button type="button" id="add-relationship-btn">Add Relationship</button>
    </form>

    <!-- Form to edit relationship (hidden by default) -->
    <div id="edit-relationship-modal" style="display: none;">
        <h2>Edit Relationship</h2>
        <form id="edit-relationship-form">
            <input type="hidden" id="edit_relationship_id" name="relationship_id">
            <input type="hidden" id="edit_member_id" name="member_id" value="<?php echo htmlspecialchars($member['id']); ?>">
            <input type="hidden" name="edit_member2_id" value="<?php echo htmlspecialchars($member['id']); ?>">
            <input type="hidden" name="edit_family_tree_id" value="<?php echo htmlspecialchars($member['family_tree_id']); ?>">
            
            <label for="edit_relationship_person1">Person 1:</label>
            <input type="text" id="edit_relationship_person1" name="person1" readonly><br>
            
            <label for="edit_relationship_person2">Person 2:</label>
            <input type="text" id="edit_relationship_person2" name="person2" readonly><br>
            
            <label for="edit_relationship_type">Relationship Type:</label>
            <select name="relationship_type" id="edit_relationship_type">
                <!-- Options will be dynamically filled via AJAX -->
            </select><br>

            <button type="button" id="update-relationship-btn">Update Relationship</button>
        </form>
    </div>

    <!-- External JavaScript file -->
    <script>
        var memberId = <?php echo json_encode($member['id']); ?>; // Pass member ID to JavaScript
        const treeId = <?php echo json_encode($member['family_tree_id']); ?>; // Pass member ID to JavaScript
        
        document.addEventListener('DOMContentLoaded', function() {
            var script = document.createElement('script');
            script.src = 'relationships.js';
            script.onload = function() {
                // Initialize relationships.js with member ID
                initializeRelationships(memberId);
            };
            document.head.appendChild(script);
        });
    </script>
</body>
</html>
