import { PersonType } from './types.js';

export class RelationshipHandlers {
    constructor(member) {
        this.member = member;
    }

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
        // Trigger change event on the checked radio to initialize autocomplete
        const checkedRadio = document.querySelector('input[name="spouse_type"]:checked');
        if (checkedRadio) {
            checkedRadio.dispatchEvent(new Event('change'));
        }
    }

    initializeChildHandlers() {
        const typeRadios = document.querySelectorAll('input[name="child_type"]');
        typeRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                const isExisting = e.target.value === PersonType.EXISTING;
                document.getElementById('child-existing-section').style.display = isExisting ? 'block' : 'none';
                document.getElementById('child-new-section').style.display = isExisting ? 'none' : 'block';
                
                if (isExisting) {
                    this.initializeAutocomplete('#child_autocomplete', 'child');
                }
            });
        });
        // Trigger initial setup
        const checkedRadio = document.querySelector('input[name="child_type"]:checked');
        if (checkedRadio) {
            checkedRadio.dispatchEvent(new Event('change'));
        }
    }

    initializeParentHandlers() {
        // First parent handlers
        const parent1TypeRadios = document.querySelectorAll('input[name="parent1_type"]');
        parent1TypeRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                const isExisting = e.target.value === PersonType.EXISTING;
                document.getElementById('parent1-existing-section').style.display = isExisting ? 'block' : 'none';
                document.getElementById('parent1-new-section').style.display = isExisting ? 'none' : 'block';
                
                if (isExisting) {
                    this.initializeAutocomplete('#parent1_autocomplete', 'parent1');
                }

                // Handle existing family option
                const secondParentSelect = document.getElementById('second_parent_select');
                if (secondParentSelect) {
                    const existingFamilyOption = Array.from(secondParentSelect.options)
                        .find(opt => opt.value === 'existing_family');
                    if (existingFamilyOption) {
                        existingFamilyOption.disabled = !isExisting;
                        if (!isExisting && secondParentSelect.value === 'existing_family') {
                            secondParentSelect.value = 'none';
                            secondParentSelect.dispatchEvent(new Event('change'));
                        }
                    }
                }
            });
        });

        // Trigger initial setup for first parent
        const checkedParent1Radio = document.querySelector('input[name="parent1_type"]:checked');
        if (checkedParent1Radio) {
            checkedParent1Radio.dispatchEvent(new Event('change'));
        }

        // Second parent handlers
        const secondParentSelect = document.getElementById('second_parent_select');
        if (secondParentSelect) {
            secondParentSelect.addEventListener('change', (e) => {
                const option = e.target.value;
                document.getElementById('existing-family-section').style.display = option === 'existing_family' ? 'block' : 'none';
                document.getElementById('parent2-new-section').style.display = option === 'new' ? 'block' : 'none';
                
                if (option === 'existing_family') {
                    this.loadExistingFamilies();
                }
            });
            secondParentSelect.dispatchEvent(new Event('change'));
        }
    }

    initializeOtherHandlers() {
        console.log(this.member);
        const typeRadios = document.querySelectorAll('input[name="other_type"]');
        typeRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                const isExisting = e.target.value === PersonType.EXISTING;
                document.getElementById('other-existing-section').style.display = isExisting ? 'block' : 'none';
                document.getElementById('other-new-section').style.display = isExisting ? 'none' : 'block';
                
                if (isExisting) {
                    this.initializeAutocomplete('#other_autocomplete', 'other');
                }
            });
        });
        // Trigger initial setup
        const checkedRadio = document.querySelector('input[name="other_type"]:checked');
        if (checkedRadio) {
            checkedRadio.dispatchEvent(new Event('change'));
        }
    }

    async initializeAutocomplete(selector, type) {
        const input = document.querySelector(selector);
        if (!input) return;
        console.log(this.member)
        // Remove any existing event listeners
        const newInput = input.cloneNode(true);
        input.parentNode.replaceChild(newInput, input);
        // Initialize autocomplete on input
        newInput.addEventListener('input', async (e) => {
            const value = e.target.value;
            if (!value) return;

            try {
                const params = new URLSearchParams({
                    action: 'autocomplete_member',
                    term: value,
                    member_id: this.member.id,
                    tree_id: this.member.tree_id
                });

                const response = await fetch(`index.php?${params.toString()}`);
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                //console.log('Autocomplete data:', data); // Debug

                const datalist = document.getElementById(`${type}-options`);
                if (!datalist) {
                    console.error(`Datalist not found for ${type}`);
                    return;
                }

                datalist.innerHTML = '';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.label;
                    option.dataset.personId = item.id;
                    datalist.appendChild(option);
                });
            } catch (error) {
                console.error('Autocomplete error:', error);
            }
        });

        // Handle selection
        newInput.addEventListener('change', (e) => {
            const datalist = document.getElementById(`${type}-options`);
            if (!datalist) return;

            const selectedValue = e.target.value;
            const options = datalist.getElementsByTagName('option');
            const selectedOption = Array.from(options).find(opt => opt.value === selectedValue);
            
            if (selectedOption) {
                const hiddenInput = document.getElementById(`selected_${type}_id`);
                if (hiddenInput) {
                    hiddenInput.value = selectedOption.dataset.personId;
                    console.log(`Selected ${type} ID:`, selectedOption.dataset.personId); // Debug
                }
            }
        });

        // Trigger initial autocomplete if there's a value
        if (newInput.value) {
            newInput.dispatchEvent(new Event('input'));
        }
    }

    async loadExistingFamilies() {
        const parent1Id = document.getElementById('selected_parent1_id').value;
        if (!parent1Id) {
            console.log('No parent ID selected');
            return;
        }

        try {
            // Debug the URL being called
            const params = new URLSearchParams({
                action: 'get_spouse_families',
                member_id: parent1Id,
                ajax: true  // Add this to ensure JSON response
            });
            const url = `index.php?${params.toString()}`;
            console.log('Fetching families from:', url);

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error(`Expected JSON response but got ${contentType}`);
            }

            const data = await response.json();
            console.log('Families data:', data);
            
            const select = document.getElementById('existing_family_select');
            select.innerHTML = '';
            
            if (data.spouse_families && data.spouse_families.length > 0) {
                data.spouse_families.forEach(family => {
                    const option = document.createElement('option');
                    option.value = family.family_id;
                    option.textContent = `With ${family.spouse_name || 'Unknown Spouse'}`;
                    select.appendChild(option);
                });
            } else {
                // If no families found, disable the existing family option
                const secondParentSelect = document.getElementById('second_parent_select');
                if (secondParentSelect) {
                    secondParentSelect.value = 'none';
                    secondParentSelect.dispatchEvent(new Event('change'));
                }
            }
        } catch (error) {
            console.error('Error loading families:', error);
            console.error('Response:', error.response); // Add this for debugging
            // Show user-friendly error message
            const select = document.getElementById('existing_family_select');
            select.innerHTML = '<option value="">Error loading families</option>';
        }
    }
} 