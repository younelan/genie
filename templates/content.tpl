<!DOCTYPE html>
<html>
<head>
    <title>{{ app_title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="res/vendor/tailwind/tailwind.js"></script>
    <link href="res/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
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
    <!-- Replace CDN D3 with local version -->
    <!-- Move D3 before all component scripts -->
    <script src="res/vendor/d3/d3.min.js"></script>
</head>
<body>
    <div id="root"></div>
    <script>
        // Add D3 check
        if (typeof d3 === 'undefined') {
            console.error('D3 library failed to load!');
        }
        // Pass PHP variables to JavaScript
        window.appTitle = "{{ app_title }}";
        window.appLogo = "{{ app_logo }}";
        window.footerText = "{{ footer_text }}";
        window.companyName = "{{ company_name }}";
        window.section = "{{ section }}";
    </script>
    <script src="res/vendor/react/react.development.js"></script>
    <script src="res/vendor/react/react-dom.development.js"></script>
    <!-- Update React Bootstrap path -->
    <script src="res/vendor/react-bootstrap/dist/react-bootstrap.min.js"></script>
    <!-- Move global declarations before component scripts -->
    <script>
        window.ReactBootstrapComponents = ReactBootstrap;
        const { 
            Card, 
            ListGroup, 
            Container, 
            Row, 
            Col, 
            Nav, 
            Modal, 
            Button, 
            Form,
            Dropdown: RBDropdown, // Rename to avoid conflict
            DropdownButton,
            ButtonGroup,
            Alert
        } = ReactBootstrap;
    </script>
    <!-- Load custom components after global declarations -->
    <script src="res/js/components/Dropdown.js"></script>
    <script src="res/js/components/Navigation.js"></script>
    <script src="res/js/components/TagInput.js"></script>
    <script src="res/js/components/Autocomplete.js"></script>
    <script src="res/js/components/ErrorBoundary.js"></script>
    <script src="res/js/components/RelationshipModal.js"></script>
    <script src="res/js/components/EditOtherRelationship.js"></script>
    <script src="res/js/components/AddSpouseModal.js"></script>
    <!-- Load views last -->
    <script src="res/js/members/MemberDetails.js"></script>
    <script src="res/js/members/MembersList.js"></script>
    <script src="res/js/trees/TreeList.js"></script>
    <script src="res/js/trees/FamilyTreeVisualization.js"></script>
    <script src="res/js/members/DescendantsView.js"></script>
    <script src="res/js/trees/EditTree.js"></script>
    <script src="res/js/members/AddMember.js"></script>
    <script src="res/js/app.js"></script>
</body>
</html>
