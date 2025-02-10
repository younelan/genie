const TagInput = ({ rowId, treeId, tagType = 'INDI' }) => {
    const [tags, setTags] = React.useState([]);
    const [inputValue, setInputValue] = React.useState('');

    React.useEffect(() => {
        if (rowId) {
            loadTags();
            setInputValue('');
        }
    }, [rowId]);

    const loadTags = async () => {
        try {
            const response = await fetch(`api/individuals.php?action=tags&member_id=${rowId}&tag_type=${tagType}`);
            const data = await response.json();
            if (data.success && data.data.tags) {
                const tagArray = data.data.tags.split(',')
                    .map(t => t.trim())
                    .filter(Boolean);
                setTags(tagArray);
            } else {
                setTags([]);
            }
        } catch (error) {
            console.error('Error loading tags:', error);
            setTags([]);
        }
    };

    const handleAddTag = async (tag) => {
        try {
            const postData = {
                action: 'add_tag',
                tag: tag.trim(),
                member_id: rowId,
                tree_id: treeId,
                tag_type: tagType
            };

            const response = await fetch('api/individuals.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(postData)
            });

            const data = await response.json();
            if (data.success) {
                await loadTags();
            } else {
                throw new Error(data.message || 'Failed to add tag');
            }
        } catch (error) {
            console.error('Error adding tag:', error);
            alert('Failed to add tag: ' + error.message);
        }
    };

    const handleDeleteTag = async (tagToDelete) => {
        try {
            const postData = {
                action: 'delete_tag',
                tag: tagToDelete.trim(),
                member_id: rowId,
                tree_id: treeId,
                tag_type: tagType
            };

            const response = await fetch('api/individuals.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(postData)
            });

            const data = await response.json();
            if (data.success) {
                await loadTags();
            } else {
                throw new Error(data.message || 'Failed to delete tag');
            }
        } catch (error) {
            console.error('Error deleting tag:', error);
            alert('Failed to delete tag: ' + error.message);
        }
    };

    const handleKeyDown = (e) => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const tag = inputValue.trim();
            if (tag && !tags.includes(tag)) {
                handleAddTag(tag);
            }
            setInputValue('');
        }
    };

    const handlePaste = (e) => {
        e.preventDefault();
        const paste = e.clipboardData.getData('text');
        const newTags = paste.split(',').map(t => t.trim()).filter(t => t && !tags.includes(t));
        newTags.forEach(handleAddTag);
    };

    const handleInputChange = (e) => {
        setInputValue(e.target.value || ''); // Ensure empty string if value is null
    };

    const handleCopyTags = () => {
        const tagText = tags.join(',');
        
        // Try modern clipboard API first
        if (window.navigator?.clipboard?.writeText) {
            window.navigator.clipboard.writeText(tagText)
                .catch(error => {
                    console.error('Clipboard API failed:', error);
                    fallbackCopy(tagText);
                });
        } else {
            fallbackCopy(tagText);
        }
    };

    const fallbackCopy = (text) => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'absolute';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        
        try {
            textarea.select();
            document.execCommand('copy');
        } catch (err) {
            console.error('Fallback copy failed:', err);
        } finally {
            document.body.removeChild(textarea);
        }
    };

    return React.createElement('div', { className: 'tag-input-container mb-3' }, [
        React.createElement('label', { key: 'label', className: 'form-label d-flex justify-content-between' }, [
            'Tags',
            React.createElement('button', {
                key: 'copy-button',
                type: 'button',
                className: 'btn btn-sm btn-outline-secondary',
                onClick: handleCopyTags
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
                        onClick: () => handleDeleteTag(tag)
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
