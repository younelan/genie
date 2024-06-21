<table border="1">
    <tr>
        <th>Person 1</th>
        <th>Person 2</th>
        <th>Relationship Type</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($relationships as $relationship): ?>
    <tr>
        <td><?php echo htmlspecialchars($relationship['person1_name']); ?></td>
        <td><?php echo htmlspecialchars($relationship['person2_name']); ?></td>
        <td><?php echo htmlspecialchars($relationship['description']); ?></td>
        <td>
            <form class="delete-relationship-form" method="post" style="display:inline;">
                <input type="hidden" name="relationship_id" value="<?php echo $relationship['id']; ?>">
                <button type="submit">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

