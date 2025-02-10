const EditOtherRelationship = ({ show, onHide, relationship, onSave }) => {
    const [formData, setFormData] = React.useState({
        relationship_id: '',
        relcode: '',  // Keep original relcode
        relation_start: '',
        relation_end: '',
        person1: '',
        person2: ''
    });
    const [relationshipTypes, setRelationshipTypes] = React.useState({});  // Keep as object for direct lookup

    // Load relationship types once
    React.useEffect(() => {
        fetch('api/app.php?action=relationship_types')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.types) {
                    setRelationshipTypes(data.types);  // Store the raw object for lookup
                }
            })
            .catch(err => console.error('Error loading relationship types:', err));
    }, []);

    // Set form data from relationship prop
    React.useEffect(() => {
        if (relationship) {
            setFormData({
                relationship_id: relationship.id,
                relcode: relationship.relcode,
                relation_start: relationship.relation_start || '',
                relation_end: relationship.relation_end || '',
                person1: `${relationship.person1_first_name} ${relationship.person1_last_name}`,
                person2: `${relationship.person2_first_name} ${relationship.person2_last_name}`
            });
        }
    }, [relationship]);

    const handleSubmit = (e) => {
        e.preventDefault();
        console.log("Submitting relationship data:", formData); // Debug
        onSave({
            id: formData.relationship_id,
            relcode: formData.relcode,
            relation_start: formData.relation_start || null,
            relation_end: formData.relation_end || null
        });
    };

    if (!show) return null;

    return React.createElement('div', {
        className: 'modal show d-block',
        tabIndex: '-1',
        role: 'dialog'
    }, 
        React.createElement('div', {
            className: 'modal-dialog',
            role: 'document'
        },
            React.createElement('div', {
                className: 'modal-content'
            }, [
                React.createElement('div', {
                    key: 'header',
                    className: 'modal-header'
                }, [
                    React.createElement('h5', {
                        key: 'title',
                        className: 'modal-title'
                    }, 'Edit Relationship'),
                    React.createElement('button', {
                        key: 'close',
                        type: 'button',
                        className: 'btn-close',
                        onClick: onHide,
                        'aria-label': 'Close'
                    })
                ]),
                React.createElement('div', {
                    key: 'body',
                    className: 'modal-body'
                },
                    React.createElement('form', {
                        id: 'edit-relationship-form',
                        onSubmit: handleSubmit
                    }, [
                        React.createElement('input', {
                            key: 'hidden-id',
                            type: 'hidden',
                            id: 'edit_relationship_id',
                            name: 'relationship_id',
                            value: formData.relationship_id
                        }),
                        React.createElement('div', {
                            key: 'person1-group',
                            className: 'mb-3'
                        }, [
                            React.createElement('label', { key: 'label1' }, 'Person 1:'),
                            React.createElement('input', {
                                key: 'input1',
                                type: 'text',
                                id: 'edit_person1',
                                className: 'form-control',
                                value: formData.person1,
                                readOnly: true
                            })
                        ]),
                        React.createElement('div', {
                            key: 'person2-group',
                            className: 'mb-3'
                        }, [
                            React.createElement('label', { key: 'label2' }, 'Person 2:'),
                            React.createElement('input', {
                                key: 'input2',
                                type: 'text',
                                id: 'edit_person2',
                                className: 'form-control',
                                value: formData.person2,
                                readOnly: true
                            })
                        ]),
                        React.createElement('div', {
                            key: 'type-group',
                            className: 'mb-3'
                        }, [
                            React.createElement('label', { key: 'label3' }, 'Relationship Type:'),
                            React.createElement('select', {
                                key: 'select',
                                id: 'edit_relationship_type',
                                name: 'relcode',
                                className: 'form-control',
                                value: formData.relcode,
                                onChange: (e) => setFormData(prev => ({
                                    ...prev,
                                    relcode: e.target.value
                                })),
                                required: true
                            }, Object.entries(relationshipTypes).map(([code, info]) => 
                                React.createElement('option', {
                                    key: code,
                                    value: code
                                }, info.description)
                            ))
                        ]),
                        React.createElement('div', {
                            key: 'start-group',
                            className: 'mb-3'
                        }, [
                            React.createElement('label', { key: 'label4' }, 'Start Date:'),
                            React.createElement('input', {
                                key: 'input4',
                                type: 'date',
                                id: 'edit_relation_start',
                                name: 'relation_start',
                                className: 'form-control',
                                value: formData.relation_start,
                                onChange: (e) => setFormData(prev => ({
                                    ...prev,
                                    relation_start: e.target.value
                                }))
                            })
                        ]),
                        React.createElement('div', {
                            key: 'end-group',
                            className: 'mb-3'
                        }, [
                            React.createElement('label', { key: 'label5' }, 'End Date:'),
                            React.createElement('input', {
                                key: 'input5',
                                type: 'date',
                                id: 'edit_relation_end',
                                name: 'relation_end',
                                className: 'form-control',
                                value: formData.relation_end,
                                onChange: (e) => setFormData(prev => ({
                                    ...prev,
                                    relation_end: e.target.value
                                }))
                            })
                        ]),
                        React.createElement('div', {
                            key: 'actions-group',
                            className: 'mb-3 text-end'
                        }, [
                            React.createElement('button', {
                                key: 'swap',
                                type: 'button',
                                className: 'btn btn-secondary me-2',
                                onClick: async () => {
                                    try {
                                        const response = await fetch('api/app.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                action: 'swap_relationship',
                                                relationship_id: formData.relationship_id
                                            })
                                        });
                                        
                                        const data = await response.json();
                                        if (data.success) {
                                            // Swap the persons in the form
                                            setFormData(prev => ({
                                                ...prev,
                                                person1: prev.person2,
                                                person2: prev.person1
                                            }));
                                        } else {
                                            throw new Error(data.error || 'Failed to swap relationship');
                                        }
                                    } catch (error) {
                                        console.error('Error swapping relationship:', error);
                                        alert('Failed to swap relationship: ' + error.message);
                                    }
                                }
                            }, '↔️ Swap Persons')
                        ])
                    ])
                ),
                React.createElement('div', {
                    key: 'footer',
                    className: 'modal-footer'
                }, [
                    React.createElement('button', {
                        key: 'cancel',
                        type: 'button',
                        className: 'btn btn-secondary',
                        onClick: onHide
                    }, 'Cancel'),
                    React.createElement('button', {
                        key: 'save',
                        type: 'button',
                        className: 'btn btn-primary',
                        onClick: handleSubmit
                    }, 'Save')
                ])
            ])
        )
    );
};
