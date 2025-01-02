<!-- Main content -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                {{ get_translation("Member Details") }}
                <a href="index.php?action=visualize_descendants&member_id={{ member.id }}" 
                   class="btn btn-primary float-right">
                    {{ get_translation("Visualize Descendants") }}
                </a>
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

                <!-- Families where person is a child -->
                <div class="card mt-4">
                    <div class="card-header">
                        {{ get_translation("Parents") }}
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
                {{ get_translation("Families") }}
            </div>
            <div class="card-body">
                <!-- Families where person is a spouse -->

                <!-- Tabs for spouses -->
                <ul class="nav nav-tabs" id="familyTabs" role="tablist">
                    {% for family in spouse_families %}
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {% if loop.first %}active{% endif %}" 
                                    id="family-tab-{{ family.family_id }}" 
                                    data-toggle="tab" 
                                    data-target="#family-{{ family.family_id }}" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="family-{{ family.family_id }}" 
                                    aria-selected="{% if loop.first %}true{% else %}false{% endif %}">
                                {% if member.gender_id == 1 and family.wife_id %}
                                    {{ family.wife_name }}
                                {% elseif member.gender_id == 2 and family.husband_id %}
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
                             id="family-{{ family.family_id }}" 
                             role="tabpanel" 
                             aria-labelledby="family-tab-{{ family.family_id }}">
                    

                            <!-- Children from this family -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    {{ get_translation("Children") }}
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                    <!--
                                        <thead>
                                            <tr>
                                                <th>{{ get_translation("Name") }}</th>
                                                <th>{{ get_translation("Birth Date") }}</th>
                                            </tr>
                                        </thead>
                                        -->
                                        <tbody>
                                            {% for child in family.children|default([]) %}
                                                <tr>
                                                    <td>
                                                        <a href="index.php?action=edit_member&member_id={{ child.id }}">
                                                            {{ child.first_name }} {{ child.last_name }}
                                                        </a>
                                                    </td>
                                                    <td>{{ child.date_of_birth ? child.date_of_birth|date("M d, Y") : '-' }}
                                                    
                                                    </td>
                                                    <td>

                                                        <button type="button" class="btn btn-danger btn-sm delete-child-btn" 
                                                                data-child-id="{{ child.id }}"
                                                                data-family-id="{{ family.family_id }}"
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
                                    {% if (member.gender_id == 1 and not family.wife_id) or (member.gender_id == 2 and not family.husband_id) %}
                                        <div>
                                            <button type="button" class="btn btn-primary btn-sm replace-spouse-btn" 
                                                    data-family-id="{{ family.family_id }}">
                                                {{ get_translation("Replace Unknown Spouse") }}
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm delete-spouse-btn" 
                                                    data-spouse-id="{{ member.gender_id == 1 ? family.wife_id : family.husband_id }}"
                                                    data-family-id="{{ family.family_id }}"
                                                    onclick="event.stopPropagation();">
                                                🗑️ {{ get_translation("Delete Spouse") }}
                                            </button>
                                        </div>
                                    {% else %}
                                        <button type="button" class="btn btn-danger btn-sm delete-spouse-btn" 
                                                data-spouse-id="{{ member.gender_id == 1 ? family.wife_id : family.husband_id }}"
                                                data-family-id="{{ family.family_id }}"
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
                    <input type="hidden" name="member_gender" value="{{ member.gender_id|e }}">

                    <!-- Relationship type selection -->
                    <div class="form-group">
                        <label>{{ get_translation("Relationship Type") }}:</label><br>
                        <label><input type="radio" name="relation_category" value="spouse" checked> {{ get_translation("Add Spouse") }}</label><br>
                        <label><input type="radio" name="relation_category" value="child"> {{ get_translation("Add Child") }}</label><br>
                        <label><input type="radio" name="relation_category" value="parent"> {{ get_translation("Add Parents") }}</label><br>
                        <label><input type="radio" name="relation_category" value="other"> {{ get_translation("Add Other Relationship") }}</label><br>
                    </div>

                    <!-- Family selection for adding child (only shown when adding child) -->
                    <div id="family-selection-section" style="display:none;">
                        <label for="family_id">{{ get_translation("Select Family") }}:</label>
                        <select name="family_id" id="family_id" class="form-control">
                            {% for family in spouse_families %}
                                <option value="{{ family.family_id }}">
                                    {% if member.gender_id == 1 and family.wife_name %}
                                        {{ get_translation("With") }} {{ family.wife_name }}
                                    {% elseif member.gender_id == 2 and family.husband_name %}
                                        {{ get_translation("With") }} {{ family.husband_name }}
                                    {% else %}
                                        {{ get_translation("Unknown Spouse") }}
                                    {% endif %}
                                </option>
                            {% endfor %}
                        </select>
                    </div>

                    <!-- Person selection type -->
                    <div class="form-group mt-3">
                        <label><input type="radio" name="member_type" value="existing" checked> {{ get_translation("Existing Person") }}</label><br>
                        <label><input type="radio" name="member_type" value="new"> {{ get_translation("New Person") }}</label>
                    </div>

                    <!-- Section for existing person selection -->
                    <div id="existing-member-section">
                        <label for="autocomplete_member">{{ get_translation("Select Existing Person") }}:</label>
                        <input type="text" id="autocomplete_member" name="autocomplete_member" list="autocomplete-options" autocomplete="off" required><br>
                        <datalist id="autocomplete-options"></datalist>
                        <input type="hidden" name="person_id1" id="person_id1" value="{{ member.id|e }}">
                        <input type="hidden" name="person_id2" id="person_id2" value="">
                    </div>

                    <!-- Section for new person -->
                    <div id="new-member-section" style="display:none;">
                        <label for="new_first_name">{{ get_translation("First Name") }}:</label>
                        <input type="text" id="new_first_name" name="new_first_name"><br>

                        <label for="new_last_name">{{ get_translation("Last Name") }}:</label>
                        <input type="text" id="new_last_name" name="new_last_name"><br>

                        <label for="new_gender">{{ get_translation("Gender") }}:</label>
                        <select name="new_gender" id="new_gender">
                            <option value="1">{{ get_translation("Man") }}</option>
                            <option value="2">{{ get_translation("Woman") }}</option>
                        </select><br>

                        <label for="new_birth_date">{{ get_translation("Birth Date") }}:</label>
                        <input type="date" id="new_birth_date" name="new_birth_date"><br>
                    </div>

                    <!-- Other relationship type selection (only shown when "other" is selected) -->
                    <div id="other-relationship-section" style="display:none;">
                        <label for="relationship_type_select">{{ get_translation("Relationship Type") }}:</label>
                        <select name="relationship_type_select" id="relationship_type_select">
                            {% for rtype in relationship_types %}
                             <option value="{{rtype.id}}"> {{get_translation(rtype.description)}} </option>
                            {% endfor %}
                        </select><br>
                    </div>

                    <!-- Family details section (only shown for spouse relationships) -->
                    <div id="family-details-section" style="display:none;">
                        <label for="marriage_date">{{ get_translation("Marriage Date") }}:</label>
                        <input type="date" id="marriage_date" name="marriage_date"><br>
                    </div>

                    <!-- Add this in the "Add Relationship" section, after the existing radio buttons -->
                    <div class="form-group">
                        <label><input type="radio" name="relation_category" value="parent"> {{ get_translation("Add Parents") }}</label>
                    </div>

                    <!-- Add a new section for parent selection -->
                    <div id="parent-selection-section" style="display:none;">
                        <!-- First Parent -->
                        <div class="first-parent-section">
                            <h5>{{ get_translation("First Parent") }}</h5>
                            <div class="form-group">
                                <label><input type="radio" name="first_parent_type" value="existing" checked> {{ get_translation("Existing Person") }}</label>
                                <label><input type="radio" name="first_parent_type" value="new"> {{ get_translation("New Person") }}</label>
                            </div>

                            <!-- Existing First Parent -->
                            <div id="existing-first-parent-section">
                                <label for="first_parent_autocomplete">{{ get_translation("Select Person") }}:</label>
                                <input type="text" id="first_parent_autocomplete" list="first-parent-options" class="form-control">
                                <datalist id="first-parent-options"></datalist>
                                <input type="hidden" id="first_parent_id" name="first_parent_id">
                            </div>

                            <!-- New First Parent -->
                            <div id="new-first-parent-section" style="display:none;">
                                <label>{{ get_translation("First Name") }}:</label>
                                <input type="text" name="first_parent_first_name" class="form-control">
                                <label>{{ get_translation("Last Name") }}:</label>
                                <input type="text" name="first_parent_last_name" class="form-control">
                                <label>{{ get_translation("Gender") }}:</label>
                                <select name="first_parent_gender" class="form-control">
                                    <option value="1">{{ get_translation("Man") }}</option>
                                    <option value="2">{{ get_translation("Woman") }}</option>
                                </select>
                                <label>{{ get_translation("Birth Date") }}:</label>
                                <input type="date" name="first_parent_birth_date" class="form-control">
                            </div>
                        </div>

                        <!-- Second Parent -->
                        <div class="second-parent-section mt-3">
                            <h5>{{ get_translation("Second Parent") }}</h5>
                            <div id="second-parent-options-section">
                                <div class="form-group">
                                    <label><input type="radio" name="second_parent_type" value="existing_family" checked> {{ get_translation("From Existing Family") }}</label>
                                    <label><input type="radio" name="second_parent_type" value="new_person"> {{ get_translation("New Person") }}</label>
                                    <label><input type="radio" name="second_parent_type" value="existing_person"> {{ get_translation("Other Existing Person") }}</label>
                                </div>

                                <!-- Existing Family Selection -->
                                <div id="existing-family-section">
                                    <select id="existing_family_select" class="form-control" style="display:none;">
                                        <!-- Will be populated via JavaScript when first parent is selected -->
                                    </select>
                                </div>

                                <!-- New Second Parent -->
                                <div id="new-second-parent-section" style="display:none;">
                                    <label>{{ get_translation("First Name") }}:</label>
                                    <input type="text" name="second_parent_first_name" class="form-control">
                                    <label>{{ get_translation("Last Name") }}:</label>
                                    <input type="text" name="second_parent_last_name" class="form-control">
                                    <label>{{ get_translation("Birth Date") }}:</label>
                                    <input type="date" name="second_parent_birth_date" class="form-control">
                                </div>

                                <!-- Existing Second Parent -->
                                <div id="existing-second-parent-section" style="display:none;">
                                    <label for="second_parent_autocomplete">{{ get_translation("Select Person") }}:</label>
                                    <input type="text" id="second_parent_autocomplete" list="second-parent-options" class="form-control">
                                    <datalist id="second-parent-options"></datalist>
                                    <input type="hidden" id="second_parent_id" name="second_parent_id">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-relationship-btn" class="btn btn-primary mt-3">
                        {{ get_translation("Add Relationship") }}
                    </button>
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
            </div>/relationships.js?ver=1.1
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
                    <input type="hidden" name="member_gender" value="{{ member.gender_id }}">

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
    const translations = {
        "Choose action for child": "{{ get_translation("Choose action for child") }}",
        "Remove from family only": "{{ get_translation("Remove from family only") }}",
        "Delete child completely": "{{ get_translation("Delete child completely") }}",
        "Press OK to remove from family only, Cancel to delete completely": "{{ get_translation("Press OK to remove from family only, Cancel to delete completely") }}",
        "Choose delete option (enter number)": "{{ get_translation("Choose delete option (enter number)") }}",
        "Remove relationship only": "{{ get_translation("Remove relationship only") }}",
        "Delete spouse (keeps children)": "{{ get_translation("Delete spouse (keeps children)") }}",
        "Delete spouse and all children": "{{ get_translation("Delete spouse and all children") }}"
    };

    // Add translation function
    function get_translation(key) {
        return translations[key] || key;
    }

    document.addEventListener('DOMContentLoaded', function() {
        var script = document.createElement('script');
        script.src = 'res/relationships.js?ver=1.4'; // Increment version number

        script.onload = function() {
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

