const EditOtherRelationship = ({ show, onHide, relationship, onSave }) => {
    const [formData, setFormData] = React.useState({
        relationship_id: '',
        relationship_type: '',
        relation_start: '',
        relation_end: '',
        person1: '',
        person2: ''
    });
    const [relationshipTypes, setRelationshipTypes] = React.useState([]);

    // Update the loadRelationshipTypes function
    React.useEffect(() => {
        const loadRelationshipTypes = async () => {
            try {
                const response = await fetch('api/app.php?action=relationship_types');
                if (!response.ok) throw new Error('Failed to load relationship types');
                const data = await response.json();
                if (data.success) {
                    setRelationshipTypes(data.types);
                } else {
                    throw new Error(data.error || 'Failed to load relationship types');
                }
            } catch (error) {
                console.error('Error loading relationship types:', error);
            }
        };
        loadRelationshipTypes();
    }, []);

    // Update form when relationship data changes
    React.useEffect(() => {
        if (relationship) {
            setFormData({
                relationship_id: relationship.id,
                relationship_type: relationship.relationship_type_id, // Use relationship_type_id instead
                relation_start: relationship.relation_start || '',
                relation_end: relationship.relation_end || '',
                person1: `${relationship.person1_first_name} ${relationship.person1_last_name}`,
                person2: `${relationship.person2_first_name} ${relationship.person2_last_name}`
            });
        }
    }, [relationship]);

    const handleSubmit = (e) => {
        e.preventDefault();
        onSave(formData);
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
                                name: 'relationship_type',
                                className: 'form-control',
                                value: formData.relationship_type,
                                onChange: (e) => setFormData(prev => ({
                                    ...prev,
                                    relationship_type: e.target.value
                                })),
                                required: true
                            }, relationshipTypes.map(type => 
                                React.createElement('option', {
                                    key: type.id,
                                    value: type.id
                                }, type.description)
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
