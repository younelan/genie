import { PersonType } from './types.js';

export class FormGenerator {
    constructor(member, spouseFamilies) {
        this.member = member;
        this.spouseFamilies = spouseFamilies;
    }

    getPersonTypeSelector(type) {
        return `
            <div class="mb-3">
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="${type}_type" id="existing_${type}" value="${PersonType.EXISTING}" checked>
                    <label class="btn btn-outline-primary" for="existing_${type}">Existing Person</label>
                    <input type="radio" class="btn-check" name="${type}_type" id="new_${type}" value="${PersonType.NEW}">
                    <label class="btn btn-outline-primary" for="new_${type}">New Person</label>
                </div>
            </div>`;
    }

    getExistingPersonSection(type) {
        return `
            <div id="${type}-existing-section">
                <div class="form-group mb-3">
                    <label>Select Existing ${type.charAt(0).toUpperCase() + type.slice(1)}:</label>
                    <input type="text" class="form-control" id="${type}_autocomplete" list="${type}-options">
                    <datalist id="${type}-options"></datalist>
                    <input type="hidden" name="${type}_id" id="selected_${type}_id">
                </div>
            </div>`;
    }

    getGenderSelector(type, defaultGender = 'M') {
        return `
            <div class="form-group mb-3">
                <label>Gender:</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="${type}_gender" id="${type}_gender_M" value="M" ${defaultGender === 'M' ? 'checked' : ''}>
                    <label class="btn btn-outline-primary" for="${type}_gender_M">Male</label>
                    <input type="radio" class="btn-check" name="${type}_gender" id="${type}_gender_F" value="F" ${defaultGender === 'F' ? 'checked' : ''}>
                    <label class="btn btn-outline-primary" for="${type}_gender_F">Female</label>
                    <input type="radio" class="btn-check" name="${type}_gender" id="${type}_gender_U" value="U" ${defaultGender === 'U' ? 'checked' : ''}>
                    <label class="btn btn-outline-primary" for="${type}_gender_U">Unspecified</label>
                </div>
            </div>`;
    }

    getNewPersonSection(type, includeGender = false, defaultGender = 'M') {
        return `
            <div id="${type}-new-section" style="display:none">
                <div class="form-group mb-3">
                    <label>New ${type.charAt(0).toUpperCase() + type.slice(1)} Details:</label>
                    <input type="text" class="form-control mb-2" name="${type}_first_name" placeholder="First Name" required>
                    <input type="text" class="form-control mb-2" name="${type}_last_name" placeholder="Last Name" required>
                    <input type="date" class="form-control mb-2" name="${type}_birth_date">
                    ${includeGender ? this.getGenderSelector(type, defaultGender) : ''}
                </div>
            </div>`;
    }

    getSpouseForm() {
        return `
            ${this.getPersonTypeSelector('spouse')}
            ${this.getExistingPersonSection('spouse')}
            ${this.getNewPersonSection('spouse', true, this.member.gender === 'M' ? 'F' : 'M')}
            <div class="form-group">
                <label>Marriage Date:</label>
                <input type="date" class="form-control" name="marriage_date">
            </div>`;
    }

    getChildForm() {
        const familyOptions = this.spouseFamilies.map(family => {
            const spouseName = this.member.gender === 'M' ? family.wife_name : family.husband_name;
            return `<option value="${family.family_id}">With ${spouseName || 'Unknown Spouse'}</option>`;
        }).join('');

        return `
            <div class="form-group mb-3">
                <label>Family:</label>
                <select class="form-control" name="family_id" id="family_select">
                    ${familyOptions}
                    <option value="new">New Family (No Spouse)</option>
                </select>
            </div>
            ${this.getPersonTypeSelector('child')}
            ${this.getExistingPersonSection('child')}
            ${this.getNewPersonSection('child', true, 'M')}`;
    }

    getParentForm() {
        return `
            <div class="mb-3">
                <h5>First Parent</h5>
                <div class="mb-3">
                    ${this.getPersonTypeSelector('parent1')}
                    ${this.getExistingPersonSection('parent1')}
                    ${this.getNewPersonSection('parent1', true, 'M')}
                </div>
            </div>
            <div class="mb-3">
                <h5>Second Parent</h5>
                <div class="form-group mb-3">
                    <select class="form-control" name="second_parent_option" id="second_parent_select">
                        <option value="none">Single Parent</option>
                        <option value="existing_family">Existing Family</option>
                        <option value="new">New Parent</option>
                    </select>
                </div>
                <div id="existing-family-section" style="display:none">
                    <select class="form-control mb-3" id="existing_family_select" name="existing_family_id"></select>
                </div>
                ${this.getNewPersonSection('parent2', true, 'M')}
            </div>`;
    }

    getOtherForm() {
        return `
            <div class="form-group mb-3">
                <label>Relationship Type:</label>
                <select class="form-control" name="relationship_type">
                    <option value="COUSIN">Cousin</option>
                    <option value="SIBLING">Sibling</option>
                    <option value="AUNT">Aunt</option>
                    <option value="NEPHEW">Nephew</option>
                    <option value="GRANDPARENT">Grandparent</option>
                    <option value="GRANDCHILD">Grandchild</option>
                    <option value="UNCLE">Uncle</option>
                    <option value="NIECE">Niece</option>
                </select>
            </div>
            ${this.getPersonTypeSelector('other')}
            ${this.getExistingPersonSection('other')}
            ${this.getNewPersonSection('other', true, 'M')}`;
    }
} 