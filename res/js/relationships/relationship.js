import { RelationshipType, PersonType } from './types.js';

export class RelationshipManager {
    constructor(member, spouseFamilies) {
        this.member = member;
        this.spouseFamilies = spouseFamilies;
        this.modal = null;
        this.activeTab = RelationshipType.SPOUSE;
    }

    initialize() {
        this.initializeModal();
        this.initializeTabs();
        this.initializeSaveButton();
        this.loadFormContent(this.activeTab);
    }

    initializeModal() {
        const modalElement = document.getElementById('addRelationshipModal');
        if (modalElement) {
            this.modal = new bootstrap.Modal(modalElement);
        }
    }

    initializeTabs() {
        const tabs = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', (e) => {
                this.activeTab = e.target.id.replace('-tab', '');
                this.loadFormContent(this.activeTab);
            });
        });
    }

    loadFormContent(type) {
        const formContent = document.querySelector(`#${type}-form-content`);
        if (!formContent) return;

        switch(type) {
            case RelationshipType.SPOUSE:
                formContent.innerHTML = this.getSpouseForm();
                this.initializeSpouseHandlers();
                break;
            case RelationshipType.CHILD:
                formContent.innerHTML = this.getChildForm();
                this.initializeChildHandlers();
                break;
            case RelationshipType.PARENT:
                formContent.innerHTML = this.getParentForm();
                this.initializeParentHandlers();
                break;
            case RelationshipType.OTHER:
                formContent.innerHTML = this.getOtherForm();
                this.initializeOtherHandlers();
                break;
        }
    }

    // Form generation methods
    getSpouseForm() {
        return `
            <div class="mb-3">
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="spouse_type" id="existing_spouse" value="${PersonType.EXISTING}" checked>
                    <label class="btn btn-outline-primary" for="existing_spouse">Existing Person</label>
                    <input type="radio" class="btn-check" name="spouse_type" id="new_spouse" value="${PersonType.NEW}">
                    <label class="btn btn-outline-primary" for="new_spouse">New Person</label>
                </div>
            </div>
            <div id="spouse-existing-section">
                <div class="form-group mb-3">
                    <label>Select Existing Spouse:</label>
                    <input type="text" class="form-control" id="spouse_autocomplete" list="spouse-options">
                    <datalist id="spouse-options"></datalist>
                    <input type="hidden" name="spouse_id" id="selected_spouse_id">
                </div>
            </div>
            <div id="spouse-new-section" style="display:none">
                <div class="form-group mb-3">
                    <label>New Spouse Details:</label>
                    <input type="text" class="form-control mb-2" name="spouse_first_name" placeholder="First Name" required>
                    <input type="text" class="form-control mb-2" name="spouse_last_name" placeholder="Last Name" required>
                    <input type="date" class="form-control mb-2" name="spouse_birth_date">
                    <input type="hidden" name="spouse_gender" value="${this.member.gender === 'M' ? 'F' : 'M'}">
                </div>
            </div>
            <div class="form-group">
                <label>Marriage Date:</label>
                <input type="date" class="form-control" name="marriage_date">
            </div>`;
    }

    // Similar methods for getChildForm(), getParentForm(), getOtherForm()
    // ...

    // Event handlers
    initializeSpouseHandlers() {
        const typeRadios = document.querySelectorAll('input[name="spouse_type"]');
        typeRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                const isExisting = e.target.value === PersonType.EXISTING;
                document.getElementById('spouse-existing-section').style.display = isExisting ? 'block' : 'none';
                document.getElementById('spouse-new-section').style.display = isExisting ? 'none' : 'block';
                
                if (isExisting) {
                    this.initializeAutocomplete('#spouse_autocomplete', 'spouse');
                }
            });
        });
    }

    // Similar methods for other handlers
    // ...

    initializeAutocomplete(selector, type) {
        const input = document.querySelector(selector);
        if (!input) return;

        input.addEventListener('input', async () => {
            if (!input.value) return;

            const response = await fetch(`index.php?action=autocomplete_member&term=${input.value}&member_id=${this.member.id}&tree_id=${this.member.tree_id}`);
            const data = await response.json();
            
            const datalist = document.getElementById(`${type}-options`);
            datalist.innerHTML = '';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.label;
                option.dataset.personId = item.id;
                datalist.appendChild(option);
            });
        });

        input.addEventListener('change', () => {
            const selectedOption = document.querySelector(`#${type}-options option[value="${input.value}"]`);
            if (selectedOption) {
                document.getElementById(`selected_${type}_id`).value = selectedOption.dataset.personId;
            }
        });
    }

    initializeSaveButton() {
        const saveButton = document.getElementById('saveRelationship');
        if (saveButton) {
            saveButton.addEventListener('click', () => this.saveRelationship());
        }
    }

    async saveRelationship() {
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