const ErrorBoundary = React.Component ? class extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false, error: null };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true, error };
    }

    render() {
        if (this.state.hasError) {
            return React.createElement('div', {
                className: 'container mx-auto px-4 py-8 text-center'
            }, [
                React.createElement('h2', { 
                    key: 'error-title',
                    className: 'text-2xl font-bold text-red-600 mb-4'
                }, 'Something went wrong'),
                React.createElement('pre', {
                    key: 'error-details',
                    className: 'text-gray-600'
                }, this.state.error?.message)
            ]);
        }
        return this.props.children;
    }
} : function() { return null; };
