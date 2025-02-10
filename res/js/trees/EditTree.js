const EditTree = () => {
    const [tree, setTree] = React.useState({
        name: '',
        description: '',
        is_public: false
    });
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(null);
    const treeId = window.location.hash.split('/')[2];

    React.useEffect(() => {
        loadTree();
    }, [treeId]);

    const loadTree = async () => {
        try {
            const response = await fetch(`api/trees.php?action=details&id=${treeId}`);
            if (!response.ok) throw new Error('Failed to load tree');
            const data = await response.json();
            if (data.success) {
                setTree(data.data);
            } else {
                throw new Error(data.message || 'Failed to load tree');
            }
        } catch (error) {
            setError(error.message);
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const formData = {
            name: tree.name,
            description: tree.description,
            is_public: tree.is_public
        };
        
        try {
            const response = await fetch(`api/trees.php?action=update&id=${treeId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            if (!response.ok) throw new Error('Failed to update tree');
            const data = await response.json();
            if (data.success) {
                window.location.hash = `#/tree/${treeId}/members`;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            setError(error.message);
        }
    };

    const handleDelete = async () => {
        if (!confirm('Are you sure you want to delete this tree? This cannot be undone.')) {
            return;
        }
        try {
            const response = await fetch(`api/trees.php?action=delete&id=${treeId}`, {
                method: 'DELETE'
            });
            if (!response.ok) throw new Error('Failed to delete tree');
            window.location.hash = '#/';
        } catch (error) {
            setError(error.message);
        }
    };

    if (loading) return React.createElement('div', { className: 'text-center p-4' }, 'Loading tree settings...');

    return React.createElement('div', { 
        className: 'min-h-screen flex flex-col'
    }, [
        React.createElement(Navigation, { 
            key: 'nav',
            title: 'Edit Tree',
            leftMenuItems: Navigation.createTreeMenu(treeId),
            rightMenuItems: Navigation.createUserMenu()
        }),
        React.createElement('div', { 
            key: 'wrapper',
            className: 'flex-1 flex flex-col' // Change to flex-1 for better compatibility
        }, [
            React.createElement('div', {
                key: 'content',
                className: 'flex-1' // Add flex-1 to push footer down
            }, [
                React.createElement('main', {
                    key: 'main',
                    className: 'container mx-auto px-4 py-8 mt-16'
                }, [
                    React.createElement(Card, { key: 'edit-card' }, [
                        React.createElement(Card.Header, { key: 'card-header' }, 'Edit Tree Settings'),

                        React.createElement(Card.Body, { key: 'card-body' }, [
                            error && React.createElement('div', { 
                                key: 'error',
                                className: 'alert alert-danger mb-4' 
                            }, error),
                            
                            React.createElement('form', { 
                                key: 'form',
                                onSubmit: handleSubmit 
                            }, [
                                React.createElement('div', { key: 'name', className: 'mb-4' }, [
                                    React.createElement('label', { 
                                        key: 'name-label',
                                        className: 'form-label' 
                                    }, 'Tree Name'),
                                    React.createElement('input', {
                                        key: 'name-input',
                                        type: 'text',
                                        name: 'name',
                                        value: tree.name, // Change from defaultValue to value
                                        onChange: (e) => setTree(prev => ({ ...prev, name: e.target.value })),
                                        className: 'form-control',
                                        required: true
                                    })
                                ]),
                                React.createElement('div', { key: 'description', className: 'mb-4' }, [
                                    React.createElement('label', { 
                                        key: 'description-label',
                                        className: 'form-label' 
                                    }, 'Description'),
                                    React.createElement('textarea', {
                                        key: 'description-input',
                                        name: 'description',
                                        value: tree.description, // Change from defaultValue to value
                                        onChange: (e) => setTree(prev => ({ ...prev, description: e.target.value })),
                                        className: 'form-control',
                                        rows: 3
                                    })
                                ]),
                                React.createElement('div', { key: 'public', className: 'mb-4' }, [
                                    React.createElement('div', { 
                                        key: 'public-check',
                                        className: 'form-check' 
                                    }, [
                                        React.createElement('input', {
                                            key: 'public-input',
                                            type: 'checkbox',
                                            name: 'is_public',
                                            id: 'is_public',
                                            checked: tree.is_public, // Change from defaultChecked to checked
                                            onChange: (e) => setTree(prev => ({ ...prev, is_public: e.target.checked })),
                                            className: 'form-check-input'
                                        }),
                                        React.createElement('label', {
                                            key: 'public-label',
                                            htmlFor: 'is_public',
                                            className: 'form-check-label'
                                        }, 'Make this tree public')
                                    ])
                                ]),
                                React.createElement('div', { 
                                    key: 'actions',
                                    className: 'flex justify-between items-center mt-6'
                                }, [
                                    React.createElement('button', {
                                        key: 'save-button',
                                        type: 'submit',
                                        className: 'btn btn-primary'
                                    }, 'Save Changes'),

                                    
                                ]),
                                
                            ])
                        ])
                    ])
                ])
            ]),
            React.createElement(Footer, { key: 'footer' })
        ])
    ]);
};
