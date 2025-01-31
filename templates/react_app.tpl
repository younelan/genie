<!DOCTYPE html>
<html>
<head>
    <title>{{ app_title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, sans-serif;
        }
    </style>
</head>
<body>
    <div id="root"></div>
    <script>
        // Pass PHP variables to JavaScript
        window.appTitle = "{{ app_title }}";
        window.appLogo = "{{ app_logo }}";
        window.footerText = "{{ footer_text }}";
        window.companyName = "{{ company_name }}";
        window.section = "{{ section }}";
    </script>
    <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="res/js/trees/TreeList.js"></script>
</body>
</html>
