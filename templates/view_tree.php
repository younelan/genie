<!-- views/view_tree.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Tree</title>
    <script src="https://d3js.org/d3.v6.min.js"></script>
    <link rel="stylesheet" href="themes/bootstrap/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.slim.min.js"></script>
    <script src="themes/bootstrap/js/bootstrap.min.js"></script>
    <script src="themes/bootstrap/js/popper.min.js"></script>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="res/style.css?Version=1">

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
<nav class="navbar navbar-expand-md navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="?">
                <img src="res/genie.png" height="40" width="auto" alt="Genie"/>&nbsp; Genie: Edition Membre 
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <?php $treeId = htmlspecialchars($_GET['family_tree_id'] ?? $_GET['tree_id']); ?>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=add_member&tree_id=<?php echo $treeId; ?>">Nouveau Membre</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=edit_tree&tree_id=<?php echo $treeId; ?>">Liste</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=list_trees">Arbres</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <a href="index.php?action=edit_tree&tree_id=<?php echo htmlspecialchars($_GET['family_tree_id'] ?? $_GET['tree_id']); ?>">Back to Home</a>
    <svg width="2960" height="2600"></svg>
    <script>
        const familyTreeId = <?php echo htmlspecialchars($_GET['family_tree_id'] ?? $_GET['tree_id']); ?>;
    </script>
    <script src="res/tree.js"></script>
</body>

</html>