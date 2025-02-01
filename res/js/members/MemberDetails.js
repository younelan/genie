const MemberDetails = () => {
    const [member, setMember] = React.useState(null);
    const [spouseFamilies, setSpouseFamilies] = React.useState([]);
    const [childFamilies, setChildFamilies] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(null);
    const [activeFamily, setActiveFamily] = React.useState(null);
    
    // Get memberId from URL hash
    const memberId = window.location.hash.split('/').pop();
    const treeId = window.location.hash.split('/')[2];

    React.useEffect(() => {
        loadMemberDetails();
    }, [memberId]);

    // Set first family as active when data loads
    React.useEffect(() => {
        if (spouseFamilies?.length > 0 && !activeFamily) {
            setActiveFamily(spouseFamilies[0].id);
        }
    }, [spouseFamilies]);

    const loadMemberDetails = async () => {
        try {
            const response = await fetch(`api/individuals.php?action=details&id=${memberId}`);
            const data = await response.json();
            if (data.success) {
                setMember(data.data.member);
                setSpouseFamilies(data.data.spouseFamilies);
                setChildFamilies(data.data.childFamilies);
            }
        } catch (error) {
            setError('Failed to load member details');
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        try {
            const response = await fetch('api/individuals.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...data, id: memberId })
            });
            if (response.ok) {
                loadMemberDetails();
            }
        } catch (error) {
            console.error('Error updating member:', error);
        }
    };

    // Add handlers for family actions
    const handleAddFamily = async () => {
        try {
            const response = await fetch('api/families.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'create',
                    member_id: memberId,
                    tree_id: treeId
                })
            });
            if (response.ok) {
                loadMemberDetails();
            }
        } catch (error) {
            console.error('Error adding family:', error);
        }
    };

    const handleDeleteFamily = async (familyId) => {
        if (!confirm('Are you sure you want to delete this family?')) return;
        try {
            const response = await fetch(`api/families.php?id=${familyId}`, {
                method: 'DELETE'
            });
            if (response.ok) {
                loadMemberDetails();
            }
        } catch (error) {
            console.error('Error deleting family:', error);
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
                    child_id: childId,
                    family_id: familyId
                })
            });
            if (response.ok) {
                loadMemberDetails();
            }
        } catch (error) {
            console.error('Error removing child:', error);
        }
    };

    const renderBasicInfo = () => React.createElement(Card, { key: 'basic-info-card' }, [
        React.createElement(Card.Header, { key: 'header' }, 'Member Details'),
        React.createElement(Card.Body, { key: 'body' },
            React.createElement('form', { onSubmit: handleSubmit }, [
                // Name fields
                React.createElement('div', { key: 'name-group', className: 'mb-3' }, [
                    React.createElement('label', { key: 'name-label' }, 'Name'),
                    React.createElement('input', {
                        key: 'first-name',
                        type: 'text',
                        name: 'first_name',
                        defaultValue: member?.first_name,
                        className: 'form-control mb-2'
                    }),
                    React.createElement('input', {
                        key: 'last-name',
                        type: 'text',
                        name: 'last_name',
                        defaultValue: member?.last_name,
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
                        defaultValue: member?.birth_date,
                        className: 'form-control mb-2'
                    }),
                    React.createElement('input', {
                        key: 'birth-place',
                        type: 'text',
                        name: 'birth_place',
                        defaultValue: member?.birth_place,
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
                            defaultValue: member?.gender,
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
                            defaultChecked: member?.alive === '1',
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
                !member?.alive && React.createElement('div', { key: 'death-group', className: 'mb-3' }, [
                    React.createElement('label', { key: 'death-label' }, 'Death'),
                    React.createElement('input', {
                        key: 'death-date',
                        type: 'date',
                        name: 'death_date',
                        defaultValue: member?.death_date,
                        className: 'form-control mb-2'
                    }),
                    React.createElement('input', {
                        key: 'death-place',
                        type: 'text',
                        name: 'death_place',
                        defaultValue: member?.death_place,
                        className: 'form-control',
                        placeholder: 'Place of Death'
                    })
                ]),
                React.createElement('button', {
                    key: 'submit',
                    type: 'submit',
                    className: 'btn btn-primary'
                }, 'Save Changes')
            ])
        )
    ]);

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
                        key: `family-tab-${family.id}`,
                        className: 'd-flex'
                    }, [
                        React.createElement(Nav.Link, {
                            key: 'tab-link',
                            active: activeFamily === family.id,
                            onClick: () => setActiveFamily(family.id)
                        }, family.spouse_name || 'Unknown Spouse'),
                        React.createElement('div', {
                            key: 'dropdown',
                            className: 'dropdown'
                        }, [
                            React.createElement('button', {
                                key: 'dropdown-toggle',
                                className: 'btn btn-link dropdown-toggle',
                                'data-bs-toggle': 'dropdown'
                            }, '⚙️'),
                            React.createElement('ul', {
                                key: 'dropdown-menu',
                                className: 'dropdown-menu'
                            }, [
                                family.spouse_id && React.createElement('li', { key: 'view-spouse' },
                                    React.createElement('a', {
                                        className: 'dropdown-item',
                                        href: `#/tree/${treeId}/member/${family.spouse_id}`
                                    }, 'View Spouse')
                                ),
                                React.createElement('li', { key: 'delete-family' },
                                    React.createElement('a', {
                                        className: 'dropdown-item text-danger',
                                        onClick: () => handleDeleteFamily(family.id)
                                    }, 'Delete Family')
                                )
                            ])
                        ])
                    ])
                ),
                React.createElement(Nav.Item, {
                    key: 'add-family',
                    className: 'ms-2'
                }, React.createElement(Nav.Link, {
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
                                    href: `#/tree/${treeId}/member/${child.id}`,
                                    className: 'text-decoration-none'
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

    const renderParents = () => React.createElement(Card, { key: 'parents-card', className: 'mt-3' }, [
        React.createElement(Card.Header, { key: 'header' }, 'Parents'),
        React.createElement(Card.Body, { key: 'body' },
            childFamilies.map(family => 
                React.createElement('div', { key: `family-${family.id}`, className: 'd-flex gap-2' }, [
                    family.husband_id && React.createElement('a', {
                        key: 'father',
                        href: `#/tree/${treeId}/member/${family.husband_id}`,
                        className: 'text-decoration-none'
                    }, family.husband_name),
                    (family.husband_id && family.wife_id) && React.createElement('span', { key: 'separator' }, ' & '),
                    family.wife_id && React.createElement('a', {
                        key: 'mother',
                        href: `#/tree/${treeId}/member/${family.wife_id}`,
                        className: 'text-decoration-none'
                    }, family.wife_name)
                ])
            )
        )
    ]);

    if (loading) return React.createElement('div', { className: 'text-center p-4' }, 'Loading...');
    if (error) return React.createElement('div', { className: 'alert alert-danger' }, error);
    if (!member) return React.createElement('div', { className: 'alert alert-warning' }, 'Member not found');

    return React.createElement('div', { className: 'container-fluid' }, [
        React.createElement(AppHeader, { key: 'header' }),
        React.createElement('main', { 
            key: 'main',
            className: 'container mx-auto px-4 py-16 mt-16 mb-16'
        }, 
            React.createElement(Row, null, [
                React.createElement(Col, { lg: 4 }, renderBasicInfo()),
                React.createElement(Col, { lg: 4 }, [
                    renderFamilyTabs(),
                    renderParents()
                ]),
                React.createElement(Col, { lg: 4 }, 
                    React.createElement(Card, null, [
                        React.createElement(Card.Header, null, 'Other Relationships'),
                        React.createElement(Card.Body, null, 
                            // Relationships content here
                            'Other relationships will be displayed here'
                        )
                    ])
                )
            ])
        ),
        React.createElement(AppFooter, { key: 'footer' })
    ]);
};
