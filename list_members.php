<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>List Family Tree Members</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> 

</head>
<body>
    <h1>Members of Family Tree</h1>
<style>
    .edit-member-form, .delete-member-form {
        display: inline-block;
    }
    </style>
    <input type="text" id="search" placeholder="Search members by name...">
    <table id="members-list">
        <?php foreach ($members as $member): ?>
            <tr>
                <td>
                <a href="index.php?action=edit_member&member_id=<?php echo $member['id']; ?>">
                    <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                </a>
        </td><td>
                <form method="get" class='edit-member-form' action="index.php?action=edit_member&member_id="<?php echo $member['id']; ?>>
                <input type='hidden' name='action' value='edit_member'>   
                <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                    <button type="submit">Edit</button>
                </form>
                <form method="post" class='delete-member-form' action="index.php?action=delete_member">
                    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                    <button type="submit">Delete</button>
                </form>
        </td>
        </tr>
        <?php endforeach; ?>
        </table>

    <?php if ($totalPages > 1): ?>
        <style>
            .pagination li {
                display: inline-block;
            }
        </style>
        <nav>
            <ul class='pagination'>
                Pages <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li><a href="index.php?action=list_members&tree_id=<?php echo $treeId; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
    <script>
        $(document).ready(function() {
            // Handle delete tree form submission with confirmation
            $('.delete-member-form').submit(function(event) {
                if (!confirm('Are you sure you want to delete this tree?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
    <br>
    <a href="index.php?action=add_member&tree_id=<?php echo $treeId; ?>">Add New Member</a>
    <br>
    <a href="index.php?action=list_trees">Back to List</a>

    <script>
        $(document).ready(function() {
            $('#search').on('input', function() {
                var query = $(this).val();
                var treeId = <?php echo $treeId; ?>;
                $.ajax({
                    url: 'index.php?action=search_members',
                    type: 'GET',
                    data: {
                        tree_id: treeId,
                        query: query
                    },
                    success: function(response) {
                        var members = JSON.parse(response);
                        var membersList = $('#members-list');
                        membersList.empty();
                        members.forEach(function(member) {
                            var listItem = $('<li></li>');
                            var link = $('<a></a>').attr('href', 'index.php?action=edit_member&member_id=' + member.id).text(member.first_name + ' ' + member.last_name);
                            listItem.append(link);
                            membersList.append(listItem);
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
