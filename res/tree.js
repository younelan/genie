// public/tree.js

document.addEventListener('DOMContentLoaded', function () {
    fetch(`index.php?action=get_tree_data&tree_id=${familyTreeId}`)
        .then(response => response.json())
        .then(data => {
            displayTree(data);
        })
        .catch(error => console.error('Error fetching tree data:', error));

    function displayTree(data) {
        const width = graphWidth;
        const height = graphHeight;

        
        const svg = d3.select("svg")
            .attr("width", width)
            .attr("height", height)
            .append("g")
            .attr("transform", "translate(40,0)");

        const simulation = d3.forceSimulation(data.nodes)
            .force("link", d3.forceLink(data.links).id(d => d.id).distance(60))
            .force("charge", d3.forceManyBody().strength(-40))
            .force("center", d3.forceCenter(width / 2+100, height / 2));

        const link = svg.append("g")
            .attr("class", "links")
            .selectAll("line")
            .data(data.links)
            .enter().append("line")
            .attr("class", "link");

        const node = svg.append("g")
            .attr("class", "nodes")
            .selectAll("g")
            .data(data.nodes)
            .enter().append("g")
            .attr("class", "node");

        node.append("circle")
            .attr("r", 5);

        node.append("text")
            .attr("dy", -10)
            .attr("x", 6)
            .style("text-anchor", "middle")
            .text(d => d.first_name + ' ' + d.last_name);

        simulation.on("tick", () => {
            link
                .attr("x1", d => d.source.x)
                .attr("y1", d => d.source.y)
                .attr("x2", d => d.target.x)
                .attr("y2", d => d.target.y);

            node
                .attr("transform", d => `translate(${d.x},${d.y})`);
        });

        node.call(d3.drag()
            .on("start", dragstarted)
            .on("drag", dragged)
            .on("end", dragended));

        function dragstarted(event, d) {
            if (!event.active) simulation.alphaTarget(0.3).restart();
            d.fx = d.x;
            d.fy = d.y;
        }

        function dragged(event, d) {
            d.fx = event.x;
            d.fy = event.y;
        }

        function dragended(event, d) {
            if (!event.active) simulation.alphaTarget(0);
            d.fx = null;
            d.fy = null;
        }
    }
});
