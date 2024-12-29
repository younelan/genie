$(document).ready(function () {
});



function initializeRelationships(memberId) {
    $(function () {
        //new add relationship
        // Show/hide sections based on radio button selection
        $('input[name="member_type"]').change(function () {
            if ($(this).val() === 'existing') {
                $('#existing-member-section').show();
                $('#new-member-section').hide();
            } else {
                $('#existing-member-section').hide();
                $('#new-member-section').show();
            }
        });

        // Autocomplete setup for selecting existing person
        $('#autocomplete_member').on('input', function () {
            var input = $(this).val();
            $.ajax({
                url: 'index.php?action=autocomplete_member&tree_id=' + treeId,
                method: 'GET',
                data: { term: input },
                dataType: 'json',
                success: function (data) {
                    $('#autocomplete-options').empty();
                    data.forEach(function (item) {
                        $('#autocomplete-options').append(`<option value="${item.label}" data-person-id="${item.id}">`);
                    });
                }
            });
        });

        // Handle selection from datalist options for existing member
        $('#autocomplete_member').on('change', function () {
            var selectedOption = $('datalist option[value="' + $(this).val() + '"]');
            if (selectedOption.length > 0) {
                $('#person_id2').val(selectedOption.data('person-id')); // Set person_id2
            } else {
                $('#person_id2').val(''); // Clear person_id2 if not selected from autocomplete
            }
        });

        // Handle add relationship button click
        $('#add-relationship-btn').click(function() {
            var formData = $('#add-relationship-form').serialize();
            const relationCategory = $('input[name="relation_category"]:checked').val();
            const memberType = $('input[name="member_type"]:checked').val();

            if (memberType === 'existing') {
                formData += '&relationship_type=' + (relationCategory === 'spouse' ? 'spouse' : 
                            relationCategory === 'child' ? 'child' : 
                            $('#relationship_type_select').val());
            } else {
                formData += '&relationship_type=' + (relationCategory === 'spouse' ? 'spouse' : 
                            relationCategory === 'child' ? 'child' : 
                            $('#relationship_type_new').val());
            }

            $.post('index.php?action=add_relationship', formData, function(response) {
                if (response.success) {
                    location.reload(); // Keep the simple reload for now
                } else {
                    alert('Failed to add relationship: ' + response.message);
                }
            }, 'json');
        });

        //end new func
        // Load relationship types for the select dropdown
        $.ajax({
            url: "index.php?action=get_relationship_types",
            dataType: "json",
            success: function (data) {
                var optionsHtml = '';
                $.each(data, function (index, relationshipType) {
                    optionsHtml += '<option value="' + relationshipType.id + '">' + relationshipType.description + '</option>';
                });
                $('#relationship_type, #edit_relationship_type').html(optionsHtml);
            }
        });

        // Function to load relationships dynamically using AJAX
        function loadRelationships(memberId) {
            $.get('index.php?action=get_relationships&member_id=' + memberId, function (data) {
                var relationshipsHtml = '';
                $.each(data, function (index, relationship) {
                    //console.log(relationship)
                    relationshipsHtml += '<tr>';
                    relationshipsHtml += '<td><a href="index.php?action=view_member&member_id=' + relationship.person1_id + '">' + relationship.person1_first_name + ' ' + relationship.person1_last_name + '</a></td>';
                    relationshipsHtml += '<td><a href="index.php?action=view_member&member_id=' + relationship.person2_id + '">' + relationship.person2_first_name + ' ' + relationship.person2_last_name + '</a></td>';
                    relationshipsHtml += '<td>' + relationship.relationship_description + '</td>';
                    relationshipsHtml += '<td>' + formatBrowserDate(relationship.relation_start) + '</td>';
                    relationshipsHtml += '<td>' + formatBrowserDate(relationship.relation_end) + '</td>';

                    relationshipsHtml += '<td>';
                    relationshipsHtml += '<form class="delete-relationship-form" method="post">';
                    relationshipsHtml += '<input type="hidden" name="relationship_id" value="' + relationship.id + '">';
                    //efface
                    relationshipsHtml += '<button class="relation-button delete-relation-button" type="submit">🗑️</button>';
                    relationshipsHtml += '</form>';
                    relationshipsHtml += '<button type="button" class="relation-button edit-relationship-btn" data-relationship-id="' + relationship.id +
                        '" data-relation-start="' + relationship.relation_start +
                        '" data-relation-end="' + relationship.relation_end +
                        '" data-person1="' + relationship.person1_first_name + ' ' + relationship.person1_last_name +
                        '" data-person1="' + relationship.person1_first_name + ' ' + relationship.person1_last_name +
                        '" data-person2="' + relationship.person2_first_name + ' ' + relationship.person2_last_name + '" data-relationship-type="' + relationship.relationship_type + '">✏️ </button>'; //Edit
                    relationshipsHtml += '<form class="swap-relationship-form" method="post">';
                    relationshipsHtml += '<input type="hidden" id="swap_relationship_id" name="relationship_id" value="' + relationship.id + '">';
                    relationshipsHtml += '<button type="button" class="relation-button swap-relationship-btn" data-relationship-id="' + relationship.id + '" data-person1="' + relationship.person1_first_name + ' ' + relationship.person1_last_name + '" data-person2="' + relationship.person2_first_name + ' ' + relationship.person2_last_name + '" data-relationship-type="' + relationship.relationship_type + '">⇄</button>';
                    //relationshipsHtml += '<button type="button" title="Swap Relationship" class="swap-relationship-btn">⇄</button>'
                    relationshipsHtml += '</form>'
                    relationshipsHtml += '</td>';
                    relationshipsHtml += '</tr>';
                });
                $('#relationships-table-body').html(relationshipsHtml); // Update relationships table body
                // Attach event listeners for edit and delete buttons
                $('.edit-relationship-btn').click(function () {
                    var relationshipId = $(this).data('relationship-id');
                    var person1 = $(this).data('person1');
                    var person2 = $(this).data('person2');
                    var relationshipType = $(this).data('relationship-type');
                    var relationStart = formatRelationDate($(this).data('relation-start')); // Format the date here
                    var relationEnd = formatRelationDate($(this).data('relation-end')); // Format the date here

                    $('#edit_relationship_id').val(relationshipId);
                    $('#edit_relationship_person1').val(person1);
                    $('#edit_relationship_person2').val(person2);
                    $('#edit_relationship_type').val(relationshipType);
                    $('#edit_relation_start').val(relationStart); // Set the formatted date here
                    $('#edit_relation_end').val(relationEnd); // Set the formatted date here

                    $('#edit-relationship-modal').show();
                });
                $('.swap-relationship-btn').click(function (e) {
                    //alert("hi")
                    var relationshipId = $(this).data('relationship-id');
                    $('#swap_relationship_id').val(relationshipId)
                    //alert(relationshipId)
                    e.preventDefault();
                    //var formData = $(this).serialize();
                    var formData = { 'relationship_id': relationshipId }
                    $.post('index.php?action=swap_relationship', formData, function (response) {
                        if (response.success) {
                            loadRelationships(memberId); // Reload relationships after deletion
                        } else {
                            alert('Failed to swap relationship.');
                        }
                    }, 'json');
                })
                $('.delete-relationship-form').submit(function (e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to delete this relationship?')) {
                        var formData = $(this).serialize();
                        $.post('index.php?action=delete_relationship', formData, function (response) {
                            if (response.success) {
                                loadRelationships(memberId); // Reload relationships after deletion
                            } else {
                                alert('Failed to delete relationship.');
                            }
                        }, 'json');
                    }
                });
            }, 'json').fail(function (jqXHR, textStatus, errorThrown) {
                console.error('Error loading relationships:', textStatus, errorThrown);
                alert('Failed to load relationships.');
            });
        }

        // Handle click event for update relationship button
        $('#update-relationship-btn').click(function () {

            var formData = $('#edit-relationship-form').serialize();
            $.post('index.php?action=update_relationship', formData, function (response) {
                if (response.success) {
                    $('#edit-relationship-modal').hide();
                    loadRelationships(memberId); // Reload relationships after update
                } else {
                    alert('Failed to update relationship.');
                }
            }, 'json');
        });
        function formatRelationDate(relationStart) {
            if (relationStart && relationStart !== '-') {
                // Parse the date
                var date = new Date(relationStart);
                if (!isNaN(date.getTime())) {
                    // Format the date (YYYY-MM-DD)
                    var year = date.getFullYear();
                    var month = ('0' + (date.getMonth() + 1)).slice(-2);
                    var day = ('0' + date.getDate()).slice(-2);
                    return year + '-' + month + '-' + day;
                }
            }
            // Return an empty string if relation_start is invalid or a minus sign
            return '';
        }
        function formatBrowserDate(relationStart) {
            if (relationStart && relationStart !== '-') {
                var date = new Date(relationStart);
                if (!isNaN(date.getTime())) {
                    // Format the date to a readable format (e.g., "MMMM D, YYYY")
                    var options = { year: 'numeric', month: 'long', day: 'numeric' };
                    return date.toLocaleDateString(undefined, options);
                }
            }
            // Return an empty string if relation_start is invalid or a minus sign
            return '';
        }

        // Initial load of relationships
        loadRelationships(memberId);

        // Handle relationship category selection
        $('input[name="relation_category"]').change(function() {
            const category = $(this).val();
            $('#other-relationship-section').toggle(category === 'other');
            $('#family-details-section').toggle(category === 'spouse');
            $('#family-selection-section').toggle(category === 'child');
            
            // Update form fields based on category
            if (category === 'spouse') {
                const memberGender = $('input[name="member_gender"]').val();
                $('#new_gender').val(memberGender === '1' ? '2' : '1');
            }
        });

        // Function to reload family section
        function reloadFamilySection(memberId) {
            $.get('index.php?action=get_families&member_id=' + memberId, function(data) {
                if (data.success) {
                    // Update the family sections...
                    // (Keep your existing update code here)
                }
            });
        }
    });
}
