        <div class="row mt-5">

    <form action="index.php?action=add_tree" method="post">
        <label for="name">{{tree_name}}:</label><br>
        <input type="text" id="name" name="name" required><br><br>

        <label for="description">{{tree_description}}:</label><br>
        <textarea id="description" name="description" rows="4" required></textarea><br><br>

        <button type="submit">{{section}}</button>
    </form>
    <br>
    <a href="index.php?action=list_trees">{{go_back}}</a>
