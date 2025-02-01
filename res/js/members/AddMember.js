const AddMember = () => {
    // Get treeId from URL hash
    const treeId = window.location.hash.split('/')[2];

    const [formData, setFormData] = React.useState({
        first_name: '',
        last_name: '',
        birth_date: '',
        treeId: treeId,
        gender: 'M',
        alive: true
    });
    const [error, setError] = React.useState(null);

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const memberData = {
                action: 'create',
                tree_id: treeId,
                first_name: formData.first_name.trim(),
                last_name: formData.last_name.trim(),
                birth_date: formData.birth_date || null,
                gender: formData.gender,
                alive: formData.alive ? '1' : '0'
            };

            const response = await fetch('api/individuals.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(memberData)
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Failed to create member');
            }
            
            if (data.success) {
                window.location.hash = `#/tree/${treeId}/members`;
            } else {
                throw new Error(data.message || 'Failed to create member');
            }
        } catch (error) {
            setError(error.message);
        }
    };

    return React.createElement('div', { className: 'container-fluid' }, [
        React.createElement(Navigation, { key: 'nav', treeId }),
        React.createElement('main', {
            key: 'main',
            className: 'container mx-auto px-4 py-16 mt-16'
        }, [
            React.createElement(Card, { key: 'add-card' }, [
                React.createElement(Card.Header, { key: 'header' }, 'Add New Member'),
                React.createElement(Card.Body, { key: 'body' }, [
                    error && React.createElement('div', {
                        key: 'error',
                        className: 'alert alert-danger mb-4'
                    }, error),
                    React.createElement('form', {
                        key: 'form',
                        onSubmit: handleSubmit
                    }, [
                        React.createElement('div', { key: 'name-group', className: 'mb-4' }, [
                            React.createElement('label', { key: 'name-label', className: 'form-label' }, 'Name'),
                            React.createElement('input', {
                                key: 'first-name',
                                type: 'text',
                                value: formData.first_name,
                                onChange: e => setFormData(prev => ({ ...prev, first_name: e.target.value })),
                                className: 'form-control mb-2',
                                placeholder: 'First Name',
                                required: true
                            }),
                            React.createElement('input', {
                                key: 'last-name',
                                type: 'text',
                                value: formData.last_name,
                                onChange: e => setFormData(prev => ({ ...prev, last_name: e.target.value })),
                                className: 'form-control',
                                placeholder: 'Last Name'
                            })
                        ]),
                        React.createElement('div', { key: 'birth-group', className: 'mb-4' }, [
                            React.createElement('label', { key: 'birth-label', className: 'form-label' }, 'Birth Date'),
                            React.createElement('input', {
                                key: 'birth-date',
                                type: 'date',
                                value: formData.birth_date,
                                onChange: e => setFormData(prev => ({ ...prev, birth_date: e.target.value })),
                                className: 'form-control'
                            })
                        ]),
                        React.createElement('div', { key: 'gender-group', className: 'mb-4' }, [
                            React.createElement('label', { key: 'gender-label', className: 'form-label' }, 'Gender'),
                            React.createElement('select', {
                                key: 'gender-select',
                                value: formData.gender,
                                onChange: e => setFormData(prev => ({ ...prev, gender: e.target.value })),
                                className: 'form-select'
                            }, [
                                React.createElement('option', { key: 'male', value: 'M' }, 'Male'),
                                React.createElement('option', { key: 'female', value: 'F' }, 'Female')
                            ])
                        ]),
                        React.createElement('div', { key: 'alive-group', className: 'mb-4' }, [
                            React.createElement('div', { key: 'alive-check', className: 'form-check' }, [
                                React.createElement('input', {
                                    key: 'alive-input',
                                    type: 'checkbox',
                                    checked: formData.alive,
                                    onChange: e => setFormData(prev => ({ ...prev, alive: e.target.checked })),
                                    className: 'form-check-input',
                                    id: 'alive'
                                }),
                                React.createElement('label', {
                                    key: 'alive-label',
                                    className: 'form-check-label',
                                    htmlFor: 'alive'
                                }, 'Alive')
                            ])
                        ]),
                        React.createElement('button', {
                            key: 'submit',
                            type: 'submit',
                            className: 'btn btn-primary'
                        }, 'Add Member')
                    ])
                ])
            ])
        ])
    ]);
};
