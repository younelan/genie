const FamilyTreeVisualization = ({ treeId }) => {
    const [data, setData] = React.useState(null);
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(null);
    const svgRef = React.useRef();

    React.useEffect(() => {
        fetchData();
    }, [treeId]);

    const fetchData = async () => {
        try {
            const response = await fetch(`api/trees.php?action=get_families&tree_id=${treeId}`);
            if (!response.ok) throw new Error('Failed to fetch family data');
            const jsonData = await response.json();
            setData(jsonData);
            setLoading(false);
        } catch (err) {
            setError(err.message);
            setLoading(false);
        }
    };

    React.useEffect(() => {
        if (!data || !svgRef.current) return;

        try {
            d3.select(svgRef.current).selectAll("*").remove();
            const { individuals, families, children } = data;

            if (!individuals?.length) {
                throw new Error('No family members found');
            }

            // Calculate dynamic dimensions based on number of individuals
            const nodeCount = individuals.length;
            const baseSize = 100; // Base size per node
            const dynamicWidth = Math.max(window.innerWidth - 80, Math.ceil(Math.sqrt(nodeCount)) * baseSize);
            const dynamicHeight = Math.max(window.innerHeight - 250, Math.ceil(Math.sqrt(nodeCount)) * baseSize);

            // Setup SVG with dynamic dimensions
            const margin = { top: 50, right: 50, bottom: 50, left: 50 };
            const width = dynamicWidth - margin.left - margin.right;
            const height = dynamicHeight - margin.top - margin.bottom;
            
            const svg = d3.select(svgRef.current)
                .attr("width", width + margin.left + margin.right)
                .attr("height", height + margin.top + margin.bottom)
                .append("g")
                .attr("class", "zoom-layer"); // Add class for zoom targeting

            // Create container for zoomable content
            const zoomContainer = svg.append("g")
                .attr("transform", `translate(${margin.left},${margin.top})`);

            // Create nodes array combining individuals and family units
            const nodes = [
                ...individuals.map(person => ({
                    id: person.id.toString(), // Ensure IDs are strings
                    name: `${person.first_name} ${person.last_name}`,
                    gender: person.gender,
                    type: 'person'
                }))
            ];

            // Only add family nodes if we have valid families
            if (families && families.length > 0) {
                nodes.push(...families.map(f => ({
                    id: `family-${f.id.toString()}`,
                    type: 'family',
                    husband_id: f.husband_id?.toString(),
                    wife_id: f.wife_id?.toString()
                })));
            }

            // Create all links
            const links = [];
            
            // Add spouse-to-family links only if we have valid families
            if (families) {
                families.forEach(family => {
                    if (!family.id) {
                        console.warn('Family without ID:', family);
                        return;
                    }

                    const familyNodeId = `family-${family.id.toString()}`;
                    
                    if (family.husband_id) {
                        links.push({
                            source: family.husband_id.toString(),
                            target: familyNodeId,
                            type: 'spouse'
                        });
                    }
                    if (family.wife_id) {
                        links.push({
                            source: family.wife_id.toString(),
                            target: familyNodeId,
                            type: 'spouse'
                        });
                    }
                });
            }

            // Add child-to-family links only if we have valid children
            if (children) {
                children.forEach(child => {
                    if (!child.family_id || !child.child_id) {
                        console.warn('Invalid child record:', child);
                        return;
                    }

                    links.push({
                        source: `family-${child.family_id.toString()}`,
                        target: child.child_id.toString(),
                        type: 'child'
                    });
                });
            }

            console.log('Processed data:', { nodes, links });

            // Adjust simulation forces based on node count
            const simulation = d3.forceSimulation(nodes)
                .force("link", d3.forceLink(links)
                    .id(d => d.id)
                    .distance(d => d.type === 'spouse' ? 50 : 100))
                .force("charge", d3.forceManyBody()
                    .strength(d => d.type === 'family' ? -300 : -1000)
                    .distanceMax(width / 2))
                .force("center", d3.forceCenter(width / 2, height / 2))
                .force("collision", d3.forceCollide().radius(40))
                .force("x", d3.forceX(width / 2).strength(0.1))
                .force("y", d3.forceY(height / 2).strength(0.1));

            // Draw elements in the zoom container
            const link = zoomContainer.append("g")
                .selectAll("line")
                .data(links)
                .join("line")
                .attr("stroke", d => d.type === 'spouse' ? "#ff9999" : "#999")
                .attr("stroke-width", d => d.type === 'spouse' ? 2 : 1);

            const node = zoomContainer.append("g")
                .selectAll("g")
                .data(nodes.filter(d => d.type === 'person'))
                .join("g");

            // Add circles for nodes
            node.append("circle")
                .attr("r", 25)
                .attr("fill", d => d.gender === 'M' ? "#b7e1cd" : "#f7c7c7")
                .attr("stroke", "#666")
                .attr("stroke-width", 2);

            // Add text labels
            node.append("text")
                .attr("dy", "0.31em")
                .attr("text-anchor", "middle")
                .attr("font-size", "12px")
                .text(d => d.name)
                .clone(true)
                .lower()
                .attr("stroke", "white")
                .attr("stroke-width", 3);

            // Update positions on each tick
            simulation.on("tick", () => {
                link
                    .attr("x1", d => d.source.x)
                    .attr("y1", d => d.source.y)
                    .attr("x2", d => d.target.x)
                    .attr("y2", d => d.target.y);

                node.attr("transform", d => `translate(${d.x},${d.y})`);
            });

            // Enhanced zoom behavior
            const zoom = d3.zoom()
                .scaleExtent([0.1, 4])  // Allow more zoom levels
                .on("zoom", (event) => {
                    zoomContainer.attr("transform", event.transform);
                });

            // Add zoom controls
            const zoomControls = svg.append("g")
                .attr("class", "zoom-controls")
                .attr("transform", `translate(${width - 100}, 20)`);

            zoomControls.append("rect")
                .attr("width", 80)
                .attr("height", 30)
                .attr("fill", "white")
                .attr("stroke", "#ccc");

            zoomControls.append("text")
                .attr("x", 10)
                .attr("y", 20)
                .attr("cursor", "pointer")
                .text("➖")
                .on("click", () => {
                    svg.transition()
                        .duration(500)
                        .call(zoom.scaleBy, 0.7);
                });

            zoomControls.append("text")
                .attr("x", 40)
                .attr("y", 20)
                .attr("cursor", "pointer")
                .text("➕")
                .on("click", () => {
                    svg.transition()
                        .duration(500)
                        .call(zoom.scaleBy, 1.3);
                });

            // Reset view button
            zoomControls.append("text")
                .attr("x", 65)
                .attr("y", 20)
                .attr("cursor", "pointer")
                .text("⟲")
                .on("click", () => {
                    svg.transition()
                        .duration(750)
                        .call(zoom.transform, d3.zoomIdentity);
                });

            // Initialize zoom and add double-click to zoom
            svg.call(zoom)
                .on("dblclick.zoom", null);

        } catch (err) {
            console.error('Visualization error:', err);
            setError(err.message);
        }
    }, [data]);

    if (loading) return React.createElement('div', { className: 'text-center p-8' }, 'Loading...');
    if (error) return React.createElement('div', { className: 'text-center p-8 text-red-500' }, error);

    return React.createElement(ErrorBoundary, null, [
        React.createElement(Navigation, { 
            key: 'nav',
            title: 'Family Tree Visualization',
            leftMenuItems: Navigation.createTreeMenu(treeId),
            rightMenuItems: Navigation.createUserMenu()
        }),
        React.createElement('div', { 
            key: 'container',
            className: 'container mx-auto px-4 py-8 mt-16'
        }, [
            React.createElement('h2', { 
                key: 'title',
                className: 'text-2xl font-bold mb-6'
            }, 'Family Tree Visualization'),
            error ? 
                React.createElement('div', {
                    key: 'error',
                    className: 'text-red-500 p-4'
                }, error) :
                React.createElement('div', {
                    key: 'viz-container',
                    className: 'bg-white rounded-lg shadow-lg p-4'
                }, React.createElement('svg', {
                    ref: svgRef,
                    className: 'w-full',
                    style: { minHeight: '600px' }
                }))
        ])
    ]);
};
