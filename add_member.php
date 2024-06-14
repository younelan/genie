<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Member</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> 

</head>
<body>
    <h1>Add New Member</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" id="first_name" required><br>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" id="last_name" required><br>

        <label for="date_of_birth">Date of Birth:</label>
        <input type="date" name="date_of_birth" id="date_of_birth"><br>

        <label for="place_of_birth">Place of Birth:</label>
        <input type="text" name="place_of_birth" id="place_of_birth"><br>

        <label for="gender_id">Gender:</label>
        <select name="gender_id" id="gender_id" required>
            <option value="1">Male</option>
            <option value="2">Female</option>
            <!-- Add more genders as needed -->
        </select><br>

        <button type="submit">Add Member</button>
    </form>
    <br>
    <a href="index.php?action=list_members&tree_id=<?php echo $treeId; ?>">Back to List</a>
</body>
</html>
