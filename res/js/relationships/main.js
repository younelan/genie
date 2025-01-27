import { RelationshipType } from './types.js';
import { FormGenerator } from './forms.js';
import { RelationshipHandlers } from './handlers.js';

export class RelationshipManager {
    constructor(member, spouseFamilies) {
        this.member = member;
        this.spouseFamilies = spouseFamilies;
        this.formGenerator = new FormGenerator(member, spouseFamilies);
        this.handlers = new RelationshipHandlers(member);
    }

    initialize() {
        this.initializeModal();
        this.initializeTabs();
        this.initializeSaveButton();
        this.loadInitialForm();
    }

    initializeModal() {
        if (document.getElementById('addRelationshipModal')) {
            this.modal = new bootstrap.Modal(document.getElementById('addRelationshipModal'));
        }
    }

    initializeTabs() {
        $('.nav-link[data-bs-toggle="tab"]').on('shown.bs.tab', (e) => {
            const targetId = $(e.target).attr('id');
            const formType = targetId.replace('-tab', '');
            this.loadFormContent(formType);
        });
    }

    loadFormContent(formType) {
        const formContent = $(`#${formType}-form-content`);
        if (!formContent.length) return;

        switch(formType) {
            case RelationshipType.SPOUSE:
                formContent.html(this.formGenerator.getSpouseForm());
                this.handlers.initializeSpouseHandlers();
                break;
            case RelationshipType.CHILD:
                formContent.html(this.formGenerator.getChildForm());
                this.handlers.initializeChildHandlers();
                break;
            case RelationshipType.PARENT:
                formContent.html(this.formGenerator.getParentForm());
                this.handlers.initializeParentHandlers();
                break;
            case RelationshipType.OTHER:
                formContent.html(this.formGenerator.getOtherRelationshipForm());
                this.handlers.initializeOtherHandlers();
                break;
        }
    }

    initializeSaveButton() {
        $('#saveRelationship').click(() => this.saveRelationship());
    }

    saveRelationship() {
        // ... (save relationship implementation)
    }

    loadInitialForm() {
        // Load the initial spouse form since it's the default active tab
        this.loadFormContent(RelationshipType.SPOUSE);
    }
} 