const handleRoute = () => {
    const hash = window.location.hash;
    const root = ReactDOM.createRoot(document.getElementById('root'));
    
    console.log('Current hash:', hash); // Debug logging

    if (hash.startsWith('#/tree/') && hash.includes('/members')) {
        console.log('Rendering MembersList'); // Debug logging
        root.render(React.createElement(MembersList));
    } else {
        console.log('Rendering TreeList'); // Debug logging
        root.render(React.createElement(TreeList));
    }
};

// Initialize routing
window.addEventListener('hashchange', handleRoute);
window.addEventListener('load', () => {
    console.log('Initial route setup'); // Debug logging
    handleRoute();
});
