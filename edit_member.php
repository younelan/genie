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
<style>
    h1 { margin-bottom: 3px}
    .neav {display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px}
    .nav ul {list-style-type: none; margin: 0; display:block; padding: 0; overflow: hidden; background-color: #c1d0d5;margin-bottom:5px}
    .nav li {display:inline-block}
    .nav a {display: inline-block; padding: 4px;  color: #333; text-decoration: none; margin-right: 5px;}
    .nav a:hover {background-color: #f2f2f2;color: red;}
    .relationship-table button {
        display: inline-block
    }
    .relationship-table form {
        display: inline-block;
    }
</style>
<?php $treeId = htmlspecialchars($member['family_tree_id']) ?>
<div class='nav'>
<ul class='nav-ul'>
<li><a href="index.php?action=add_member&tree_id=<?php echo $treeId; ?>">New Member</a></li>
<li><a href="index.php?action=edit_tree&tree_id=<?php echo $treeId; ?>">Edit Tree</a></li>
<li><a href="index.php?action=view_tree&tree_id=<?php echo $treeId; ?>">View Tree</a></li>
<li><a href="index.php?action=list_trees">Tree List</a></li>
</ul>

</div>


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


    
    <h2>Add Relationship</h2>
    <form id="add-relationship-form">
        <input type="hidden" id="member_id" name="member_id" value="<?php echo htmlspecialchars($member['id']); ?>">
        <input type="hidden" name="family_tree_id" value="<?php echo htmlspecialchars($member['family_tree_id']); ?>">

        <!-- Radio buttons to choose between existing or new member -->
        <label><input type="radio" name="member_type" value="existing" checked> Add Relationship with Existing Member</label><br>
        <label><input type="radio" name="member_type" value="new"> Add Relationship with New Member</label><br><br>

        <!-- Section for existing member selection -->
        <div id="existing-member-section">
            <label for="autocomplete_member">Select Existing Member:</label>
            <input type="text" id="autocomplete_member" name="autocomplete_member" list="autocomplete-options" autocomplete="off" required><br>
            <datalist id="autocomplete-options"></datalist><br>

            <!-- Hidden fields for person IDs and relationship type -->
            <input type="hidden" name="person_id1" id="person_id1" value="<?php echo htmlspecialchars($member['id']); ?>">
            <input type="hidden" name="person_id2" id="person_id2" value="">
            <input type="hidden" name="relationship_type" id="relationship_type" value="">

            <label for="relationship_type_select">Relationship Type:</label>
            <select name="relationship_type_select" id="relationship_type_select">
                <!-- Options will be populated dynamically via AJAX -->
            </select><br>
        </div>

        <!-- Section for new member form -->
        <div id="new-member-section" style="display:none;">
            <label for="new_first_name">First Name:</label>
            <input type="text" id="new_first_name" name="new_first_name"><br>

            <label for="new_last_name">Last Name:</label>
            <input type="text" id="new_last_name" name="new_last_name"><br>

            <label for="relationship_type_new">Relationship Type:</label>
            <select name="relationship_type_new" id="relationship_type_new">
                <!-- Options will be populated dynamically via AJAX -->
            </select><br>
        </div>

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
    <hr>
    <h2>Delete Member</h2>
    Warning, this can not be undone
    <form method="post" class='delete-member-form' action="index.php?action=delete_member">
                    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                    <button type="submit">Delete</button>
    </form>

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

        $(document).ready(function() {
            // Handle delete tree form submission with confirmation
            $('.delete-member-form').submit(function(event) {
                if (!confirm('Are you sure you want to delete this tree?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
