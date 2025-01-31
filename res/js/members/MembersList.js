const MembersList = () => {
    const [members, setMembers] = React.useState([]);
    const [lastUpdates, setLastUpdates] = React.useState([]);
    const [stats, setStats] = React.useState({});
    const [page, setPage] = React.useState(1);
    const [totalPages, setTotalPages] = React.useState(1);
    const [searchQuery, setSearchQuery] = React.useState('');
    const treeId = window.location.hash.split('/')[2];

    React.useEffect(() => {
        loadMembers();
        loadStats();
    }, [treeId, page]);

    const loadMembers = async () => {
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

    return React.createElement(Container, { fluid: true },
        React.createElement(Row, null, [
            // Members List Column
            React.createElement(Col, { lg: 4, className: 'mb-4' },
                React.createElement(Card, null, [
                    React.createElement(Card.Header, null, 'Family Members'),
                    React.createElement(Card.Body, null, [
                        // Search input
                        React.createElement('input', {
                            type: 'text',
                            placeholder: 'Search by name...',
                            value: searchQuery,
                            onChange: handleSearch,
                            className: 'form-control mb-3'
                        }),
                        // Members list
                        React.createElement(ListGroup, null,
                            members.map(member =>
                                React.createElement(ListGroup.Item, {
                                    key: member.id,
                                    action: true,
                                    onClick: () => {
                                        window.location.hash = `#/member/${member.id}`;
                                    }
                                }, `${member.gender === 'M' ? '♂️' : '♀️'} ${member.first_name} ${member.last_name}`)
                            )
                        ),
                        // Pagination
                        totalPages > 1 && React.createElement(Nav, { className: 'mt-3' },
                            [...Array(totalPages)].map((_, i) =>
                                React.createElement(Nav.Item, { key: i },
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
            React.createElement(Col, { lg: 4, className: 'mb-4' },
                React.createElement(Card, null, [
                    React.createElement(Card.Header, null, 'Statistics'),
                    React.createElement(Card.Body, null,
                        Object.entries(stats).map(([category, data]) =>
                            React.createElement('div', { key: category },
                                React.createElement('h6', null, category),
                                React.createElement(ListGroup, { className: 'mb-3' },
                                    Object.entries(data).map(([key, value]) =>
                                        React.createElement(ListGroup.Item, {
                                            key: key,
                                            className: 'd-flex justify-content-between align-items-center'
                                        }, key, React.createElement('span', {
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
            React.createElement(Col, { lg: 4, className: 'mb-4' },
                React.createElement(Card, null, [
                    React.createElement(Card.Header, null, 'Recent Updates'),
                    React.createElement(Card.Body, null,
                        React.createElement(ListGroup, null,
                            lastUpdates.map(member =>
                                React.createElement(ListGroup.Item, {
                                    key: member.id,
                                    action: true,
                                    onClick: () => {
                                        window.location.hash = `#/member/${member.id}`;
                                    }
                                }, `${member.gender === 'M' ? '♂️' : '♀️'} ${member.first_name} ${member.last_name}`)
                            )
                        )
                    )
                ])
            )
        ])
    );
};

// Mount component when the route matches
if (window.location.hash.includes('/members')) {
    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(React.createElement(MembersList));
}
