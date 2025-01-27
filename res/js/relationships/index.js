import { RelationshipType } from './types.js';
import { FormGenerator } from './forms.js';
import { RelationshipHandlers } from './handlers.js';

class RelationshipManager {
    constructor(member, spouseFamilies) {
        this.member = member;
        this.spouseFamilies = spouseFamilies;
        this.formGenerator = new FormGenerator(member, spouseFamilies);
        this.handlers = new RelationshipHandlers(member);
        this.modal = null;
        this.activeTab = RelationshipType.SPOUSE;
    }

    initialize() {
        console.log('Initializing relationship manager...'); // Debug
        this.initializeModal();
        this.initializeTabs();
        this.initializeSaveButton();
        this.loadInitialForm();
        this.initializeAddButton();
    }

    initializeModal() {
        const modalElement = document.getElementById('addRelationshipModal');
        if (modalElement) {
            this.modal = new bootstrap.Modal(modalElement);
            console.log('Modal initialized'); // Debug
        }
    }

    initializeTabs() {
        const tabs = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                // Remove active class from all tabs
                tabs.forEach(t => {
                    t.classList.remove('active');
                    const pane = document.querySelector(t.dataset.bsTarget);
                    if (pane) {
                        pane.classList.remove('show', 'active');
                    }
                });
                
                // Activate clicked tab
                tab.classList.add('active');
                const targetPane = document.querySelector(tab.dataset.bsTarget);
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }
                
                this.activeTab = tab.id.replace('-tab', '');
                this.loadFormContent(this.activeTab);
                console.log('Tab changed to:', this.activeTab);
            });
        });
    }

    loadFormContent(type) {
        console.log('Loading form content for:', type); // Debug
        const formContent = document.querySelector(`#${type}-form-content`);
        if (!formContent) {
            console.error(`Form content container not found for ${type}`); // Debug
            return;
        }

        switch(type) {
            case RelationshipType.SPOUSE:
                formContent.innerHTML = this.formGenerator.getSpouseForm();
                this.handlers.initializeSpouseHandlers();
                break;
            case RelationshipType.CHILD:
                formContent.innerHTML = this.formGenerator.getChildForm();
                this.handlers.initializeChildHandlers();
                break;
            case RelationshipType.PARENT:
                formContent.innerHTML = this.formGenerator.getParentForm();
                this.handlers.initializeParentHandlers();
                break;
            case RelationshipType.OTHER:
                formContent.innerHTML = this.formGenerator.getOtherForm();
                this.handlers.initializeOtherHandlers();
                break;
        }
    }

    loadInitialForm() {
        console.log('Loading initial form...'); // Debug
        this.loadFormContent(this.activeTab);
    }

    initializeSaveButton() {
        const saveButton = document.getElementById('saveRelationship');
        if (saveButton) {
            saveButton.addEventListener('click', () => this.saveRelationship());
            console.log('Save button handler initialized'); // Debug
        }
    }

    initializeAddButton() {
        const addButton = document.querySelector('[data-bs-target="#addRelationshipModal"]');
        if (addButton) {
            addButton.addEventListener('click', () => {
                console.log('Add button clicked');
                this.modal.show();
            });
            console.log('Add button handler initialized');
        } else {
            console.error('Add relationship button not found');
        }
    }

    async saveRelationship() {
        console.log('Saving relationship...'); // Debug
        const form = document.getElementById('add-relationship-form');
        const formData = new FormData(form);
        formData.append('relationship_type', this.activeTab);

        try {
            const response = await fetch('index.php?action=add_relationship', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                this.modal.hide();
                window.location.reload();
            } else {
                alert(data.message || 'Failed to add relationship');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to add relationship. Please try again.');
        }
    }
}

// Initialize when the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing relationship manager...'); // Debug
    const { member, spouseFamilies } = window.relationshipData;
    if (!member || !spouseFamilies) {
        console.error('Required data not found:', { member, spouseFamilies }); // Debug
        return;
    }
    const manager = new RelationshipManager(member, spouseFamilies);
    manager.initialize();
    const addRelationshipModal = new bootstrap.Modal(document.getElementById('addRelationshipModal'));

});

// Add at the top of the file
const resizeObserverError = error => {
    if (error.message.includes('ResizeObserver')) {
        // Ignore ResizeObserver errors
        return;
    }
    console.error(error);
};

window.addEventListener('error', resizeObserverError);
window.addEventListener('unhandledrejection', resizeObserverError); 