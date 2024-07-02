<!-- views/view_tree.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Tree</title>
    <script src="https://d3js.org/d3.v6.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="res/style.css">

    <style>
        .node circle {
            fill: #eaf5c5;
            stroke: #b02f3b;
            stroke-width: 2px;
        }

        .node text {
            font: 12px sans-serif;
            color: red;
        }

        .link {
            fill: #ffc;
            stroke: #555;
            stroke-opacity: 0.4;
            stroke-width: 1.5px;
        }
    </style>
</head>

<body>
    <h1>Interactive Hierarchical Tree</h1>
    <?php $treeId = htmlspecialchars($_GET['family_tree_id'] ?? $_GET['tree_id']) ?>

    <div class='nav'>
        <ul class='nav-ul'>
            <li><a href="index.php?action=add_member&tree_id=<?php echo $treeId; ?>">New Member</a></li>
            <li><a href="index.php?action=edit_tree&tree_id=<?php echo $treeId; ?>">Edit Tree</a></li>
            <li><a href="index.php?action=list_trees">Tree List</a></li>
        </ul>

    </div>
    <a href="index.php?action=edit_tree&tree_id=<?php echo htmlspecialchars($_GET['family_tree_id'] ?? $_GET['tree_id']); ?>">Back to Home</a>
    <svg width="2960" height="2600"></svg>
    <script>
        const familyTreeId = <?php echo htmlspecialchars($_GET['family_tree_id'] ?? $_GET['tree_id']); ?>;
    </script>
    <script src="res/tree.js"></script>
</body>

</html>