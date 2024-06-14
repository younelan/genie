<!-- views/view_tree.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Tree</title>
    <script src="https://d3js.org/d3.v6.min.js"></script>
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
    <svg width="960" height="600"></svg>
    <script>
        const familyTreeId = <?php echo htmlspecialchars($_GET['family_tree_id']??$_GET['tree_id']); ?>;
    </script>
    <script src="tree.js"></script>
</body>
</html>
