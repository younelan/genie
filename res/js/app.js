const root = ReactDOM.createRoot(document.getElementById('root'));

const handleRoute = () => {
    const hash = window.location.hash;
    
    if (hash.startsWith('#/tree/') && hash.includes('/members')) {
        root.render(React.createElement(MembersList));
    } else if (hash.match(/#\/tree\/\d+\/member\/\d+/)) {
        // Pass treeId and memberId as props
        const [, , treeId, , memberId] = hash.split('/');
        root.render(React.createElement(MemberDetails));
    } else {
        root.render(React.createElement(TreeList));
    }
};

window.addEventListener('hashchange', handleRoute);
window.addEventListener('load', handleRoute);
