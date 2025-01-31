document.addEventListener('DOMContentLoaded', function () {
    // Initialize only if needed elements exist
    if (document.getElementById('deleteSpouseModal') || 
        document.getElementById('deleteChildModal') || 
        document.getElementById('replaceSpouseModal')) {
        initializeRelationships(memberId);
    }
    if (document.getElementById('replaceSpouseModal')) {
        initializeReplaceSpouse();
    }
    if (document.getElementById('deleteSpouseModal')) {
        initializeDeleteHandlers();
    }
});

function initializeRelationships(memberId) {
    // Initialize Bootstrap modals with null checks
    const deleteSpouseModalEl = document.getElementById('deleteSpouseModal');
    const deleteChildModalEl = document.getElementById('deleteChildModal');
    const addFamilyModalEl = document.getElementById('addFamilyModal');
    const editRelationshipModalEl = document.getElementById('editRelationshipModal');

    // Store modal instances
    const modals = {
        deleteSpouse: deleteSpouseModalEl ? new bootstrap.Modal(deleteSpouseModalEl, { backdrop: 'static' }) : null,
        deleteChild: deleteChildModalEl ? new bootstrap.Modal(deleteChildModalEl, { backdrop: 'static' }) : null,
        addFamily: addFamilyModalEl ? new bootstrap.Modal(addFamilyModalEl) : null,
        editRelationship: editRelationshipModalEl ? new bootstrap.Modal(editRelationshipModalEl) : null
    };

    // Add handlers only if elements exist
    const addFamilyBtn = document.querySelector('.add-family-btn');
    if (addFamilyBtn && modals.addFamily) {
        const addFamilyModalInstance = modals.addFamily;
        
        addFamilyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            addFamilyModalInstance.show();
        });

        // Handle create new family confirmation
        const confirmAddFamily = document.getElementById('confirmAddFamily');
        if (confirmAddFamily) {
            confirmAddFamily.addEventListener('click', function() {
                const formData = new FormData();
                formData.append('member_id', memberId);
                formData.append('tree_id', treeId);

                fetch('index.php?action=create_empty_family', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to create family');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to create family. Please try again.');
                });

                addFamilyModalInstance.hide();
            });
        }
    }

    // Delete child handlers with null check
    const confirmDeleteChild = document.getElementById('confirmDeleteChild');
    if (confirmDeleteChild && modals.deleteChild) {
        document.querySelectorAll('.delete-child-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const childId = this.getAttribute('data-child-id');
                const familyId = this.getAttribute('data-family-id');

                document.getElementById('deleteChildId').value = childId;
                document.getElementById('deleteChildFamilyId').value = familyId;
                modals.deleteChild.show();
            });
        });

        confirmDeleteChild.addEventListener('click', function () {
            const childId = document.getElementById('deleteChildId').value;
            const familyId = document.getElementById('deleteChildFamilyId').value;
            const deleteType = document.getElementById('childDeleteOption').value;
        
            const formData = new FormData();
            formData.append('child_id', childId);
            formData.append('family_id', familyId);
            formData.append('delete_type', deleteType);
        
            fetch('index.php?action=delete_family_member', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                modals.deleteChild.hide();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to delete child');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete child. Please try again.');
            });
        });
    }

    // Delete spouse handlers with null check
    const confirmDeleteSpouse = document.getElementById('confirmDeleteSpouse');
    if (confirmDeleteSpouse && modals.deleteSpouse) {
        document.querySelectorAll('.delete-spouse-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const spouseId = this.getAttribute('data-spouse-id');
                const familyId = this.getAttribute('data-family-id');

                document.getElementById('deleteSpouseId').value = spouseId;
                document.getElementById('deleteFamilyId').value = familyId;
                modals.deleteSpouse.show();
            });
        });

        confirmDeleteSpouse.addEventListener('click', function () {
            const spouseId = document.getElementById('deleteSpouseId').value;
            const familyId = document.getElementById('deleteFamilyId').value;
            const deleteType = document.getElementById('spouseDeleteOption').value;
            const formData = new FormData();
            formData.append('spouse_id', spouseId);
            formData.append('family_id', familyId);
            formData.append('delete_type', deleteType);

            fetch('index.php?action=delete_family_member', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                modals.deleteSpouse.hide();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to delete spouse');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete spouse. Please try again.');
            });
        });
    }

    // Add handler for delete family button in dropdown
    document.querySelectorAll('.delete-family-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const familyId = this.getAttribute('data-family-id');
            const spouseId = this.getAttribute('data-spouse-id');

            document.getElementById('deleteSpouseId').value = spouseId;
            document.getElementById('deleteFamilyId').value = familyId;
            modals.deleteSpouse.show();
        });
    });

    // Add handler for delete family button
    document.querySelectorAll('.delete-family-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const familyId = this.getAttribute('data-family-id');
            
            // Use the same delete spouse modal but preset values
            document.getElementById('deleteSpouseId').value = '';
            document.getElementById('deleteFamilyId').value = familyId;
            
            // Show the delete modal
            const deleteSpouseModal = new bootstrap.Modal(document.getElementById('deleteSpouseModal'));
            deleteSpouseModal.show();
        });
    });

    // Add handler for add family button
    document.querySelector('.add-family-btn').addEventListener('click', function(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('member_id', memberId);
        formData.append('tree_id', treeId);

        fetch('index.php?action=create_empty_family', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to create family');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to create family. Please try again.');
        });
    });

    // Handle replace spouse button click
    document.querySelectorAll('.replace-spouse-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const familyId = this.getAttribute('data-family-id');
            document.getElementById('replace_family_id').value = familyId;
            new bootstrap.Modal(document.getElementById('replaceSpouseModal')).show();
        });
    });

    // Handle spouse type selection
    document.querySelectorAll('input[name="spouse_type"]').forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'existing') {
                document.getElementById('replace-existing-section').style.display = 'block';
                document.getElementById('replace-new-section').style.display = 'none';
            } else {
                document.getElementById('replace-existing-section').style.display = 'none';
                document.getElementById('replace-new-section').style.display = 'block';
            }
        });
    });

    // Handle autocomplete for replace spouse
    document.getElementById('replace_spouse').addEventListener('input', function () {
        const input = this.value;
        const options = document.getElementById('replace-spouse-options');
        options.innerHTML = '';
        
        fetch(`index.php?action=autocomplete_member&tree_id=${treeId}&term=${input}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.label;
                    option.dataset.personId = item.id;
                    options.appendChild(option);
                });

                // Find if we have an exact match
                const exactMatch = data.find(item => item.label === input);
                if (exactMatch) {
                    document.getElementById('replace_spouse_id').value = exactMatch.id;
                }
            });
    });

    document.getElementById('confirmReplaceSpouse').addEventListener('click', function () {
        // Check if we're adding an existing spouse
        if (document.querySelector('input[name="spouse_type"]:checked').value === 'existing') {
            const spouseId = document.getElementById('replace_spouse_id').value;
            if (!spouseId) {
                alert('Please select a valid spouse from the list');
                return;
            }
        }

        const formData = new FormData(document.getElementById('replace-spouse-form'));
        
        // Add debugging log
        console.log('Form data before submit:', Object.fromEntries(formData.entries()));
        
        formData.append('action', 'replace_spouse');
        formData.append('tree_id', treeId);

        fetch('index.php?action=replace_spouse', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to replace spouse');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to replace spouse. Please try again.');
        });
    });

    // Add a change event handler for the autocomplete input
    document.getElementById('replace_spouse').addEventListener('change', function() {
        const input = this.value;
        const options = document.getElementById('replace-spouse-options').options;
        
        // Find matching option
        for (let i = 0; i < options.length; i++) {
            if (options[i].value === input) {
                document.getElementById('replace_spouse_id').value = options[i].dataset.personId;
                return;
            }
        }
        
        // Clear spouse_id if no match found
        document.getElementById('replace_spouse_id').value = '';
    });

    // Initial load of relationships if table exists
    if (document.getElementById('relationships-table-body')) {
        loadRelationships(memberId);
    }
}

function showHideDeath() {
    const deathFields = document.getElementById('death-fields');
    const alive = document.getElementById('form_alive');
    if (alive && alive.checked != true) {
        deathFields.style.display = 'block';
    } else {
        deathFields.style.display = 'none';
    }
}

function loadRelationships(memberId) {
    fetch(`index.php?action=get_relationships&member_id=${memberId}`)
        .then(response => response.ok ? response.json() : [])
        .then(data => {
            const relationshipsTableBody = document.getElementById('relationships-table-body');
            if (!relationshipsTableBody) return;
            
            relationshipsTableBody.innerHTML = '';
            data.forEach(relationship => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><a href="index.php?action=edit_member&member_id=${relationship.person1_id}">${relationship.person1_first_name} ${relationship.person1_last_name}</a></td>
                    <td><a href="index.php?action=edit_member&member_id=${relationship.person2_id}">${relationship.person2_first_name} ${relationship.person2_last_name}</a></td>
                    <td>${relationship.relationship_description}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                ⚙️
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button class="dropdown-item edit-relationship-btn" type="button"
                                        data-relationship-id="${relationship.id}"
                                        data-person1="${relationship.person1_first_name} ${relationship.person1_last_name}"
                                        data-person2="${relationship.person2_first_name} ${relationship.person2_last_name}"
                                        data-relationship-type="${relationship.relationship_type_id}"
                                        data-relation-start="${relationship.relation_start || ''}"
                                        data-relation-end="${relationship.relation_end || ''}">
                                        ✏️ Edit
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item swap-relationship-btn" type="button"
                                        data-relationship-id="${relationship.id}">
                                        🔄 Swap
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item delete-relation-button text-danger" type="button"
                                        data-relationship-id="${relationship.id}">
                                        🗑️ Delete
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                `;
                relationshipsTableBody.appendChild(row);
            });

            // Initialize dropdowns and add event handlers
            const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
            dropdowns.forEach(dropdown => {
                new bootstrap.Dropdown(dropdown);
            });

            document.querySelectorAll('.edit-relationship-btn').forEach(btn => {
                btn.addEventListener('click', handleEditClick);
            });
            
            document.querySelectorAll('.swap-relationship-btn').forEach(btn => {
                btn.addEventListener('click', handleSwapClick);
            });
            
            document.querySelectorAll('.delete-relation-button').forEach(btn => {
                btn.addEventListener('click', handleDeleteClick);
            });
        });
}

