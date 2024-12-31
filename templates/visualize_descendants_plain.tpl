<script>
    const CARD_WIDTH = 150;
    const CARD_HEIGHT = 80;
    const LEVEL_HEIGHT = 120;
    const SPOUSE_GAP = 40;
    const SIBLING_GAP = 20;

    function visualizeDescendants(data, containerId) {
        const container = document.getElementById(containerId);
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('width', '100%');

        // Calculate initial dimensions
        const dimensions = calculateTreeDimensions(data);
        svg.setAttribute('height', dimensions.height + 200);
        
        const mainGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        mainGroup.setAttribute('transform', 'translate(100, 100)');
        svg.appendChild(mainGroup);
        
        drawPerson(mainGroup, data, 0, 0, 0);
        container.appendChild(svg);
    }

    function calculateTreeDimensions(person, depth = 0, cache = new Map()) {
        if (cache.has(person.id)) return { width: 0, height: 0 };
        cache.set(person.id, true);

        let width = CARD_WIDTH;
        let height = CARD_HEIGHT;

        if (person.families) {
            person.families.forEach(family => {
                const spouseWidth = CARD_WIDTH + SPOUSE_GAP;
                width = Math.max(width, spouseWidth);

                if (family.children) {
                    const childrenDims = family.children.reduce((acc, child) => {
                        const childDims = calculateTreeDimensions(child, depth + 1, cache);
                        return {
                            width: acc.width + childDims.width + SIBLING_GAP,
                            height: Math.max(acc.height, childDims.height)
                        };
                    }, { width: 0, height: 0 });

                    width = Math.max(width, childrenDims.width);
                    height = Math.max(height, LEVEL_HEIGHT + childrenDims.height);
                }
            });
        }

        return { width, height };
    }

    function drawPerson(svg, person, x, y, depth) {
        const personGroup = createPersonCard(person, x, y, depth);
        svg.appendChild(personGroup);

        if (person.families) {
            let currentX = x;
            person.families.forEach(family => {
                if (family.spouse) {
                    // Draw spouse
                    const spouseX = currentX + CARD_WIDTH + SPOUSE_GAP;
                    const spouseGroup = createPersonCard(family.spouse, spouseX, y, depth);
                    svg.appendChild(spouseGroup);

                    // Draw marriage connector
                    const marriageLineY = y + CARD_HEIGHT/2;
                    svg.appendChild(createLine(
                        currentX + CARD_WIDTH,
                        marriageLineY,
                        spouseX,
                        marriageLineY
                    ));

                    // Draw children
                    if (family.children) {
                        const marriageCenterX = currentX + CARD_WIDTH + (SPOUSE_GAP/2);
                        const childrenY = y + LEVEL_HEIGHT;

                        // Vertical line to children
                        svg.appendChild(createLine(
                            marriageCenterX,
                            marriageLineY,
                            marriageCenterX,
                            childrenY
                        ));

                        // Calculate children positions
                        let childX = marriageCenterX - ((family.children.length * (CARD_WIDTH + SIBLING_GAP))/2);
                        family.children.forEach(child => {
                            // Horizontal line to child
                            svg.appendChild(createLine(
                                marriageCenterX,
                                childrenY,
                                childX + CARD_WIDTH/2,
                                childrenY
                            ));

                            // Draw child and their descendants
                            drawPerson(svg, child, childX, childrenY, depth + 1);
                            childX += CARD_WIDTH + SIBLING_GAP;
                        });
                    }
                }
                currentX += CARD_WIDTH * 2 + SPOUSE_GAP;
            });
        }
    }

    function createPersonCard(person, x, y, depth) {
        const group = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        group.setAttribute('transform', `translate(${x},${y})`);
        
        const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        rect.setAttribute('width', CARD_WIDTH);
        rect.setAttribute('height', CARD_HEIGHT);
        rect.setAttribute('rx', '5');
        rect.setAttribute('class', 'person-card');
        
        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', CARD_WIDTH/2);
        text.setAttribute('y', CARD_HEIGHT/2);
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('dominant-baseline', 'middle');
        text.textContent = `${person.first_name} ${person.last_name}`;
        
        group.appendChild(rect);
        group.appendChild(text);
        return group;
    }

    function createLine(x1, y1, x2, y2) {
        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', x1);
        line.setAttribute('y1', y1);
        line.setAttribute('x2', x2);
        line.setAttribute('y2', y2);
        line.setAttribute('class', 'connector-line');
        return line;
    }
</script>