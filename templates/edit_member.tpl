
<!-- Main content -->
<div class="row mt-5">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                {{ get_translation("Member Details") }}
            </div>
            <div class="card-body">

                {% if error is defined %}
                    <p style="color: red;">{{ error }}</p>
                {% endif %}

                <form id="edit-member-form" method="post" action="">
                    <input type="hidden" name="member_id" value="{{ member.id|e }}">

                    <label for="first_name">{{ get_translation("First Name") }}:</label>
                    <input type="text" name="first_name" id="first_name" value="{{ member.first_name|e }}" required><br>

                    <label for="middle_name">{{ get_translation("Middle Name") }}:</label>
                    <input type="text" name="middle_name" id="middle_name" value="{{ member.middle_name|e }}"><br>

                    <label for="last_name">{{ get_translation("Last Name") }}:</label>
                    <input type="text" name="last_name" id="last_name" value="{{ member.last_name|e }}"><br>

                    <label for="date_of_birth">{{ get_translation("Date of Birth") }}:</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ member.date_of_birth|e }}"><br>

                    <label for="place_of_birth">{{ get_translation("Place of Birth") }}:</label>
                    <input type="text" name="place_of_birth" id="place_of_birth" value="{{ member.place_of_birth|e }}"><br>

                    <label for="gender_id">{{ get_translation("Gender") }}:</label>
                    <select name="gender_id" id="gender_id">
                        <option value="1" {% if member.gender_id == 1 %}selected{% endif %}>{{ get_translation("Man") }}</option>
                        <option value="2" {% if member.gender_id == 2 %}selected{% endif %}>{{ get_translation("Woman") }}</option>
                        <!-- Add more options as needed -->
                    </select><br>
                    <div id="taxonomy-tags">
                        <div class="tag-label">{{ get_translation("Tags") }}: 
                            <button style='float:right;margin-right:30px;' id="copyTagsButton1">{{ get_translation("Copy") }}</button>
                        </div>
                        <div class="tag-input-container" data-tags="{{ tagString }}" data-endpoint="?"></div>
                    </div>
                    <label for="source">{{ get_translation("Source") }}:</label>
                    <input type="text" name="source" id="source" value="{{ member.source|e }}"><br>
                    <label for="alive">{{ get_translation("Alive") }}:</label>
                    <input type="checkbox" name="alive" id="alive" value="1" {% if member.alive %}checked{% endif %}><br>

                    <div id="additional-fields" style="display: none;">
                        <label for="title">{{ get_translation("Title") }}:</label>
                        <input type="text" name="title" id="title" value="{{ member.title|e }}"><br>
                        <label for="alias1">{{ get_translation("Alias 1") }}:</label>
                        <input type="text" name="alias1" id="alias1" value="{{ member.alias1|e }}"><br>
                        <label for="alias2">{{ get_translation("Alias 2") }}:</label>
                        <input type="text" name="alias2" id="alias2" value="{{ member.alias2|e }}"><br>
                        <label for="alias3">{{ get_translation("Alias 3") }}:</label>
                        <input type="text" name="alias3" id="alias3" value="{{ member.alias3|e }}"><br>
                        <label for="body">{{ get_translation("Details") }}</label>
                        <textarea id="body" name="body" cols="50" rows="10">{{ member.body|e }}</textarea><br>
                        <label for="date_of_death">{{ get_translation("Date of Death") }}:</label>
                        <input type="date" name="date_of_death" id="date_of_death" value="{{ member.date_of_death|e }}"><br>
                        <label for="place_of_death">{{ get_translation("Place of Death") }}:</label>
                        <input type="text" name="place_of_death" id="place_of_death" value="{{ member.place_of_death|e }}"><br>
                    </div>
                    <br />

                    <button type="submit">{{ get_translation("Update Member") }}</button>
                    <button type="button" id="toggle-fields-btn">{{ get_translation("More") }}</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                {{ get_translation("Existing Relations") }}
            </div>
            <div class="card-body">
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
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                {{ get_translation("Changes") }}
            </div>
            <div class="card-body">
                <h2>{{ get_translation("Add Relationship") }}</h2>
                <form id="add-relationship-form">
                    <input type="hidden" id="member_id" name="member_id" value="{{ member.id|e }}">
                    <input type="hidden" name="family_tree_id" value="{{ member.family_tree_id|e }}">

                    <!-- Radio buttons to choose between existing or new member -->
                    <label><input type="radio" name="member_type" value="existing" checked> {{ get_translation("Add Relationship With Existing Member") }}</label><br>
                    <label><input type="radio" name="member_type" value="new"> {{ get_translation("Add Relationship With New Member") }}</label><br><br>

                    <!-- Section for existing member selection -->
                    <div id="existing-member-section">
                        <label for="autocomplete_member">{{ get_translation("Select Existing Member") }}:</label>
                        <input type="text" id="autocomplete_member" name="autocomplete_member" list="autocomplete-options" autocomplete="off" required><br>
                        <datalist id="autocomplete-options"></datalist><br>

                        <!-- Hidden fields for person IDs and relationship type -->
                        <input type="hidden" name="person_id1" id="person_id1" value="{{ member.id|e }}">
                        <input type="hidden" name="person_id2" id="person_id2" value="">
                        <input type="hidden" name="relationship_type" id="relationship_type" value="">

                        <label for="relationship_type_select">{{ get_translation("Relationship Type") }}:</label>
                        <select name="relationship_type_select" id="relationship_type_select">
                            <!-- Options will be populated dynamically via AJAX -->
                            {% for rtype in relationship_types %}
                             <option value="{{rtype.id}}"> {{get_translation(rtype.description)}} </option>
                            {% endfor %}

                        </select><br>
                    </div>

                    <!-- Section for new member form -->
                    <div id="new-member-section" style="display:none;">
                        <label for="new_first_name">{{ get_translation("First Name") }}:</label>
                        <input type="text" id="new_first_name" name="new_first_name"><br>

                        <label for="new_last_name">{{ get_translation("Last Name") }}:</label>
                        <input type="text" id="new_last_name" name="new_last_name"><br>

                        <label for="relationship_type_new">{{ get_translation("Relationship Type") }}:</label>
                        <select name="relationship_type_new" id="relationship_type_new">
                            <!-- Options will be populated dynamically via AJAX -->
                            {% for rtype in relationship_types %}
                             <option value="{{rtype.id}}"> {{get_translation(rtype.description)}} </option>
                            {% endfor %}

                        </select><br>
                    </div>

                    <button type="button" id="add-relationship-btn">{{ get_translation("Add Relationship") }}</button>
                </form>

                <!-- Form to edit relationship (hidden by default) -->
                <div id="edit-relationship-modal" style="display: none;">
                    <h2>{{ get_translation("Edit Relationship") }}</h2>
                    <form id="edit-relationship-form">
                        <input type="hidden" id="edit_relationship_id" name="relationship_id">
                        <input type="hidden" id="edit_member_id" name="member_id" value="{{ member.id|e }}">
                        <input type="hidden" name="edit_member2_id" value="{{ member.id|e }}">
                        <input type="hidden" name="edit_family_tree_id" value="{{ member.family_tree_id|e }}">

                        <label for="edit_relationship_person1">{{ get_translation("Person 1") }}:</label>
                        <input type="text" id="edit_relationship_person1" name="person1" readonly><br>

                        <label for="edit_relationship_person2">{{ get_translation("Person 2") }}:</label>
                        <input type="text" id="edit_relationship_person2" name="person2" readonly><br>
                        <input type="date" id="edit_relation_start" name="relation_start"><br>
                        <input type="date" id="edit_relation_end" name="relation_end"><br>

                        <label for="edit_relationship_type">{{ get_translation("Relationship Type") }}:</label>
                        <select name="relationship_type" id="edit_relationship_type">
                            <!-- Options will be dynamically filled via AJAX -->
                        </select><br>

                        <button type="button" id="update-relationship-btn">{{ get_translation("Update Relationship") }}</button>
                    </form>
                </div>
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


<script>
    var memberId = {{memberId}}; // Pass member ID to JavaScript
    const treeId = {{treeId}}; // Pass member ID to JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        var script = document.createElement('script');
        script.src = 'res/relationships.js?ver=1.1';

        script.onload = function() {
            // Initialize relationships.js with member ID
            initializeRelationships(memberId);
        };
        document.head.appendChild(script);
    });

    $(document).ready(function() {
        // Handle delete tree form submission with confirmation
        $('.delete-member-form').submit(function(event) {
            if (!confirm('{{ get_translation("Are you sure you want to delete this tree?")}}')) {
                event.preventDefault();
            }
        });
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

