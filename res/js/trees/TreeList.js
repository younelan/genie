// Remove AppFooter component
const TreeList = () => {
  const [trees, setTrees] = React.useState([]);
  const [loading, setLoading] = React.useState(true);
  const [error, setError] = React.useState(null);
  const [showAddModal, setShowAddModal] = React.useState(false);
  const [showImportModal, setShowImportModal] = React.useState(false);

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
      const data = await response.json();
      
      if (!data.success) {
        throw new Error(data.error || 'Failed to delete tree');
      }
      
      await fetchTrees(); // Only refresh if deletion was successful
      
    } catch (err) {
      console.error('Error deleting tree:', err);
      alert(err.message);
    }
  };

  const handleEmptyTree = async (treeId) => {
    if (!confirm('Are you sure you want to empty this tree? All members and relationships will be deleted. This cannot be undone.')) {
      return;
    }
    
    try {
      const response = await fetch(`api/trees.php?action=empty&id=${treeId}`, {
        method: 'POST'
      });
      if (!response.ok) throw new Error('Failed to empty tree');
      await fetchTrees(); // Refresh the list
    } catch (err) {
      console.error('Error emptying tree:', err);
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
            is_public: formData.is_public // Keep as boolean, don't convert
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

  const ImportTreeModal = () => {
    if (!showImportModal) return null;

    const [formData, setFormData] = React.useState({ 
      name: '',
      file: null
    });

    const handleSubmit = async (e) => {
      e.preventDefault();
      const data = new FormData();
      data.append('name', formData.name);
      data.append('file', formData.file);

      try {
        const response = await fetch('api/trees.php?action=import_gedcom', {
          method: 'POST',
          body: data
        });
        
        if (!response.ok) throw new Error('Failed to import GEDCOM');
        const result = await response.json();
        
        if (result.success) {
          setShowImportModal(false);
          setFormData({ name: '', file: null });
          fetchTrees();
        }
      } catch (err) {
        console.error('Error importing GEDCOM:', err);
        alert(err.message);
      }
    };

    return React.createElement('div', {
      className: 'fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50'
    },
      React.createElement('div', {
        className: 'bg-white rounded-xl shadow-2xl w-full max-w-md mx-4'
      }, [
        React.createElement('div', {
          key: 'modal-header',
          className: 'px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-xl'
        }, [
          React.createElement('h3', {
            className: 'text-xl font-semibold text-white'
          }, 'Import GEDCOM File'),
          React.createElement('button', {
            onClick: () => setShowImportModal(false),
            className: 'text-white hover:text-blue-100 text-2xl font-bold'
          }, '×')
        ]),
        React.createElement('form', {
          onSubmit: handleSubmit,
          className: 'p-6 space-y-4'
        }, [
          React.createElement('div', {
            className: 'space-y-2'
          }, [
            React.createElement('label', {
              className: 'text-sm font-medium text-gray-700'
            }, 'Tree Name'),
            React.createElement('input', {
              type: 'text',
              value: formData.name,
              onChange: (e) => setFormData(prev => ({ ...prev, name: e.target.value })),
              className: 'w-full px-3 py-2 border border-gray-300 rounded-md',
              required: true
            })
          ]),
          React.createElement('div', {
            className: 'space-y-2'
          }, [
            React.createElement('label', {
              className: 'text-sm font-medium text-gray-700'
            }, 'GEDCOM File'),
            React.createElement('input', {
              type: 'file',
              accept: '.ged',
              onChange: (e) => setFormData(prev => ({ ...prev, file: e.target.files[0] })),
              className: 'w-full',
              required: true
            })
          ]),
          React.createElement('div', {
            className: 'flex justify-end gap-3 mt-6'
          }, [
            React.createElement('button', {
              type: 'button',
              onClick: () => setShowImportModal(false),
              className: 'px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md'
            }, 'Cancel'),
            React.createElement('button', {
              type: 'submit',
              className: 'px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md'
            }, 'Import')
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

  // Move createDropdownItems before treeCard
  const createDropdownItems = (tree) => [
    {
      label: 'View Members',
      onClick: (e) => {
        e.preventDefault();
        window.location.hash = `#/tree/${tree.id}/members`;
      }
    },
    {
      label: 'Edit Settings',
      onClick: (e) => {
        e.preventDefault();
        window.location.hash = `#/tree/${tree.id}/edit`;
      }
    },
    {
      label: 'Manage Synonyms',
      onClick: (e) => {
        e.preventDefault();
        window.location.hash = `#/tree/${tree.id}/synonyms`;
      }
    },
    {
      label: 'Export GEDCOM',
      onClick: (e) => {
        e.preventDefault();
        window.location = `api/trees.php?action=export_gedcom&tree_id=${tree.id}`;
      }
    },
    {
      label: 'Empty Tree',
      onClick: () => handleEmptyTree(tree.id),
      className: 'text-orange-600 hover:bg-orange-50'
    },
    {
      label: 'Delete Tree',
      onClick: () => deleteTree(tree.id),
      className: 'text-red-600 hover:bg-red-50'
    }
  ].map(item => React.createElement('button', {
    key: item.label,
    onClick: item.onClick,
    className: `w-full text-left px-4 py-2 text-sm ${item.className || 'text-gray-700 hover:bg-gray-100'}`
  }, item.label));

  const treeCard = (tree) => React.createElement('div', {
    key: tree.id,
    className: 'bg-white rounded shadow hover:shadow-lg transition-all duration-200'
  }, [
    React.createElement('a', {
      key: `header-${tree.id}`,
      href: `#/tree/${tree.id}/members`,
      className: 'block p-3 bg-primary rounded-t cursor-pointer hover:opacity-90'
    }, [
      React.createElement('h3', {
        key: `title-${tree.id}`,
        className: 'text-xl font-semibold text-white mb-1'
      }, tree.name),
      React.createElement('div', {
        key: 'meta',
        className: 'flex items-center gap-3 text-white text-sm font-medium'
      }, [
        React.createElement('span', null, [
          '📅 ',
          new Date(tree.created_at).toLocaleDateString()
        ]),
        // Fix public icon display - only show if true, don't convert to number
        Boolean(tree.is_public) && React.createElement('span', {
          className: 'flex items-center gap-1 bg-white/20 px-2 py-0.5 rounded'
        }, [
          '🌍 Public'
        ])
      ])
    ]),

    React.createElement('div', { 
      key: `content-${tree.id}`,
      className: 'p-3 bg-[#dcd8e0]' 
    }, [
      React.createElement('p', { 
        key: `desc-${tree.id}`,
        className: 'text-[#2c0a2a] mb-2 h-10 md:h-14 overflow-hidden text-base' // Increased height and font size
      }, tree.description || ''),
      React.createElement('div', {
        key: `stats-${tree.id}`,
        className: 'flex items-center justify-between gap-4 pt-3 border-t border-gray-300 px-3 py-2'
      }, [
        // Make stats clickable separately from dropdown
        React.createElement('a', {
          href: `#/tree/${tree.id}/members`,
          className: 'flex items-center text-[#2c0a2a] hover:text-[#1e0a76] font-medium hover:bg-[#a7a2c5] rounded-lg px-3 py-2 transition-colors'
        }, [
          React.createElement('span', {
            className: 'bg-primary text-white px-3 py-1 rounded-md mr-2 text-lg font-bold'
          }, tree.member_count || 0),
          'Members'
        ]),
        // Stop event propagation on dropdown click
        React.createElement('div', {
          onClick: (e) => e.preventDefault(),
          className: 'z-10'
        }, React.createElement(Dropdown, { key: 'dropdown' }, createDropdownItems(tree)))
      ])
    ])
  ]);

  // Main content wrapper with reorganized layout
  const mainContent = [
    React.createElement('main', {
      key: 'main',
      className: 'container mx-auto px-4 py-8 mt-16 flex-grow' // Add flex-grow here
    }, [
      // Remove header section completely
      React.createElement('div', {
        key: 'tree-grid',
        className: 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6' // Added bottom margin
      }, trees.map(tree => treeCard(tree))),

      // New Tree button moved below grid
      React.createElement('div', {
        key: 'buttons-container',
        className: 'flex justify-center gap-4'
      }, [
        React.createElement('button', {
          className: 'inline-flex items-center px-6 py-3 bg-primary hover:opacity-90 text-white rounded shadow transition-colors',
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
          'Create New Family Tree'
        ]),
        React.createElement('button', {
          className: 'inline-flex items-center px-6 py-3 bg-green-600 hover:opacity-90 text-white rounded shadow',
          onClick: () => setShowImportModal(true)
        }, [
          React.createElement('svg', {
            className: 'w-5 h-5 mr-2',
            fill: 'none',
            viewBox: '0 0 24 24',
            stroke: 'currentColor'
          }, React.createElement('path', {
            strokeLinecap: 'round',
            strokeLinejoin: 'round',
            strokeWidth: 2,
            d: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'
          })),
          'Import GEDCOM'
        ])
      ])
    ]),
    React.createElement(Footer, { key: 'footer' }),
    React.createElement(AddTreeModal, { key: 'add-modal' }),
    showImportModal && React.createElement(ImportTreeModal, { key: 'import-modal' })
  ];

  if (loading) return React.createElement('div', { className: 'text-center p-4' }, 'Loading...');
  if (error) return React.createElement('div', { className: 'alert alert-danger' }, error);

  return React.createElement('div', { 
    className: 'min-h-screen flex flex-col' // Keep min-height and flex
  }, [
    React.createElement(Navigation, { 
        key: 'nav',
        title: 'Family Trees',
        rightMenuItems: Navigation.createUserMenu()
    }),
    React.createElement('div', { 
        key: 'content',
        className: 'flex flex-col flex-grow' // Add flex-grow here
    }, mainContent)
  ]);
};

// ...existing code...