function handleEditClick(e) {
    const btn = e.currentTarget;
    const editModal = document.getElementById('editRelationshipModal');
    
    if (!editModal) {
        console.error('Edit modal not found');
        return;
    }

    const modalInstance = new bootstrap.Modal(editModal);

    try {
        // Populate the basic fields
        const relationshipId = document.getElementById('edit_relationship_id');
        const person1Input = document.getElementById('edit_person1');
        const person2Input = document.getElementById('edit_person2');
        const relationStartInput = document.getElementById('edit_relation_start');
        const relationEndInput = document.getElementById('edit_relation_end');
        
        if (relationshipId) relationshipId.value = btn.dataset.relationshipId;
        if (person1Input) person1Input.value = btn.dataset.person1;
        if (person2Input) person2Input.value = btn.dataset.person2;
        if (relationStartInput) relationStartInput.value = formatRelationDate(btn.dataset.relationStart);
        if (relationEndInput) relationEndInput.value = formatRelationDate(btn.dataset.relationEnd);

        // Fetch and populate relationship types
        fetch(`index.php?action=get_relationship_types&tree_id=${treeId}`)
            .then(response => response.json())
            .then(types => {
                const select = document.getElementById('edit_relationship_type');
                if (!select) {
                    console.error('Relationship type select not found');
                    return;
                }

                select.innerHTML = types.map(type => 
                    `<option value="${type.id}">
                        ${type.description}
                    </option>`
                ).join('');
                
                const currentTypeId = btn.dataset.relationshipType;
                if (currentTypeId) {
                    select.value = currentTypeId;
                }

                // Show the modal after everything is set up
                modalInstance.show();

                // Add save button handler here after modal is shown
                const saveBtn = document.getElementById('saveEditRelationship');
                if (saveBtn) {
                    saveBtn.onclick = function() {
                        const formData = new FormData(document.getElementById('edit-relationship-form'));
                        formData.append('member_id', memberId);
                        formData.append('tree_id', treeId);
                        
                        fetch('index.php?action=update_relationship', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok'))
                        .then(data => {
                            if (data.success) {
                                modalInstance.hide();
                                loadRelationships(memberId);
                            } else {
                                alert(data.message || 'Failed to update relationship');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to update relationship. Please try again.');
                        });
                    };
                }

                // Add handlers for close buttons
                const closeButtons = editModal.querySelectorAll('[data-bs-dismiss="modal"]');
                closeButtons.forEach(button => {
                    button.onclick = function() {
                        modalInstance.hide();
                    };
                });
            })
            .catch(error => {
                console.error('Error fetching relationship types:', error);
            });

    } catch (error) {
        console.error('Error populating edit form:', error);
        alert('Error opening edit form. Please try again.');
    }
}

function handleSwapClick(e) {
    const relationshipId = e.currentTarget.dataset.relationshipId;
    const formData = new FormData();
    formData.append('relationship_id', relationshipId);

    fetch('index.php?action=swap_relationship', {
        method: 'POST',
        body: formData // Changed from JSON to FormData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadRelationships(memberId);
        } else {
            console.error('Swap failed:', data);
            alert(data.message || 'Failed to swap relationship.');
        }
    })
    .catch(error => {
        console.error('Error in swap:', error);
        alert('Failed to swap relationship. Check console for details.');
    });
}

function handleDeleteClick(e) {
    if (confirm('Are you sure you want to delete this relationship?')) {
        const relationshipId = e.currentTarget.dataset.relationshipId;
        const formData = new FormData();
        formData.append('relationship_id', relationshipId);
        fetch('index.php?action=delete_relationship', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadRelationships(memberId);
            } else {
                alert('Failed to delete relationship.');
            }
        });
    }
}

