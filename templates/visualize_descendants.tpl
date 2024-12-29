<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>{{ get_translation("Descendants of") }} {{ member.first_name }} {{ member.last_name }}</h2>
                    <div class="btn-group">
                        <button class="btn btn-primary btn-sm" id="zoomIn">+</button>
                        <button class="btn btn-primary btn-sm" id="zoomOut">-</button>
                        <button class="btn btn-secondary btn-sm" id="resetView">Reset</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="tree-container"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const margin = {top: 20, right: 90, bottom: 30, left: 90};
    const width = window.innerWidth - margin.left - margin.right - 40;
    const height = window.innerHeight - margin.top - margin.bottom - 100;

    // Create SVG container
    const svg = d3.select('#tree-container')
        .append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
        .call(d3.zoom().on('zoom', zoomed))
        .append('g')
        .attr('transform', `translate(${margin.left},${margin.top})`);

    // Create tree layout
    const tree = d3.tree()
        .size([height, width - 200])
        .separation((a, b) => (a.parent === b.parent ? 1 : 1.2));

    fetch(`index.php?action=get_descendants_data&member_id={{ memberId }}`)
        .then(response => response.json())
        .then(data => {
            // Process the data to create a proper hierarchy
            function processData(data) {
                let nodes = [];
                
                // Add root person
                nodes.push({
                    id: data.id,
                    name: data.name,
                    data: data.data,
                    level: 0,
                    parent: null
                });

                // Process each marriage and its descendants
                data.marriages.forEach((marriage, index) => {
                    if (marriage.spouse) {
                        const spouseId = `spouse_${marriage.id}`;
                        nodes.push({
                            id: spouseId,
                            name: marriage.spouse.name,
                            data: marriage.spouse.data,
                            level: 0,
                            parent: data.id,
                            isSpouse: true
                        });
                    }

                    // Process children
                    marriage.children.forEach(child => {
                        processChildAndDescendants(child, data.id, nodes, 1);
                    });
                });

                return nodes;
            }

            function processChildAndDescendants(person, parentId, nodes, level) {
                nodes.push({
                    id: person.id,
                    name: person.name,
                    data: person.data,
                    level: level,
                    parent: parentId
                });

                person.marriages?.forEach(marriage => {
                    if (marriage.spouse) {
                        nodes.push({
                            id: `spouse_${marriage.id}`,
                            name: marriage.spouse.name,
                            data: marriage.spouse.data,
                            level: level,
                            parent: person.id,
                            isSpouse: true
                        });
                    }

                    marriage.children.forEach(child => {
                        processChildAndDescendants(child, person.id, nodes, level + 1);
                    });
                });
            }

            const nodes = processData(data);

            // Create hierarchical layout
            const stratify = d3.stratify()
                .id(d => d.id)
                .parentId(d => d.parent);

            const root = stratify(nodes);
            const treeData = tree(root);

            // Draw links
            const links = svg.selectAll('.link')
                .data(treeData.links())
                .join('path')
                .attr('class', 'link')
                .attr('d', d3.linkHorizontal()
                    .x(d => d.y)
                    .y(d => d.x));

            // Create nodes
            const node = svg.selectAll('.node')
                .data(treeData.descendants())
                .join('g')
                .attr('class', d => `node${d.data.isSpouse ? ' spouse' : ''}`)
                .attr('transform', d => `translate(${d.y},${d.x})`);

            // Add spouse lines
            svg.selectAll('.spouse-line')
                .data(treeData.descendants().filter(d => d.data.isSpouse))
                .join('line')
                .attr('class', 'spouse-line')
                .attr('x1', d => d.parent.y)
                .attr('y1', d => d.parent.x)
                .attr('x2', d => d.y)
                .attr('y2', d => d.x);

            // Add circles for nodes
            node.append('circle')
                .attr('r', 6)
                .attr('fill', d => d.data.data.gender === 1 ? '#7cbade' : '#de7c99');

            // Add labels
            node.append('text')
                .attr('dy', '0.31em')
                .attr('x', d => d.children || d.data.isSpouse ? -8 : 8)
                .attr('text-anchor', d => d.children || d.data.isSpouse ? 'end' : 'start')
                .text(d => d.data.name)
                .clone(true).lower()
                .attr('stroke', 'white')
                .attr('stroke-width', 3);

            // Add click handler
            node.on('click', (event, d) => {
                if (!d.data.isSpouse) {
                    window.location.href = `index.php?action=edit_member&member_id=${d.data.id}`;
                }
            });

            // Center the tree
            const initialTransform = d3.zoomIdentity
                .translate(width/2 - root.y, height/2 - root.x)
                .scale(0.8);
            svg.call(d3.zoom().transform, initialTransform);
        });

    function zoomed(event) {
        svg.attr('transform', event.transform);
    }
});
</script>

<style>
#tree-container {
    width: 100%;
    height: calc(100vh - 150px);
    overflow: hidden;
    background-color: #fff;
    border-radius: 4px;
}

.node {
    cursor: pointer;
}

.node circle {
    stroke-width: 2px;
}

.node text {
    font: 12px sans-serif;
    fill: #333;
}

.node .dates {
    font-size: 10px;
    fill: #666;
}

.link {
    fill: none;
    stroke: #ccc;
    stroke-width: 1.5px;
}

.spouse-line {
    stroke: #999;
    stroke-width: 1.5px;
    stroke-dasharray: 4;
}

.node.spouse circle {
    stroke-dasharray: 3;
}

.card {
    height: calc(100vh - 100px);
}

.card-body {
    overflow: hidden;
    padding: 0;
}
</style>
