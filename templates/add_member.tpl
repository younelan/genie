<h1>{{get_translation("Add New Member")}}</h1>
    {% if error is defined %}
        <p class='error'>{{ error }}</p>
    {% endif %}
    <form method="post" action="">
        <label for="first_name">{{get_translation("First Name")}}:</label>
        <input type="text" name="first_name" id="first_name" required><br>

        <label for="last_name">{{get_translation("Last Name")}}:</label>
        <input type="text" name="last_name" id="last_name" required><br>

        <label for="birth_date">{{get_translation("Date of Birth")}}:</label>
        <input type="date" name="birth_date" id="birth_date"><br>

        <label for="birth_place">{{get_translation("Place of Birth")}}:</label>
        <input type="text" name="birth_place" id="birth_place"><br>

        <label for="gender">{{get_translation("Gender")}}:</label>
        <select name="gender" id="gender" required>
            <option value="1">{{get_translation("Man")}}</option>
            <option value="2">{{get_translation("Woman")}}</option>
            <!-- Add more genders as needed -->
        </select><br>

        <button type="submit">{{get_translation("Add New Member")}}</button>
    </form>
    <br>
    <a href="index.php?action=list_members&tree_id={{ treeId }}">{{get_translation("Back to List")}}</a>