function handleEditRelationship(id, start, end, person1, person2, type) {
    // Show edit modal or form with relationship details
    const editModal = document.getElementById('edit-relationship-modal');
    if (editModal) {
        document.getElementById('edit_relationship_id').value = id;
        document.getElementById('edit_relationship_person1').value = person1;
        document.getElementById('edit_relationship_person2').value = person2;
        document.getElementById('edit_relationship_type').value = type;
        document.getElementById('edit_relation_start').value = formatRelationDate(start);
        document.getElementById('edit_relation_end').value = formatRelationDate(end);
        new bootstrap.Modal(editModal).show();
    }
}

function handleSwapRelationship(relationshipId) {
    fetch('index.php?action=swap_relationship', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ relationship_id: relationshipId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadRelationships(memberId);
        } else {
            alert('Failed to swap relationship.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to swap relationship.');
    });
}

function attachDeleteHandlers() {
    document.querySelectorAll('.delete-relation-button').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this relationship?')) {
                const relationshipId = this.getAttribute('data-relationship-id');
                const formData = new FormData();
                formData.append('relationship_id', relationshipId);
                fetch('index.php?action=delete_relationship', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadRelationships(memberId);
                    } else {
                        alert('Failed to delete relationship.');
                    }
                });
            }
        });
    });
}

