const root = ReactDOM.createRoot(document.getElementById('root'));

const handleRoute = () => {
    const hash = window.location.hash;
    
    // Better route pattern matching
    const treePattern = /^#\/tree\/(\d+)\/members$/;
    const memberPattern = /^#\/tree\/(\d+)\/member\/(\d+)$/;
    const descendantsPattern = /^#\/tree\/(\d+)\/member\/(\d+)\/descendants$/;

    if (treePattern.test(hash)) {
        root.render(React.createElement(MembersList));
    } else if (memberPattern.test(hash)) {
        const matches = hash.match(memberPattern);
        const [, treeId, memberId] = matches;
        root.render(React.createElement(MemberDetails, { treeId, memberId }));
    } else if (descendantsPattern.test(hash)) {
        const matches = hash.match(descendantsPattern);
        const [, treeId, memberId] = matches;
        root.render(React.createElement(DescendantsView, { treeId, memberId }));
    } else {
        root.render(React.createElement(TreeList));
    }
};

window.addEventListener('hashchange', handleRoute);
window.addEventListener('load', handleRoute);
