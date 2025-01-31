const TreeList = () => {
  const [trees, setTrees] = React.useState([]);
  const [loading, setLoading] = React.useState(true);
  const [error, setError] = React.useState(null);
  const [showAddModal, setShowAddModal] = React.useState(false);

  React.useEffect(() => {
    fetchTrees();
  }, []);

  const fetchTrees = async () => {
    try {
      // Use the dedicated API endpoint
      const response = await fetch('api/trees.php');
      if (!response.ok) throw new Error('Failed to fetch trees');
      const data = await response.json();
      console.log('Trees data:', data);
      setTrees(data.data || []);
    } catch (err) {
      console.error('Error fetching trees:', err);
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const deleteTree = async (treeId) => {
    if (!confirm('Are you sure you want to delete this tree?')) return;
    
    try {
      // Use the dedicated API endpoint with DELETE method
      const response = await fetch(`api/trees.php?id=${treeId}`, {
        method: 'DELETE'
      });
      if (!response.ok) throw new Error('Failed to delete tree');
      await fetchTrees();
    } catch (err) {
      console.error('Error deleting tree:', err);
      alert(err.message);
    }
  };

  const AddTreeModal = () => {
    // Move form state inside the modal component
    const [formData, setFormData] = React.useState({ name: '', description: '', is_public: false });
    
    const handleSubmit = async (e) => {
      e.preventDefault();
      try {
        const response = await fetch('api/trees.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            ...formData,
            is_public: formData.is_public ? 1 : 0
          })
        });
        
        if (!response.ok) throw new Error('Failed to create tree');
        const data = await response.json();
        if (data.success) {
          setShowAddModal(false);
          setFormData({ name: '', description: '', is_public: false });
          fetchTrees();
        }
      } catch (err) {
        console.error('Error creating tree:', err);
        alert(err.message);
      }
    };
  
    return React.createElement('div', {
      className: 'modal' + (showAddModal ? ' show d-block' : ' d-none'),
      tabIndex: '-1',
      role: 'dialog'
    },
      React.createElement('div', { className: 'modal-dialog modal-dialog-centered' },
        React.createElement('div', { className: 'modal-content shadow' },
          React.createElement('div', { className: 'modal-header' },
            React.createElement('h5', { className: 'modal-title' }, 'Create New Family Tree'),
            React.createElement('button', {
              type: 'button',
              className: 'btn-close',
              onClick: () => setShowAddModal(false)
            })
          ),
          React.createElement('form', { onSubmit: handleSubmit },
            React.createElement('div', { className: 'modal-body' },
              React.createElement('div', { className: 'mb-3' },
                React.createElement('label', { className: 'form-label' }, 'Tree Name'),
                React.createElement('input', {
                  type: 'text',
                  className: 'form-control',
                  value: formData.name,
                  onChange: (e) => setFormData(prev => ({ ...prev, name: e.target.value })),
                  required: true
                })
              ),
              React.createElement('div', { className: 'mb-3' },
                React.createElement('label', { className: 'form-label' }, 'Description'),
                React.createElement('textarea', {
                  className: 'form-control',
                  value: formData.description,
                  onChange: (e) => setFormData(prev => ({ ...prev, description: e.target.value })),
                  rows: '3'
                })
              ),
              React.createElement('div', { className: 'mb-3 form-check' },
                React.createElement('input', {
                  type: 'checkbox',
                  className: 'form-check-input',
                  id: 'isPublic',
                  checked: formData.is_public,
                  onChange: (e) => setFormData(prev => ({ ...prev, is_public: e.target.checked }))
                }),
                React.createElement('label', {
                  className: 'form-check-label',
                  htmlFor: 'isPublic'
                }, 'Make this tree public')
              )
            ),
            React.createElement('div', { className: 'modal-footer' },
              React.createElement('button', {
                type: 'button',
                className: 'btn btn-secondary',
                onClick: () => setShowAddModal(false)
              }, 'Cancel'),
              React.createElement('button', {
                type: 'submit',
                className: 'btn btn-primary'
              }, 'Create Tree')
            )
          )
        )
      )
    );
  };

  if (loading) return React.createElement('div', { className: 'text-center p-4' }, 'Loading...');
  if (error) return React.createElement('div', { className: 'alert alert-danger' }, error);

  return React.createElement(React.Fragment, null,
    React.createElement('div', { className: 'container-fluid px-4 py-5' },
      React.createElement('div', { className: 'row mb-5 align-items-center' },
        React.createElement('div', { className: 'col' },
          React.createElement('h1', { className: 'display-4 fw-bold text-primary' }, 'Family Trees')
        ),
        React.createElement('div', { className: 'col-auto' },
          React.createElement('button', { 
            className: 'btn btn-primary btn-lg shadow-sm',
            onClick: () => setShowAddModal(true)
          }, 
          React.createElement('div', { className: 'd-flex align-items-center gap-2' },
            React.createElement('i', { className: 'bi bi-plus-circle' }, ''),
            'New Tree'
          ))
        )
      ),
      React.createElement('div', { className: 'row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4' },
        trees.map(tree => 
          React.createElement('div', { className: 'col', key: tree.id },
            React.createElement('div', { className: 'card h-100 shadow-sm border-0 hover-shadow' },
              React.createElement('div', { className: 'card-body p-4' },
                React.createElement('h5', { className: 'card-title fw-bold mb-3' }, tree.name),
                React.createElement('p', { className: 'card-text text-muted mb-4' }, 
                  tree.description || 'No description available'
                ),
                React.createElement('div', { className: 'd-flex gap-2 align-items-center' },
                  React.createElement('a', {
                    className: 'btn btn-outline-primary flex-grow-1',
                    href: `index.php?action=list_members&tree_id=${tree.id}`
                  }, `${tree.member_count || 0} Members`),
                  React.createElement('button', {
                    className: 'btn btn-outline-danger',
                    onClick: () => deleteTree(tree.id)
                  }, 'Delete')
                )
              )
            )
          )
        )
      )
    ),
    React.createElement(AddTreeModal)
  );
};

// Mount the component when the document is ready
document.addEventListener('DOMContentLoaded', () => {
  const root = ReactDOM.createRoot(document.getElementById('root'));
  root.render(React.createElement(TreeList));
});
