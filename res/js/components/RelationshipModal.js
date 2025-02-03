const RelationshipModal = ({ show, onHide, member, onSave }) => {
    const [activeTab, setActiveTab] = React.useState('spouse');
    const [formData, setFormData] = React.useState({
        spouse_type: 'existing',
        child_type: 'existing',
        parent1_type: 'existing',
        other_type: 'existing',
        relationship_type: 'spouse'
    });

    const [spouseFamilies, setSpouseFamilies] = React.useState([]);
    const [visibilityState, setVisibilityState] = React.useState({
        showParent2Fields: false,
        showExistingFamilySelect: false,
        showParent2New: false
    });

    React.useEffect(() => {
        if (show && member) {
            loadSpouseFamilies();
        }
    }, [show, member]);

    const handleAddEmptyFamily = async () => {
        try {
            const response = await fetch('api/individuals.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'add_relationship',
                    type: 'spouse',
                    member_id: member.id,
                    tree_id: member.tree_id,
                    member_gender: member.gender,
                    create_empty: true
                })
            });

            if (!response.ok) throw new Error('Network response was not ok');
            const result = await response.json();
            
            if (result.success) {
                // Refresh families list
                await loadSpouseFamilies();
                onHide();
                if (onSave) onSave(result);
            } else {
                throw new Error(result.message || 'Failed to create family');
            }
        } catch (error) {
            console.error('Error creating family:', error);
            alert('Failed to create family: ' + error.message);
        }
    };

    const loadSpouseFamilies = async () => {
        if (!member?.id) {
            console.error('No member ID provided');
            return;
        }

        try {
            const response = await fetch(`api/families.php?action=spouses&member_id=${member.id}`);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            if (data.success && data.spouse_families) {
                setSpouseFamilies(data.spouse_families);
            } else {
                setSpouseFamilies([]);
            }
        } catch (error) {
            console.error('Error loading families:', error);
            setSpouseFamilies([]);
        }
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handleParent1TypeChange = (e) => {
        const isExisting = e.target.value === 'existing';
        setFormData(prev => ({
            ...prev,
            parent1_type: e.target.value
        }));
        
        // Reset parent2 options if parent1 is new
        if (!isExisting) {
            setVisibilityState(prev => ({
                ...prev,
                showExistingFamilySelect: false
            }));
            setFormData(prev => ({
                ...prev,
                second_parent_option: 'none'
            }));
        }
    };

    const handleSecondParentOptionChange = (e) => {
        const value = e.target.value;
        setFormData(prev => ({
            ...prev,
            second_parent_option: value
        }));
        
        setVisibilityState(prev => ({
            ...prev,
            showExistingFamilySelect: value === 'existing_family',
            showParent2New: value === 'new',
            showParent2Fields: value !== 'none'
        }));
    };

    const handleSpouseTypeChange = (e) => {
        setFormData(prev => ({
            ...prev,
            spouse_type: e.target.value
        }));
    };

    const handleSave = async () => {
        try {
            const form = new FormData();
            
            // Common fields
            form.append('action', 'add_relationship');
            form.append('type', activeTab);
            form.append('member_id', member.id);
            form.append('tree_id', member.tree_id);
            form.append('member_gender', member.gender);
            form.append('relationship_type', activeTab);

            // Add form data based on active tab
            switch (activeTab) {
                case 'spouse':
                    form.append('spouse_type', formData.spouse_type);
                    if (formData.spouse_type === 'existing') {
                        if (!formData.spouse_id) {
                            throw new Error('Please select a spouse');
                        }
                        form.append('spouse_id', formData.spouse_id);
                    } else {
                        // For new spouse, append all relevant fields
                        Object.entries(formData).forEach(([key, value]) => {
                            if (key.startsWith('spouse_') && value) {
                                form.append(key, value);
                            }
                        });
                    }
                    if (formData.marriage_date) {
                        form.append('marriage_date', formData.marriage_date);
                    }
                    break;
                case 'child':
                    form.append('child_type', formData.child_type);
                    form.append('family_id', formData.family_id || 'new');
                    
                    if (formData.child_type === 'existing') {
                        if (!formData.child_id) {
                            throw new Error('Please select a child');
                        }
                        form.append('child_id', formData.child_id);
                    } else {
                        // For new child, append all child-related fields
                        if (!formData.child_first_name || !formData.child_last_name) {
                            throw new Error('Child first and last name are required');
                        }
                        Object.entries(formData).forEach(([key, value]) => {
                            if (key.startsWith('child_') && value) {
                                form.append(key, value);
                            }
                        });
                    }
                    break;
                // ... other cases remain the same ...
            }

            const response = await fetch('api/individuals.php', {
                method: 'POST',
                body: form
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server response:', errorText);
                throw new Error('Server response was not ok');
            }

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to add relationship');
            }

            // Close modal and reload page
            onHide();
            window.location.reload();

        } catch (error) {
            console.error('Error saving relationship:', error);
            alert('Failed to save relationship: ' + error.message);
        }
    };

    const handleNewPersonInputChange = (e, type) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [`${type}_${name}`]: value,
            // Also set the regular field name for API compatibility
            [name]: value
        }));
    };

    const renderSpouseTab = () => {
        return React.createElement('div', { className: 'tab-pane active' }, [
            React.createElement('div', { key: 'type-selector', className: 'mb-3' },
                React.createElement('div', { className: 'btn-group w-100' }, [
                    React.createElement('input', {
                        key: 'existing',
                        type: 'radio',
                        className: 'btn-check',
                        name: 'spouse_type',
                        id: 'existing_spouse',
                        value: 'existing',
                        checked: formData.spouse_type === 'existing',
                        onChange: handleSpouseTypeChange
                    }),
                    React.createElement('label', {
                        className: 'btn btn-outline-primary',
                        htmlFor: 'existing_spouse'
                    }, 'Existing Person'),
                    React.createElement('input', {
                        key: 'new',
                        type: 'radio',
                        className: 'btn-check',
                        name: 'spouse_type',
                        id: 'new_spouse',
                        value: 'new',
                        checked: formData.spouse_type === 'new',
                        onChange: handleSpouseTypeChange
                    }),
                    React.createElement('label', {
                        className: 'btn btn-outline-primary',
                        htmlFor: 'new_spouse'
                    }, 'New Person')
                ])
            ),
            formData.spouse_type === 'existing' ? 
                renderExistingPersonSection('spouse') : 
                renderNewPersonSection('spouse')
        ]);
    };

    const renderChildTab = () => {
        // Build family options including spouses' families
        const familyOptions = spouseFamilies.map(family => {
            let spouseName;
            if (family.spouse_name) {
                spouseName = family.spouse_name;
            } else if (member.id === family.husband_id) {
                spouseName = family.wife_name;
            } else {
                spouseName = family.husband_name;
            }
            return React.createElement('option', {
                key: `family-${family.id}`,
                value: family.id
            }, `With ${spouseName || 'Unknown Spouse'}`);
        });

        return React.createElement('div', { className: 'tab-pane' }, [
            React.createElement('div', { 
                key: 'family-select',
                className: 'form-group mb-3' 
            }, [
                React.createElement('label', { key: 'family-label' }, 'Family:'),
                React.createElement('select', {
                    key: 'family-select',
                    className: 'form-control',
                    name: 'family_id',
                    onChange: handleInputChange
                }, [
                    React.createElement('option', { 
                        key: 'select-family',
                        value: ''
                    }, '-- Select Family --'),
                    ...familyOptions,
                    React.createElement('option', {
                        key: 'new-family',
                        value: 'new'
                    }, 'New Family (No Spouse)')
                ])
            ]),
            React.createElement('div', { key: 'type-selector' }, 
                renderPersonTypeSelector('child')
            ),
            formData.child_type === 'existing' ? 
                renderExistingPersonSection('child') : 
                renderNewPersonSection('child', true)
        ]);
    };

    const renderParentTab = () => {
        return React.createElement('div', { className: 'tab-pane' }, [
            React.createElement('h5', { key: 'parent1-header' }, 'First Parent'),
            React.createElement('div', { key: 'parent1-section' }, [
                renderPersonTypeSelector('parent1'),
                formData.parent1_type === 'existing' ? 
                    renderExistingPersonSection('parent1') : 
                    renderNewPersonSection('parent1', true)
            ]),
            React.createElement('h5', { key: 'parent2-header' }, 'Second Parent'),
            React.createElement('div', { key: 'parent2-section' }, [
                React.createElement('select', {
                    key: 'second-parent-select',
                    className: 'form-control mb-3',
                    name: 'second_parent_option',
                    value: formData.second_parent_option,
                    onChange: handleSecondParentOptionChange
                }, [
                    React.createElement('option', { key: 'none', value: 'none' }, 'Single Parent'),
                    formData.parent1_type === 'existing' && React.createElement('option', { 
                        key: 'existing', 
                        value: 'existing_family' 
                    }, 'Existing Family'),
                    React.createElement('option', { key: 'new', value: 'new' }, 'New Parent')
                ].filter(Boolean)),

                visibilityState.showExistingFamilySelect && React.createElement(Autocomplete, {
                    key: 'family-autocomplete',
                    type: 'family',
                    memberId: member.id,
                    treeId: member.tree_id,
                    onSelect: (selected) => {
                        setFormData(prev => ({
                            ...prev,
                            existing_family_id: selected.id
                        }));
                    }
                }),

                visibilityState.showParent2Fields && formData.second_parent_option === 'existing' && 
                React.createElement(Autocomplete, {
                    key: 'parent2-autocomplete',
                    type: 'parent2',
                    memberId: member.id,
                    treeId: member.tree_id,
                    onSelect: (selected) => {
                        setFormData(prev => ({
                            ...prev,
                            parent2_id: selected.id
                        }));
                    }
                }),

                visibilityState.showParent2New && renderNewPersonSection('parent2', true)
            ])
        ]);
    };

    const renderOtherTab = () => {
        return React.createElement('div', { className: 'tab-pane' }, [
            React.createElement('div', { 
                key: 'relationship-type',
                className: 'form-group mb-3' 
            }, [
                React.createElement('label', { key: 'type-label' }, 'Relationship Type:'),
                React.createElement('select', {
                    key: 'type-select',
                    className: 'form-control',
                    name: 'relationship_type',
                    onChange: handleInputChange
                }, [
                    React.createElement('option', { key: 'cousin', value: 'COUSIN' }, 'Cousin'),
                    React.createElement('option', { key: 'sibling', value: 'SIBLING' }, 'Sibling'),
                    React.createElement('option', { key: 'aunt', value: 'AUNT' }, 'Aunt/Uncle'),
                    React.createElement('option', { key: 'niece', value: 'NIECE' }, 'Niece/Nephew')
                ])
            ]),
            React.createElement('div', { key: 'type-selector' }, 
                renderPersonTypeSelector('other')
            ),
            formData.other_type === 'existing' ? 
                renderExistingPersonSection('other') : 
                renderNewPersonSection('other', true)
        ]);
    };

    const renderExistingPersonSection = (type) => {
        const typeLabel = type.charAt(0).toUpperCase() + type.slice(1);
        return React.createElement('div', { 
            key: `${type}-existing-section`,
            className: 'form-group mb-3' 
        }, [
            React.createElement('label', { 
                key: `${type}-label`,
                htmlFor: `${type}_autocomplete`
            }, `Select Existing ${typeLabel}:`),
            React.createElement(Autocomplete, {
                key: `${type}-autocomplete`,
                type: type,
                memberId: member.id,
                treeId: member.tree_id,
                onSelect: (selected) => {
                    setFormData(prev => ({
                        ...prev,
                        [`${type}_id`]: selected.id
                    }));
                }
            })
        ]);
    };

    const renderNewPersonSection = (type) => {
        return React.createElement('div', { 
            key: `${type}-new-section`,
            className: 'form-group mb-3' 
        }, [
            React.createElement('input', {
                key: `${type}-first-name`,
                type: 'text',
                className: 'form-control mb-2',
                name: 'first_name',
                placeholder: 'First Name',
                onChange: (e) => handleNewPersonInputChange(e, type),
                required: true
            }),
            React.createElement('input', {
                key: `${type}-last-name`,
                type: 'text',
                className: 'form-control mb-2',
                name: 'last_name',
                placeholder: 'Last Name',
                onChange: (e) => handleNewPersonInputChange(e, type),
                required: true
            }),
            React.createElement('input', {
                key: `${type}-birth-date`,
                type: 'date',
                className: 'form-control mb-2',
                name: 'birth_date',
                onChange: (e) => handleNewPersonInputChange(e, type)
            }),
            React.createElement('select', {
                key: `${type}-gender`,
                className: 'form-control',
                name: 'gender',
                onChange: (e) => handleNewPersonInputChange(e, type),
                value: formData[`${type}_gender`] || ''
            }, [
                React.createElement('option', { key: 'select', value: '' }, 'Select Gender'),
                React.createElement('option', { key: 'male', value: 'M' }, 'Male'),
                React.createElement('option', { key: 'female', value: 'F' }, 'Female')
            ])
        ]);
    };

    const renderPersonTypeSelector = (type) => {
        return React.createElement('div', { className: 'btn-group w-100 mb-3' }, [
            React.createElement('input', {
                key: `${type}-existing`,
                type: 'radio',
                className: 'btn-check',
                name: `${type}_type`,
                id: `existing_${type}`,
                value: 'existing',
                checked: formData[`${type}_type`] === 'existing',
                onChange: handleInputChange
            }),
            React.createElement('label', {
                key: `${type}-existing-label`,
                className: 'btn btn-outline-primary',
                htmlFor: `existing_${type}`
            }, 'Existing Person'),
            React.createElement('input', {
                key: `${type}-new`,
                type: 'radio',
                className: 'btn-check',
                name: `${type}_type`,
                id: `new_${type}`,
                value: 'new',
                checked: formData[`${type}_type`] === 'new',
                onChange: handleInputChange
            }),
            React.createElement('label', {
                key: `${type}-new-label`,
                className: 'btn btn-outline-primary',
                htmlFor: `new_${type}`
            }, 'New Person')
        ]);
    };

    const renderTabPanelContent = () => {
        const tabContent = React.createElement('div', {
            key: 'tab-content',
            className: 'tab-content pt-3'
        }, [
            React.createElement('div', {
                key: 'tab-panel',
                className: 'tab-pane active'
            }, [
                React.createElement('div', {
                    key: 'tab-panel-content'
                }, activeTab === 'spouse' ? renderSpouseTab() :
                   activeTab === 'child' ? renderChildTab() :
                   activeTab === 'parent' ? renderParentTab() :
                   renderOtherTab())
            ])
        ]);

        return tabContent;
    };

    const renderTabs = () => {
        const tabs = ['spouse', 'child', 'parent', 'other'];
        return React.createElement('ul', {
            key: 'tabs-list',
            className: 'nav nav-tabs',
            role: 'tablist'
        }, tabs.map(tab => 
            React.createElement('li', {
                key: `tab-item-${tab}`,
                className: 'nav-item',
                role: 'presentation'
            }, [
                React.createElement('button', {
                    key: `tab-button-${tab}`,
                    className: `nav-link ${activeTab === tab ? 'active' : ''}`,
                    onClick: () => setActiveTab(tab)
                }, tab.charAt(0).toUpperCase() + tab.slice(1))
            ])
        ));
    };

    return React.createElement('div', {
        className: `modal ${show ? 'show' : ''}`,
        style: { display: show ? 'block' : 'none' }
    }, 
        React.createElement('div', { 
            key: 'modal-dialog',
            className: 'modal-dialog modal-lg' 
        },
            React.createElement('div', { 
                key: 'modal-content',
                className: 'modal-content' 
            }, [
                React.createElement('div', { 
                    key: 'modal-header',
                    className: 'modal-header' 
                }, [
                    React.createElement('h5', { 
                        key: 'modal-title',
                        className: 'modal-title'
                    }, 'Add Relationship'),
                    React.createElement('button', {
                        key: 'close-button',
                        type: 'button',
                        className: 'btn-close',
                        onClick: onHide
                    })
                ]),
                React.createElement('div', {
                    key: 'modal-body',
                    className: 'modal-body'
                }, [
                    renderTabs(),
                    renderTabPanelContent()
                ]),
                React.createElement('div', {
                    key: 'modal-footer',
                    className: 'modal-footer'
                }, [
                    React.createElement('button', {
                        key: 'close-btn',
                        type: 'button',
                        className: 'btn btn-secondary',
                        onClick: onHide
                    }, 'Close'),
                    activeTab === 'spouse' && formData.spouse_type === 'new' ?
                        React.createElement('button', {
                            key: 'empty-family-btn',
                            type: 'button',
                            className: 'btn btn-outline-primary',
                            onClick: handleAddEmptyFamily
                        }, 'Create Empty Family')
                        : null,
                    React.createElement('button', {
                        key: 'save-btn',
                        type: 'button',
                        className: 'btn btn-primary',
                        onClick: handleSave
                    }, 'Save')
                ])
            ])
        )
    );
};
