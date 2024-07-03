<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edition Membre</title>
    <script src="themes/bootstrap/js/jquery-3.7.0.min.js"></script>
    <script src="themes/bootstrap/js/popper.min.js"></script>
    <script src="themes/bootstrap/js/bootstrap.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="res/style.css?Version=1">
    <link rel="stylesheet" href="themes/bootstrap/css/bootstrap.min.css">

    <title>Genie: Membres Famille</title>
    <!-- Bootstrap CSS -->
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
        li {
        list-style-type: none
    }

    .tree-list {
        font-size: 1.5em;
        padding: 20px;
        text-align: center;
    }
    </style>
</head>
<body>
<div class="container-fluid py-4">
<nav class="navbar navbar-expand-md navbar-dark fixed-top">
        <img src="res/genie.png" height="40" width="auto" alt="Genie"/> &nbsp;

            <a class="navbar-brand" href="?">Genie: Arbres Généalogiques</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li><a class="nav-link" href="index.php?action=add_tree">Nouvel Arbre</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <div class="row mt-5">


    <ul class=tree-list>
        <?php foreach ($trees as $tree) : ?>
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