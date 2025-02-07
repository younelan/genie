<!DOCTYPE html>
<html>
<head>
    <title>{{ app_title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add Bootstrap CSS for Card, ListGroup components -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
.card-header {
 background-color: #1e0a76;
color: white;
font-weight: 500;
font-size: 1.2em;
}
.ceard-body {
background: #a7a2c5
}
.bg-primary {
background: rgb(94 9 113) !important
}
.list-group-item {
background: #dcd8e0;
color: #2c0a2a;
}
        body {
background: linear-gradient(135deg, #576088 0%, #c1cde5 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, sans-serif;
        }
    </style>
    <script src="https://d3js.org/d3.v7.min.js"></script>
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
    <!-- Include React Bootstrap components -->
    <script src="https://cdn.jsdelivr.net/npm/react-bootstrap@2.0.0/dist/react-bootstrap.min.js"></script>
    <script>
        // Make React Bootstrap components available globally
        const { Card, ListGroup, Container, Row, Col, Nav } = ReactBootstrap;
    </script>
    <script src="res/js/members/MemberDetails.js"></script>
    <script src="res/js/members/MembersList.js"></script>
    <script src="res/js/trees/TreeList.js"></script>
    <script src="res/js/trees/FamilyTreeVisualization.js"></script>
    <script src="res/js/components/ErrorBoundary.js"></script>
    <script src="res/js/members/DescendantsView.js"></script>
    <script src="res/js/trees/EditTree.js"></script>
    <script src="res/js/members/AddMember.js"></script>
    <script src="res/js/app.js"></script>
    <script src="res/js/components/TagInput.js"></script>
    <script src="res/js/components/Dropdown.js"></script>
    <script src="res/js/components/Navigation.js"></script>
    <script src="res/js/components/Autocomplete.js"></script>
    <script src="res/js/components/RelationshipModal.js"></script>
    <script src="res/js/components/EditOtherRelationship.js"></script>
    <script src="res/js/components/AddSpouseModal.js"></script>
</body>
</html>
