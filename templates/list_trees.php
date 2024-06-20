<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>List Family Trees</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="res/style.css"> 
</head>
<style>
    li {list-style-type: none}
    .tree-list {font-size: 1.5em;padding:20px;text-align: center;}
</style>
<body>
    <h1>Family Trees</h1>
    <ul class=tree-list>
        <?php foreach ($trees as $tree): ?>
            <li>
                <a href=?action=edit_tree&tree_id=<?php echo htmlspecialchars($tree['id']); ?>>
                <?php echo htmlspecialchars($tree['name']); ?>
                </a>
                <!--
                <form action="?action=delete_tree" method="post" class="delete-tree-form" style="display: inline;">
                <input type="hidden" name="action" value="delete_tree">
                <input type="hidden" name="tree_id" value="<?php echo $tree['id']; ?>">
                    <button type="submit">Delete</button>
                </form>
                <form action="?action=edit_tree" method="get" style="display: inline;">
                <input type="hidden" name="action" value="edit_tree">
                <input type="hidden" name="tree_id" value="<?php echo $tree['id']; ?>">
                    <button type="submit">Edit</button>
                </form>
                <form action="?action=view_tree&tree_id=<?php echo $tree['id']; ?>" method="get" style="display: inline;">
                <input type="hidden" name="action" value="view_tree">
                <input type="hidden" name="tree_id" value="<?php echo $tree['id']; ?>">
                    <button type="submit">View</button>
                </form> -->

            </li>
        <?php endforeach; ?>
    </ul>
    <form action="index.php?action=add_tree" method="get">
    <input type="hidden" name="action" value="add_tree">
        <button type="submit">Add New Tree</button>
    </form>

    <script>
        $(document).ready(function() {
            // Handle delete tree form submission with confirmation
            $('.delete-tree-form').submit(function(event) {
                if (!confirm('Are you sure you want to delete this tree?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
