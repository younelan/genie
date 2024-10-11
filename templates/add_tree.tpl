<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo get_translation("Add New Family Tree");?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="res/style.css">

</head>

<body>
    <h1><?php echo get_translation("Add New Family Tree");?></h1>
    <form action="index.php?action=add_tree" method="post">
        <label for="name"><?php echo get_translation("Tree Name");?>:</label><br>
        <input type="text" id="name" name="name" required><br><br>

        <label for="description"><?php echo get_translation("Description");?>:</label><br>
        <textarea id="description" name="description" rows="4" required></textarea><br><br>

        <button type="submit"><?php echo get_translation("Add Tree");?></button>
    </form>
    <br>
    <a href="index.php?action=list_trees"><?php echo get_translation("Back to List");?></a>
</body>

</html>