<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="res/genie.gif">
    <title>{{app_title}}: {{section}}</title>
    <script src="https://d3js.org/d3.v6.min.js"></script>
    <script src="themes/bootstrap/js/jquery-3.7.0.min.js"></script>
    <script src="themes/bootstrap/js/popper.min.js"></script>
    <script src="themes/bootstrap/js/bootstrap.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="themes/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="res/style.css?Version=1.0.1">
</head>
<body>
    <div class="container-fluid py-4">
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
        <div class="main-content row mt-5">
            {{content|raw}}
        </div>
    </div>
<div class="navbar fixed-bottom footer">
    &copy; Opensitez Genie (c) 2025
    </div>
</body>
</html>
