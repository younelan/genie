const MembersList = () => {
    const [members, setMembers] = React.useState([]);
    const [lastUpdates, setLastUpdates] = React.useState([]);
    const [stats, setStats] = React.useState({});
    const [page, setPage] = React.useState(1);
    const [totalPages, setTotalPages] = React.useState(1);
    const [searchQuery, setSearchQuery] = React.useState('');
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(null);
    const treeId = window.location.hash.split('/')[2];
    const [activeTab, setActiveTab] = React.useState(0);

    React.useEffect(() => {
        loadMembers();
        loadStats();
    }, [treeId, page]);

    const loadMembers = async () => {
        setLoading(true);
        try {
            const response = await fetch(`api/individuals.php?action=list&tree_id=${treeId}&page=${page}`);
            const data = await response.json();
            if (data.success) {
                setMembers(data.data.members);
                setLastUpdates(data.data.lastUpdates);
                setTotalPages(data.data.totalPages);
            }
        } catch (error) {
            console.error('Error loading members:', error);
            setError(error.message);
        } finally {
            setLoading(false);
        }
    };

    const loadStats = async () => {
        try {
            const response = await fetch(`api/individuals.php?action=stats&tree_id=${treeId}`);
            const data = await response.json();
            if (data.success) {
                setStats(data.data);
            }
        } catch (error) {
            console.error('Error loading stats:', error);
            setError(error.message);
        }
    };

    const handleSearch = async (e) => {
        const query = e.target.value;
        setSearchQuery(query);
        if (query.length > 2) {
            try {
                const response = await fetch(`api/individuals.php?action=search&tree_id=${treeId}&query=${query}`);
                const data = await response.json();
                if (data.success) {
                    setMembers(data.data);
                }
            } catch (error) {
                console.error('Error searching members:', error);
            }
        } else if (query.length === 0) {
            loadMembers();
        }
    };

    const handleMemberClick = (memberId) => {
        // Fix the routing to properly navigate to member details
        window.location.hash = `#/tree/${treeId}/member/${memberId}`;
    };

    const renderParents = () => React.createElement(Card, { className: 'mt-3' }, [
        React.createElement(Card.Header, { key: 'header' }, T('Parents')),
        React.createElement(Card.Body, { key: 'body' },
            childFamilies.map(family => 
                React.createElement('div', { 
                    key: `family-${family.id}`, 
                    className: 'd-flex gap-2 mb-2' 
                }, [
                    family.husband_id && React.createElement('a', {
                        href: `#/tree/${treeId}/member/${family.husband_id}`,
                        className: 'text-primary text-decoration-none'
                    }, family.husband_name),
                    (family.husband_id && family.wife_id) && React.createElement('span', null, ' & '),
                    family.wife_id && React.createElement('a', {
                        href: `#/tree/${treeId}/member/${family.wife_id}`,
                        className: 'text-primary text-decoration-none'
                    }, family.wife_name)
                ])
            )
        )
    ]);

    const renderRecentUpdates = () => React.createElement(ListGroup, { key: 'updates-list' },
        lastUpdates.map(member =>
            React.createElement(ListGroup.Item, {
                key: `update-${member.id}`,
                action: true,
                onClick: () => handleMemberClick(member.id) // Use the same handler
            }, `${member.gender === 'M' ? '♂️' : '♀️'} ${member.first_name} ${member.last_name}`)
        )
    );

    const mainContent = [
        React.createElement('main', { 
            key: 'main',
            className: 'w-full lg:container lg:mx-auto px-0 lg:px-4 py-2 flex-grow' // Adjusted for full width on mobile
        }, [
            // Mobile tabs - adjust position to stick right under nav
            React.createElement('div', {
                key: 'mobile-tabs',
                className: 'lg:hidden sticky top-14 bg-white z-10 shadow-sm' // Adjusted top position
            }, [
                React.createElement('div', {
                    className: 'flex bg-gray-100'
                }, [
                    [T('Members'), T('Statistics'), T('Updates')].map((tab, index) =>
                        React.createElement('button', {
                            key: `tab-${index}`,
                            onClick: () => setActiveTab(index),
                            className: `flex-1 py-3 ${activeTab === index 
                                ? 'bg-primary text-white font-medium' 
                                : 'text-gray-600 hover:bg-gray-200'}`
                        }, tab)
                    )
                ])
            ]),

            // Desktop view (3 columns)
            React.createElement('div', { 
                key: 'desktop-view',
                className: 'hidden lg:grid lg:grid-cols-3 lg:gap-4 lg:mt-16'
            }, [
                // Members List Column
                React.createElement('div', { 
                    key: 'members-col',
                    className: 'mb-4'
                },
                    React.createElement(Card, { key: 'members-card' }, [
                        React.createElement(Card.Header, { key: 'members-header' }, T('Family Members')),
                        React.createElement(Card.Body, { key: 'members-body' }, [
                            React.createElement('input', {
                                key: 'search-input',
                                type: 'text',
                                placeholder: T('Search by name...'),
                                value: searchQuery,
                                onChange: handleSearch,
                                className: 'form-control mb-3'
                            }),
                            React.createElement(ListGroup, { key: 'members-list' },
                                members.map(member =>
                                    React.createElement(ListGroup.Item, {
                                        key: `member-${member.id}`,
                                        action: true,
                                        onClick: () => handleMemberClick(member.id)
                                    }, `${member.gender === 'M' ? '♂️' : '♀️'} ${member.first_name} ${member.last_name}`)
                                )
                            ),
                            totalPages > 1 && React.createElement(Nav, { key: 'pagination', className: 'mt-3' },
                                [...Array(totalPages)].map((_, i) =>
                                    React.createElement(Nav.Item, { key: `page-${i}` },
                                        React.createElement(Nav.Link, {
                                            onClick: () => setPage(i + 1),
                                            disabled: page === i + 1
                                        }, i + 1)
                                    )
                                )
                            )
                        ])
                    ])
                ),
                // Statistics Column
                React.createElement('div', { 
                    key: 'stats-col',
                    className: 'mb-4'
                },
                    React.createElement(Card, { key: 'stats-card' }, [
                        React.createElement(Card.Header, { key: 'stats-header' }, T('Statistics')),
                        React.createElement(Card.Body, { key: 'stats-body' },
                            Object.entries(stats).map(([category, data], index) =>
                                React.createElement('div', { key: `stat-category-${index}` },
                                    React.createElement('h6', { key: `stat-title-${index}` }, category),
                                    React.createElement(ListGroup, { key: `stat-list-${index}`, className: 'mb-3' },
                                        Object.entries(data).map(([key, value], subIndex) =>
                                            React.createElement(ListGroup.Item, {
                                                key: `stat-item-${index}-${subIndex}`,
                                                className: 'd-flex justify-content-between align-items-center'
                                            }, 
                                            key, 
                                            React.createElement('span', {
                                                key: `stat-value-${index}-${subIndex}`,
                                                className: 'badge bg-primary rounded-pill'
                                            }, value))
                                        )
                                    )
                                )
                            )
                        )
                    ])
                ),
                // Recent Updates Column
                React.createElement('div', { 
                    key: 'updates-col',
                    className: 'mb-4'
                },
                    React.createElement(Card, { key: 'updates-card' }, [
                        React.createElement(Card.Header, { key: 'updates-header' }, T('Recent Updates')),
                        React.createElement(Card.Body, { key: 'updates-body' },
                            renderRecentUpdates()
                        )
                    ])
                )
            ]),

            // Mobile view - adjust padding to account for new tab position
            React.createElement('div', {
                key: 'mobile-view',
                className: 'lg:hidden w-full' // Removed mt-14, added w-full
            }, [
                activeTab === 0 && React.createElement('div', { className: 'w-full' },
                    React.createElement(Card, { 
                        key: 'members-card',
                        className: 'border-x-0 rounded-none' // Changed border-0 to border-x-0
                    }, [
                        React.createElement(Card.Header, { key: 'members-header' }, T('Family Members')),
                        React.createElement(Card.Body, { key: 'members-body' }, [
                            React.createElement('input', {
                                key: 'search-input',
                                type: 'text',
                                placeholder: T('Search by name...'),
                                value: searchQuery,
                                onChange: handleSearch,
                                className: 'form-control mb-3'
                            }),
                            React.createElement(ListGroup, { key: 'members-list' },
                                members.map(member =>
                                    React.createElement(ListGroup.Item, {
                                        key: `member-${member.id}`,
                                        action: true,
                                        onClick: () => handleMemberClick(member.id)
                                    }, `${member.gender === 'M' ? '♂️' : '♀️'} ${member.first_name} ${member.last_name}`)
                                )
                            ),
                            totalPages > 1 && React.createElement(Nav, { key: 'pagination', className: 'mt-3' },
                                [...Array(totalPages)].map((_, i) =>
                                    React.createElement(Nav.Item, { key: `page-${i}` },
                                        React.createElement(Nav.Link, {
                                            onClick: () => setPage(i + 1),
                                            disabled: page === i + 1
                                        }, i + 1)
                                    )
                                )
                            )
                        ])
                    ])
                ),
                activeTab === 1 && React.createElement('div', { className: 'w-full' },
                    React.createElement(Card, { 
                        key: 'stats-card',
                        className: 'border-0 rounded-none'
                    }, [
                        React.createElement(Card.Header, { key: 'stats-header' }, T('Statistics')),
                        React.createElement(Card.Body, { key: 'stats-body' },
                            Object.entries(stats).map(([category, data], index) =>
                                React.createElement('div', { key: `stat-category-${index}` },
                                    React.createElement('h6', { key: `stat-title-${index}` }, category),
                                    React.createElement(ListGroup, { key: `stat-list-${index}`, className: 'mb-3' },
                                        Object.entries(data).map(([key, value], subIndex) =>
                                            React.createElement(ListGroup.Item, {
                                                key: `stat-item-${index}-${subIndex}`,
                                                className: 'd-flex justify-content-between align-items-center'
                                            }, 
                                            key, 
                                            React.createElement('span', {
                                                key: `stat-value-${index}-${subIndex}`,
                                                className: 'badge bg-primary rounded-pill'
                                            }, value))
                                        )
                                    )
                                )
                            )
                        )
                    ])
                ),
                activeTab === 2 && React.createElement('div', { className: 'w-full' },
                    React.createElement(Card, { 
                        key: 'updates-card',
                        className: 'border-0 rounded-none'
                    }, [
                        React.createElement(Card.Header, { key: 'updates-header' }, T('Recent Updates')),
                        React.createElement(Card.Body, { key: 'updates-body' },
                            renderRecentUpdates()
                        )
                    ])
                )
            ])
        ]),
        React.createElement(Footer, { key: 'footer' })
    ];

    if (loading) return React.createElement('div', { className: 'text-center p-4' }, 'Loading...');
    if (error) return React.createElement('div', { className: 'alert alert-danger' }, error);

    return React.createElement('div', { 
        className: 'min-h-screen flex flex-col' // Keep min-height and flex
    }, [
        React.createElement(Navigation, { 
            key: 'nav',
            title: T('Family Members'), // Add translation
            leftMenuItems: Navigation.createTreeMenu(treeId),
            rightMenuItems: Navigation.createUserMenu()
        }),
        React.createElement('div', { 
            key: 'content',
            className: 'flex flex-col flex-grow' // Add flex-grow here
        }, mainContent)
    ]);
};
