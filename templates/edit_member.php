<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edition Membre</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!--<link rel="stylesheet" href="res/style.css?Version=1">-->
    <!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script> -->

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Genie: Membres Famille</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        /* Add custom styles here */
        body {
            background-color: #dfc9a7;
        }
        .navbar {
            background-color: #62313c !important;
        }
        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #f0e2d8;
            color: black;
        }
        .card a  {
            color: #240c0c;
        }
        .card-header {
            background-color: #e5d7d3;
            border-bottom: 1px solid #dee2e6;
            font-weight: bold;
            color: #6d1818;
            }
        .list-group-item {
            background-color: #fff3f3;
            border: 1px solid rgba(234, 186, 186, 0.56);
        }
        .relation-button {
            width: 23px;
            border: 1px solid #cbb;  
            padding: 0px;
            display: inline-block;
            height: 30px;
        }
        .relationship-table form {
            display: inline-block;
        }
        .badge-primary {
            color: #b3ccf5;
            background-color: #06158e;
        }
        #search {
            display: block;
            width: 100%;
            margin-bottom: 5px;
            border: 1px solid #ccc;
            padding: 5px;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">

<!-- Navigation menu -->
<nav class="navbar navbar-expand-md navbar-dark fixed-top">
    <a class="navbar-brand" href="#">Genie: Edition Membre</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <?php $treeId = htmlspecialchars($member['family_tree_id']) ?>

    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li><a class="nav-link" href="index.php?action=add_member&tree_id=<?php echo $treeId; ?>">Nouveau Membre</a></li>
            <li><a class="nav-link" href="index.php?action=edit_tree&tree_id=<?php echo $treeId; ?>">Liste</a></li>
            <li><a class="nav-link" href="index.php?action=view_tree&tree_id=<?php echo $treeId; ?>">Visualiser</a></li>
            <li><a class="nav-link" href="index.php?action=list_trees">Arbres</a></li>

        </ul>
    </div>
</nav>

<!-- Main content -->
<div class="row mt-5">
<div class="col-lg-4 mb-4">
    <div class="card">
        <div class="card-header">
            Détails Membre
        </div>
    <div class="card-body">

    <?php if (isset($error)) : ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form id="edit-member-form" method="post" action="">
        <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($member['id']); ?>">

        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($member['first_name']); ?>" required><br>

        <label for="middle_name">Middle Name:</label>
        <input type="text" name="middle_name" id="middle_name" value="<?php echo htmlspecialchars($member['middle_name']); ?>"><br>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($member['last_name']); ?>"><br>

        <label for="date_of_birth">Date of Birth:</label>
        <input type="date" name="date_of_birth" id="date_of_birth" value="<?php echo htmlspecialchars($member['date_of_birth']); ?>"><br>

        <label for="place_of_birth">Place of Birth:</label>
        <input type="text" name="place_of_birth" id="place_of_birth" value="<?php echo htmlspecialchars($member['place_of_birth']); ?>"><br>


        <label for="gender_id">Gender:</label>
        <select name="gender_id" id="gender_id">
            <option value="1" <?php if ($member['gender_id'] == 1) echo 'selected'; ?>>Homme</option>
            <option value="2" <?php if ($member['gender_id'] == 2) echo 'selected'; ?>>Femme</option>
            <!-- Add more options as needed -->
        </select><br>



        <div id="additional-fields" style="display: none;">
            <label for="alias1">Title:</label>
            <input type="text" name="title" id="alias1" value="<?php echo htmlspecialchars($member['title']); ?>"><br>
            <label for="alias1">Alias1:</label>
            <input type="text" name="alias1" id="alias1" value="<?php echo htmlspecialchars($member['alias1']); ?>"><br>
            <label for="alias2">Alias2:</label>
            <input type="text" name="alias2" id="alias2" value="<?php echo htmlspecialchars($member['alias2']); ?>"><br>
            <label for="alias3">Alias3:</label>
            <input type="text" name="alias3" id="alias3" value="<?php echo htmlspecialchars($member['alias3']); ?>"><br>
            <label for="body">Details</label>
            <textarea id="body" name=body cols=50 rows=10><?php echo htmlspecialchars($member['body']); ?></textarea>
            <br />
            <label for="date_of_death">Date of Death:</label>
            <input type="date" name="date_of_death" id="date_of_death" value="<?php echo htmlspecialchars($member['date_of_death']); ?>"><br>

            <label for="place_of_death">Place of Death:</label>
            <input type="text" name="place_of_death" id="place_of_death" value="<?php echo htmlspecialchars($member['place_of_death']); ?>"><br>

        </div>
        <br />

        <button type="submit">Update Member</button> <button type="button" id="toggle-fields-btn">More</button>
    </form>
    </div>

</div>
</div>
<div class="col-lg-4 mb-4">
    <div class="card">
        <div class="card-header">
            Relations Existantes
        </div>
        <div class="card-body">

        <div id="relationships">
            <table class="relationship-table">
                <tr>
                    <th>Person 1</th>
                    <th>Person 2</th>
                    <th>Type</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Actions</th>
                </tr>
                <tbody id="relationships-table-body">
                    <!-- Relationships will be dynamically filled via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<div class="col-lg-4 mb-4">
    <div class="card">
        <div class="card-header">
            Changements
        </div>
        <div class="card-body">

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
            <input type="date" id="edit_relation_start" name="relation_start"><br>
            <input type="date" id="edit_relation_end" name="relation_end"><br>

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
        <button type="submit">🗑️ Delete</button>
    </form>
    </div>
    </div>
</div>
    <!-- External JavaScript file -->
    <script>
        var memberId = <?php echo json_encode($member['id']); ?>; // Pass member ID to JavaScript
        const treeId = <?php echo json_encode($member['family_tree_id']); ?>; // Pass member ID to JavaScript

        document.addEventListener('DOMContentLoaded', function() {
            var script = document.createElement('script');
            script.src = 'res/relationships.js?ver=1';

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

        document.getElementById('toggle-fields-btn').addEventListener('click', function() {
            var additionalFields = document.getElementById('additional-fields');
            if (additionalFields.style.display === 'none') {
                additionalFields.style.display = 'block';
                this.textContent = 'Less Fields';
            } else {
                additionalFields.style.display = 'none';
                this.textContent = 'More Fields';
            }
        });
    </script>
</body>

</html>