<style>
input[type="date"] {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  padding: 0;
  padding-left: 0px;
  padding-right:0px;
  margin: 0;
  border: 1px solid #ccc;
  box-sizing: border-box;
  font-size: inherit;
  font-family: inherit;
  height: auto;
  /* Add overflow hidden */
    overflow: hidden;
}
</style>
<!-- Main content -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h4>{{ get_translation("Member Details") }}</h4>
                <a href="index.php?action=visualize_descendants&member_id={{ member.id }}" 
                   class="btn btn-primary float-right">
                    {{ get_translation("Visualize Descendants") }}
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRelationshipModal">
                    {{ get_translation("Add Relationship") }}
                </button>


                <!-- Add Relationship Modal -->                
                <div class="modal fade" id="addRelationshipModal" tabindex="-1" aria-labelledby="addRelationshipModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addRelationshipModalLabel">{{ get_translation("Add Relationship") }}</h5>
                                <button type="button" id="closeRelModalX" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="add-relationship-form">
                                    <input type="hidden" id="member_id" name="member_id" value="{{ member.id|e }}">
                                    <input type="hidden" name="tree_id" value="{{ member.tree_id|e }}">
                                    <input type="hidden" name="member_gender" value="{{ member.gender|e }}">

                                    <!-- Relationship Type Tabs -->
                                    <ul class="nav nav-tabs" id="relationshipTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="spouse-tab" data-bs-toggle="tab" data-bs-target="#spouse-tab-pane" type="button" role="tab">
                                                {{ get_translation("Add Spouse") }}
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="child-tab" data-bs-toggle="tab" data-bs-target="#child-tab-pane" type="button" role="tab">
                                                {{ get_translation("Add Child") }}
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="parent-tab" data-bs-toggle="tab" data-bs-target="#parent-tab-pane" type="button" role="tab">
                                                {{ get_translation("Add Parents") }}
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other-tab-pane" type="button" role="tab">
                                                {{ get_translation("Other Relationship") }}
                                            </button>
                                        </li>
                                    </ul>

                                    <div class="tab-content pt-3" id="relationshipTabContent">
                                        <!-- Spouse Tab -->
                                        <div class="tab-pane fade show active" id="spouse-tab-pane" role="tabpanel" tabindex="0">
                                            <div id="spouse-form-content">
                                                <!-- Content loaded dynamically -->
                                            </div>
                                        </div>

                                        <!-- Child Tab -->
                                        <div class="tab-pane fade" id="child-tab-pane" role="tabpanel" tabindex="0">
                                            <div id="child-form-content">
                                                <!-- Content loaded dynamically -->
                                            </div>
                                        </div>

                                        <!-- Parent Tab -->
                                        <div class="tab-pane fade" id="parent-tab-pane" role="tabpanel" tabindex="0">
                                            <div id="parent-form-content">
                                                <!-- Content loaded dynamically -->
                                            </div>
                                        </div>

                                        <!-- Other Tab -->
                                        <div class="tab-pane fade" id="other-tab-pane" role="tabpanel" tabindex="0">
                                            <div id="other-form-content">
                                                <!-- Content loaded dynamically -->
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="dismissRelModal" data-bs-dismiss="modal">{{ get_translation("Close") }}</button>
                                <button type="button" class="btn btn-primary" id="saveRelationship">{{ get_translation("Save") }}</button>
                            </div>
                        </div>
                    </div>
                </div>

    


            </div>
            <div class="card-body">

                {% if error is defined %}
                    <p style="color: red;">{{ error }}</p>
                {% endif %}

                <form id="edit-member-form" method="post" action="">
                    <input type="hidden" name="member_id" value="{{ member.id|e }}">

                    <div class="d-flex align-items-center gap-2 mb-2">
                        <label for="first_name">{{ get_translation("Name") }}:</label>
                        <input class="form-control" placeholder="{{ get_translation("First Name") }}" type="text" name="first_name" id="first_name" value="{{ member.first_name|e }}" required>
                        <input class="form-control" placeholder="{{ get_translation("Last Name") }}" type="text" name="last_name" id="last_name" value="{{ member.last_name|e }}">
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-2">
                        <label for="birth_date">{{ get_translation("Birth") }}:</label>
                        <input class="form-control" type="date" name="birth_date" id="birth_date" value="{{ member.birth_date|e }}">
                        <input class="form-control" placeholder="{{ get_translation("Place of Birth") }}" type="text" name="birth_place" id="birth_place" value="{{ member.birth_place|e }}">
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-2">
<label for="gender">{{ get_translation("Gender") }}:</label>
                    <select name="gender" id="gender">
                        <option value="M" {% if member.gender == 'M' %}selected{% endif %}>{{ get_translation("Male") }}</option>
                        <option value="F" {% if member.gender == 'F' %}selected{% endif %}>{{ get_translation("Female") }}</option>
                        <!-- Add more options as needed -->
                    </select>
                        <label for="alive">{{ get_translation("Alive") }}:</label>
                        <input type="checkbox" name="alive" id="alive" value="1" {% if member.alive %}checked{% endif %}>
                    </div>

                    <div id="death-fields" style="display: none;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <label for="death_date">{{ get_translation("Death") }}:</label>
                            <input class="form-control" type="date" name="death_date" id="death_date" value="{{ member.death_date|e }}">
                            <input class="form-control" placeholder="{{ get_translation("Place of Death") }}" type="text" name="death_place" id="death_place" value="{{ member.death_place|e }}">
                        </div>
                    </div>

                    <br>
                    <div id="taxonomy-tags">
                        <div class="tag-label">{{ get_translation("Tags") }}: 
                            <button style='float:right;margin-right:30px;' id="copyTagsButton1">{{ get_translation("Copy") }}</button>
                        </div>
                        <div class="tag-input-container" data-tags="{{ tagString }}" data-endpoint="?"></div>
                    </div>
                    <label for="source">{{ get_translation("Source") }}:</label>
                    <input type="text" name="source" id="source" value="{{ member.source|e }}"><br>

                    <div id="additional-fields" style="display: none;">

                <!-- Other relationships section -->
                <h5 class="mt-4">{{ get_translation("Other Relationships") }}</h5>
                <div id="relationships">
                    <table class="relationship-table">
                        <tr>
                            <th>{{ get_translation("Person 1") }}</th>
                            <th>{{ get_translation("Person 2") }}</th>
                            <th>{{ get_translation("Type") }}</th>
                            <th>{{ get_translation("Start") }}</th>
                            <th>{{ get_translation("End") }}</th>
                            <th>{{ get_translation("Actions") }}</th>
                        </tr>
                        <tbody id="relationships-table-body">
                            <!-- Relationships will be dynamically filled via JavaScript -->
                        </tbody>
                    </table>
                </div>
                    </div>
                    <br />

                    <button type="submit">{{ get_translation("Update Member") }}</button>
                    <button type="button" id="toggle-fields-btn">{{ get_translation("More") }}</button>
                </form>
            </div>
        </div>

                <!-- Families where person is a child -->
                <div class="card mt-4">
                    <div class="card-header">
                       <h5> {{ get_translation("Parents") }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                        <!--
                            <thead>
                                <tr>
                                    <th>{{ get_translation("Father") }}</th>
                                    <th>{{ get_translation("Mother") }}</th>
                                </tr>
                            </thead>
-->
                            <tbody>
                                {% for family in child_families %}
                                    <tr>
                                        <td>
                                            {% if family.husband_id %}
                                                <a href="index.php?action=edit_member&member_id={{ family.husband_id }}">
                                                    {{ family.husband_name }}
                                                </a>
                                            {% else %}
                                                -
                                            {% endif %}
                                        </td>
                                        <td>
                                            {% if family.wife_id %}
                                                <a href="index.php?action=edit_member&member_id={{ family.wife_id }}">
                                                    {{ family.wife_name }}
                                                </a>
                                            {% else %}
                                                -
                                            {% endif %}
                                        </td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                    </div>
                </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5>{{ get_translation("Families") }}</h5>
            </div>
            <div class="card-body">
                <!-- Families where person is a spouse -->

                <!-- Tabs for spouses -->
                <ul class="nav nav-tabs" id="familyTabs" role="tablist">
                    {% for family in spouse_families %}
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {% if loop.first %}active{% endif %}" 
                                    id="family-tab-{{ family.id }}" 
                                    data-toggle="tab" 
                                    data-target="#family-{{ family.id }}" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="family-{{ family.id }}" 
                                    aria-selected="{% if loop.first %}true{% else %}false{% endif %}">
                                {% if member.gender == 'M' and family.wife_id %}
                                    {{ family.wife_name }}
                                {% elseif member.gender == 'F' and family.husband_id %}
                                    {{ family.husband_name }}
                                {% else %}
                                    {{ get_translation("Unknown Spouse") }}
                                {% endif %}
                            </button>
                        </li>
                    {% endfor %}
                </ul>

                <!-- Tab content -->
                <div class="tab-content" id="familyTabsContent">
                    {% for family in spouse_families %}
                        <div class="tab-pane fade {% if loop.first %}show active{% endif %}" 
                             id="family-{{ family.id }}" 
                             role="tabpanel" 
                             aria-labelledby="family-tab-{{ family.id }}">
                    
                            <!-- Children from this family -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    {{ get_translation("Children") }}
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <tbody>
                                            {% for child in family.children|default([]) %}
                                                <tr>
                                                    <td>
                                                        <a href="index.php?action=edit_member&member_id={{ child.id }}">
                                                            {{ child.first_name }} {{ child.last_name }}
                                                        </a>
                                                    </td>
                                                    <td>{{ child.birth_date ? child.birth_date|date("M d, Y") : '-' }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm delete-child-btn" 
                                                                data-child-id="{{ child.id }}"
                                                                data-family-id="{{ family.id }}"
                                                                onclick="event.stopPropagation();">
                                                            🗑️
                                                        </button>
                                                    </td>
                                                </tr>
                                            {% endfor %}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Marriage Details -->
                            <div class="card mt-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    {{ get_translation("Marriage Details") }}
                                    {% if (member.gender == 'M' and not family.wife_id) or (member.gender == 'F' and not family.husband_id) %}
                                        <div>
                                            <button type="button" class="btn btn-primary btn-sm replace-spouse-btn" 
                                                    data-family-id="{{ family.id }}">
                                                {{ get_translation("Replace Unknown Spouse") }}
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm delete-spouse-btn" 
                                                    data-spouse-id="{{ member.gender == 'M' ? family.wife_id : family.husband_id }}"
                                                    data-family-id="{{ family.id }}"
                                                    onclick="event.stopPropagation();">
                                                🗑️ {{ get_translation("Delete Spouse") }}
                                            </button>
                                        </div>
                                    {% else %}
                                        <button type="button" class="btn btn-danger btn-sm delete-spouse-btn" 
                                                data-spouse-id="{{ member.gender == 'M' ? family.wife_id : family.husband_id }}"
                                                data-family-id="{{ family.id }}"
                                                onclick="event.stopPropagation();">
                                            🗑️ {{ get_translation("Delete Spouse") }}
                                        </button>
                                    {% endif %}
                                </div>
                                <div class="card-body">
                                    <p><strong>{{ get_translation("Marriage Date") }}:</strong> 
                                        {{ family.marriage_date ? family.marriage_date|date("M d, Y") : '-' }}
                                    </p>
                                    <p><strong>{{ get_translation("Divorce Date") }}:</strong> 
                                        {{ family.divorce_date ? family.divorce_date|date("M d, Y") : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    {% endfor %}
                </div>


            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
            <h5>
                {{ get_translation("Changes") }}
                </h5>
            </div>
            <div class="card-body">
                <!-- Add Relationship Button -->




                <hr>
                <h2>{{ get_translation("Delete Member") }}</h2>
                {{ get_translation("Warning, This Can Not Be Undone") }}
                <form method="post" class='delete-member-form' action="index.php?action=delete_member">
                    <input type="hidden" name="member_id" value="{{ member.id }}">
                    <button type="submit">🗑️ {{ get_translation("Delete") }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Spouse Modal -->
<div class="modal fade" id="deleteSpouseModal" tabindex="-1" role="dialog" aria-labelledby="deleteSpouseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSpouseModalLabel">{{ get_translation("Delete Spouse") }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ get_translation("Choose delete option:") }}</p>
                <select id="spouseDeleteOption" class="form-control">
                    <option value="1">{{ get_translation("Remove relationship only") }}</option>
                    <option value="2">{{ get_translation("Delete spouse (keeps children)") }}</option>
                    <option value="3">{{ get_translation("Delete spouse and all children") }}</option>
                </select>
                <input type="hidden" id="deleteSpouseId">
                <input type="hidden" id="deleteFamilyId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ get_translation("Cancel") }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSpouse">{{ get_translation("Delete") }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Child Modal -->
<div class="modal fade" id="deleteChildModal" tabindex="-1" role="dialog" aria-labelledby="deleteChildModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteChildModalLabel">{{ get_translation("Delete Child") }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ get_translation("Choose action:") }}</p>
                <select id="childDeleteOption" class="form-control">
                    <option value="remove">{{ get_translation("Remove from family only") }}</option>
                    <option value="delete">{{ get_translation("Delete child completely") }}</option>
                </select>
                <input type="hidden" id="deleteChildId">
                <input type="hidden" id="deleteChildFamilyId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ get_translation("Cancel") }}</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteChild">{{ get_translation("Delete") }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Add this with your other modals -->
<div class="modal fade" id="replaceSpouseModal" tabindex="-1" role="dialog" aria-labelledby="replaceSpouseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="replaceSpouseModalLabel">{{ get_translation("Replace Unknown Spouse") }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="replace-spouse-form">
                    <input type="hidden" id="replace_family_id" name="family_id">
                    <input type="hidden" name="member_gender" value="{{ member.gender }}">

                    <!-- Person selection type -->
                    <div class="form-group">
                        <label><input type="radio" name="spouse_type" value="existing" checked> {{ get_translation("Existing Person") }}</label><br>
                        <label><input type="radio" name="spouse_type" value="new"> {{ get_translation("New Person") }}</label>
                    </div>

                    <!-- Existing person section -->
                    <div id="replace-existing-section">
                        <label for="replace_spouse">{{ get_translation("Select Person") }}:</label>
                        <input type="text" id="replace_spouse" name="replace_spouse" list="replace-spouse-options" autocomplete="off" class="form-control">
                        <datalist id="replace-spouse-options"></datalist>
                        <input type="hidden" name="spouse_id" id="replace_spouse_id">
                    </div>

                    <!-- New person section -->
                    <div id="replace-new-section" style="display:none;">
                        <label>{{ get_translation("First Name") }}:</label>
                        <input type="text" name="new_first_name" class="form-control"><br>
                        <label>{{ get_translation("Last Name") }}:</label>
                        <input type="text" name="new_last_name" class="form-control"><br>
                        <label>{{ get_translation("Birth Date") }}:</label>
                        <input type="date" name="new_birth_date" class="form-control">
                    </div>

                    <div class="form-group mt-3">
                        <label>{{ get_translation("Marriage Date") }}:</label>
                        <input type="date" name="marriage_date" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ get_translation("Cancel") }}</button>
                <button type="button" class="btn btn-primary" id="confirmReplaceSpouse">{{ get_translation("Replace Spouse") }}</button>
            </div>
        </div>
    </div>

<script>
    var memberId = {{memberId}}; // Pass member ID to JavaScript
    const treeId = {{treeId}}; // Pass member ID to JavaScript
    
    // Add translations object
    const translations = {{translations|raw}};

    // Add translation function
    function get_translation(key) {
        return translations[key] || key;
    }
    function showHideDeath() {
        var deathFields = document.getElementById('death-fields');
        const alive = document.getElementById('alive');
        if (alive.checked!=true) {
            deathFields.style.display = 'block';
        } else {
            deathFields.style.display = 'none';
        }        
    }
    $(document).ready(function() {
        // Handle delete tree form submission with confirmation
        $('.delete-member-form').submit(function(event) {
            if (!confirm('{{ get_translation("Are you sure you want to delete this tree?")}}')) {
                event.preventDefault();
            }
        });
        showHideDeath();
    });
    document.getElementById('alive').addEventListener('click', function() {
        showHideDeath()
    });


    document.getElementById('toggle-fields-btn').addEventListener('click', function() {
        var additionalFields = document.getElementById('additional-fields');
        if (additionalFields.style.display === 'none') {
            additionalFields.style.display = 'block';
            this.textContent = '{{ get_translation("Less Fields") }} ';
        } else {
            additionalFields.style.display = 'none';
            this.textContent = '{{ get_translation("More Fields") }}';
        }
    });
</script>



<script>
    class TagInput {
        constructor(container) {
            this.container = container;
            this.tagInput = document.createElement('div');
            this.hiddenInput = document.createElement('input');
            this.tags = [];
            this.endpoint = this.container.getAttribute('data-endpoint');
            // this.memberId = this.container.getAttribute('data-member-id');
            // this.treeId = this.container.getAttribute('data-tree-id');
            this.memberId = memberId
            this.treeId = treeId
            this.tagInput.classList.add('tag-input');
            this.tagInput.contentEditable = true;
            this.hiddenInput.type = 'hidden';
            this.hiddenInput.name = 'tags';
            this.container.appendChild(this.tagInput);
            this.container.appendChild(this.hiddenInput);

            this.init();
        }

        async init() {
            const initialTags = this.container.getAttribute('data-tags');
            if (initialTags) {
                this.tags = initialTags.split(',').map(tag => tag.trim());
                this.tags.forEach(tag => this.renderTag(tag));
            }

            this.tagInput.addEventListener('keydown', (e) => this.handleKeyDown(e));
            this.tagInput.addEventListener('input', (e) => this.handleInput(e));
            this.tagInput.addEventListener('paste', (e) => this.handlePaste(e));
            this.container.addEventListener('click', () => this.tagInput.focus());
        }
        async handleInput(e) {
            const tagText = this.tagInput.innerText.trim();
            if (tagText.includes(',')) {
                const tags = tagText.split(',').map(tag => tag.trim()).filter(tag => tag && !this.tags.includes(tag));
                if (tags.length > 0) {
                    for (const tag of tags) {
                        await this.addTagToServer(tag);
                    }
                    this.tagInput.innerText = '';
                }
            }
        }
        async handleKeyDown(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const tagText = this.tagInput.innerText.trim().replace(/,$/, '');
                if (tagText && !this.tags.includes(tagText)) {
                    await this.addTagToServer(tagText);
                }
                this.tagInput.innerText = '';
            } else if (e.key === 'Backspace' && this.tagInput.innerText === '') {
                const removedTag = this.tags.pop();
                if (removedTag) {
                    await this.removeTagFromServer(removedTag);
                }
            }
        }

        async handlePaste(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const pastedTags = paste.split(',').map(tag => tag.trim()).filter(tag => tag && !this.tags.includes(tag));
            for (const tag of pastedTags) {
                await this.addTagToServer(tag);
            }
        }

        async addTagToServer(tag) {
            const formData = new FormData();
            formData.append('action', 'add_tag');
            formData.append('tag', tag);
            formData.append('member_id', this.memberId);
            formData.append('tree_id', this.treeId);

            const response = await fetch(this.endpoint, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                if (data.status === 'success') {
                    this.addTag(tag);
                } else {
                    console.error('Failed to add tag to server', data.message);
                }
            } else {
                console.error('Failed to add tag to server');
            }
        }

        async removeTagFromServer(tag) {
            const formData = new FormData();
            formData.append('action', 'delete_tag');
            formData.append('tag', tag);
            formData.append('member_id', this.memberId);
            formData.append('tree_id', this.treeId);

            const response = await fetch(this.endpoint, {
                method: 'POST',
                body: formData
            });
            if (response.ok) {
                const data = await response.json();
                if (data.status === 'success') {
                    this.removeTag(tag);
                } else {
                    console.error('Failed to remove tag from server', data.message);
                }
            } else {
                console.error('Failed to remove tag from server');
            }
        }

        async reloadTagsFromServer() {
            const formData = new FormData();
            formData.append('action', 'reload_tag');
            formData.append('member_id', this.memberId);
            formData.append('tree_id', this.treeId);

            const response = await fetch(this.endpoint, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                if (data.status === 'success') {
                    this.tags = data.tags;
                    this.updateTagsDisplay();
                } else {
                    console.error('Failed to reload tags from server', data.message);
                }
            } else {
                console.error('Failed to reload tags from server');
            }
        }

        renderTag(text) {
            const tagHtml = `
            <div class="tag">
                <span class="tag-text">${text}</span>
                <span class="close-btn">x</span>
            </div>`;
            const template = document.createElement('template');
            template.innerHTML = tagHtml.trim();
            const tagElement = template.content.firstChild;
            tagElement.querySelector('.close-btn').addEventListener('click', async () => {
                await this.removeTagFromServer(text);
            });
            this.container.insertBefore(tagElement, this.tagInput);
            this.tags.push(text);
            this.updateHiddenInput();
        }

        addTag(text) {
            this.renderTag(text);
        }

        removeTag(text) {
            const tagElement = Array.from(this.container.querySelectorAll('.tag')).find(el => el.querySelector('.tag-text').innerText === text);
            if (tagElement) {
                this.container.removeChild(tagElement);
            }

            this.tags = this.tags.filter(t => t !== text);
            this.updateHiddenInput();
        }

        updateTagsDisplay() {
            this.container.querySelectorAll('.tag').forEach(tagElement => this.container.removeChild(tagElement));
            this.tags.forEach(tag => this.renderTag(tag));
        }

        updateHiddenInput() {
            this.hiddenInput.value = this.tags.join(',');
        }

        copyTagsToClipboard() {
            const tagsString = this.tags.join(',');
            navigator.clipboard.writeText(tagsString).then(() => {
                alert('Tags copied to clipboard');
            }).catch(err => {
                console.error('Could not copy text: ', err);
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const tagInputContainers = document.querySelectorAll('.tag-input-container');
        const tagInputs = Array.from(tagInputContainers).map(container => new TagInput(container));

        document.getElementById('copyTagsButton1').addEventListener('click', () => {
            tagInputs[0].copyTagsToClipboard();
        });

        /*
        // Load relationship types for the select dropdown (existing member)
        $.ajax({
            url: "index.php?action=get_relationship_types",
            dataType: "json",
            success: function (data) {
                var optionsHtml = '';
                $.each(data, function (index, relationshipType) {
                    optionsHtml += '<option value="' + relationshipType.id + '">' + relationshipType.description + '</option>';
                });
                $('#relationship_type_select').html(optionsHtml);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching relationship types:', status, error);
            }
        });

        // Load relationship types for the select dropdown (new member)
        $.ajax({
            url: "index.php?action=get_relationship_types",
            dataType: "json",
            success: function (data) {
                var optionsHtml = '';
                $.each(data, function (index, relationshipType) {
                    optionsHtml += '<option value="' + relationshipType.id + '">' + relationshipType.description + '</option>';
                });
                $('#relationship_type_new').html(optionsHtml);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching relationship types:', status, error);
            }
        });
        */

    });
</script>

<!-- Initialize data -->
<script>
    // Make data available globally
    window.relationshipData = {
        member: {
            id: {{ member.id|json_encode|raw }},
            gender: {{ member.gender|json_encode|raw }},
            tree_id: {{member.tree_id}}
        },
        spouseFamilies: {{ spouse_families|json_encode|raw }}
    };
</script>

<!-- Load the relationship manager -->
<script type="module" src="res/js/relationships/index.js"></script>

