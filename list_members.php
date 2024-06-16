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
    <h1>Family Members</h1>
<style>
    .edit-member-form, .delete-member-form {
        display: inline-block;
    }
    h1 { margin-bottom: 3px}
    .neav {display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px}
    .nav ul {list-style-type: none; margin: 0; display:block; padding: 0; overflow: hidden; background-color: #c1d0d5;margin-bottom:5px}
    .nav li {display:inline-block}
    .nav a {display: inline-block; padding: 4px;  color: #333; text-decoration: none; margin-right: 5px;}
    .nav a:hover {background-color: #f2f2f2;color: red;}
    </style>
<div class='nav'>
<ul class='nav-ul'>
<li><a href="index.php?action=add_member&tree_id=<?php echo $treeId; ?>">New Member</a></li>
<li><a href="index.php?action=view_tree&tree_id=<?php echo $treeId; ?>">Visualize</a></li>
<li><a href="index.php?action=list_trees">Tree List</a></li>
</ul>
<input type="text" id="search" placeholder="Search members by name...">

</div>
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
    <?php if($treeId) : ?>
    <form action="?action=delete_tree" method="post" class="delete-tree-form" style="display: inline;">
                <input type="hidden" name="action" value="delete_tree">
                <input type="hidden" name="tree_id" value="<?php echo $treeId; ?>">
                    <button type="submit">Delete</button>
        </form>

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
</body>
</html>
