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
        this.initializeTabs();        // For relationship modal tabs
        this.initializeButtons();
        this.loadInitialForm();
        this.initializeAddButton();
        this.initializeFamilyTabs();  // For family section tabs
    }

    initializeModal() {
        const modalElement = document.getElementById('addRelationshipModal');
        if (modalElement) {
            this.modal = new bootstrap.Modal(modalElement);
            console.log('Modal initialized'); // Debug
        }

        // Initialize replace spouse handlers
        //this.initializeReplaceSpouseHandlers();
    }

    initializeTabs() {
        // Remove the complex tab initialization and use Bootstrap's native handling
        const triggerTabList = document.querySelectorAll('#relationshipTabs button');
        triggerTabList.forEach(triggerEl => {
            const tabTrigger = new bootstrap.Tab(triggerEl);
            triggerEl.addEventListener('click', event => {
                event.preventDefault();
                tabTrigger.show();
                this.activeTab = triggerEl.id.replace('-tab', '');
                this.loadFormContent(this.activeTab);
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

        // Clean up any existing popper/dropdown instances
        const dropdowns = formContent.querySelectorAll('[data-bs-toggle="dropdown"]');
        dropdowns.forEach(dropdown => {
            const instance = bootstrap.Dropdown.getInstance(dropdown);
            if (instance) {
                instance.dispose();
            }
        });

        // Load new content
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

        // Reinitialize Bootstrap components
        const newDropdowns = formContent.querySelectorAll('[data-bs-toggle="dropdown"]');
        newDropdowns.forEach(dropdown => {
            new bootstrap.Dropdown(dropdown);
        });
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

    async saveRelationship() {
        try {
            const form = document.getElementById('add-relationship-form');
            if (!form) {
                throw new Error('Relationship form not found');
            }

            const formData = new FormData(form);

            // Get type from active tab
            const activeTabButton = document.querySelector('.nav-link[data-bs-toggle="tab"].active');
            if (!activeTabButton) {
                throw new Error('No active tab found');
            }

            const type = activeTabButton.id.replace('-tab', '');
            formData.append('type', type);
            formData.append('member_id', this.member.id);
            formData.append('tree_id', this.member.tree_id);

            // Debug what we're sending
            console.log('Sending relationship data:', type);
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            const response = await fetch('index.php?action=add_relationship', {
                method: 'POST',
                body: formData
            });

            let result;
            const responseText = await response.text(); // Get raw response text first
            
            try {
                result = JSON.parse(responseText); // Try to parse as JSON
            } catch (e) {
                console.error('Server response:', responseText); // Log raw response for debugging
                throw new Error('Invalid server response format');
            }

            if (!result.success) {
                throw new Error(result.message || 'Failed to save relationship');
            }

            this.closeRelModal();
            await this.reloadFamilySection();
            return true;

        } catch (error) {
            console.error('Error saving relationship:', error);
            alert(error.message || 'Failed to save relationship');
            return false;
        }
    }

    async reloadFamilySection() {
        try {
            const response = await fetch(`index.php?action=get_families&member_id=${this.member.id}`);
            const data = await response.json();
            
            if (data.success) {
                // Update spouse families
                this.updateSpouseFamiliesSection(data.spouse_families);
                // Update child families
                this.updateChildFamiliesSection(data.child_families);
            }
        } catch (error) {
            console.error('Error reloading family section:', error);
        }
    }

    updateSpouseFamiliesSection(spouseFamilies) {
        const tabsContainer = document.getElementById('familyTabs');
        const tabContentContainer = document.getElementById('familyTabsContent');
        
        if (!tabsContainer || !tabContentContainer) return;

        // Clear existing content
        tabsContainer.innerHTML = '';
        tabContentContainer.innerHTML = '';

        // Rebuild the tabs and content
        spouseFamilies.forEach((family, index) => {
            // Create tab
            const tabHtml = this.generateSpouseFamilyTab(family, index === 0);
            tabsContainer.innerHTML += tabHtml;

            // Create content
            const contentHtml = this.generateSpouseFamilyContent(family, index === 0);
            tabContentContainer.innerHTML += contentHtml;
        });

        // Reinitialize event listeners for the new content
        this.initializeFamilyEventListeners();
    }

    updateChildFamiliesSection(childFamilies) {
        const container = document.querySelector('.card-body table tbody');
        if (!container) return;

        container.innerHTML = childFamilies.map(family => `
            <tr>
                <td>
                    ${family.husband_id ? 
                        `<a href="index.php?action=edit_member&member_id=${family.husband_id}">${family.husband_name}</a>` : 
                        '-'}
                </td>
                <td>
                    ${family.wife_id ? 
                        `<a href="index.php?action=edit_member&member_id=${family.wife_id}">${family.wife_name}</a>` : 
                        '-'}
                </td>
            </tr>
        `).join('');
    }

    initializeFamilyEventListeners() {
        // Reinitialize delete buttons
        document.querySelectorAll('.delete-child-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                // ... existing delete child handler code ...
            });
        });

        // Reinitialize spouse buttons
        document.querySelectorAll('.delete-spouse-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                // ... existing delete spouse handler code ...
            });
        });

        // Reinitialize replace spouse buttons
        document.querySelectorAll('.replace-spouse-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                // ... existing replace spouse handler code ...
            });
        });
    }

    generateSpouseFamilyTab(family, isActive = false) {
        const spouseName = family.spouse_name || this.translations['Unknown Spouse'];
        return `
            <li class="nav-item dropdown" role="presentation">
                <button class="nav-link ${isActive ? 'active' : ''} dropdown-toggle" 
                        id="family-tab-${family.id}" 
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                    ${spouseName}
                </button>
                <ul class="dropdown-menu">
                    ${family.has_spouse ? 
                        `<li><a class="dropdown-item" href="index.php?action=edit_member&member_id=${family.spouse_id}">
                            ${this.translations["View Spouse"]}</a></li>` : 
                        `<li><button class="dropdown-item replace-spouse-btn" data-family-id="${family.id}">
                            ${this.translations["Add Spouse"]}</button></li>`
                    }
                    <li><button class="dropdown-item delete-family-btn" data-family-id="${family.id}">
                        ${this.translations["Delete Family"]}</button></li>
                </ul>
            </li>`;
    }

    generateSpouseFamilyContent(family, isActive = false) {
        return `
            <div class="tab-pane fade ${isActive ? 'show active' : ''}" 
                 id="family-${family.id}" 
                 role="tabpanel" 
                 aria-labelledby="family-tab-${family.id}">
                ${this.generateChildrenSection(family)}
                ${this.generateMarriageDetailsSection(family)}
            </div>`;
    }

    generateChildrenSection(family) {
        const children = family.children || [];
        return `
            <div class="card mt-3">
                <div class="card-header">${this.translations["Children"]}</div>
                <div class="card-body">
                    <table class="table">
                        <tbody>
                            ${children.map(child => `
                                <tr>
                                    <td>
                                        <a href="index.php?action=edit_member&member_id=${child.id}">
                                            ${child.first_name} ${child.last_name}
                                        </a>
                                    </td>
                                    <td>${child.birth_date ? new Date(child.birth_date).toLocaleDateString() : '-'}</td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm delete-child-btn" 
                                                data-child-id="${child.id}"
                                                data-family-id="${family.id}">
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>`;
    }

    generateMarriageDetailsSection(family) {
        return `
            <div class="card mt-3">
                <div class="card-header">${this.translations["Marriage Details"]}</div>
                <div class="card-body">
                    <p><strong>${this.translations["Marriage Date"]}:</strong> 
                        ${family.marriage_date ? new Date(family.marriage_date).toLocaleDateString() : '-'}
                    </p>
                </div>
            </div>`;
    }

    // Add method to handle replace spouse validation
    initializeReplaceSpouseHandlers() {
        const form = document.getElementById('replace-spouse-form');
        const replaceModal = new bootstrap.Modal(document.getElementById('replaceSpouseModal'));
        if (!form) return;

        // Handle replace spouse button clicks
        document.querySelectorAll('.replace-spouse-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const familyId = e.target.dataset.familyId;
                document.getElementById('replace_family_id').value = familyId;
                replaceModal.show();
            });
        });

        // Handle radio button changes
        const spouseTypeRadios = form.querySelectorAll('input[name="spouse_type"]');
        const existingSection = document.getElementById('replace-existing-section');
        const newSection = document.getElementById('replace-new-section');

        spouseTypeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                existingSection.style.display = radio.value === 'existing' ? 'block' : 'none';
                newSection.style.display = radio.value === 'new' ? 'block' : 'none';
            });
        });

        // Handle form submission
        document.getElementById('confirmReplaceSpouse').addEventListener('click', async () => {
            const formData = new FormData(form);
            const spouseType = form.querySelector('input[name="spouse_type"]:checked').value;

            if (spouseType === 'existing') {
                const spouseId = document.getElementById('replace_spouse_id').value;
                if (!spouseId) {
                    alert(this.translations['Please select a valid spouse']);
                    return;
                }
            } else {
                // Validate new spouse fields
                const firstName = form.querySelector('input[name="new_first_name"]').value;
                const lastName = form.querySelector('input[name="new_last_name"]').value;
                if (!firstName || !lastName) {
                    alert(this.translations['Please enter spouse name']);
                    return;
                }
            }

            try {
                const response = await fetch('index.php?action=replace_spouse', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    replaceModal.hide();
                    await this.reloadFamilySection();
                } else {
                    throw new Error(result.message || 'Failed to replace spouse');
                }
            } catch (error) {
                console.error('Error replacing spouse:', error);
                alert(error.message || 'Failed to replace spouse');
            }
        });

        // Initialize spouse autocomplete
        const spouseInput = document.getElementById('replace_spouse');
        if (spouseInput) {
            spouseInput.addEventListener('input', async () => {
                try {
                    const response = await fetch(`index.php?action=autocomplete_member&term=${spouseInput.value}&tree_id=${this.member.tree_id}&member_id=${this.member.id}`);
                    const data = await response.json();
                    
                    const datalist = document.getElementById('replace-spouse-options');
                    datalist.innerHTML = data.map(member => 
                        `<option value="${member.name}" data-id="${member.id}">`
                    ).join('');
                } catch (error) {
                    console.error('Error fetching spouse suggestions:', error);
                }
            });

            spouseInput.addEventListener('change', () => {
                const selectedOption = document.querySelector(`#replace-spouse-options option[value="${spouseInput.value}"]`);
                if (selectedOption) {
                    document.getElementById('replace_spouse_id').value = selectedOption.dataset.id;
                }
            });
        }
    }

    initializeFamilyTabs() {
        // Initialize tab functionality
        document.querySelectorAll('#familyTabs .nav-link[data-bs-toggle="tab"]').forEach(tabEl => {
            tabEl.addEventListener('click', (event) => {
                if (!event.target.closest('.dropdown')) {
                    const tab = new bootstrap.Tab(tabEl);
                    tab.show();
                }
            });
        });

        // Initialize dropdowns separately
        document.querySelectorAll('#familyTabs .dropdown-toggle').forEach(dropdown => {
            new bootstrap.Dropdown(dropdown);
        });
    }

    handleFamilyTabClick = (event) => {
        event.preventDefault();
        event.stopPropagation();
        
        // Only handle tab click if not clicking dropdown toggle
        const target = event.currentTarget;
        if (!target.classList.contains('dropdown-toggle')) {
            // Deactivate all tabs
            const tabs = document.querySelectorAll('#familyTabs .nav-link[data-toggle="tab"]');
            tabs.forEach(t => {
                t.classList.remove('active');
                const paneId = t.getAttribute('data-target');
                if (paneId) {
                    const pane = document.querySelector(paneId);
                    if (pane) {
                        pane.classList.remove('show', 'active');
                    }
                }
            });

            // Activate clicked tab
            target.classList.add('active');
            const paneId = target.getAttribute('data-target');
            if (paneId) {
                const pane = document.querySelector(paneId);
                if (pane) {
                    pane.classList.add('show', 'active');
                }
            }
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

    // Event handler for save relationship button
    document.getElementById('saveRelationship').addEventListener('click', async () => {
        try {
            await manager.saveRelationship();
        } catch (error) {
            console.error('Error:', error);
            // Optionally show error to user
            alert(error.message || 'Failed to save relationship');
        }
    });
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