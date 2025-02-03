    <style>
        .graph {
            overflow-x: scroll;
        }
    </style>
    <a href="index.php?action=edit_tree&tree_id={{tree_id}}">{{go_home}}</a>
    <div class="graph">
    <svg width="{{ graph['width'] }}" height="{{ graph['height'] }}"></svg>
    </div>
    <script>
        const familyTreeId = {{tree_id}};
        const graphWidth = {{ graph['width'] }};
        const graphHeight = {{ graph['height'] }};

    </script>
    <script src="res/tree.js?version=1.2"></script>
