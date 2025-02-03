const Autocomplete = ({ type, memberId, treeId, onSelect }) => {
    const [suggestions, setSuggestions] = React.useState([]);
    const [inputValue, setInputValue] = React.useState('');
    const [selectedId, setSelectedId] = React.useState('');

    const handleInput = async (e) => {
        const value = e.target.value;
        setInputValue(value);
        if (!value) {
            setSuggestions([]);
            return;
        }

        try {
            const params = new URLSearchParams({
                action: 'autocomplete_member',
                term: value,
                member_id: memberId,
                tree_id: treeId
            });

            const response = await fetch(`api/individuals.php?${params.toString()}`);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            setSuggestions(data);
        } catch (error) {
            console.error('Autocomplete error:', error);
            setSuggestions([]);
        }
    };

    const handleSelect = (e) => {
        const value = e.target.value;
        const selectedSuggestion = suggestions.find(s => s.label === value);
        if (selectedSuggestion) {
            setSelectedId(selectedSuggestion.id);
            onSelect(selectedSuggestion);
        }
    };

    return React.createElement('div', { className: 'autocomplete-wrapper' }, [
        React.createElement('input', {
            key: 'search-input',
            type: 'text',
            className: 'form-control',
            value: inputValue,
            onChange: handleInput,
            onBlur: handleSelect,
            placeholder: `Search for ${type}...`,
            list: `${type}-suggestions`
        }),
        React.createElement('datalist', {
            key: 'suggestions-list',
            id: `${type}-suggestions`
        }, suggestions.map(item => 
            React.createElement('option', {
                key: `suggestion-${item.id}`,
                value: item.label,
                'data-id': item.id
            })
        )),
        React.createElement('input', {
            key: 'selected-id',
            type: 'hidden',
            name: `${type}_id`,
            value: selectedId
        })
    ]);
};
