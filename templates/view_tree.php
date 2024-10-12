
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


    <a href="index.php?action=edit_tree&tree_id={{tree_id}}>{{go_home}}</a>
    <svg width="{{ graph['width'] }}" height="{{ graph['height'] }}"></svg>
    <script>
        const familyTreeId = {{tree_id}};
        const graphWidth = {{ graph['width'] }};
        const graphHeight = {{ graph['height'] }};

    </script>
    <script src="res/tree.js?version=1.2"></script>
