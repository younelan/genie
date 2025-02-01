const TagInput = ({ memberId, treeId }) => {
    const [tags, setTags] = React.useState([]);
    const [inputValue, setInputValue] = React.useState('');

    React.useEffect(() => {
        if (memberId) {
            loadTags();
            setInputValue(''); // Reset input value when member changes
        }
    }, [memberId]);

    const loadTags = async () => {
        try {
            const response = await fetch(`api/individuals.php?action=tags&member_id=${memberId}`);
            const data = await response.json();
            if (data.success && data.data.tags) {
                setTags(data.data.tags.split(',').filter(Boolean));
            }
        } catch (error) {
            console.error('Error loading tags:', error);
        }
    };

    const addTag = async (tagText) => {
        try {
            const response = await fetch('api/individuals.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'add_tag',
                    tag: tagText,
                    member_id: memberId,
                    tree_id: treeId
                })
            });
            const data = await response.json();
            if (data.success) {
                setTags(prev => [...prev, tagText]);
            }
        } catch (error) {
            console.error('Error adding tag:', error);
        }
    };

    const deleteTag = async (tagText) => {
        try {
            const response = await fetch('api/individuals.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete_tag',
                    tag: tagText,
                    member_id: memberId,
                    tree_id: treeId
                })
            });
            const data = await response.json();
            if (data.success) {
                setTags(prev => prev.filter(t => t !== tagText));
            }
        } catch (error) {
            console.error('Error deleting tag:', error);
        }
    };

    const handleKeyDown = (e) => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const tag = inputValue.trim();
            if (tag && !tags.includes(tag)) {
                addTag(tag);
            }
            setInputValue('');
        }
    };

    const handlePaste = (e) => {
        e.preventDefault();
        const paste = e.clipboardData.getData('text');
        const newTags = paste.split(',').map(t => t.trim()).filter(t => t && !tags.includes(t));
        newTags.forEach(addTag);
    };

    const handleInputChange = (e) => {
        setInputValue(e.target.value || ''); // Ensure empty string if value is null
    };

    return React.createElement('div', { className: 'tag-input-container mb-3' }, [
        React.createElement('label', { key: 'label', className: 'form-label d-flex justify-content-between' }, [
            'Tags',
            React.createElement('button', {
                key: 'copy-button',
                type: 'button',
                className: 'btn btn-sm btn-outline-secondary',
                onClick: () => navigator.clipboard.writeText(tags.join(','))
            }, 'Copy')
        ]),
        React.createElement('div', { 
            key: 'tags-wrapper',
            className: 'border rounded p-2 mb-2 d-flex flex-wrap gap-2'
        }, [
            // Render existing tags
            ...(tags || []).map(tag => 
                React.createElement('span', {
                    key: `tag-${tag}`,
                    className: 'badge bg-primary d-flex align-items-center'
                }, [
                    tag,
                    React.createElement('button', {
                        key: 'remove',
                        type: 'button',
                        className: 'btn-close btn-close-white ms-2',
                        onClick: () => deleteTag(tag)
                    })
                ])
            ),
            // Always render the input field, regardless of tags length
            React.createElement('input', {
                key: 'tag-input',
                type: 'text',
                className: 'border-0 flex-grow-1',
                value: inputValue,
                onChange: handleInputChange,
                onKeyDown: handleKeyDown,
                onPaste: handlePaste,
                placeholder: 'Type and press Enter to add tags'
            })
        ])
    ]);
};
