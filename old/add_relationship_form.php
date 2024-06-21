<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Relationship</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <h1>Add Relationship</h1>
    <form id="addRelationshipForm" method="post">
        <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($_GET['member_id']); ?>">
        <label for="person_id">Select Person:</label>
        <input type="text" id="personInput" name="person_id" placeholder="Type to search..." required>
        
        <label for="relationship_type">Relationship Type:</label>
        <select name="relationship_type" id="relationshipTypeSelect" required>
            <?php foreach ($relationshipTypes as $type): ?>
                <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['description']); ?></option>
            <?php endforeach; ?>
        </select><br>
        
        <button type="submit">Add Relationship</button>
    </form>

    <script>
    $(function() {
        // Autocomplete for person input
        $('#personInput').autocomplete({
            source: 'autocomplete_member.php', // Adjust URL as needed
            minLength: 2,
            select: function(event, ui) {
                $(this).val(ui.item.label);
                $('#personIdHidden').val(ui.item.id);
                return false;
            }
        });
    });
    </script>
</body>
</html>