function formatRelationDate(relationStart) {
    if (relationStart && relationStart !== '-') {
        const date = new Date(relationStart);
        if (!isNaN(date.getTime())) {
            const year = date.getFullYear();
            const month = ('0' + (date.getMonth() + 1)).slice(-2);
            const day = ('0' + date.getDate()).slice(-2);
            return `${year}-${month}-${day}`;
        }
    }
    return '';
}

function formatBrowserDate(relationStart) {
    if (relationStart && relationStart !== '-') {
        const date = new Date(relationStart);
        if (!isNaN(date.getTime())) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString(undefined, options);
        }
    }
    return '';
}

// Add this to initialize the replace spouse functionality
function initializeReplaceSpouse() {
    const replaceSpouseModal = new bootstrap.Modal(document.getElementById('replaceSpouseModal'));
    const form = document.getElementById('replace-spouse-form');
    if (!form) return;

    // Handle replace spouse button clicks
    document.querySelectorAll('.replace-spouse-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const familyId = e.target.dataset.familyId;
            document.getElementById('replace_family_id').value = familyId;
            
            // Reset form
            form.reset();
            document.getElementById('replace_spouse').value = '';
            document.getElementById('replace_spouse_id').value = '';
            document.getElementById('replace-existing-section').style.display = 'block';
            document.getElementById('replace-new-section').style.display = 'none';
            
            replaceSpouseModal.show();
        });
    });

    // Handle radio button changes
    const spouseTypeRadios = form.querySelectorAll('input[name="spouse_type"]');
    const existingSection = document.getElementById('replace-existing-section');
    const newSection = document.getElementById('replace-new-section');

    spouseTypeRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            existingSection.style.display = radio.value === 'existing' ? 'block' : 'none';
            newSection.style.display = radio.value === 'new' ? 'block' : 'none';
        });
    });

    // Initialize spouse autocomplete
    const spouseInput = document.getElementById('replace_spouse');
    if (spouseInput) {
        let timeout = null;
        spouseInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(async () => {
                try {
                    const response = await fetch(`index.php?action=autocomplete_member&term=${spouseInput.value}&tree_id=${treeId}`);
                    const data = await response.json();
                    
                    const datalist = document.getElementById('replace-spouse-options');
                    datalist.innerHTML = '';
                    
                    data.forEach(member => {
                        const option = document.createElement('option');
                        option.value = member.label;
                        option.setAttribute('data-id', member.id);
                        datalist.appendChild(option);

                        // If exact match, set the ID immediately
                        if (member.label === spouseInput.value) {
                            document.getElementById('replace_spouse_id').value = member.id;
                        }
                    });
                } catch (error) {
                    console.error('Error fetching spouse suggestions:', error);
                }
            }, 300);
        });

        // Handle selection from datalist
        spouseInput.addEventListener('change', () => {
            const selectedValue = spouseInput.value;
            const options = document.getElementById('replace-spouse-options').getElementsByTagName('option');
            
            for (let option of options) {
                if (option.value === selectedValue) {
                    const spouseId = option.getAttribute('data-id');
                    document.getElementById('replace_spouse_id').value = spouseId;
                    return; // Exit once we've found the match
                }
            }
        });
    }

    // Handle form submission
    document.getElementById('confirmReplaceSpouse').addEventListener('click', async () => {
        const spouseType = form.querySelector('input[name="spouse_type"]:checked').value;
        const formData = new FormData(form);
        formData.append('tree_id', treeId);

        try {
            if (spouseType === 'existing') {
                const spouseId = document.getElementById('replace_spouse_id').value;
                const spouseName = document.getElementById('replace_spouse').value;
                if (!spouseId || !spouseName) {
                    throw new Error('Please select a valid spouse from the list');
                }
            } else {
                const firstName = form.querySelector('input[name="new_first_name"]').value;
                const lastName = form.querySelector('input[name="new_last_name"]').value;
                if (!firstName || !lastName) {
                    throw new Error('Please enter first and last name for new spouse');
                }
            }

            const response = await fetch('index.php?action=replace_spouse', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                replaceSpouseModal.hide();
                location.reload();
            } else {
                throw new Error(result.message || 'Failed to replace spouse');
            }
        } catch (error) {
            console.error('Error replacing spouse:', error);
            alert(error.message || 'Failed to replace spouse');
        }
    });
}

