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

    const renderParents = () => React.createElement('div', { className: 'bg-white shadow-lg rounded-lg mt-3' }, [
        React.createElement('div', { 
            key: 'header', 
            className: 'px-4 py-3 bg-gray-50 border-b border-gray-200 rounded-t-lg'
        }, T('Parents')),
        React.createElement('div', { 
            key: 'body',
            className: 'p-4'
        },
            childFamilies.map(family => 
                React.createElement('div', { 
                    key: `family-${family.id}`, 
                    className: 'flex gap-2 mb-2' 
                }, [
                    family.husband_id && React.createElement('a', {
                        href: `#/tree/${treeId}/member/${family.husband_id}`,
                        className: 'text-blue-600 hover:text-blue-800'
                    }, family.husband_name),
                    (family.husband_id && family.wife_id) && React.createElement('span', null, ' & '),
                    family.wife_id && React.createElement('a', {
                        href: `#/tree/${treeId}/member/${family.wife_id}`,
                        className: 'text-blue-600 hover:text-blue-800'
                    }, family.wife_name)
                ])
            )
        )
    ]);

    const renderRecentUpdates = () => React.createElement('div', { 
        className: 'divide-y divide-gray-200'
    },
        lastUpdates.map(member =>
            React.createElement('div', {
                key: `update-${member.id}`,
                className: 'px-4 py-2 hover:bg-gray-50 cursor-pointer transition-colors',
                onClick: () => handleMemberClick(member.id)
            }, 
            React.createElement('div', { className: 'flex items-center gap-2' },
                React.createElement('span', { className: 'text-gray-500' },
                    member.gender === 'M' ? '♂️' : '♀️'
                ),
                `${member.first_name} ${member.last_name}`
            ))
        )
    );

    const renderCardHeader = (title) => React.createElement('div', {
        className: 'card-header'
    }, title);

    const renderMobileTab = (label, index) => React.createElement('button', {
        key: `tab-${index}`,
        onClick: () => setActiveTab(index),
        className: `mobile-tab ${activeTab === index ? 'active' : ''}`
    }, label);

    const mainContent = React.createElement('main', { 
        // Remove default padding, add padding only on desktop
        className: 'w-full max-w-7xl mx-auto px-0 lg:px-4 pt-16 flex-grow' 
    }, [
        // Mobile tabs
        React.createElement('div', {
            key: 'mobile-tabs',
            className: 'lg:hidden sticky top-16 bg-white z-20 border-b border-gray-200'
        }, 
            React.createElement('div', {
                className: 'grid grid-cols-3'
            }, [T('Members'), T('Statistics'), T('Updates')].map((tab, index) =>
                React.createElement('button', {
                    key: `tab-${index}`,
                    onClick: () => setActiveTab(index),
                    className: `py-3 px-4 text-center font-medium ${activeTab === index 
                        ? 'bg-indigo-900 text-white' 
                        : 'bg-white text-gray-600 hover:bg-gray-50'}`
                }, tab)
            ))
        ),

        // Mobile content - remove padding and update list item spacing
        React.createElement('div', {
            key: 'mobile-content',
            className: 'lg:hidden'
        }, [
            activeTab === 0 && React.createElement('div', { className: 'bg-white shadow-lg overflow-hidden' }, [
                React.createElement('div', { 
                    className: 'bg-gradient-to-r from-indigo-900 to-indigo-800 px-4 py-3 text-white font-medium text-lg'
                }, T('Family Members')),
                React.createElement('div', { className: 'p-4' }, [
                    React.createElement('input', {
                        type: 'text',
                        placeholder: T('Search by name...'),
                        value: searchQuery,
                        onChange: handleSearch,
                        className: 'w-full px-3 py-2 border border-gray-300 rounded-md mb-3' // Reduced margin bottom
                    }),
                    React.createElement('div', { 
                        className: 'divide-y divide-gray-200'
                    }, members.map(member => 
                        React.createElement('div', {
                            key: `member-${member.id}`,
                            onClick: () => handleMemberClick(member.id),
                            className: 'px-4 py-1.5 hover:bg-gray-50 cursor-pointer flex items-center gap-2' // Reduced vertical padding
                        }, [
                            React.createElement('span', { className: 'text-gray-500' }, 
                                member.gender === 'M' ? '♂️' : '♀️'
                            ),
                            `${member.first_name} ${member.last_name}`
                        ])
                    ))
                ])
            ]),

            // Update vertical padding for other tabs too
            activeTab === 1 && React.createElement('div', { className: 'bg-white shadow-lg overflow-hidden' }, [
                React.createElement('div', { 
                    className: 'bg-gradient-to-r from-indigo-900 to-indigo-800 px-4 py-3 text-white font-medium text-lg'
                }, T('Statistics')),
                React.createElement('div', { className: 'p-4' },
                    Object.entries(stats).map(([category, data], index) =>
                        React.createElement('div', { 
                            key: `stat-category-${index}`,
                            className: 'mb-3' // Reduced margin
                        }, [
                            React.createElement('h6', { className: 'font-medium mb-2' }, category),
                            React.createElement('div', { className: 'space-y-1' }, // Reduced gap
                                Object.entries(data).map(([key, value], subIndex) =>
                                    React.createElement('div', {
                                        key: `stat-item-${index}-${subIndex}`,
                                        className: 'flex justify-between items-center px-4 py-1.5 bg-gray-50 rounded' // Reduced padding
                                    }, [
                                        React.createElement('span', null, key),
                                        React.createElement('span', {
                                            className: 'bg-indigo-900 text-white px-2 py-1 rounded-full text-sm'
                                        }, value)
                                    ])
                                )
                            )
                        ])
                    )
                )
            ]),

            activeTab === 2 && React.createElement('div', { className: 'bg-white shadow-lg overflow-hidden' }, [
                React.createElement('div', { 
                    className: 'bg-gradient-to-r from-indigo-900 to-indigo-800 px-4 py-3 text-white font-medium text-lg'
                }, T('Recent Updates')),
                React.createElement('div', { className: 'p-4' },
                    React.createElement('div', { className: 'divide-y divide-gray-200' },
                        lastUpdates.map(member =>
                            React.createElement('div', {
                                key: `update-${member.id}`,
                                onClick: () => handleMemberClick(member.id),
                                className: 'px-4 py-1.5 hover:bg-gray-50 cursor-pointer' // Reduced vertical padding
                            }, `${member.gender === 'M' ? '♂️' : '♀️'} ${member.first_name} ${member.last_name}`)
                        )
                    )
                )
            ])
        ]),

        // Desktop view - update top margin
        React.createElement('div', { 
            key: 'desktop-view',
            className: 'hidden lg:grid lg:grid-cols-3 lg:gap-6 lg:mt-4'
        }, [
            // Members List Column
            React.createElement('div', { key: 'members-col' },
                React.createElement('div', { className: 'bg-white rounded-lg shadow-lg overflow-hidden' }, [
                    React.createElement('div', { 
                        key: 'header',
                        className: 'bg-gradient-to-r from-indigo-900 to-indigo-800 px-4 py-3 text-white font-medium text-lg'
                    }, T('Family Members')),
                    React.createElement('div', { key: 'body', className: 'p-4' }, [
                        React.createElement('input', {
                            key: 'search',
                            type: 'text',
                            placeholder: T('Search by name...'),
                            value: searchQuery,
                            onChange: handleSearch,
                            className: 'w-full px-3 py-2 border border-gray-300 rounded-md mb-4'
                        }),
                        React.createElement('div', { 
                            key: 'members-list',
                            className: 'divide-y divide-gray-200'
                        }, members.map(member => 
                            React.createElement('div', {
                                key: `member-${member.id}`,
                                onClick: () => handleMemberClick(member.id),
                                className: 'px-4 py-2 hover:bg-gray-50 cursor-pointer'
                            }, `${member.gender === 'M' ? '♂️' : '♀️'} ${member.first_name} ${member.last_name}`)
                        )),
                        totalPages > 1 && React.createElement('div', { 
                            key: 'pagination',
                            className: 'flex gap-1 justify-center mt-4 pt-4 border-t border-gray-200' 
                        }, [...Array(totalPages)].map((_, i) =>
                            React.createElement('button', {
                                key: `page-${i}`,
                                onClick: () => setPage(i + 1),
                                className: `px-3 py-1 rounded ${page === i + 1 
                                    ? 'bg-indigo-900 text-white' 
                                    : 'bg-gray-100 hover:bg-gray-200'}`
                            }, i + 1)
                        ))
                    ])
                ])
            ),

            // Statistics Column
            React.createElement('div', { key: 'stats-col' },
                React.createElement('div', { className: 'bg-white rounded-lg shadow-lg overflow-hidden' }, [
                    React.createElement('div', { 
                        key: 'header',
                        className: 'bg-gradient-to-r from-indigo-900 to-indigo-800 px-4 py-3 text-white font-medium text-lg'
                    }, T('Statistics')),
                    React.createElement('div', { key: 'body', className: 'p-4' },
                        Object.entries(stats).map(([category, data], index) =>
                            React.createElement('div', { 
                                key: `stat-category-${index}`,
                                className: 'mb-4'
                            }, [
                                React.createElement('h6', { className: 'font-medium mb-2' }, category),
                                React.createElement('div', { className: 'space-y-2' },
                                    Object.entries(data).map(([key, value], subIndex) =>
                                        React.createElement('div', {
                                            key: `stat-item-${index}-${subIndex}`,
                                            className: 'flex justify-between items-center px-4 py-2 bg-gray-50 rounded'
                                        }, [
                                            React.createElement('span', null, key),
                                            React.createElement('span', {
                                                className: 'bg-indigo-900 text-white px-2 py-1 rounded-full text-sm'
                                            }, value)
                                        ])
                                    )
                                )
                            ])
                        )
                    )
                ])
            ),

            // Recent Updates Column
            React.createElement('div', { key: 'updates-col' },
                React.createElement('div', { className: 'bg-white rounded-lg shadow-lg overflow-hidden' }, [
                    React.createElement('div', { 
                        key: 'header',
                        className: 'bg-gradient-to-r from-indigo-900 to-indigo-800 px-4 py-3 text-white font-medium text-lg'
                    }, T('Recent Updates')),
                    React.createElement('div', { key: 'body', className: 'p-4' },
                        React.createElement('div', { className: 'divide-y divide-gray-200' },
                            lastUpdates.map(member =>
                                React.createElement('div', {
                                    key: `update-${member.id}`,
                                    onClick: () => handleMemberClick(member.id),
                                    className: 'px-4 py-2 hover:bg-gray-50 cursor-pointer'
                                }, `${member.gender === 'M' ? '♂️' : '♀️'} ${member.first_name} ${member.last_name}`)
                            )
                        )
                    )
                ])
            )
        ])
    ]);

    if (loading) return React.createElement('div', { className: 'flex items-center justify-center min-h-screen' }, 'Loading...');
    if (error) return React.createElement('div', { className: 'mx-4 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md' }, error);

    return React.createElement('div', { 
        className: 'min-h-screen flex flex-col bg-gray-100'
    }, [
        React.createElement(Navigation, { 
            key: 'nav',
            title: T('Family Members'),
            leftMenuItems: Navigation.createTreeMenu(treeId),
            rightMenuItems: Navigation.createUserMenu()
        }),
        mainContent
    ]);
};
