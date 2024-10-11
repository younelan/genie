<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{app_title}}: {{section}}</title>
    <script src="https://d3js.org/d3.v6.min.js"></script>
    <script src="themes/bootstrap/js/jquery-3.7.0.min.js"></script>
    <script src="themes/bootstrap/js/popper.min.js"></script>
    <script src="themes/bootstrap/js/bootstrap.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="res/style.css?Version=1">
    <link rel="stylesheet" href="themes/bootstrap/css/bootstrap.min.css">

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
    .tree-list {
        font-size: 2.5em;
        padding: 20px;
        text-align: center;
    
    }

    </style>
</head>
<body>

    <div class="container-fluid py-4">

        <!-- Navigation menu -->
        <nav class="navbar navbar-expand-md navbar-dark fixed-top">
        <img src="res/genie.png" height="40" width="auto" alt="Genie"/> &nbsp;

            <a class="navbar-brand" href="?">{{app_title}}: {{section}}</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    {% for key,item in menu %}
                    <li><a class="nav-link" href="{{item.link|raw}}">{{item.text|raw}}</a></li>
                    {% endfor %}
                </ul>
            </div>
        </nav>

    {{content|raw}}
    </div>
</body>
</html>
