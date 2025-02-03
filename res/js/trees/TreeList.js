const AppFooter = () => {
  return React.createElement('footer', {
    className: 'bg-white border-t border-gray-200 fixed bottom-0 w-full'
  }, React.createElement('div', {
    className: 'max-w-7xl mx-auto px-4 py-3 text-center text-sm text-gray-600'
  }, `© ${new Date().getFullYear()} ${window.companyName} - ${window.footerText}`));
};

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
      const response = await fetch('api/trees.php?action=list');
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
      const response = await fetch(`api/trees.php?action=delete&id=${treeId}`, {
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
    if (!showAddModal) return null;

    const [formData, setFormData] = React.useState({ 
      name: '', 
      description: '', 
      is_public: false 
    });

    const handleSubmit = async (e) => {
      e.preventDefault();
      try {
        const response = await fetch('api/trees.php?action=create', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
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
      className: 'fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50'
    },
      React.createElement('div', { 
        className: 'bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all'
      }, [
        React.createElement('div', { 
          key: 'modal-header',
          className: 'px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-xl'
        }, [
          React.createElement('h3', { 
            key: 'modal-title',
            className: 'text-xl font-semibold text-white'
          }, 'Create New Family Tree'),
          React.createElement('button', {
            key: 'modal-close',
            onClick: () => setShowAddModal(false),
            className: 'text-white hover:text-blue-100 text-2xl font-bold'
          }, '×')
        ]),
        React.createElement('form', {
          key: 'modal-form',
          onSubmit: handleSubmit,
          className: 'p-6 space-y-4'
        }, [
          React.createElement('div', { 
            key: 'name-field',
            className: 'space-y-2' 
          }, [
            React.createElement('label', { 
              key: 'name-label',
              className: 'text-sm font-medium text-gray-700'
            }, 'Tree Name'),
            React.createElement('input', {
              key: 'name-input',
              type: 'text',
              value: formData.name,
              onChange: (e) => setFormData(prev => ({ ...prev, name: e.target.value })),
              className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
              required: true
            })
          ]),
          React.createElement('div', { 
            key: 'description-field',
            className: 'space-y-2' 
          }, [
            React.createElement('label', { 
              key: 'description-label',
              className: 'text-sm font-medium text-gray-700'
            }, 'Description'),
            React.createElement('textarea', {
              key: 'description-input',
              value: formData.description,
              onChange: (e) => setFormData(prev => ({ ...prev, description: e.target.value })),
              className: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
              rows: '3'
            })
          ]),
          React.createElement('label', { 
            key: 'public-field',
            className: 'flex items-center gap-2 text-sm text-gray-600'
          }, [
            React.createElement('input', {
              key: 'public-input',
              type: 'checkbox',
              checked: formData.is_public,
              onChange: (e) => setFormData(prev => ({ ...prev, is_public: e.target.checked })),
              className: 'rounded text-blue-600 focus:ring-blue-500'
            }),
            'Make this tree public'
          ]),
          React.createElement('div', { 
            key: 'button-group',
            className: 'flex justify-end gap-3 mt-6'
          }, [
            React.createElement('button', {
              key: 'cancel-button',
              type: 'button',
              onClick: () => setShowAddModal(false),
              className: 'px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md'
            }, 'Cancel'),
            React.createElement('button', {
              key: 'submit-button',
              type: 'submit',
              className: 'px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow-sm'
            }, 'Create Tree')
          ])
        ])
      ])
    );
  };

  const Dropdown = ({ children }) => {
    const [isOpen, setIsOpen] = React.useState(false);
    const dropdownRef = React.useRef(null);

    React.useEffect(() => {
      const handleClickOutside = (event) => {
        if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
          setIsOpen(false);
        }
      };
      document.addEventListener('mousedown', handleClickOutside);
      return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    return React.createElement('div', {
      className: 'relative inline-block',
      ref: dropdownRef
    }, [
      React.createElement('button', {
        key: 'trigger',
        onClick: () => setIsOpen(!isOpen),
        className: 'p-2 text-gray-600 hover:text-gray-800 rounded-full hover:bg-gray-100'
      }, '⚙️'),
      isOpen && React.createElement('div', {
        key: 'menu',
        className: 'absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10'
      }, children)
    ]);
  };

  const treeCard = (tree) => React.createElement('div', {
    key: tree.id,
    className: 'bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 border border-gray-100'
  }, [
    React.createElement('div', { 
      key: `content-${tree.id}`,
      className: 'p-6' 
    }, [
      React.createElement('h3', { 
        key: `title-${tree.id}`,
        className: 'text-xl font-semibold text-gray-900 mb-2' 
      }, tree.name),
      React.createElement('p', { 
        key: `desc-${tree.id}`,
        className: 'text-gray-600 mb-4 h-20 overflow-hidden' 
      }, tree.description || 'No description available'),
      React.createElement('div', { 
        key: `actions-${tree.id}`,
        className: 'flex items-center justify-between gap-4 pt-4 border-t border-gray-100' 
      }, [
        React.createElement('button', {
          key: `members-${tree.id}`,
          onClick: () => {
            window.location.hash = `#/tree/${tree.id}/members`;
          },
          className: 'inline-flex items-center text-blue-600 hover:text-blue-800 font-medium'
        }, [
          React.createElement('span', {
            key: `count-${tree.id}`,
            className: 'bg-blue-100 text-blue-600 px-2 py-1 rounded-md mr-2'
          }, tree.member_count || 0),
          React.createElement('span', {
            key: `label-${tree.id}`,
            className: 'whitespace-nowrap'
          }, 'Members')
        ]),
        React.createElement(Dropdown, { key: 'dropdown' }, [
          React.createElement('div', { 
            key: 'menu-items',
            className: 'py-1'
          }, [
            React.createElement('a', {
              key: 'view',
              href: `#/tree/${tree.id}/members`,
              className: 'block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100'
            }, 'View Members'),
            React.createElement('a', {
              key: 'edit',
              href: `#/tree/${tree.id}/edit`,
              className: 'block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100'
            }, 'Edit Settings'),
            React.createElement('button', {
              key: 'delete',
              onClick: () => deleteTree(tree.id),
              className: 'w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50'
            }, 'Delete Tree')
          ])
        ])
      ])
    ])
  ]);

  const mainContent = [
    React.createElement('main', { 
      key: 'main',
      className: 'container mx-auto px-4 py-16 mt-16 mb-16'
    }, [
      React.createElement('div', { 
        key: 'header-psection',
        className: 'flex justify-between items-center mb-8' 
      }, [
        React.createElement('h2', { 
          key: 'title',
          className: 'text-3xl font-bold text-gray-900' 
        }, 'Your Family Trees'),
        React.createElement('button', {
          key: 'add-button',
          className: 'inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow-sm transition-colors',
          onClick: () => setShowAddModal(true)
        }, [
          React.createElement('svg', {
            key: 'icon',
            className: 'w-5 h-5 mr-2',
            fill: 'none',
            viewBox: '0 0 24 24',
            stroke: 'currentColor'
          }, React.createElement('path', {
            strokeLinecap: 'round',
            strokeLinejoin: 'round',
            strokeWidth: 2,
            d: 'M12 4v16m8-8H4'
          })),
          'New Tree'
        ])
      ]),
      React.createElement('div', { 
        key: 'tree-grid',
        className: 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6' 
      }, trees.map(tree => treeCard(tree)))
    ]),
    React.createElement(AppFooter, { key: 'footer' }),
    React.createElement(AddTreeModal, { key: 'modal' })
  ];

  if (loading) return React.createElement('div', { className: 'text-center p-4' }, 'Loading...');
  if (error) return React.createElement('div', { className: 'alert alert-danger' }, error);

  return React.createElement('div', { className: 'container-fluid' }, [
    React.createElement(Navigation, { 
        key: 'nav',
        title: 'Family Trees',
        rightMenuItems: Navigation.createUserMenu()
    }),
    React.createElement(React.Fragment, null, mainContent)
  ]);
};

// ...existing code...
