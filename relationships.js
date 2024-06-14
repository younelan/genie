function initializeRelationships(memberId) {
    $(function() {
        // Autocomplete setup for person field
        $("#autocomplete_member").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "index.php?action=autocomplete_member&tree_id="+treeId,
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.label,
                                value: item.id
                            };
                        }));
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                console.log(ui.item)
                $('#autocomplete_member').val(ui.item.label); // Set selected member's name
                $('#member_id').val(ui.item.value); // Store selected member's ID
                return false;
            }
        });

        // Load relationship types for the select dropdown
        $.ajax({
            url: "index.php?action=get_relationship_types",
            dataType: "json",
            success: function(data) {
                var optionsHtml = '';
                $.each(data, function(index, relationshipType) {
                    optionsHtml += '<option value="' + relationshipType.id + '">' + relationshipType.description + '</option>';
                });
                $('#relationship_type, #edit_relationship_type').html(optionsHtml);
            }
        });

        // Handle click event for add relationship button
        $('#add-relationship-btn').click(function() {
            var formData = $('#add-relationship-form').serialize();
            $.post('index.php?action=add_relationship', formData, function(response) {
                if (response.success) {
                    $('#add-relationship-form')[0].reset(); // Clear form
                    loadRelationships(memberId); // Reload relationships after addition
                } else {
                    alert('Failed to add relationship.');
                }
            }, 'json');
        });

        // Function to load relationships dynamically using AJAX
        function loadRelationships(memberId) {
            $.get('index.php?action=get_relationships&member_id=' + memberId, function(data) {
                var relationshipsHtml = '';
                $.each(data, function(index, relationship) {
                    relationshipsHtml += '<tr>';
                    relationshipsHtml += '<td><a href="index.php?action=view_member&member_id=' + relationship.person1_id + '">' + relationship.person1_first_name + ' ' + relationship.person1_last_name + '</a></td>';
                    relationshipsHtml += '<td><a href="index.php?action=view_member&member_id=' + relationship.person2_id + '">' + relationship.person2_first_name + ' ' + relationship.person2_last_name + '</a></td>';
                    relationshipsHtml += '<td>' + relationship.relationship_description + '</td>';
                    relationshipsHtml += '<td>';
                    relationshipsHtml += '<form class="delete-relationship-form" method="post" style="display:inline;">';
                    relationshipsHtml += '<input type="hidden" name="relationship_id" value="' + relationship.id + '">';
                    relationshipsHtml += '<button type="submit">Delete</button>';
                    relationshipsHtml += '</form>';
                    relationshipsHtml += '<button type="button" class="edit-relationship-btn" data-relationship-id="' + relationship.id + '" data-person1="' + relationship.person1_first_name + ' ' + relationship.person1_last_name + '" data-person2="' + relationship.person2_first_name + ' ' + relationship.person2_last_name + '" data-relationship-type="' + relationship.relationship_type + '">Edit</button>';
                    relationshipsHtml += '</td>';
                    relationshipsHtml += '</tr>';
                });
                $('#relationships-table-body').html(relationshipsHtml); // Update relationships table body

                // Attach event listeners for edit and delete buttons
                $('.edit-relationship-btn').click(function() {
                    var relationshipId = $(this).data('relationship-id');
                    var person1 = $(this).data('person1');
                    var person2 = $(this).data('person2');
                    var relationshipType = $(this).data('relationship-type');

                    $('#edit_relationship_id').val(relationshipId);
                    $('#edit_relationship_person1').val(person1);
                    $('#edit_relationship_person2').val(person2);
                    $('#edit_relationship_type').val(relationshipType);

                    $('#edit-relationship-modal').show();
                });

                $('.delete-relationship-form').submit(function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to delete this relationship?')) {
                        var formData = $(this).serialize();
                        $.post('index.php?action=delete_relationship', formData, function(response) {
                            if (response.success) {
                                loadRelationships(memberId); // Reload relationships after deletion
                            } else {
                                alert('Failed to delete relationship.');
                            }
                        }, 'json');
                    }
                });
            }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Error loading relationships:', textStatus, errorThrown);
                alert('Failed to load relationships.');
            });
        }

        // Handle click event for update relationship button
        $('#update-relationship-btn').click(function() {
            var formData = $('#edit-relationship-form').serialize();
            $.post('index.php?action=update_relationship', formData, function(response) {
                if (response.success) {
                    $('#edit-relationship-modal').hide();
                    loadRelationships(memberId); // Reload relationships after update
                } else {
                    alert('Failed to update relationship.');
                }
            }, 'json');
        });

        // Initial load of relationships
        loadRelationships(memberId);
    });
}
