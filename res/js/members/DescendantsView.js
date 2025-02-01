const DescendantsView = ({ treeId, memberId }) => {
    const [data, setData] = React.useState(null);
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(null);
    const svgRef = React.useRef();
    const containerRef = React.useRef();
    const zoomRef = React.useRef(null);

    React.useEffect(() => {
        loadDescendants();
    }, [memberId]);

    const loadDescendants = async () => {
        try {
            const response = await fetch(`api/individuals.php?action=get_descendants&member_id=${memberId}`);
            const result = await response.json();
            if (result.success) {
                setData(result.data);
            } else {
                throw new Error(result.message || 'Failed to load descendants');
            }
        } catch (error) {
            setError(error.message);
        } finally {
            setLoading(false);
        }
    };

    const renderHeader = () => React.createElement('div', { 
        className: 'flex justify-between items-center p-4 bg-white shadow' 
    }, [
        React.createElement('div', { key: 'nav', className: 'flex gap-4' }, [
            React.createElement('a', {
                key: 'back',
                href: `#/tree/${treeId}/member/${memberId}`,
                className: 'btn btn-link'
            }, '← Back to Member'),
            React.createElement('h2', { key: 'title' }, 
                `Descendants of ${data?.name || ''}`
            )
        ]),
        React.createElement('div', { key: 'controls', className: 'flex gap-2' }, [
            React.createElement('button', {
                key: 'zoom-in',
                onClick: () => zoomRef.current?.scaleBy(1.2),
                className: 'btn btn-primary btn-sm'
            }, '+'),
            React.createElement('button', {
                key: 'zoom-out',
                onClick: () => zoomRef.current?.scaleBy(0.8),
                className: 'btn btn-primary btn-sm'
            }, '-'),
            React.createElement('button', {
                key: 'reset',
                onClick: resetView,
                className: 'btn btn-secondary btn-sm'
            }, 'Reset')
        ])
    ]);

    const resetView = () => {
        if (!svgRef.current || !containerRef.current) return;
        
        const svgElement = d3.select(svgRef.current);
        const svgWidth = svgElement.node().getBoundingClientRect().width;
        const svgHeight = svgElement.node().getBoundingClientRect().height;
        
        d3.select(containerRef.current)
            .transition()
            .duration(750)
            .call(zoomRef.current.transform, 
                d3.zoomIdentity
                    .translate(svgWidth/2, svgHeight/2)
                    .scale(0.8)
            );
    };

    React.useEffect(() => {
        if (!data || !svgRef.current) return;

        const width = window.innerWidth;
        const height = window.innerHeight - 100;
        
        const svgElement = d3.select(svgRef.current)
            .attr('width', width)
            .attr('height', height);

        // Clear previous content
        svgElement.selectAll('*').remove();

        const container = svgElement.append('g');
        containerRef.current = container.node();

        // Initialize zoom behavior
        zoomRef.current = d3.zoom()
            .scaleExtent([0.1, 3])
            .on('zoom', (event) => {
                container.attr('transform', event.transform);
            });

        svgElement.call(zoomRef.current);

        // Create tree layout
        const tree = d3.tree()
            .size([height - 100, width - 400])
            .separation((a, b) => (a.parent === b.parent ? 2 : 3));

        // Process data
        const root = d3.hierarchy(data, d => 
            d.marriages?.flatMap(m => m.children || []) || []
        );

        tree(root);

        // Draw links
        container.selectAll('.link')
            .data(root.links())
            .join('path')
            .attr('class', 'link')
            .attr('d', d3.linkHorizontal()
                .x(d => d.y)
                .y(d => d.x)
            );

        // Create node groups
        const node = container.selectAll('.node')
            .data(root.descendants())
            .join('g')
            .attr('class', d => `node${d.data.isSpouse ? ' spouse' : ''}`)
            .attr('transform', d => `translate(${d.y},${d.x})`);

        // Add circles to nodes
        node.append('circle')
            .attr('r', 6)
            .attr('fill', d => d.data.data.gender === 'M' ? '#7cbade' : '#de7c99')
            .attr('cursor', 'pointer')
            .on('click', (event, d) => {
                window.location.hash = `#/tree/${treeId}/member/${d.data.id}`;
            });

        // Add labels
        node.append('text')
            .attr('dy', '0.31em')
            .attr('x', d => d.children ? -8 : 8)
            .attr('text-anchor', d => d.children ? 'end' : 'start')
            .text(d => d.data.name)
            .clone(true).lower()
            .attr('stroke', 'white')
            .attr('stroke-width', 3);

        // Initial centering
        resetView();

    }, [data]);

    if (loading) {
        return React.createElement('div', { className: 'flex justify-center items-center h-screen' },
            'Loading descendants tree...'
        );
    }

    return React.createElement('div', { className: 'h-screen flex flex-col' }, [
        React.createElement('div', { key: 'header' }, renderHeader()),
        React.createElement('div', {
            key: 'tree-container',
            className: 'flex-grow bg-gray-50'
        }, [
            React.createElement('svg', {
                key: 'svg',
                ref: svgRef,
                className: 'w-full h-full'
            })
        ])
    ]);
};
