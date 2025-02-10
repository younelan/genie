const AddSpouseModal = ({ show, onHide, member, familyId, spousePosition }) => {
    const [formData, setFormData] = React.useState({
        spouse_type: 'existing',
        spouse_first_name: '',
        spouse_last_name: '',
        spouse_birth_date: '',
        spouse_gender: spousePosition === 'husband' ? 'M' : 'F'
    });

    const handleSpouseTypeChange = (e) => {
        const newType = e.target.value;
        console.log("Setting spouse type to:", newType); // Debug
        setFormData(prev => ({
            ...prev,
            spouse_type: newType,
            // Reset fields based on type
            ...(newType === 'existing' ? {
                spouse_first_name: '',
                spouse_last_name: '',
                spouse_birth_date: '',
                spouse_id: null
            } : {
                spouse_id: null
            })
        }));
    };

    const handleSave = async () => {
        try {
            // Change to use JSON instead of FormData since our API expects JSON
            const requestData = {
                type: 'add_spouse_to_family',  // Match the type the API expects
                family_id: familyId,
                spouse_position: spousePosition,
                member_id: member.id,
                tree_id: member.tree_id,
                spouse_type: formData.spouse_type
            };

            if (formData.spouse_type === 'existing') {
                if (!formData.spouse_id) {
                    throw new Error('Please select a spouse');
                }
                requestData.spouse_id = formData.spouse_id;
            } else {
                // Add new person data
                requestData.spouse_first_name = formData.spouse_first_name;
                requestData.spouse_last_name = formData.spouse_last_name;
                requestData.spouse_birth_date = formData.spouse_birth_date;
                requestData.spouse_gender = formData.spouse_gender || (spousePosition === 'husband' ? 'M' : 'F');
            }

            const response = await fetch('api/families.php', {  // Changed endpoint to families.php
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            });

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to add spouse');
            }

            onHide();
            window.location.reload();
        } catch (error) {
            console.error('Error saving:', error);
            alert('Failed to save: ' + error.message);
        }
    };

    const renderContent = () => (
        React.createElement('div', { className: 'tab-pane active' }, [
            React.createElement('div', { key: 'type-selector', className: 'mb-3' },
                React.createElement('div', { className: 'btn-group w-100' }, [
                    React.createElement('input', {
                        key: 'existing',
                        type: 'radio',
                        className: 'btn-check',
                        name: 'add_spouse_type', // Changed to be unique
                        id: 'add_spouse_existing', // Changed to be unique
                        value: 'existing',
                        checked: formData.spouse_type === 'existing',
                        onChange: handleSpouseTypeChange,
                        autoComplete: 'off'
                    }),
                    React.createElement('label', {
                        className: 'btn btn-outline-primary',
                        htmlFor: 'add_spouse_existing' // Match new ID
                    }, 'Existing Person'),
                    React.createElement('input', {
                        key: 'new',
                        type: 'radio',
                        className: 'btn-check',
                        name: 'add_spouse_type', // Changed to be unique
                        id: 'add_spouse_new', // Changed to be unique
                        value: 'new',
                        checked: formData.spouse_type === 'new',
                        onChange: handleSpouseTypeChange,
                        autoComplete: 'off'
                    }),
                    React.createElement('label', {
                        className: 'btn btn-outline-primary',
                        htmlFor: 'add_spouse_new' // Match new ID
                    }, 'New Person')
                ])
            ),
            formData.spouse_type === 'existing' ? renderExistingPersonSection() : renderNewPersonSection()
        ])
    );

    const renderExistingPersonSection = () => {
        return React.createElement('div', {
            key: 'add-spouse-existing-section',
            className: 'form-group mb-3'
        }, [
            React.createElement('label', {
                key: 'spouse-label',
                htmlFor: 'add_spouse_autocomplete' // Changed to be unique
            }, 'Select Existing Person:'),
            React.createElement(Autocomplete, {
                key: 'spouse-autocomplete',
                type: 'spouse',
                memberId: member.id,
                treeId: member.tree_id,
                onSelect: (selected) => {
                    setFormData(prev => ({
                        ...prev,
                        spouse_id: selected.id
                    }));
                }
            })
        ]);
    };

    const renderNewPersonSection = () => {
        return React.createElement('div', { 
            key: 'spouse-new-section',
            className: 'form-group mb-3' 
        }, [
            React.createElement('div', { key: 'name-fields', className: 'mb-3' }, [
                React.createElement('label', { key: 'first-name-label', htmlFor: 'spouse_first_name' }, 'First Name'),
                React.createElement('input', {
                    key: 'first-name-input',
                    type: 'text',
                    className: 'form-control mb-2',
                    name: 'spouse_first_name',
                    value: formData.spouse_first_name || '',
                    placeholder: 'First Name',
                    onChange: handleInputChange,
                    required: true
                }),
                React.createElement('label', { key: 'last-name-label', htmlFor: 'spouse_last_name' }, 'Last Name'),
                React.createElement('input', {
                    key: 'last-name-input',
                    type: 'text',
                    className: 'form-control',
                    name: 'spouse_last_name',
                    value: formData.spouse_last_name || '',
                    placeholder: 'Last Name',
                    onChange: handleInputChange,
                    required: true
                })
            ]),
            React.createElement('div', { key: 'birth-date-field', className: 'mb-3' }, [
                React.createElement('label', { key: 'birth-date-label' }, 'Birth Date'),
                React.createElement('input', {
                    key: 'birth-date-input',
                    type: 'date',
                    className: 'form-control',
                    name: 'spouse_birth_date',
                    value: formData.spouse_birth_date || '',
                    onChange: handleInputChange
                })
            ]),
            React.createElement('div', { key: 'gender-field', className: 'mb-3' }, [
                React.createElement('label', { key: 'gender-label' }, 'Gender'),
                React.createElement('select', {
                    key: 'gender-select',
                    className: 'form-control',
                    name: 'spouse_gender',
                    value: formData.spouse_gender || (spousePosition === 'husband' ? 'M' : 'F'),
                    onChange: handleInputChange
                }, [
                    React.createElement('option', { key: 'select', value: '' }, 'Select Gender'),
                    React.createElement('option', { key: 'male', value: 'M' }, 'Male'),
                    React.createElement('option', { key: 'female', value: 'F' }, 'Female')
                ])
            ])
        ]);
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    return React.createElement('div', {
        className: `modal ${show ? 'show' : ''}`,
        style: { display: show ? 'block' : 'none' }
    }, 
        React.createElement('div', { 
            key: 'modal-dialog',
            className: 'modal-dialog' 
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
                    }, 'Add Spouse to Family'), // Changed title to be generic
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
                }, renderContent()),
                React.createElement('div', {
                    key: 'modal-footer',
                    className: 'modal-footer'
                }, [
                    React.createElement('button', {
                        key: 'close-btn',
                        type: 'button',
                        className: 'btn btn-secondary',
                        onClick: onHide
                    }, 'Cancel'),
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
