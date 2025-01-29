import { RelationshipType } from './types.js';
import { FormGenerator } from './forms.js';
import { RelationshipHandlers } from './handlers.js';

class RelationshipManager {
    constructor(member, spouseFamilies,translations) {
        this.member = member;
        this.spouseFamilies = spouseFamilies;
        this.translations = translations;
        this.formGenerator = new FormGenerator(member, spouseFamilies,translations);
        this.handlers = new RelationshipHandlers(member);
        this.modal = null;
        this.activeTab = RelationshipType.SPOUSE;
    }

    initialize() {
        console.log('Initializing relationship manager...'); // Debug
        this.initializeModal();
        this.initializeTabs();
        this.initializeButtons();
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

        switch (type) {
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
    initializeTranslations(translations) {
        this.translations = translations;
    }
    initializeButtons() {
        //console.log(translate('Initializing buttons...')); // Debug
        const saveButton = document.getElementById('saveRelationship');
        if (saveButton) {
            saveButton.addEventListener('click', (event) => {
                try {
                    this.saveRelationship(event);
                } catch (error) {
                    console.error('Error on initializeSaveButton', error);
                }
            });
            console.log('Save button handler initialized'); // Debug
        }
        const closeRelModalX = document.getElementById('closeRelModalX');
        if (closeRelModalX) {
            closeRelModalX.addEventListener('click', (event) => {
                this.closeRelModal();
            });    
        }
        const dismissRelModal = document.getElementById('dismissRelModal');
        if (dismissRelModal) {
            dismissRelModal.addEventListener('click', (event) => {
                this.closeRelModal();
            });
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

    closeRelModal() {
        this.modal.hide();
    }

    saveRelationship(event) {
        const activeTab = $('.nav-link.active').attr('id').replace('-tab', '');
        const formData = $(`#${activeTab}-form-content :input`).serializeArray();

        // Retrieve hidden fields' values
        const memberId = $('#member_id').val();
        const treeId = $('input[name="tree_id"]').val();
        const memberGender = $('input[name="member_gender"]').val();

        // Append hidden fields to form data
        formData.push({ name: 'member_id', value: memberId });
        formData.push({ name: 'tree_id', value: treeId });
        formData.push({ name: 'member_gender', value: memberGender });

        const relationshipData = {
            type: activeTab, // Relationship type (spouse, child, parent, other)
            data: formData,  // Form data
        };

        $.ajax({
            url: 'index.php',
            method: 'POST',
            data: {
                action: 'add_relationship',
                relationship: relationshipData,
            },
            success: (response) => {
                console.log('Relationship saved:', response);
                //this.modal.hide();

            },
            error: (xhr, status, error) => {
                console.error('Error saving relationship:', error);
            },
        });
    }


    async oldsaveRelationship(event) {
        console.log('Saving relationship...'); // Debug
        alert("hi");
        try {
            event.preventDefault();
            const form = document.getElementById('add-relationship-form');
            const formData = new FormData(form);
            const activeTab = document.querySelector('#addRelationshipModal.nav-link.active[data-bs-toggle="tab"]').id.replace('-tab', '');
            formData.append('relationship_type', activeTab);


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
            console.error('Error saving relationship', error)
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
    console.log(translations)
    const manager = new RelationshipManager(member, spouseFamilies,translations);
    manager.initialize();
    const addRelationshipModal = new bootstrap.Modal(document.getElementById('addRelationshipModal'));

});

// Add at the top of the file
const resizeObserverError = error => {
    if (error && error.message && error.message.includes('ResizeObserver')) {
        // Ignore ResizeObserver errors
        return;
    }
    console.error(error);
};

window.addEventListener('error', resizeObserverError);
window.addEventListener('unhandledrejection', resizeObserverError); 