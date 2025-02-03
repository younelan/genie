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
        React.createElement(Card.Header, { key: 'header' }, 'Parents'),
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
            className: 'container mx-auto px-4 py-16 mt-16 mb-16'
        }, [
            React.createElement(Row, { key: 'row' }, [
                // Members List Column
                React.createElement(Col, { key: 'members-col', lg: 4, className: 'mb-4' },
                    React.createElement(Card, { key: 'members-card' }, [
                        React.createElement(Card.Header, { key: 'members-header' }, 'Family Members'),
                        React.createElement(Card.Body, { key: 'members-body' }, [
                            React.createElement('input', {
                                key: 'search-input',
                                type: 'text',
                                placeholder: 'Search by name...',
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
                React.createElement(Col, { key: 'stats-col', lg: 4, className: 'mb-4' },
                    React.createElement(Card, { key: 'stats-card' }, [
                        React.createElement(Card.Header, { key: 'stats-header' }, 'Statistics'),
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
                React.createElement(Col, { key: 'updates-col', lg: 4, className: 'mb-4' },
                    React.createElement(Card, { key: 'updates-card' }, [
                        React.createElement(Card.Header, { key: 'updates-header' }, 'Recent Updates'),
                        React.createElement(Card.Body, { key: 'updates-body' },
                            renderRecentUpdates()
                        )
                    ])
                )
            ])
        ]),
        React.createElement(AppFooter, { key: 'footer' })
    ];

    if (loading) return React.createElement('div', { className: 'text-center p-4' }, 'Loading...');
    if (error) return React.createElement('div', { className: 'alert alert-danger' }, error);

    return React.createElement('div', { className: 'container-fluid' }, [
        React.createElement(Navigation, { 
            key: 'nav',
            title: 'Family Members',
            leftMenuItems: Navigation.createTreeMenu(treeId),
            rightMenuItems: Navigation.createUserMenu()
        }),
        React.createElement(React.Fragment, null, mainContent)
    ]);
};
