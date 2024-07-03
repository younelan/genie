<!DOCTYPE html>
<html lang="en">
<head>
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
            <a class="navbar-brand" href="#">Genie: Membres Famille</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li><a class="nav-link" href="index.php?action=add_member&tree_id=<?php echo $treeId; ?>">Nouveau Membre</a></li>
                    <li><a class="nav-link" href="index.php?action=view_tree&tree_id=<?php echo $treeId; ?>">Visualiser</a></li>
                    <li><a class="nav-link" href="index.php?action=list_trees">Arbres</a></li>
<!--
                    <li class="nav-item">
                        <a class="nav-link" href="#">Statistics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Settings</a>
                    </li>
    -->
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <div class="row mt-5">

            <!-- People list -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        People in the Tree
                    </div>
                    <div class="card-body">

                    <?php if ($totalPages > 1) : ?>
                        <style>
                            .pagination li {
                                display: inline-block;
                            }
                        </style>
                        <nav>
                            <ul class='pagination'>
                                <b>Pages: &nbsp;</b> <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                                    <li><a href="index.php?action=list_members&tree_id=<?php echo $treeId; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>&nbsp;
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    <input type="text" id="search" placeholder="Chercher par nom...">
                        <div class="list-group">
                            <?php foreach ($members as $member) : ?>
                                <a class="list-group-item list-group-item-action" href="index.php?action=edit_member&member_id=<?php echo $member['id']; ?>">
                                    <?php echo getGenderSymbol($member['gender_id']) ?>
                                    <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                </a>
                            <?php endforeach; ?>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        Statistiques
                    </div>
                    <div class="card-body">
                        <?php foreach($stats as $blockname=>$statblock) : ?>
                            <div><?php echo $blockname ?></div>
                            <ul class="list-group">
                            <?php foreach ($statblock as $idx => $val): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo "$idx "; ?> 
                                <span class="badge badge-primary badge-pill"><?php echo $val ?></span>
                                </li>
                            <?php endforeach; ?> 
                            </ul>
                        <?php endforeach; ?> 
                    </div>
                </div>
            </div>

            <!-- Widgets -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        Activités Recentes
                    </div>
                    <div class="card-body">
                            <ul class="list-group">
                            <?php foreach ($activities as $idx => $val): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo "$idx "; ?> 
                                <span class="badge badge-primary badge-pill"><?php echo $val ?></span>
                                </li>
                            <?php endforeach; ?> 
                            </ul>


                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        Evenements
                    </div>
                    <div class="card-body">
                    <ul class="list-group">
                            <?php foreach ($events as $idx => $val): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo "$idx "; ?> 
                                <span class="badge badge-primary badge-pill"><?php echo $val ?></span>
                                </li>
                            <?php endforeach; ?> 
                            </ul>

                    
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
