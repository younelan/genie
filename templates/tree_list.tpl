

    <ul class=tree-list>
        {% for tree in trees  %}
            <li>
                <a href=?action=edit_tree&tree_id={{tree.id}}>
                    {{tree.name}}
                </a>
                <!--
                <form action="?action=delete_tree" method="post" class="delete-tree-form" style="display: inline;">
                <input type="hidden" name="action" value="delete_tree">
                <input type="hidden" name="tree_id" value="{{tree.id}}">
                    <button type="submit">Delete</button>
                </form>
                <form action="?action=edit_tree" method="get" style="display: inline;">
                <input type="hidden" name="action" value="edit_tree">
                <input type="hidden" name="tree_id" value="{{tree.id}}">
                    <button type="submit">Edit</button>
                </form>
                <form action="?action=view_tree&tree_id={{tree.id}}" method="get" style="display: inline;">
                <input type="hidden" name="action" value="view_tree">
                <input type="hidden" name="tree_id" value={{tree.id}}">
                    <button type="submit">View</button>
                </form> -->

            </li>
        {% endfor %}
    </ul>


    <script>
        $(document).ready(function() {
            // Handle delete tree form submission with confirmation
            $('.delete-tree-form').submit(function(event) {
                if (!confirm('Are you sure you want to delete this tree?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
