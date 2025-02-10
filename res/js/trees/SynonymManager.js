const SynonymManager = () => {
    const [synonyms, setSynonyms] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(null);
    const [newSynonym, setNewSynonym] = React.useState({ key: '', value: '' });
    const [editingSynonym, setEditingSynonym] = React.useState(null);

    const treeId = window.location.hash.split('/')[2];

    React.useEffect(() => {
        fetchSynonyms();
    }, []);

    const fetchSynonyms = async () => {
        try {
            const response = await fetch(`api/trees.php?action=get_synonyms&tree_id=${treeId}`);
            if (!response.ok) throw new Error('Failed to fetch synonyms');
            const data = await response.json();
            setSynonyms(data.data || []);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleAdd = async (e) => {
        e.preventDefault();
        try {
            const response = await fetch(`api/trees.php?action=add_synonym&tree_id=${treeId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(newSynonym)
            });
            if (!response.ok) throw new Error('Failed to add synonym');
            setNewSynonym({ key: '', value: '' });
            await fetchSynonyms();
        } catch (err) {
            setError(err.message);
        }
    };

    const handleUpdate = async (synonym) => {
        try {
            const response = await fetch(`api/trees.php?action=update_synonym&id=${synonym.syn_id}&tree_id=${treeId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(synonym)
            });
            if (!response.ok) throw new Error('Failed to update synonym');
            setEditingSynonym(null);
            await fetchSynonyms();
        } catch (err) {
            setError(err.message);
        }
    };

    const handleDelete = async (synonymId) => {
        if (!confirm('Are you sure you want to delete this synonym?')) return;
        
        try {
            const response = await fetch(`api/trees.php?action=delete_synonym&id=${synonymId}&tree_id=${treeId}`, {
                method: 'DELETE'
            });
            if (!response.ok) throw new Error('Failed to delete synonym');
            await fetchSynonyms();
        } catch (err) {
            setError(err.message);
        }
    };

    if (loading) return React.createElement('div', { className: 'text-center p-4' }, 'Loading...');
    if (error) return React.createElement('div', { className: 'alert alert-danger' }, error);

    return React.createElement('div', { 
        className: 'min-h-screen flex flex-col'
    }, [
        React.createElement(Navigation, {
            key: 'nav',
            title: 'Manage Synonyms',
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
                    React.createElement('h2', { 
                        key: 'title',
                        className: 'text-2xl font-bold mb-6' 
                    }, 'Manage Synonyms'),
                    
                    // Add new synonym form
                    React.createElement('form', {
                        key: 'add-form',
                        onSubmit: handleAdd,
                        className: 'mb-8 p-4 bg-white rounded-lg shadow'
                    }, [
                        React.createElement('div', { 
                            key: 'form-grid',
                            className: 'grid grid-cols-1 md:grid-cols-3 gap-4' 
                        }, [
                            React.createElement('input', {
                                key: 'key-input',
                                type: 'text',
                                placeholder: 'Original Term',
                                value: newSynonym.key,
                                onChange: (e) => setNewSynonym(prev => ({ ...prev, key: e.target.value })),
                                className: 'border p-2 rounded'
                            }),
                            React.createElement('input', {
                                key: 'value-input',
                                type: 'text',
                                placeholder: 'Replacement Term',
                                value: newSynonym.value,
                                onChange: (e) => setNewSynonym(prev => ({ ...prev, value: e.target.value })),
                                className: 'border p-2 rounded'
                            }),
                            React.createElement('button', {
                                key: 'submit-button',
                                type: 'submit',
                                className: 'bg-blue-500 text-white p-2 rounded hover:bg-blue-600'
                            }, 'Add Synonym')
                        ])
                    ]),

                    // Synonyms list
                    React.createElement('div', {
                        key: 'synonyms-list',
                        className: 'bg-white rounded-lg shadow overflow-hidden'
                    }, [
                        React.createElement('table', {
                            key: 'table',
                            className: 'min-w-full'
                        }, [
                            React.createElement('thead', {
                                key: 'thead',
                                className: 'bg-gray-50'
                            }, React.createElement('tr', {}, [
                                React.createElement('th', { className: 'px-6 py-3 text-left' }, 'Original Term'),
                                React.createElement('th', { className: 'px-6 py-3 text-left' }, 'Replacement Term'),
                                React.createElement('th', { className: 'px-6 py-3 text-right' }, 'Actions')
                            ])),
                            React.createElement('tbody', {
                                key: 'tbody',
                                className: 'divide-y divide-gray-200'
                            }, synonyms.map(synonym => 
                                React.createElement('tr', {
                                    key: synonym.syn_id,
                                    className: 'hover:bg-gray-50'
                                }, [
                                    React.createElement('td', { 
                                        key: 'key',
                                        className: 'px-6 py-4'
                                    }, editingSynonym?.syn_id === synonym.syn_id ?
                                        React.createElement('input', {
                                            type: 'text',
                                            value: editingSynonym.key,
                                            onChange: (e) => setEditingSynonym(prev => ({ ...prev, key: e.target.value })),
                                            className: 'border p-1 rounded'
                                        }) :
                                        synonym.key
                                    ),
                                    React.createElement('td', { 
                                        key: 'value',
                                        className: 'px-6 py-4'
                                    }, editingSynonym?.syn_id === synonym.syn_id ?
                                        React.createElement('input', {
                                            type: 'text',
                                            value: editingSynonym.value,
                                            onChange: (e) => setEditingSynonym(prev => ({ ...prev, value: e.target.value })),
                                            className: 'border p-1 rounded'
                                        }) :
                                        synonym.value
                                    ),
                                    React.createElement('td', { 
                                        key: 'actions',
                                        className: 'px-6 py-4 text-right space-x-2'
                                    }, editingSynonym?.syn_id === synonym.syn_id ?
                                        [
                                            React.createElement('button', {
                                                key: 'save',
                                                onClick: () => handleUpdate(editingSynonym),
                                                className: 'text-green-600 hover:text-green-900'
                                            }, 'Save'),
                                            React.createElement('button', {
                                                key: 'cancel',
                                                onClick: () => setEditingSynonym(null),
                                                className: 'text-gray-600 hover:text-gray-900'
                                            }, 'Cancel')
                                        ] :
                                        [
                                            React.createElement('button', {
                                                key: 'edit',
                                                onClick: () => setEditingSynonym({ ...synonym }),
                                                className: 'text-blue-600 hover:text-blue-900'
                                            }, 'Edit'),
                                            React.createElement('button', {
                                                key: 'delete',
                                                onClick: () => handleDelete(synonym.syn_id),
                                                className: 'text-red-600 hover:text-red-900'
                                            }, 'Delete')
                                        ]
                                    )
                                ])
                            ))
                        ])
                    ])
                ])
            ]),
            React.createElement(Footer, { key: 'footer' })
        ])
    ]);
};
