const App = () => {
    const [currentComponent, setCurrentComponent] = React.useState(null);

    const handleRoute = () => {
        const hash = window.location.hash;
        let componentToRender = null;
        
        if (/^#\/tree\/(\d+)\/visualize$/.test(hash)) {
            const [, treeId] = hash.match(/^#\/tree\/(\d+)\/visualize$/);
            componentToRender = React.createElement(FamilyTreeVisualization, { title: T('Visualize'), treeId });
        } else if (/^#\/tree\/(\d+)\/edit$/.test(hash)) {
            componentToRender = React.createElement(EditTree, { title: T('Edit Tree') });
        } else if (/^#\/tree\/(\d+)\/member\/add$/.test(hash)) {
            componentToRender = React.createElement(AddMember, { title: T('Add Member') });
        } else if (/^#\/tree\/(\d+)\/members$/.test(hash)) {
            componentToRender = React.createElement(MembersList, { title: T('View Members') });
        } else if (/^#\/tree\/(\d+)\/member\/(\d+)$/.test(hash)) {
            const [, treeId, memberId] = hash.match(/^#\/tree\/(\d+)\/member\/(\d+)$/);
            componentToRender = React.createElement(MemberDetails, { title: T('Member Details'), treeId, memberId });
        } else if (/^#\/tree\/(\d+)\/member\/(\d+)\/descendants$/.test(hash)) {
            const [, treeId, memberId] = hash.match(/^#\/tree\/(\d+)\/member\/(\d+)\/descendants$/);
            componentToRender = React.createElement(DescendantsView, { title: T('Visualize'), treeId, memberId });
        } else if (/^#\/tree\/(\d+)\/synonyms$/.test(hash)) {
            componentToRender = React.createElement(SynonymManager, { title: T('Manage Synonyms') });
        } else {
            componentToRender = React.createElement(TreeList, { title: T('Family Trees') });
        }
        setCurrentComponent(componentToRender);
    };

    React.useEffect(() => {
        window.addEventListener('hashchange', handleRoute);
        handleRoute();
        return () => window.removeEventListener('hashchange', handleRoute);
    }, []);

    return React.createElement('div', null, currentComponent || 'Loading...');
};

// Initialize the app when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(React.createElement(App));
});
