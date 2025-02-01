// Create root once and store it
const root = ReactDOM.createRoot(document.getElementById('root'));

const handleRoute = () => {
    const hash = window.location.hash;
    
    console.log('Current hash:', hash);

    if (hash.startsWith('#/tree/') && hash.includes('/members')) {
        console.log('Rendering MembersList');
        root.render(React.createElement(MembersList));
    } else if (hash.match(/#\/tree\/\d+\/member\/\d+/)) {
        const [, , treeId, , memberId] = hash.split('/');
        console.log('Rendering MemberDetails');
        root.render(React.createElement(MemberDetails, { treeId, memberId }));
    } else {
        console.log('Rendering TreeList');
        root.render(React.createElement(TreeList));
    }
};

// Remove root creation from individual components
window.addEventListener('hashchange', handleRoute);
window.addEventListener('load', handleRoute);
