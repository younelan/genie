const MemberDetails = ({ treeId, memberId }) => {
    const [member, setMember] = React.useState(null);
    const [spouseFamilies, setSpouseFamilies] = React.useState([]);
    const [childFamilies, setChildFamilies] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(null);
    const [activeFamily, setActiveFamily] = React.useState(null);
    const [showDeathFields, setShowDeathFields] = React.useState(false);
    const [formData, setFormData] = React.useState({
        first_name: '',
        last_name: '',
        birth_date: '',
        birth_place: '',
        death_date: '',
        death_place: '',
        gender: 'M',
        alive: true,
        source: ''
    });
    // Add new state for relationship modal
    const [showRelationshipModal, setShowRelationshipModal] = React.useState(false);
    
    // Add these new state variables at the top with other state declarations
    const [relationships, setRelationships] = React.useState([]);
    const [relationshipTypes, setRelationshipTypes] = React.useState([]);

    // Add new state variables for edit modal
    const [showEditOtherRelationship, setShowEditOtherRelationship] = React.useState(false);
    const [editingRelationship, setEditingRelationship] = React.useState(null);

    // Add new state for add spouse modal
    const [showAddSpouseModal, setShowAddSpouseModal] = React.useState(false);
    const [editingFamily, setEditingFamily] = React.useState(null);

    // Get IDs from props or URL as fallback
    const currentMemberId = memberId || window.location.hash.split('/').find(part => /^\d+$/.test(part));
    const currentTreeId = treeId || window.location.hash.split('/')[2];

    React.useEffect(() => {
        if (currentMemberId && /^\d+$/.test(currentMemberId)) {
            loadMemberDetails();
            loadRelationships();  // Add this line
        } else {
            setError('Invalid member ID');
            setLoading(false);
        }
    }, [currentMemberId]);

    // Set first family as active when data loads
    React.useEffect(() => {
        if (spouseFamilies?.length > 0 && !activeFamily) {
            setActiveFamily(spouseFamilies[0].id);
        }
    }, [spouseFamilies]);

    React.useEffect(() => {
        if (member) {
            const aliveValue = member.alive == '1' || member.alive === true || member.alive === 'true';
            setFormData(prev => ({
                first_name: member.first_name || '',
                last_name: member.last_name || '',
                birth_date: member.birth_date || '',
                birth_place: member.birth_place || '',
                death_date: member.death_date || '',
                death_place: member.death_place || '',
                gender: member.gender || 'M',
                alive: aliveValue,
                source: member.source || ''
            }));
            setShowDeathFields(!aliveValue);
        }
    }, [member]);

    const loadMemberDetails = async () => {
        try {
            const response = await fetch(`api/individuals.php?action=details&id=${currentMemberId}`);
            if (!response.ok) {
                throw new Error('Failed to load member details');
            }
            const data = await response.json();
            if (data.success) {
                setMember(data.data.member);
                setSpouseFamilies(data.data.spouseFamilies);
                setChildFamilies(data.data.childFamilies);
                // Load tags into form data
                setFormData(prev => ({
                    ...prev,
                    ...data.data.member,
                    alive: data.data.member.alive == '1' || data.data.member.alive === true || data.data.member.alive === 'true'
                }));
            } else {
                throw new Error(data.message || 'Failed to load member details');
            }
        } catch (error) {
            setError(error.message);
        } finally {
            setLoading(false);
        }
    };

    // Add this new function to load relationships
    const loadRelationships = async () => {
        try {
            // Fetch relationship types
            const typesResponse = await fetch(`api/individuals.php?action=get_relationship_types&tree_id=${currentTreeId}`);
            if (!typesResponse.ok) throw new Error('Failed to load relationship types');
            const typesData = await typesResponse.json();
            setRelationshipTypes(typesData);

            // Fetch relationships
            const relResponse = await fetch(`api/individuals.php?action=get_relationships&member_id=${currentMemberId}`);
            if (!relResponse.ok) throw new Error('Failed to load relationships');
            const relData = await relResponse.json();
            if (relData.success) {
                setRelationships(relData.relationships || []);
            }
        } catch (error) {
            console.error('Error loading relationships:', error);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const form = new FormData();
        
        form.append('_method', 'PUT');
        
        // Add all form fields
        Object.keys(formData).forEach(key => {
            if (key === 'alive') {
                // Convert boolean to string '1' or '0'
                const aliveValue = formData[key] ? '1' : '0';
                console.log('Submitting alive value:', aliveValue);
                form.append(key, aliveValue);
            } else {
                form.append(key, formData[key]);
            }
        });
        form.append('id', currentMemberId);

        try {
            const response = await fetch('api/individuals.php', {
                method: 'POST',
                body: form
            });
            const data = await response.json();
            if (data.success) {
                loadMemberDetails();
            } else {
                throw new Error(data.message || 'Failed to update member');
            }
        } catch (error) {
            console.error('Error updating member:', error);
            setError(error.message);
        }
    };

    const handleInputChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : (value || '') // Ensure empty string if value is null
        }));
    };

    // Add handlers for family actions
    const handleAddFamily = async () => {
        if (!confirm('Create a new family with no spouse?')) return;
        
        try {
            const response = await fetch('api/individuals.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'add_relationship',
                    type: 'spouse',
                    member_id: currentMemberId,
                    tree_id: currentTreeId,
                    relationship_type: 'spouse',
                    spouse_type: 'new',  // This indicates we want a new empty family
                    create_empty: true    // Special flag for empty family
                })
            });

            if (!response.ok) throw new Error('Network response was not ok');
            const result = await response.json();
            
            if (result.success) {
                loadMemberDetails(); // Reload to show new family
            } else {
                throw new Error(result.message || 'Failed to create family');
            }
        } catch (error) {
            console.error('Error creating family:', error);
            alert('Failed to create family: ' + error.message);
        }
    };

    const handleDeleteFamily = async (familyId) => {
        if (!confirm('Are you sure you want to delete this family? This will remove all relationships.')) return;
        try {
            const response = await fetch('api/individuals.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'delete_family',
                    family_id: familyId
                })
            });

            const data = await response.json();
            if (data.success) {
                loadMemberDetails();
            } else {
                throw new Error(data.error || 'Failed to delete family');
            }
        } catch (error) {
            console.error('Error deleting family:', error);
            alert('Failed to delete family: ' + error.message);
        }
    };

    const handleDeleteChild = async (childId, familyId) => {
        if (!confirm('Are you sure you want to remove this child?')) return;
        try {
            const response = await fetch('api/families.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'removeChild',
                    type: 'remove_child',  // Add this line to match API expectation
                    child_id: childId,
                    family_id: familyId
                })
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Failed to remove child from family');
            }

            if (data.success) {
                loadMemberDetails();
            } else {
                throw new Error(data.message || 'Failed to remove child from family');
            }
        } catch (error) {
            console.error('Error removing child:', error);
            alert('Error removing child: ' + error.message);
        }
    };

    const handleAliveChange = (e) => {
        const isAlive = e.target.checked;
        setFormData(prev => ({
            ...prev,
            alive: isAlive,
            // Clear death fields if becoming alive
            death_date: isAlive ? '' : prev.death_date,
            death_place: isAlive ? '' : prev.death_place
        }));
        setShowDeathFields(!isAlive);
    };

    const handleAddRelationship = (formData) => {
        // Handle saving the relationship
        console.log('Saving relationship:', formData);
        setShowRelationshipModal(false);
    };

    // In the renderBasicInfo function, update the Dropdown items array:
    const renderBasicInfo = () => React.createElement(Card, { key: 'basic-info-card' }, [
        React.createElement(Card.Header, { 
            key: 'header',
            className: 'flex justify-between items-center'
        }, [
            React.createElement('h4', { key: 'title' }, 'Member Details'),
            React.createElement(Dropdown, {
                key: 'actions',
                trigger: '⚙️',
                items: [
                    {
                        label: '🌳 Visualize Descendants',
                        href: `#/tree/${currentTreeId}/member/${currentMemberId}/descendants`
                    },
                    {
                        label: '➕ Add Relationship',
                        onClick: () => setShowRelationshipModal(true)
                    },
                    {
                        label: '🗑️ Delete Member',
                        onClick: handleDeleteMember,
                        className: 'text-red-600'
                    }
                ]
            })
        ]),
        React.createElement(Card.Body, { key: 'body' },
            React.createElement('form', { onSubmit: handleSubmit }, [
                // Name fields
                React.createElement('div', { key: 'name-group', className: 'mb-3' }, [
                    React.createElement('label', { key: 'name-label' }, 'Name'),
                    React.createElement('input', {
                        key: 'first-name',
                        type: 'text',
                        name: 'first_name',
                        value: formData.first_name || '',
                        onChange: handleInputChange,
                        className: 'form-control mb-2'
                    }),
                    React.createElement('input', {
                        key: 'last-name',
                        type: 'text',
                        name: 'last_name',
                        value: formData.last_name || '',
                        onChange: handleInputChange,
                        className: 'form-control'
                    })
                ]),
                // Birth fields
                React.createElement('div', { key: 'birth-group', className: 'mb-3' }, [
                    React.createElement('label', { key: 'birth-label' }, 'Birth'),
                    React.createElement('input', {
                        key: 'birth-date',
                        type: 'date',
                        name: 'birth_date',
                        value: formData.birth_date || '',
                        onChange: handleInputChange,
                        className: 'form-control mb-2'
                    }),
                    React.createElement('input', {
                        key: 'birth-place',
                        type: 'text',
                        name: 'birth_place',
                        value: formData.birth_place || '',
                        onChange: handleInputChange,
                        className: 'form-control',
                        placeholder: 'Place of Birth'
                    })
                ]),
                // Gender and Alive
                React.createElement('div', { key: 'status-group', className: 'mb-3' }, [
                    React.createElement('div', { key: 'gender-field', className: 'mb-2' }, [
                        React.createElement('label', { key: 'gender-label' }, 'Gender'),
                        React.createElement('select', {
                            key: 'gender-select',
                            name: 'gender',
                            value: formData.gender,
                            onChange: handleInputChange,
                            className: 'form-select'
                        }, [
                            React.createElement('option', { key: 'male', value: 'M' }, 'Male'),
                            React.createElement('option', { key: 'female', value: 'F' }, 'Female')
                        ])
                    ]),
                    React.createElement('div', { key: 'alive-field', className: 'form-check' }, [
                        React.createElement('input', {
                            key: 'alive-checkbox',
                            type: 'checkbox',
                            id: 'alive',
                            name: 'alive',
                            checked: formData.alive,
                            onChange: e => {
                                setFormData(prev => ({
                                    ...prev,
                                    alive: e.target.checked
                                }));
                                setShowDeathFields(!e.target.checked);
                            },
                            className: 'form-check-input'
                        }),
                        React.createElement('label', {
                            key: 'alive-label',
                            htmlFor: 'alive',
                            className: 'form-check-label'
                        }, 'Alive')
                    ])
                ]),
                // Death fields (shown if not alive)
                !showDeathFields ? null : React.createElement('div', { key: 'death-group', className: 'mb-3' }, [
                    React.createElement('label', { key: 'death-label' }, 'Death'),
                    React.createElement('input', {
                        key: 'death-date',
                        type: 'date',
                        name: 'death_date',
                        value: formData.death_date || '',
                        onChange: handleInputChange,
                        className: 'form-control mb-2'
                    }),
                    React.createElement('input', {
                        key: 'death-place',
                        type: 'text',
                        name: 'death_place',
                        value: formData.death_place || '',
                        onChange: handleInputChange,
                        className: 'form-control',
                        placeholder: 'Place of Death'
                    })
                ]),
                // Add TagInput before the submit button
                React.createElement(TagInput, {
                    key: 'tags',
                    memberId: currentMemberId,
                    treeId: currentTreeId,
                    member: member // Pass the entire member object
                }),
                React.createElement('button', {
                    key: 'submit',
                    type: 'submit',
                    className: 'btn btn-primary'
                }, 'Save Changes')
            ])
        )
    ]);

    const handleDeleteMember = async () => {
        if (!confirm('Are you sure you want to delete this member?')) return;
        try {
            const response = await fetch(`api/individuals.php?id=${currentMemberId}`, {
                method: 'DELETE'
            });
            if (response.ok) {
                window.location.hash = `#/tree/${currentTreeId}/members`;
            }
        } catch (error) {
            console.error('Error deleting member:', error);
        }
    };

    const handleEditFamily = async (familyId) => {
        const family = spouseFamilies.find(f => f.id === familyId);
        if (!family) return;
    
        setEditingFamily({
            id: familyId,
            spousePosition: !family.husband_id ? 'husband' : 'wife'
        });
        setShowAddSpouseModal(true);
    };

    const renderFamilyTab = (family) => {
        const dropdownItems = [
            family.spouse_id && {
                label: 'View Spouse',
                href: `#/tree/${currentTreeId}/member/${family.spouse_id}`
            },
            {
                label: family.spouse_id ? 'Remove Spouse' : 'Add Spouse',
                onClick: family.spouse_id ? 
                    () => handleRemoveSpouse(family.id) : 
                    () => handleEditFamily(family.id)
            },
            {
                label: 'Delete Family',
                onClick: () => handleDeleteFamily(family.id),
                className: 'text-danger'
            }
        ].filter(Boolean);

        return React.createElement('div', {
            className: 'flex items-center'
        }, [
            React.createElement(Nav.Link, {
                key: 'tab-link',
                active: activeFamily === family.id,
                onClick: () => setActiveFamily(family.id),
                className: 'flex-grow'
            }, family.spouse_name || 'Unknown Spouse'),
            React.createElement(Dropdown, {
                key: 'family-actions',
                trigger: '⚙️',
                items: dropdownItems
            })
        ]);
    };

    const renderFamilyTabs = () => React.createElement(Card, { key: 'family-card' }, [
        React.createElement(Card.Header, { key: 'header' }, 'Families'),
        React.createElement(Card.Body, { key: 'body' }, [
            React.createElement(Nav, {
                key: 'family-nav',
                variant: 'tabs',
                className: 'mb-3'
            }, [
                ...spouseFamilies.map(family => 
                    React.createElement(Nav.Item, { 
                        key: `family-tab-${family.id}`
                    }, 
                        renderFamilyTab(family)
                    )
                ),
                React.createElement(Nav.Item, {
                    key: 'add-family',
                    className: 'ms-2'
                }, React.createElement(Nav.Link, {
                    key: 'add-family-link',
                    onClick: handleAddFamily
                }, '+'))
            ]),
            // Active family content
            spouseFamilies.map(family => 
                family.id === activeFamily && React.createElement('div', {
                    key: `family-content-${family.id}`
                }, [
                    React.createElement('div', { key: 'marriage-details', className: 'mb-3' }, [
                        React.createElement('strong', { key: 'marriage-label' }, 'Marriage Date: '),
                        React.createElement('span', { key: 'marriage-date' }, family.marriage_date || 'Unknown'),
                        family.divorce_date && [
                            React.createElement('br', { key: 'br' }),
                            React.createElement('strong', { key: 'divorce-label' }, 'Divorce Date: '),
                            React.createElement('span', { key: 'divorce-date' }, family.divorce_date)
                        ]
                    ]),
                    React.createElement('h6', { key: 'children-header' }, 'Children'),
                    React.createElement(ListGroup, { key: 'children-list' },
                        (family.children || []).map(child =>
                            React.createElement(ListGroup.Item, {
                                key: `child-${child.id}`,
                                className: 'd-flex justify-content-between align-items-center'
                            }, [
                                React.createElement('a', {
                                    key: 'child-link',
                                    href: `#/tree/${currentTreeId}/member/${child.id}`,
                                    className: 'text-decoration-none',
                                    onClick: (e) => {
                                        e.preventDefault();
                                        window.location.hash = `#/tree/${currentTreeId}/member/${child.id}`;
                                    }
                                }, `${child.gender === 'M' ? '♂️' : '♀️'} ${child.first_name} ${child.last_name}`),
                                React.createElement('button', {
                                    key: 'delete-child',
                                    className: 'btn btn-sm btn-danger',
                                    onClick: (e) => {
                                        e.preventDefault();
                                        handleDeleteChild(child.id, family.id);
                                    }
                                }, '🗑️')
                            ])
                        )
                    )
                ])
            )
        ])
    ]);

    const handleDeleteFromFamily = async (familyId) => {
        if (!confirm('Are you sure you want to remove this parent relationship?')) return;
        try {
            const response = await fetch('api/families.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'removeChild',
                    type: 'remove_child',  // Add this line to specify the action type
                    child_id: currentMemberId,
                    family_id: familyId
                })
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Failed to remove from family');
            }

            if (data.success) {
                loadMemberDetails();
            } else {
                throw new Error(data.message || 'Failed to remove from family');
            }
        } catch (error) {
            console.error('Error removing from family:', error);
            alert('Error removing parent relationship: ' + error.message);
        }
    };

    const renderParents = () => React.createElement(Card, { key: 'parents-card', className: 'mt-3' }, [
        React.createElement(Card.Header, { key: 'header' }, 'Parents'),
        React.createElement(Card.Body, { key: 'body' },
            childFamilies.map(family => 
                React.createElement('div', { 
                    key: `family-${family.id}`, 
                    className: 'd-flex justify-content-between align-items-center mb-2'
                }, [
                    React.createElement('div', { 
                        key: 'parents-names',
                        className: 'd-flex gap-2' 
                    }, [
                        family.husband_id && React.createElement('a', {
                            key: 'father',
                            href: `#/tree/${currentTreeId}/member/${family.husband_id}`,
                            className: 'text-decoration-none'
                        }, family.husband_name),
                        (family.husband_id && family.wife_id) && React.createElement('span', { key: 'separator' }, ' & '),
                        family.wife_id && React.createElement('a', {
                            key: 'mother',
                            href: `#/tree/${currentTreeId}/member/${family.wife_id}`,
                            className: 'text-decoration-none'
                        }, family.wife_name)
                    ]),
                    React.createElement('button', {
                        key: 'delete-button',
                        className: 'btn btn-sm btn-danger',
                        onClick: () => handleDeleteFromFamily(family.id),
                        title: 'Remove parent relationship'
                    }, '🗑️')
                ])
            )
        )
    ]);

    // Add this new handler function
    const handleDeleteRelationship = async (relationshipId) => {
        if (!confirm('Are you sure you want to delete this relationship?')) return;
        
        try {
            const response = await fetch('api/individuals.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'delete_relationship',
                    relationship_id: relationshipId
                })
            });
            
            if (response.ok) {
                loadRelationships();  // Reload the relationships
            } else {
                throw new Error('Failed to delete relationship');
            }
        } catch (error) {
            console.error('Error deleting relationship:', error);
            alert('Failed to delete relationship: ' + error.message);
        }
    };

    // Add edit handler
    const handleEditOtherRelationship = (relationship) => {
        setEditingRelationship(relationship);
        setShowEditOtherRelationship(true);
    };

    const handleRemoveSpouse = async (familyId) => {
        if (!confirm('Are you sure you want to remove the spouse from this family?')) return;
        try {
            const response = await fetch('api/individuals.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'remove_spouse',
                    family_id: familyId,
                    member_id: currentMemberId  // Add this line
                })
            });

            const data = await response.json();
            if (data.success) {
                loadMemberDetails();
            } else {
                throw new Error(data.error || 'Failed to remove spouse');
            }
        } catch (error) {
            console.error('Error removing spouse:', error);
            alert('Failed to remove spouse: ' + error.message);
        }
    };

    if (loading) return React.createElement('div', { className: 'text-center p-4' }, 'Loading...');
    if (error) {
        // Add home link to error state
        return React.createElement('div', { className: 'alert alert-danger' }, [
            error,
            React.createElement('a', {
                key: 'home-link',
                href: `#/tree/${currentTreeId}/members`,
                className: 'btn btn-link'
            }, 'Return to Members List')
        ]);
    }
    if (!member) return React.createElement('div', { className: 'alert alert-warning' }, 'Member not found');

    return React.createElement('div', { className: 'container-fluid' }, [
        React.createElement(Navigation, { 
            key: 'nav',
            title: `${member.first_name} ${member.last_name}`,
            leftMenuItems: Navigation.createTreeMenu(currentTreeId),
            rightMenuItems: Navigation.createUserMenu()
        }),
        React.createElement('main', { 
            key: 'main',
            className: 'container mx-auto px-4 py-16 mt-16 mb-16'
        }, 
            React.createElement(Row, { key: 'content-row' }, [
                React.createElement(Col, { key: 'col-basic', lg: 4 }, renderBasicInfo()),
                React.createElement(Col, { key: 'col-families', lg: 4 }, [
                    renderFamilyTabs(),
                    renderParents()
                ]),
                React.createElement(Col, { key: 'col-other', lg: 4 }, 
                    React.createElement(Card, { key: 'other-card' }, [
                        React.createElement(Card.Header, { key: 'other-header' }, 'Other Relationships'),
                        React.createElement(Card.Body, { key: 'other-body' }, 
                            relationships.length === 0 
                            ? 'No other relationships found'
                            : React.createElement('ul', { className: 'list-group' },
                                relationships.map(rel => 
                                    React.createElement('li', { 
                                        key: `rel-${rel.id}`,
                                        className: 'list-group-item d-flex justify-content-between align-items-center'
                                    }, [
                                        React.createElement('span', { key: 'rel-desc' }, [
                                            `${rel.person1_id === parseInt(currentMemberId) 
                                                ? rel.person2_first_name + ' ' + rel.person2_last_name 
                                                : rel.person1_first_name + ' ' + rel.person1_last_name}`,
                                            React.createElement('small', { 
                                                key: 'rel-type',
                                                className: 'text-muted ms-2'
                                            }, `(${rel.relationship_description})`)
                                        ]),
                                        React.createElement('div', { key: 'actions' }, [
                                            React.createElement('button', {
                                                key: 'edit',
                                                className: 'btn btn-sm btn-link',
                                                onClick: () => handleEditOtherRelationship(rel)
                                            }, '✏️'),
                                            React.createElement('button', {
                                                key: 'view',
                                                className: 'btn btn-sm btn-link',
                                                onClick: () => {
                                                    const otherId = rel.person1_id === parseInt(currentMemberId) 
                                                        ? rel.person2_id 
                                                        : rel.person1_id;
                                                    window.location.hash = `#/tree/${currentTreeId}/member/${otherId}`;
                                                }
                                            }, '👁️'),
                                            React.createElement('button', {
                                                key: 'delete',
                                                className: 'btn btn-sm btn-link text-danger',
                                                onClick: () => handleDeleteRelationship(rel.id)
                                            }, '🗑️')
                                        ])
                                    ])
                                )
                            )
                        )
                    ])
                )
            ])
        ),
        React.createElement(RelationshipModal, {
            key: 'relationship-modal',
            show: showRelationshipModal,
            onHide: () => setShowRelationshipModal(false),
            member: member,
            onSave: handleAddRelationship
        }),
        // Update onSave handler in EditOtherRelationship props
        React.createElement(EditOtherRelationship, {
            key: 'edit-relationship-modal',
            show: showEditOtherRelationship,
            onHide: () => {
                setShowEditOtherRelationship(false);
                setEditingRelationship(null);
            },
            relationship: editingRelationship,
            onSave: async (formData) => {
                try {
                    const response = await fetch(`api/individuals.php?action=edit_relationship&id=${editingRelationship.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            relationship_type_id: formData.relationship_type,
                            relation_start: formData.relation_start || null,
                            relation_end: formData.relation_end || null
                        })
                    });
                    
                    const data = await response.json();
                    if (!data.success) {
                        throw new Error(data.error || 'Failed to update relationship');
                    }
                    
                    loadRelationships();
                    setShowEditOtherRelationship(false);
                    setEditingRelationship(null);
                } catch (error) {
                    console.error('Error updating relationship:', error);
                    alert('Failed to update relationship: ' + error.message);
                }
            }
        }),
        React.createElement(AddSpouseModal, {
            key: 'add-spouse-modal',
            show: showAddSpouseModal,
            onHide: () => {
                setShowAddSpouseModal(false);
                setEditingFamily(null);
            },
            member: member,
            familyId: editingFamily?.id,
            spousePosition: editingFamily?.spousePosition
        })
    ]);
};
