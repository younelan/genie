<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>List Family Trees</title>
</head>
<body>
    <h1>List of Your Family Trees</h1>
    <ul>
        <?php foreach ($trees as $tree): ?>
            <li>
                <?php echo htmlspecialchars($tree['name']); ?>
                <form action="delete_tree.php?action=delete_tree" method="post">
                    <input type="hidden" name="tree_id" value="<?php echo $tree['id']; ?>">
                    <button type="submit">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="add_tree.php?action=add_tree">Add New Tree</a>
</body>
</html>

