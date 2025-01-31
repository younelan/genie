<!DOCTYPE html>
<html>
<head>
    <title>{{ app_title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
        }
        .hover-shadow:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
            transition: all .2s ease-in-out;
        }
        .card {
            transition: all .2s ease-in-out;
            background: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body>
    <div id="root"></div>
    <!-- React -->
    <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <!-- Our App -->
    <script src="res/js/trees/TreeList.js"></script>
</body>
</html>