function initializeDeleteHandlers() {
    const deleteSpouseModalEl = document.getElementById('deleteSpouseModal');
    if (!deleteSpouseModalEl) return;

    const deleteSpouseModal = new bootstrap.Modal(deleteSpouseModalEl);
    const deleteFamilyForm = document.getElementById('deleteFamilyForm');
    const deleteFamilyId = document.getElementById('deleteFamilyId');
    
    if (!deleteFamilyForm || !deleteFamilyId) return;

    // Delete form checkbox logic
    const deleteSpouseRelationship = document.getElementById('deleteSpouseRelationship');
    const deleteSpouse = document.getElementById('deleteSpouse');
    const deleteChildren = document.getElementById('deleteChildren');
    const deleteFamily = document.getElementById('deleteFamily');

    if (deleteSpouse && deleteSpouseRelationship) {
        // When "Delete spouse record" is checked, ensure relationship is also checked
        deleteSpouse.addEventListener('change', function() {
            if (this.checked) {
                deleteSpouseRelationship.checked = true;
            }
        });

        // When relationship is unchecked, uncheck delete spouse
        deleteSpouseRelationship.addEventListener('change', function() {
            if (!this.checked) {
                deleteSpouse.checked = false;
            }
        });
    }

    // Show delete modal handler
    document.querySelectorAll('.delete-family-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const familyId = this.getAttribute('data-family-id');
            if (familyId && deleteFamilyId) {
                deleteFamilyId.value = familyId;
                deleteSpouseModal.show();
            }
        });
    });

    // Handle delete confirmation
    const confirmDeleteFamily = document.getElementById('confirmDeleteFamily');
    if (confirmDeleteFamily) {
        confirmDeleteFamily.addEventListener('click', function() {
            const formData = new FormData(deleteFamilyForm);
            
            fetch('index.php?action=delete_family_member', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                deleteSpouseModal.hide();
                if (data.success) {
                    location.reload();
                } else {
                    throw new Error(data.message || 'Failed to delete family member');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'Failed to delete family member. Please try again.');
            });
        });
    }
}
