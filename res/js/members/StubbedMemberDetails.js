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
    // NEW: add relationship types state as a lookup object keyed by code
    const [relationshipTypes, setRelationshipTypes] = React.useState({});

    // Add new state variables for edit modal
    const [showEditOtherRelationship, setShowEditOtherRelationship] = React.useState(false);
    const [editingRelationship, setEditingRelationship] = React.useState(null);

    // Add new state for add spouse modal
    const [showAddSpouseModal, setShowAddSpouseModal] = React.useState(false);
    const [editingFamily, setEditingFamily] = React.useState(null);

    // Add new state for relationship modal data
    const [relationshipModalData, setRelationshipModalData] = React.useState({
        tab: 'spouse',
        prefilledData: null
    });

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

    React.useEffect(() => {
        // Load relationship types from app endpoint
        fetch('api/app.php?action=relationship_types')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.types) {
                    // data.types is already an object with code => {description} structure
                    setRelationshipTypes(data.types);
                }
            })
            .catch(err => console.error('Error loading relationship types:', err));
    }, []);

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
        if (!confirm(T('Create a new family with no spouse?'))) return;
        
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
        if (!confirm(T('Are you sure you want to delete this family? This will remove all relationships.'))) return;
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
        if (!confirm(T('Are you sure you want to remove this child?'))) return;
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
    const renderBasicInfo = () => React.createElement('div', { 
        className: 'bg-white shadow-md lg:rounded-lg' 
    }, [
        React.createElement('div', { 
            key: 'header',
            className: 'bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 lg:rounded-t-lg flex justify-between items-center'
        }, [
            React.createElement('h4', { 
                className: 'text-lg font-medium text-white'
            }, T('Member Details')),
            React.createElement('div', {
                key: 'dropdown',
            }, React.createElement(Dropdown, {
                trigger: '⚙️',
                items: [
                    {
                        label: T('Visualize Descendants'),
                        href: `#/tree/${currentTreeId}/member/${currentMemberId}/descendants`,
                        className: 'text-gray-700' // Add text color class
                    },
                    {
                        label: T('Add Relationship'),
                        onClick: () => setShowRelationshipModal(true),
                        className: 'text-gray-700' // Add text color class
                    },
                    {
                        label: T('Delete Member'),
                        onClick: handleDeleteMember,
                        className: 'text-red-600' // Keep red for delete
                    }
                ]
            }))
        ]),
        React.createElement('div', { 
            key: 'body',
            className: 'p-4'
        },
            React.createElement('form', { 
                onSubmit: handleSubmit,
                className: 'space-y-4' 
            }, [
                // Name fields
                React.createElement('div', { key: 'name-group', className: 'space-y-2' }, [
                    React.createElement('label', { className: 'block text-sm font-medium text-gray-700' }, T('Name')),
                    React.createElement('input', {
                        type: 'text',
                        name: 'first_name',
                        value: formData.first_name || '',
                        onChange: handleInputChange,
                        className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary'
                    }),
                    React.createElement('input', {
                        key: 'last-name',
                        type: 'text',
                        name: 'last_name',
                        value: formData.last_name || '',
                        onChange: handleInputChange,
                        className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary'
                    })
                ]),
                // Birth fields
                React.createElement('div', { key: 'birth-group', className: 'space-y-2' }, [
                    React.createElement('label', { className: 'block text-sm font-medium text-gray-700' }, T('Birth')),
                    React.createElement('input', {
                        key: 'birth-date',
                        type: 'date',
                        name: 'birth_date',
                        value: formData.birth_date || '',
                        onChange: handleInputChange,
                        className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary'
                    }),
                    React.createElement('input', {
                        key: 'birth-place',
                        type: 'text',
                        name: 'birth_place',
                        value: formData.birth_place || '',
                        onChange: handleInputChange,
                        className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary',
                        placeholder: T('Place of Birth')
                    })
                ]),
                // Gender and Alive
                React.createElement('div', { key: 'status-group', className: 'space-y-2' }, [
                    React.createElement('div', { key: 'gender-field', className: 'space-y-2' }, [
                        React.createElement('label', { className: 'block text-sm font-medium text-gray-700' }, T('Gender')),
                        React.createElement('select', {
                            key: 'gender-select',
                            name: 'gender',
                            value: formData.gender,
                            onChange: handleInputChange,
                            className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary'
                        }, [
                            React.createElement('option', { key: 'male', value: 'M' }, T('Male')),
                            React.createElement('option', { key: 'female', value: 'F' }, T('Female'))
                        ])
                    ]),
                    React.createElement('div', { key: 'alive-field', className: 'flex items-center space-x-2' }, [
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
                            className: 'block text-sm font-medium text-gray-700'
                        }, T('Alive'))
                    ])
                ]),
                // Death fields (shown if not alive)
                !showDeathFields ? null : React.createElement('div', { key: 'death-group', className: 'space-y-2' }, [
                    React.createElement('label', { className: 'block text-sm font-medium text-gray-700' }, T('Death')),
                    React.createElement('input', {
                        key: 'death-date',
                        type: 'date',
                        name: 'death_date',
                        value: formData.death_date || '',
                        onChange: handleInputChange,
                        className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary'
                    }),
                    React.createElement('input', {
                        key: 'death-place',
                        type: 'text',
                        name: 'death_place',
                        value: formData.death_place || '',
                        onChange: handleInputChange,
                        className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary',
                        placeholder: T('Place of Death')
                    })
                ]),
                // Fix the TagInput props here - change memberId to rowId
                React.createElement(TagInput, {
                    key: 'tags',
                    rowId: currentMemberId,  // Use currentMemberId for rowId
                    treeId: currentTreeId,
                    tagType: 'INDI'
                }),
                React.createElement('button', {
                    key: 'submit',
                    type: 'submit',
                    className: 'btn btn-primary'
                }, T('Save Changes'))
            ])
        )
    ]);

    const handleDeleteMember = async () => {
        if (!confirm(T('Are you sure you want to delete this member?'))) return;
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

    // Update the renderFamilyTab function
    const renderFamilyTab = (family) => {
        const dropdownItems = [
            family.spouse_id && {
                label: T('View Spouse'),
                href: `#/tree/${currentTreeId}/member/${family.spouse_id}`
            },
            {
                label: T('Add Child'),
                onClick: () => {
                    setRelationshipModalData({
                        tab: 'child',
                        prefilledData: {
                            family_id: family.id
                        }
                    });
                    setShowRelationshipModal(true);
                }
            },
            {
                label: family.spouse_id ? T('Remove Spouse') : T('Add Spouse'),
                onClick: family.spouse_id ? 
                    () => handleRemoveSpouse(family.id) : 
                    () => handleEditFamily(family.id)
            },
            {
                label: T('Delete Family'),
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

    const renderFamilyTabs = () => React.createElement('div', { 
        className: 'bg-white shadow-md lg:rounded-lg' 
    }, [
        React.createElement('div', { 
            key: 'header',
            className: 'bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 lg:rounded-t-lg flex justify-between items-center'
        }, React.createElement('h4', { className: 'text-lg font-medium text-white' }, T('Families'))),
        React.createElement('div', { 
            key: 'body',
            className: 'p-4'
        }, [
            React.createElement('div', {
                key: 'family-nav',
                className: 'flex space-x-2 mb-3'
            }, [
                ...spouseFamilies.map(family => 
                    React.createElement('div', { 
                        key: `family-tab-${family.id}`,
                        className: 'flex items-center'
                    }, 
                        renderFamilyTab(family)
                    )
                ),
                React.createElement('div', {
                    key: 'add-family',
                    className: 'ms-2'
                }, React.createElement('button', {
                    key: 'add-family-link',
                    onClick: handleAddFamily,
                    className: 'text-primary hover:text-primary-dark'
                }, '+'))
            ]),
            // Active family content
            spouseFamilies.map(family => 
                family.id === activeFamily && React.createElement('div', {
                    key: `family-content-${family.id}`
                }, [
                    React.createElement('div', { key: 'marriage-details', className: 'mb-3' }, [
                        React.createElement('strong', { key: 'marriage-label' }, T('Marriage Date:')),
                        React.createElement('span', { key: 'marriage-date' }, family.marriage_date || 'Unknown'),
                        family.divorce_date && [
                            React.createElement('br', { key: 'br' }),
                            React.createElement('strong', { key: 'divorce-label' }, T('Divorce Date:')),
                            React.createElement('span', { key: 'divorce-date' }, family.divorce_date)
                        ]
                    ]),
                    // Add TagInput for family before the children section
                    React.createElement(TagInput, {
                        key: `family-tags-${family.id}`,
                        rowId: family.id,      // Use family.id instead of memberId
                        treeId: currentTreeId,
                        tagType: 'FAM'         // Use FAM type for family tags
                    }),
                    React.createElement('h6', { key: 'children-header' }, T('Children')),
                    React.createElement('ul', { key: 'children-list', className: 'list-none space-y-2' },
                        (family.children || []).map(child =>
                            React.createElement('li', {
                                key: `child-${child.id}`,
                                className: 'flex justify-between items-center'
                            }, [
                                React.createElement('a', {
                                    key: 'child-link',
                                    href: `#/tree/${currentTreeId}/member/${child.id}`,
                                    className: 'text-primary hover:text-primary-dark',
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
        if (!confirm(T('Are you sure you want to remove this parent relationship?'))) return;
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

    const renderParents = () => React.createElement('div', { 
        className: 'bg-white shadow-md lg:rounded-lg mt-4' 
    }, [
        React.createElement('div', { 
            key: 'header',
            className: 'bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 lg:rounded-t-lg flex justify-between items-center'
        }, [
            React.createElement('h4', { className: 'text-lg font-medium text-white' }, T('Parents')),
            React.createElement(Dropdown, {
                key: 'parents-actions',
                trigger: '⚙️',
                items: [
                    {
                        label: T('Add Parents'),
                        onClick: () => {
                            setRelationshipModalData({
                                tab: 'parent',
                                prefilledData: null
                            });
                            setShowRelationshipModal(true);
                        },
                        className: 'text-gray-700' // Add default text color
                    }
                ]
            })
        ]),
        React.createElement('div', { 
            key: 'body',
            className: 'p-4'
        },
            childFamilies.length === 0 ?
                React.createElement('div', { className: 'text-muted' }, T('No parents added')) :
                childFamilies.map(family => 
                    React.createElement('div', { 
                        key: `family-${family.id}`, 
                        className: 'flex justify-between items-center mb-2'
                    }, [
                        React.createElement('div', { 
                            key: 'parents-names',
                            className: 'flex gap-2' 
                        }, [
                            family.husband_id && React.createElement('a', {
                                key: 'father',
                                href: `#/tree/${currentTreeId}/member/${family.husband_id}`,
                                className: 'text-primary hover:text-primary-dark'
                            }, family.husband_name),
                            (family.husband_id && family.wife_id) && React.createElement('span', { key: 'separator' }, ' & '),
                            family.wife_id && React.createElement('a', {
                                key: 'mother',
                                href: `#/tree/${currentTreeId}/member/${family.wife_id}`,
                                className: 'text-primary hover:text-primary-dark'
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
        if (!confirm(T('Are you sure you want to delete this relationship?'))) return;
        
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
        if (!confirm(T('Are you sure you want to remove the spouse from this family?'))) return;
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

    // Move renderRelationship here to access relationshipTypes from state
    const renderRelationship = (rel) => {
        const otherPerson = rel.person_id1 === parseInt(currentMemberId) 
            ? `${rel.person2_first_name} ${rel.person2_last_name}`
            : `${rel.person1_first_name} ${rel.person1_last_name}`;

        const otherPersonId = rel.person_id1 === parseInt(currentMemberId) 
            ? rel.person_id2 
            : rel.person_id1;

        return React.createElement('li', {
            key: `rel-${rel.id}`,
            className: 'flex justify-between items-center px-4 py-2 hover:bg-gray-50'
        }, [
            React.createElement('div', { className: 'flex items-center gap-2' }, [
                React.createElement('a', {
                    href: `#/tree/${currentTreeId}/member/${otherPersonId}`,
                    className: 'text-primary hover:text-primary-dark'
                }, otherPerson),
                React.createElement('span', { 
                    className: 'text-sm text-gray-500' 
                }, `(${rel.description})`)
            ]),
            React.createElement('div', { key: 'actions' }, [
                React.createElement('button', {
                    key: 'edit',
                    className: 'btn btn-sm btn-link',
                    onClick: () => handleEditOtherRelationship(rel)
                }, '✏️'),
                React.createElement('button', {
                    key: 'delete',
                    className: 'btn btn-sm btn-link text-danger',
                    onClick: () => handleDeleteRelationship(rel.id)
                }, '🗑️')
            ])
        ]);
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

    // Update main layout structure
    return React.createElement('div', { className: 'min-h-screen flex flex-col' }, [
        React.createElement(Navigation, { 
            key: 'nav',
            title: `${member.first_name} ${member.last_name}`,
            leftMenuItems: Navigation.createTreeMenu(currentTreeId),
            rightMenuItems: Navigation.createUserMenu()
        }),
        React.createElement('main', { 
            key: 'main',
            className: 'w-full lg:container lg:mx-auto px-0 lg:px-4 py-2 mt-14 flex-grow'
        }, 
            React.createElement('div', { 
                className: 'grid grid-cols-1 lg:grid-cols-3 gap-4 mt-0 lg:mt-16'
            }, [
                // Column 1: Basic Info
                React.createElement('div', { key: 'col-basic' },
                    React.createElement(Card, { className: 'shadow-md' }, [
                        React.createElement(Card.Header, { 
                            key: 'header',
                            className: 'flex justify-between items-center'
                        }, [
                            React.createElement('h4', { className: 'text-lg font-medium' }, T('Member Details')),
                            React.createElement('div', {
                                key: 'dropdown',
                            }, React.createElement(Dropdown, {
                                trigger: '⚙️',
                                items: [
                                    {
                                        label: T('Visualize Descendants'),
                                        href: `#/tree/${currentTreeId}/member/${currentMemberId}/descendants`,
                                        className: 'text-gray-700' // Add text color class
                                    },
                                    {
                                        label: T('Add Relationship'),
                                        onClick: () => setShowRelationshipModal(true),
                                        className: 'text-gray-700' // Add text color class
                                    },
                                    {
                                        label: T('Delete Member'),
                                        onClick: handleDeleteMember,
                                        className: 'text-red-600' // Keep red for delete
                                    }
                                ]
                            }))
                        ]),
                        React.createElement('div', { 
                            key: 'body',
                            className: 'p-4'
                        },
                            React.createElement('form', { 
                                onSubmit: handleSubmit,
                                className: 'space-y-4' 
                            }, [
                                // Name fields
                                React.createElement('div', { key: 'name-group', className: 'space-y-2' }, [
                                    React.createElement('label', { className: 'block text-sm font-medium text-gray-700' }, T('Name')),
                                    React.createElement('input', {
                                        type: 'text',
                                        name: 'first_name',
                                        value: formData.first_name || '',
                                        onChange: handleInputChange,
                                        className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary'
                                    }),
                                    React.createElement('input', {
                                        key: 'last-name',
                                        type: 'text',
                                        name: 'last_name',
                                        value: formData.last_name || '',
                                        onChange: handleInputChange,
                                        className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary'
                                    })
                                ]),
                                // Birth fields
                                React.createElement('div', { key: 'birth-group', className: 'space-y-2' }, [
                                    React.createElement('label', { className: 'block text-sm font-medium text-gray-700' }, T('Birth')),
                                    React.createElement('input', {
                                        key: 'birth-date',
                                        type: 'date',
                                        name: 'birth_date',
                                        value: formData.birth_date || '',
                                        onChange: handleInputChange,
                                        className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary'
                                    }),
                                    React.createElement('input', {
                                        key: 'birth-place',
                                        type: 'text',
                                        name: 'birth_place',
                                        value: formData.birth_place || '',
                                        onChange: handleInputChange,
                                        className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary',
                                        placeholder: T('Place of Birth')
                                    })
                                ]),
                                // Gender and Alive
                                React.createElement('div', { key: 'status-group', className: 'space-y-2' }, [
                                    React.createElement('div', { key: 'gender-field', className: 'space-y-2' }, [
                                        React.createElement('label', { className: 'block text-sm font-medium text-gray-700' }, T('Gender')),
                                        React.createElement('select', {
                                            key: 'gender-select',
                                            name: 'gender',
                                            value: formData.gender,
                                            onChange: handleInputChange,
                                            className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary'
                                        }, [
                                            React.createElement('option', { key: 'male', value: 'M' }, T('Male')),
                                            React.createElement('option', { key: 'female', value: 'F' }, T('Female'))
                                        ])
                                    ]),
                                    React.createElement('div', { key: 'alive-field', className: 'flex items-center space-x-2' }, [
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
                                            className: 'block text-sm font-medium text-gray-700'
                                        }, T('Alive'))
                                    ])
                                ]),
                                // Death fields (shown if not alive)
                                !showDeathFields ? null : React.createElement('div', { key: 'death-group', className: 'space-y-2' }, [
                                    React.createElement('label', { className: 'block text-sm font-medium text-gray-700' }, T('Death')),
                                    React.createElement('input', {
                                        key: 'death-date',
                                        type: 'date',
                                        name: 'death_date',
                                        value: formData.death_date || '',
                                        onChange: handleInputChange,
                                        className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary'
                                    }),
                                    React.createElement('input', {
                                        key: 'death-place',
                                        type: 'text',
                                        name: 'death_place',
                                        value: formData.death_place || '',
                                        onChange: handleInputChange,
                                        className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary focus:border-primary',
                                        placeholder: T('Place of Death')
                                    })
                                ]),
                                // Fix the TagInput props here - change memberId to rowId
                                React.createElement(TagInput, {
                                    key: 'tags',
                                    rowId: currentMemberId,  // Use currentMemberId for rowId
                                    treeId: currentTreeId,
                                    tagType: 'INDI'
                                }),
                                React.createElement('button', {
                                    key: 'submit',
                                    type: 'submit',
                                    className: 'btn btn-primary'
                                }, T('Save Changes'))
                            ])
                        )
                    ])
                ),
                // Column 2: Families
                React.createElement('div', { key: 'col-families' }, [
                    React.createElement(Card, { className: 'shadow-md' }, [
                        React.createElement(Card.Header, { 
                            key: 'header',
                            className: 'flex justify-between items-center'
                        }, [
                            React.createElement('h4', { className: 'text-lg font-medium' }, T('Families')),
                            React.createElement('div', { key: 'add-family' }, /* ...existing add family button... */)
                        ]),
                        React.createElement(Card.Body, { key: 'body' },
                            // ...existing families content...
                        )
                    ]),
                    // Parents section
                    React.createElement(Card, { 
                        className: 'shadow-md mt-4'
                    }, [
                        React.createElement(Card.Header, { 
                            key: 'header',
                            className: 'flex justify-between items-center'
                        }, [
                            React.createElement('h4', { className: 'text-lg font-medium' }, T('Parents')),
                            // ...existing parents dropdown...
                        ]),
                        React.createElement(Card.Body, { key: 'body' },
                            // ...existing parents content...
                        )
                    ])
                ]),
                // Column 3: Other Relationships
                React.createElement('div', { key: 'col-other' },
                    React.createElement(Card, { className: 'shadow-md' }, [
                        React.createElement(Card.Header, { 
                            key: 'header',
                            className: 'flex justify-between items-center'
                        }, [
                            React.createElement('h4', { className: 'text-lg font-medium' }, T('Other Relationships')),
                            // ...existing dropdown...
                        ]),
                        React.createElement(Card.Body, { key: 'body' },
                            // ...existing relationships content...
                        )
                    ])
                )
            ])
        ),
        React.createElement(RelationshipModal, {
            key: 'relationship-modal',
            show: showRelationshipModal,
            onHide: () => {
                setShowRelationshipModal(false);
                setRelationshipModalData({ tab: 'spouse', prefilledData: null });
            },
            member: member,
            initialTab: relationshipModalData.tab,
            prefilledData: relationshipModalData.prefilledData,
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
                            relcode: formData.relcode,  // Changed from relationship_type_id
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
