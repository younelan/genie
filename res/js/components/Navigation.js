const Navigation = ({ treeId }) => {
    const [showTreeMenu, setShowTreeMenu] = React.useState(false);
    const [showUserMenu, setShowUserMenu] = React.useState(false);
    const [isMobileMenuOpen, setIsMobileMenuOpen] = React.useState(false);
    const treeMenuRef = React.useRef(null);
    const userMenuRef = React.useRef(null);

    React.useEffect(() => {
        const handleClickOutside = (event) => {
            if (treeMenuRef.current && !treeMenuRef.current.contains(event.target)) {
                setShowTreeMenu(false);
            }
            if (userMenuRef.current && !userMenuRef.current.contains(event.target)) {
                setShowUserMenu(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const treeMenu = treeId ? [
        { label: 'List Members', href: `#/tree/${treeId}/members` },
        { label: 'Add Member', href: `#/tree/${treeId}/member/add` },
        { label: 'Visualize Tree', href: `#/tree/${treeId}/visualize` },
        { label: 'Tree Settings', href: `#/tree/${treeId}/edit` }
    ] : [];

    const userMenu = [
        { label: 'Profile', href: '#/profile' },
        { label: 'Settings', href: '#/settings' },
        { label: 'Logout', onClick: () => console.log('Logout clicked') }
    ];

    return React.createElement('nav', {
        className: 'bg-gray-800 text-white px-4 py-2 fixed w-full top-0 z-50'
    }, [
        React.createElement('div', { 
            className: 'max-w-7xl mx-auto flex justify-between items-center',
            key: 'nav-container'
        }, [
            // Left side - Logo and Tree menu
            React.createElement('div', { 
                className: 'flex items-center gap-4',
                key: 'left-section'
            }, [
                React.createElement('a', {
                    href: '#/',
                    className: 'text-xl font-bold flex items-center gap-2'
                }, [
                    window.appLogo && React.createElement('img', {
                        src: window.appLogo,
                        alt: 'Logo',
                        className: 'h-8'
                    }),
                    React.createElement('span', {
                        className: 'hidden md:inline'
                    }, window.appTitle || 'Genie')
                ]),
                // Tree menu - visible on desktop and in mobile menu
                treeId && React.createElement('div', { 
                    className: 'relative hidden md:block',
                    ref: treeMenuRef
                }, [
                    React.createElement('button', {
                        className: 'px-4 py-2 hover:bg-gray-700 rounded-md flex items-center gap-2',
                        onClick: () => setShowTreeMenu(!showTreeMenu)
                    }, [
                        '🌳 Tree',
                        React.createElement('span', { className: 'ml-1' }, '▼')
                    ]),
                    showTreeMenu && React.createElement('div', {
                        className: 'absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5'
                    }, 
                        React.createElement('div', {
                            className: 'py-1',
                            role: 'menu'
                        }, treeMenu.map((item, index) =>
                            React.createElement('a', {
                                key: `tree-item-${index}`,
                                href: item.href,
                                onClick: item.onClick,
                                className: 'block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100',
                                role: 'menuitem'
                            }, item.label)
                        ))
                    )
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

                // User menu
                React.createElement('div', { 
                    key: 'user-menu',
                    className: 'hidden md:block relative',
                    ref: userMenuRef
                }, [
                    React.createElement('button', {
                        className: 'px-4 py-2 hover:bg-gray-700 rounded-md flex items-center gap-2',
                        onClick: () => setShowUserMenu(!showUserMenu)
                    }, [
                        '👤 User',
                        React.createElement('span', { className: 'ml-1' }, '▼')
                    ]),
                    showUserMenu && React.createElement('div', {
                        className: 'absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5'
                    },
                        React.createElement('div', {
                            className: 'py-1',
                            role: 'menu'
                        }, userMenu.map((item, index) =>
                            React.createElement('a', {
                                key: `user-item-${index}`,
                                href: item.href,
                                onClick: item.onClick,
                                className: 'block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100',
                                role: 'menuitem'
                            }, item.label)
                        ))
                    )
                ])
            ])
        ]),

        // Mobile menu - shows both tree and user menus
        isMobileMenuOpen && React.createElement('div', {
            className: 'md:hidden bg-gray-700 mt-2 p-2'
        }, [
            // Tree menu items in mobile view
            treeId && React.createElement('div', { key: 'mobile-tree' }, [
                React.createElement('div', { 
                    className: 'text-sm font-bold text-gray-400 px-4 py-2'
                }, 'Tree Menu'),
                ...treeMenu.map((item, index) =>
                    React.createElement('a', {
                        key: `mobile-tree-item-${index}`,
                        href: item.href,
                        className: 'block py-2 text-white hover:bg-gray-600 px-4'
                    }, item.label)
                ),
                React.createElement('hr', { className: 'my-2 border-gray-600' })
            ]),
            // User menu items in mobile view
            React.createElement('div', { key: 'mobile-user' }, [
                React.createElement('div', { 
                    className: 'text-sm font-bold text-gray-400 px-4 py-2'
                }, 'User Menu'),
                ...userMenu.map((item, index) =>
                    React.createElement('a', {
                        key: `mobile-user-item-${index}`,
                        href: item.href,
                        onClick: item.onClick,
                        className: 'block py-2 text-white hover:bg-gray-600 px-4'
                    }, item.label)
                )
            ])
        ])
    ]);
};
