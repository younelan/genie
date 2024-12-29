$(document).ready(function () {
});



function initializeRelationships(memberId) {
    $(function () {
        // Initialize Bootstrap modals
        $('#deleteSpouseModal').modal({
            show: false,
            backdrop: 'static'
        });
        
        $('#deleteChildModal').modal({
            show: false,
            backdrop: 'static'
        });

        // Delete child handlers
        $('.delete-child-btn').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const childId = $(this).data('child-id');
            const familyId = $(this).data('family-id');
            
            console.log('Delete child clicked:', childId, familyId);
            
            $('#deleteChildId').val(childId);
            $('#deleteChildFamilyId').val(familyId);
            $('#deleteChildModal').modal('show');
        });

        $('#confirmDeleteChild').on('click', function() {
            const childId = $('#deleteChildId').val();
            const familyId = $('#deleteChildFamilyId').val();
            const deleteType = $('#childDeleteOption').val();

            console.log('Sending delete request:', {
                child_id: childId,
                family_id: familyId,
                delete_type: deleteType
            });

            $.ajax({
                url: 'index.php',
                method: 'POST',
                data: {
                    action: 'delete_family_member',
                    child_id: childId,
                    family_id: familyId,
                    delete_type: deleteType
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Server response:', response);
                    $('#deleteChildModal').modal('hide');
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert(response.message || 'Failed to delete child');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                    alert('Failed to delete child. Please try again.');
                }
            });
        });

        // Delete spouse handlers
        $('.delete-spouse-btn').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const spouseId = $(this).data('spouse-id');
            const familyId = $(this).data('family-id');
            
            console.log('Delete spouse clicked:', spouseId, familyId);
            
            $('#deleteSpouseId').val(spouseId);
            $('#deleteFamilyId').val(familyId);
            $('#deleteSpouseModal').modal('show');
        });

        $('#confirmDeleteSpouse').on('click', function() {
            const spouseId = $('#deleteSpouseId').val();
            const familyId = $('#deleteFamilyId').val();
            const deleteType = $('#spouseDeleteOption').val();

            console.log('Sending delete request:', {
                spouse_id: spouseId,
                family_id: familyId,
                delete_type: deleteType
            });

            $.ajax({
                url: 'index.php',
                method: 'POST',
                data: {
                    action: 'delete_family_member',
                    spouse_id: spouseId,
                    family_id: familyId,
                    delete_type: deleteType
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Server response:', response);
                    $('#deleteSpouseModal').modal('hide');
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert(response.message || 'Failed to delete spouse');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                    alert('Failed to delete spouse. Please try again.');
                }
            });
        });

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

            console.log('Sending form data:', formData);

            $.post('index.php?action=add_relationship', formData, function(response) {
                console.log('Server response:', response);
                if (response.success) {
                    window.location.reload();
                } else {
                    alert('Failed to add relationship: ' + response.message);
                }
            }, 'json')
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.error('Response Text:', jqXHR.responseText);
                alert('Failed to add relationship. Please try again.');
            });
        });

        // Fix Firefox datalist handling
        $('#autocomplete_member').on('input change', function () {
            var input = $(this).val();
            var option = $('#autocomplete-options option[value="' + input + '"]');
            
            if (option.length > 0) {
                $('#person_id2').val(option.data('person-id'));
            } else {
                // If no exact match, check if we're in the middle of typing
                if (!this.list || !this.list.options.length) {
                    // If no options or still typing, make the AJAX call
                    $.ajax({
                        url: 'index.php?action=autocomplete_member&tree_id=' + treeId,
                        method: 'GET',
                        data: { term: input },
                        dataType: 'json',
                        success: function (data) {
                            $('#autocomplete-options').empty();
                            data.forEach(function (item) {
                                $('#autocomplete-options').append(
                                    `<option value="${item.label}" data-person-id="${item.id}">`
                                );
                            });
                        }
                    });
                }
                $('#person_id2').val('');
            }
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
        $('input[name="relation_category"]').on('change', function() {
            const category = $(this).val();
            console.log('Category changed to:', category);
            
            // Hide all special sections first
            $('#other-relationship-section').hide();
            $('#family-details-section').hide();
            $('#family-selection-section').hide();
            
            // Show appropriate section based on category
            switch(category) {
                case 'other':
                    $('#other-relationship-section').show();
                    break;
                case 'spouse':
                    $('#family-details-section').show();
                    break;
                case 'child':
                    $('#family-selection-section').show();
                    break;
            }
            
            // Log visibility status after changes
            logVisibility();
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

        // Add this new function to handle updating the family display
        function updateFamilyDisplay(data) {
            // Update spouse families section
            if (data.spouse_families) {
                let tabsHtml = '';
                let contentHtml = '';
                
                data.spouse_families.forEach((family, index) => {
                    const isActive = index === 0;
                    const memberGender = $('input[name="member_gender"]').val();
                    const spouseName = memberGender == 1 ? family.wife_name : family.husband_name;
                    
                    // Build tabs HTML
                    tabsHtml += `
                        <li class="nav-item" role="presentation">
                            <button class="nav-link ${isActive ? 'active' : ''}" 
                                    id="family-tab-${family.family_id}" 
                                    data-toggle="tab" 
                                    data-target="#family-${family.family_id}" 
                                    type="button" 
                                    role="tab">
                                ${spouseName || 'Unknown Spouse'}
                            </button>
                        </li>`;

                    // Build content HTML
                    contentHtml += `
                        <div class="tab-pane fade ${isActive ? 'show active' : ''}" 
                             id="family-${family.family_id}" 
                             role="tabpanel">
                            <!-- Marriage Details -->
                            <div class="card mt-3">
                                <div class="card-header">Marriage Details</div>
                                <div class="card-body">
                                    <p><strong>Marriage Date:</strong> ${family.marriage_date ? formatBrowserDate(family.marriage_date) : '-'}</p>
                                    <p><strong>Divorce Date:</strong> ${family.divorce_date ? formatBrowserDate(family.divorce_date) : '-'}</p>
                                </div>
                            </div>
                            <!-- Children -->
                            <div class="card mt-3">
                                <div class="card-header">Children</div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Birth Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${family.children ? family.children.map(child => `
                                                <tr>
                                                    <td>
                                                        <a href="index.php?action=edit_member&member_id=${child.id}">
                                                            ${child.first_name} ${child.last_name}
                                                        </a>
                                                    </td>
                                                    <td>${child.date_of_birth ? formatBrowserDate(child.date_of_birth) : '-'}</td>
                                                </tr>
                                            `).join('') : ''}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>`;
                });

                $('#familyTabs').html(tabsHtml);
                $('#familyTabsContent').html(contentHtml);
            }

            // Update child families section
            if (data.child_families) {
                let parentsHtml = data.child_families.map(family => `
                    <tr>
                        <td>${family.husband_id ? 
                            `<a href="index.php?action=edit_member&member_id=${family.husband_id}">
                                ${family.husband_name}
                            </a>` : '-'}</td>
                        <td>${family.wife_id ? 
                            `<a href="index.php?action=edit_member&member_id=${family.wife_id}">
                                ${family.wife_name}
                            </a>` : '-'}</td>
                    </tr>
                `).join('');
                
                $('#child-families-body').html(parentsHtml);
            }
        }

        // Trigger the change event on page load to set initial state
        $('input[name="relation_category"]:checked').trigger('change');

        // Handle replace spouse button click
        $('.replace-spouse-btn').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const familyId = $(this).data('family-id');
            $('#replace_family_id').val(familyId);
            $('#replaceSpouseModal').modal('show');
        });

        // Handle spouse type selection
        $('input[name="spouse_type"]').change(function() {
            if ($(this).val() === 'existing') {
                $('#replace-existing-section').show();
                $('#replace-new-section').hide();
            } else {
                $('#replace-existing-section').hide();
                $('#replace-new-section').show();
            }
        });

        // Handle autocomplete for replace spouse
        $('#replace_spouse').on('input', function() {
            var input = $(this).val();
            $.ajax({
                url: 'index.php?action=autocomplete_member&tree_id=' + treeId,
                method: 'GET',
                data: { term: input },
                dataType: 'json',
                success: function(data) {
                    $('#replace-spouse-options').empty();
                    data.forEach(function(item) {
                        $('#replace-spouse-options').append(
                            `<option value="${item.label}" data-person-id="${item.id}">`
                        );
                    });
                }
            });
        });

        // Handle selection from datalist options for replace spouse
        $('#replace_spouse').on('change', function () {
            var selectedOption = $('#replace-spouse-options option[value="' + $(this).val() + '"]');
            if (selectedOption.length > 0) {
                $('#replace_spouse_id').val(selectedOption.data('person-id'));
            } else {
                $('#replace_spouse_id').val('');
            }
        });

        // Update the replace spouse confirmation handler
        $('#confirmReplaceSpouse').click(function() {
            const formData = new FormData($('#replace-spouse-form')[0]);
            formData.append('action', 'replace_spouse');
            formData.append('family_tree_id', treeId); // Add tree ID
            
            // If existing spouse, get the ID from the hidden input
            if ($('input[name="spouse_type"]:checked').val() === 'existing') {
                const spouseId = $('#replace_spouse_id').val();
                if (!spouseId) {
                    alert('Please select an existing person');
                    return;
                }
                formData.set('spouse_id', spouseId);
            }

            console.log('Sending replace spouse request:', {
                family_id: formData.get('family_id'),
                spouse_type: formData.get('spouse_type'),
                spouse_id: formData.get('spouse_id'),
                member_gender: formData.get('member_gender'),
                tree_id: formData.get('family_tree_id')
            });

            $.ajax({
                url: 'index.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('Server response:', response);
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message || 'Failed to replace spouse');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    console.error('Response:', xhr.responseText);
                    alert('Failed to replace spouse. Please try again.');
                }
            });
        });
    });
}

// Add this function at the top level
function logVisibility() {
    console.log('Visibility status:');
    console.log('Other relationship section:', $('#other-relationship-section').is(':visible'));
    console.log('Family details section:', $('#family-details-section').is(':visible'));
    console.log('Family selection section:', $('#family-selection-section').is(':visible'));
}
