const Navigation = function({ title = '', leftMenuItems = [], rightMenuItems = [] }) {  
    const [showLeftMenu, setShowLeftMenu] = React.useState(false);
    const [showRightMenu, setShowRightMenu] = React.useState(false);
    const [isMobileMenuOpen, setIsMobileMenuOpen] = React.useState(false);
    const leftMenuRef = React.useRef(null);
    const rightMenuRef = React.useRef(null);

    React.useEffect(() => {
        const handleClickOutside = (event) => {
            if (leftMenuRef.current && !leftMenuRef.current.contains(event.target)) {
                setShowLeftMenu(false);
            }
            if (rightMenuRef.current && !rightMenuRef.current.contains(event.target)) {
                setShowRightMenu(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    return React.createElement('nav', {
        className: 'bg-gray-800 text-white px-4 py-2 fixed w-full top-0 z-50'
    }, [
        React.createElement('div', {
            key: 'nav-container',
            className: 'max-w-7xl mx-auto flex justify-between items-center'
        }, [
            React.createElement('div', {
                key: 'left-section',
                className: 'flex items-center gap-4'
            }, [
                React.createElement('a', {
                    key: 'logo-link',
                    href: '#/',
                    className: 'text-xl font-bold flex items-center gap-2'
                }, [
                    window.appLogo && React.createElement('img', {
                        key: 'logo',
                        src: window.appLogo,
                        alt: 'Logo',
                        className: 'h-8'
                    }),
                    React.createElement('span', {
                        key: 'title',
                        className: 'hidden md:inline'
                    }, window.appTitle || 'Genie')
                ]),
                // Add section title if provided
                title && React.createElement('span', {
                    key: 'section-title',
                    className: 'text-lg font-semibold'
                }, title),
                // Left menu - visible on desktop and in mobile menu
                leftMenuItems.length > 0 && React.createElement('div', { 
                    key: 'left-menu',
                    className: 'relative hidden md:block',
                    ref: leftMenuRef
                }, [
                    React.createElement('button', {
                        key: 'left-menu-button',
                        className: 'px-4 py-2 hover:bg-gray-700 rounded-md flex items-center gap-2',
                        onClick: () => setShowLeftMenu(!showLeftMenu)
                    }, [
                        React.createElement('span', { key: 'left-text' }, T('Tree')),
                        React.createElement('span', { key: 'left-arrow' }, '▼')
                    ]),
                    showLeftMenu && React.createElement('div', {
                        key: 'left-menu-dropdown',
                        className: 'absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5'
                    }, React.createElement('div', {
                        className: 'py-1',
                        role: 'menu'
                    }, leftMenuItems.map((item, index) =>
                        React.createElement('a', {
                            key: `left-item-${index}`,
                            href: item.href,
                            onClick: item.onClick,  // Add this line
                            className: 'block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100',
                            role: 'menuitem'
                        }, T(item.label))
                    )))
                ])
            ]),

            // Right side menus
            React.createElement('div', { 
                className: 'flex items-center gap-2',
                key: 'right-section'
            }, [
                // Mobile menu button
                React.createElement('button', {
                    key: 'mobile-menu',
                    className: 'md:hidden p-2',
                    onClick: () => setIsMobileMenuOpen(!isMobileMenuOpen)
                }, '☰'),

                // Right menu
                rightMenuItems.length > 0 && React.createElement('div', { 
                    key: 'right-menu',
                    className: 'hidden md:block relative',
                    ref: rightMenuRef
                }, [
                    React.createElement('button', {
                        key: 'right-menu-button',
                        className: 'px-4 py-2 hover:bg-gray-700 rounded-md flex items-center gap-2',
                        onClick: () => setShowRightMenu(!showRightMenu)
                    }, [
                        React.createElement('span', { key: 'right-text' }, '👤 User'),
                        React.createElement('span', { key: 'right-arrow', className: 'ml-1' }, '▼')
                    ]),
                    showRightMenu && React.createElement('div', {
                        key: 'right-menu-dropdown',
                        className: 'absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5'
                    },
                        React.createElement('div', {
                            className: 'py-1',
                            role: 'menu'
                        }, rightMenuItems.map((item, index) =>
                            React.createElement('a', {
                                key: `right-item-${index}`,
                                href: item.href,
                                onClick: item.onClick,
                                className: 'block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100',
                                role: 'menuitem'
                            }, T(item.label))
                        ))
                    )
                ])
            ])
        ]),

        // Mobile menu - shows both left and right menus
        isMobileMenuOpen && React.createElement('div', {
            key: 'mobile-menu-container',
            className: 'md:hidden bg-gray-700 mt-2 p-2'
        }, [
            // Left menu items in mobile view
            leftMenuItems.length > 0 && React.createElement('div', { key: 'mobile-left' }, [
                React.createElement('div', { 
                    key: 'mobile-left-title',
                    className: 'text-sm font-bold text-gray-400 px-4 py-2'
                }, 'Tree Menu'),
                ...leftMenuItems.map((item, index) =>
                    React.createElement('a', {
                        key: `mobile-left-item-${index}`,
                        href: item.href,
                        className: 'block py-2 text-white hover:bg-gray-600 px-4'
                    }, T(item.label))
                ),
                React.createElement('hr', { key: 'mobile-left-divider', className: 'my-2 border-gray-600' })
            ]),
            // Right menu items in mobile view
            rightMenuItems.length > 0 && React.createElement('div', { key: 'mobile-right' }, [
                React.createElement('div', { 
                    key: 'mobile-right-title',
                    className: 'text-sm font-bold text-gray-400 px-4 py-2'
                }, 'User Menu'),
                ...rightMenuItems.map((item, index) =>
                    React.createElement('a', {
                        key: `mobile-right-item-${index}`,
                        href: item.href,
                        onClick: item.onClick,
                        className: 'block py-2 text-white hover:bg-gray-600 px-4'
                    }, T(item.label))
                )
            ])
        ])
    ]);
};

// Helper function to create tree menu items
Navigation.createTreeMenu = (treeId) => ([
    { 
        label: T('List Members'), 
        href: `#/tree/${treeId}/members`,
        onClick: (e) => {
            e.preventDefault();
            window.location.hash = `#/tree/${treeId}/members`;
        }
    },
    { 
        label: T('Add Member'), 
        href: `#/tree/${treeId}/member/add`,
        onClick: (e) => {
            e.preventDefault();
            window.location.hash = `#/tree/${treeId}/member/add`;
        }
    },
    { 
        label: T('Visualize Tree'), 
        href: `#/tree/${treeId}/visualize`,
        onClick: (e) => {
            e.preventDefault();
            window.location.hash = `#/tree/${treeId}/visualize`;
        }
    },
    { 
        label: T('Manage Synonyms'), 
        href: `#/tree/${treeId}/synonyms`,
        onClick: (e) => {
            e.preventDefault();
            window.location.hash = `#/tree/${treeId}/synonyms`;
        }
    },
    { 
        label: T('Tree Settings'), 
        href: `#/tree/${treeId}/edit`,
        onClick: (e) => {
            e.preventDefault();
            window.location.hash = `#/tree/${treeId}/edit`;
        }
    },
    { 
        label: T('Export GEDCOM'), 
        href: `api/trees.php?action=export_gedcom&tree_id=${treeId}`,
        onClick: (e) => {
            e.preventDefault();
            window.location.href = `api/trees.php?action=export_gedcom&tree_id=${treeId}`;
        }
    }
]);

// Helper function to create user menu items
Navigation.createUserMenu = () => ([
    { 
        label: T('Profile'), 
        href: '#/profile',
        onClick: (e) => {
            e.preventDefault();
            window.location.hash = '#/profile';
        }
    },
    { 
        label: T('Settings'), 
        href: '#/settings',
        onClick: (e) => {
            e.preventDefault();
            window.location.hash = '#/settings';
        }
    },
    { 
        label: T('Logout'), 
        href: '#',
        onClick: (e) => {
            e.preventDefault();
            console.log('Logout clicked');
            // Add logout logic here
        }
    }
]);