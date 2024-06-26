<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Membres Famille</title>
    <script src="res/jquery-3.6.0.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="res/style.css"> 

</head>
<body>
    <h1>Membres Famille</h1>
<div class='nav'>
<ul class='nav-ul'>
<li><a href="index.php?action=add_member&tree_id=<?php echo $treeId; ?>">Nouveau Membre</a></li>
<li><a href="index.php?action=view_tree&tree_id=<?php echo $treeId; ?>">Visualiser</a></li>
<li><a href="index.php?action=list_trees">Arbres</a></li>
</ul>
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
<div class='treecount'>Membres: <?php echo $totalMembers ?> 
(<?php foreach($countByGender as $idx=>$val) echo "$idx: $val "; ?> ) 
<div>Relations: <?php echo $totalRelationships ?><div></div>
<input type="text" id="search" placeholder="Chercher par nom...">

</div>

    <table id="members-list">
        <?php foreach ($members as $member): ?>
            <tr>
                <td>
                <a href="index.php?action=edit_member&member_id=<?php echo $member['id']; ?>">
                <?php echo getGenderSymbol($member['gender_id']) ?>
                    <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                </a>
        </td><td>
            <!--
                <form method="get" class='edit-member-form' action="index.php?action=edit_member&member_id="<?php echo $member['id']; ?>>
                <input type='hidden' name='action' value='edit_member'>   
                <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                    <button type="submit">✏️ Edit</button>
                </form>
                <form method="post" class='delete-member-form' action="index.php?action=delete_member">
                    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                    <button type="submit">🗑️ Delete</button>
                </form> -->
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
                    <button type="submit">🗑️ Delete</button>
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
