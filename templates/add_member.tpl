
    <style>
        .error {
            color: red;
        }
    </style>

<body>
    <h1>{{get_translation("Add New Member")}}</h1>
    {% if error is defined %}
        <p class='error'>{{ error }}</p>
    {% endif %}
    <form method="post" action="">
        <label for="first_name">{{get_translation("First Name")}}:</label>
        <input type="text" name="first_name" id="first_name" required><br>

        <label for="last_name">{{get_translation("Last Name")}}:</label>
        <input type="text" name="last_name" id="last_name" required><br>

        <label for="date_of_birth">{{get_translation("Date of Birth")}}:</label>
        <input type="date" name="date_of_birth" id="date_of_birth"><br>

        <label for="place_of_birth">{{get_translation("Place of Birth")}}:</label>
        <input type="text" name="place_of_birth" id="place_of_birth"><br>

        <label for="gender_id">{{get_translation("Gender")}}:</label>
        <select name="gender_id" id="gender_id" required>
            <option value="1">{{get_translation("Man")}}</option>
            <option value="2">{{get_translation("Woman")}}</option>
            <!-- Add more genders as needed -->
        </select><br>

        <button type="submit">{{get_translation("Add New Member")}}</button>
    </form>
    <br>
    <a href="index.php?action=list_members&tree_id={{ treeId }}">{{get_translation("Back to List")}}</a>
</body>

</html>
