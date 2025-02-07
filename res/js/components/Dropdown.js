// Rename the component to CustomDropdown to avoid naming conflicts
const CustomDropdown = ({ trigger, items }) => {
    const [isOpen, setIsOpen] = React.useState(false);
    const dropdownRef = React.useRef(null);

    React.useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    return React.createElement('div', {
        className: 'relative inline-block',
        ref: dropdownRef
    }, [
        React.createElement('button', {
            key: 'trigger',
            onClick: () => setIsOpen(!isOpen),
            className: 'p-1 hover:bg-gray-100 rounded'
        }, trigger),
        isOpen && React.createElement('ul', {
            key: 'menu',
            className: 'absolute right-0 mt-2 py-2 w-48 bg-white rounded-md shadow-xl z-50'
        }, items.map((item, index) =>
            React.createElement('li', { key: index },
                React.createElement('a', {
                    href: item.href,
                    onClick: (e) => {
                        if (item.onClick) {
                            e.preventDefault();
                            item.onClick();
                        }
                        setIsOpen(false);
                    },
                    className: `block px-4 py-2 text-sm hover:bg-gray-100 ${item.className || ''}`
                }, item.label)
            )
        ))
    ]);
};

// Export the renamed component
window.Dropdown = CustomDropdown;
