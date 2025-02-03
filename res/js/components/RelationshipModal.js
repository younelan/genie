const RelationshipModal = ({ show, onHide, member, onSave }) => {
    const [activeTab, setActiveTab] = React.useState('spouse');
    const [formData, setFormData] = React.useState({
        spouse_type: 'existing',
        child_type: 'existing',
        parent1_type: 'existing',
        other_type: 'existing'
    });

    const [spouseFamilies, setSpouseFamilies] = React.useState([]);

    React.useEffect(() => {
        if (show && member) {
            loadSpouseFamilies();
        }
    }, [show, member]);

    const loadSpouseFamilies = async () => {
        if (!member?.id) {
            console.error('No member ID provided');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'spouses');
            formData.append('member_id', member.id);

            const response = await fetch('api/families.php', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (data.spouse_families) {
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
                        onChange: handleInputChange
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
                        onChange: handleInputChange
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
        const familyOptions = spouseFamilies.map(family => {
            const spouseName = family.spouse_name || 'Unknown Spouse';
            return React.createElement('option', {
                key: `family-${family.id}`,
                value: family.id
            }, `With ${spouseName}`);
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
                    onChange: handleInputChange
                }, [
                    React.createElement('option', { key: 'none', value: 'none' }, 'Single Parent'),
                    React.createElement('option', { key: 'existing', value: 'existing_family' }, 'Existing Family'),
                    React.createElement('option', { key: 'new', value: 'new' }, 'New Parent')
                ])
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
        return React.createElement('div', { className: 'form-group mb-3' }, [
            React.createElement('label', { key: 'label' }, `Select Existing ${type.charAt(0).toUpperCase() + type.slice(1)}:`),
            React.createElement('input', {
                key: 'input',
                type: 'text',
                className: 'form-control',
                id: `${type}_autocomplete`,
                list: `${type}-options`
            }),
            React.createElement('datalist', {
                key: 'datalist',
                id: `${type}-options`
            }),
            React.createElement('input', {
                key: 'hidden',
                type: 'hidden',
                name: `${type}_id`,
                id: `selected_${type}_id`
            })
        ]);
    };

    const renderNewPersonSection = (type) => {
        return React.createElement('div', { className: 'form-group mb-3' }, [
            React.createElement('input', {
                key: 'first-name',
                type: 'text',
                className: 'form-control mb-2',
                name: `${type}_first_name`,
                placeholder: 'First Name',
                required: true
            }),
            React.createElement('input', {
                key: 'last-name',
                type: 'text',
                className: 'form-control mb-2',
                name: `${type}_last_name`,
                placeholder: 'Last Name',
                required: true
            }),
            React.createElement('input', {
                key: 'birth-date',
                type: 'date',
                className: 'form-control mb-2',
                name: `${type}_birth_date`
            })
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
                    React.createElement('button', {
                        key: 'save-btn',
                        type: 'button',
                        className: 'btn btn-primary',
                        onClick: () => onSave(formData)
                    }, 'Save')
                ])
            ])
        )
    );
};
