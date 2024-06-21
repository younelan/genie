<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Family Tree</title>
</head>
<body>
    <h1>Add New Family Tree</h1>
    <form action="add_tree.php?action=add_tree" method="post">
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" required><br><br>
        
        <label for="description">Description:</label><br>
        <textarea id="description" name="description" rows="4" required></textarea><br><br>
        
        <button type="submit">Add Tree</button>
    </form>
    <br>
    <a href="index.php?action=list_trees">Back to List</a>
</body>
</html>

