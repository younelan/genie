        <!-- Main content -->
        <div class="row mt-5">


<!-- People list -->
<div class="col-lg-4 mb-4">
    <div class="card">
        <div class="card-header">
            {{ str_family_members }}
        </div>
        <div class="card-body">

            {% if totalPages > 1 %}
                <style>
                    .pagination {
                        display: flex;
                        flex-wrap: wrap;
                    }
                </style>
                <nav>
                    <div class='pagination'>
                        <b>{{ str_pages }}: &nbsp;</b>
                        {% for i in 1..totalPages %}
                            <div>
                                <a href="index.php?action=list_members&tree_id={{ treeId }}&page={{ i }}">{{ i }}</a> &nbsp;
                            </div>
                        {% endfor %}
                    </div>
                </nav>
            {% endif %}

            <input type="text" id="search" placeholder="{{ get_translation('Search By Name') }}...">
            <div class="list-group" id="memberslist">
                {% for member in members %}
                    <a class="list-group-item list-group-item-action" href="index.php?action=edit_member&member_id={{ member.id }}">
                        {{ getGenderSymbol(member.gender_id) }}
                        {{ member.first_name|e }} {{ member.last_name|e }}
                    </a>
                {% endfor %}
            </div>

            {% if totalPages > 1 %}
                <nav>
                    <div class='pagination'>
                        <b>{{ get_translation('Pages') }}: &nbsp;</b>
                        {% for i in 1..totalPages %}
                            <div>
                                <a href="index.php?action=list_members&tree_id={{ treeId }}&page={{ i }}">{{ i }}</a>&nbsp;
                            </div>
                        {% endfor %}
                    </div>
                </nav>
            {% endif %}
            
            {% if treeId %}
                <form action="?action=delete_tree" method="post" class="delete-tree-form" style="display: inline;">
                    <input type="hidden" name="action" value="delete_tree">
                    <input type="hidden" name="tree_id" value="{{ treeId }}">
                    <button type="submit">🗑️ {{ get_translation('Delete') }}</button>
                </form>
            {% endif %}

        </div>
    </div>

    <!-- Statistics -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                {{ get_translation('Statistics') }}
            </div>
            <div class="card-body">
                {% for blockname, statblock in stats %}
                    <div>{{ get_translation(blockname) }}</div>
                    <ul class="list-group">
                        {% for idx, val in statblock %}
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ get_translation(idx) }} 
                                <span class="badge badge-primary badge-pill">{{ val }}</span>
                            </li>
                        {% endfor %}
                    </ul>
                {% endfor %}
            </div>
        </div>
    </div>

    <!-- Widgets -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                {{ get_translation('Recent Activity') }}
            </div>
            <div class="card-body">
                <ul class="list-group">
                    {% for idx, val in eactivities|default([]) %}
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ get_translation(idx) }} 
                            <span class="badge badge-primary badge-pill">{{ val }}</span>
                        </li>
                    {% endfor %}
                </ul>

                {% for member in lastUpdates %}
                    <a class="list-group-item list-group-item-action" href="index.php?action=edit_member&member_id={{ member.id }}">
                        {{ getGenderSymbol(member.gender_id) }}
                        {{ member.first_name|e }} {{ member.last_name|e }}
                    </a>
                {% endfor %}
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-header">
                {{ get_translation('Events') }}
            </div>
            <div class="card-body">
                <ul class="list-group">
                    {% for idx, val in events %}
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ get_translation(idx) }} 
                            <span class="badge badge-primary badge-pill">{{ val }}</span>
                        </li>
                    {% endfor %}
                </ul>
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function() {
        // Handle delete tree form submission with confirmation
        $('.delete-member-form').submit(function(event) {
            if (!confirm('Are you sure you want to delete this tree?')) {
                event.preventDefault();
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('search');
        function getGenderSymbol(genderId) {
            switch (genderId) {
                case 1:
                    return '♂️';
                case 2:
                    return '♀️';
                default:
                    return '';
            }
        }
        searchInput.addEventListener('input', function() {
            var query = this.value;
            var treeId = {{ treeId }};
            fetch(`index.php?action=search_members&tree_id=${treeId}&query=${encodeURIComponent(query)}`, {
                method: 'GET',
            })
            .then(function(response) {
                return response.json(); // Parse the JSON from the response
            })
            .then(function(members) {
                var membersList = document.getElementById('memberslist');
                membersList.innerHTML = ''; // Clear existing content
                
                members.forEach(function(member) {
                    genderSymbol = getGenderSymbol(member.gender_id);
                    var listItem = document.createElement('span');
                    listItem.innerHTML = `<a href="index.php?action=edit_member&member_id=${member.id}" class="list-group-item list-group-item-action">
                    ${genderSymbol} ${member.first_name} ${member.last_name}</a>`;
                    membersList.appendChild(listItem);
                });
            })
            .catch(function(error) {
                console.error('Error fetching members:', error);
            });
        });
    });
</script>
