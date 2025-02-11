const Autocomplete = ({ type, memberId, treeId, onSelect }) => {
    const [query, setQuery] = React.useState('');
    const [suggestions, setSuggestions] = React.useState([]);
    const [selectedPerson, setSelectedPerson] = React.useState(null);
    const [isOpen, setIsOpen] = React.useState(false);
    const wrapperRef = React.useRef(null);

    // Close dropdown when clicking outside
    React.useEffect(() => {
        const handleClickOutside = (event) => {
            if (wrapperRef.current && !wrapperRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    // Debounce search
    const debounce = (func, wait) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    };

    const searchPeople = async (searchTerm) => {
        if (!searchTerm) {
            setSuggestions([]);
            return;
        }

        try {
            const params = new URLSearchParams({
                action: 'autocomplete_member',
                term: searchTerm,
                member_id: memberId,
                tree_id: treeId
            });

            const response = await fetch(`api/individuals.php?${params.toString()}`);
            if (!response.ok) throw new Error('Search failed');
            
            const data = await response.json();
            setSuggestions(data);
            setIsOpen(true);
        } catch (error) {
            console.error('Search error:', error);
            setSuggestions([]);
        }
    };

    const debouncedSearch = React.useCallback(debounce(searchPeople, 300), []);

    const handleInputChange = (e) => {
        const value = e.target.value;
        setQuery(value);
        setSelectedPerson(null);
        debouncedSearch(value);
    };

    const handleSelectPerson = (person) => {
        setSelectedPerson(person);
        setQuery(person.label);
        setSuggestions([]);
        setIsOpen(false);
        if (onSelect) {
            onSelect(person);
        }
    };

    return React.createElement('div', {
        ref: wrapperRef,
        className: 'relative'
    }, [
        // Input field with translated placeholder
        React.createElement('input', {
            key: 'search-input',
            type: 'text',
            className: 'form-control',
            value: query,
            onChange: handleInputChange,
            placeholder: T(`Search for ${type}...`), // Change this line to use T()
            autoComplete: 'off'
        }),

        // Suggestions dropdown
        isOpen && suggestions.length > 0 && React.createElement('div', {
            key: 'suggestions-dropdown',
            className: 'position-absolute w-100 mt-1 bg-white border rounded shadow-sm z-50'
        }, 
            React.createElement('ul', {
                className: 'list-group'
            }, 
                suggestions.map(person => 
                    React.createElement('li', {
                        key: person.id,
                        className: 'list-group-item list-group-item-action cursor-pointer',
                        onClick: () => handleSelectPerson(person)
                    }, person.label)
                )
            )
        ),

        // Hidden input for form submission
        React.createElement('input', {
            key: 'hidden-input',
            type: 'hidden',
            name: `${type}_id`,
            value: selectedPerson ? selectedPerson.id : ''
        })
    ]);
};
