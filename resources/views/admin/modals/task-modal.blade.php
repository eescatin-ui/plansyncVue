<div class="modal" id="task-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="task-modal-title">Add New Task</h2>
            <button class="close-modal" data-modal="task-modal">×</button>
        </div>
        <form id="task-form" method="POST">
            @csrf
            <input type="hidden" id="task-id" name="id">
            
            <div class="form-group">
                <label for="task-user-id">User *</label>
                <select class="form-control" id="task-user-id" name="user_id" required>
                    <option value="">Select User</option>
                    <!-- Users will be loaded via AJAX -->
                </select>
            </div>
            
            <div class="form-group">
                <label for="task-title">Title *</label>
                <input type="text" class="form-control" id="task-title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="task-description">Description</label>
                <textarea class="form-control" id="task-description" name="description" rows="3"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="task-status">Status *</label>
                    <select class="form-control" id="task-status" name="status" required>
                        <option value="todo">To Do</option>
                        <option value="inprogress">In Progress</option>
                        <option value="done">Done</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="task-priority">Priority</label>
                    <select class="form-control" id="task-priority" name="priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="task-due-date">Due Date *</label>
                    <input type="date" class="form-control" id="task-due-date" name="due_date" required>
                </div>
                
                <div class="form-group">
                    <label for="task-category">Category</label>
                    <input type="text" class="form-control" id="task-category" name="category" placeholder="e.g., Work, Personal, Study">
                </div>
            </div>
            
            <div class="form-group">
                <label for="task-reminder">Set Reminder</label>
                <div class="checkbox-group">
                    <input type="checkbox" id="task-reminder-checkbox" name="set_reminder">
                    <label for="task-reminder-checkbox">Create reminder for this task</label>
                </div>
                <div id="reminder-details" style="display: none; margin-top: 10px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reminder-date">Reminder Date</label>
                            <input type="date" class="form-control" id="reminder-date" name="reminder_date">
                        </div>
                        <div class="form-group">
                            <label for="reminder-time">Reminder Time</label>
                            <input type="time" class="form-control" id="reminder-time" name="reminder_time">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="submit" class="btn" id="save-task">Save Task</button>
                <button type="button" class="btn btn-danger" id="delete-task" style="display: none;">Delete Task</button>
                <button type="button" class="btn" onclick="Admin.closeModal('task-modal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load users when task modal opens
    const taskModal = document.getElementById('task-modal');
    if (taskModal) {
        taskModal.addEventListener('show', function() {
            loadUsersForTaskModal();
        });
        
        document.querySelectorAll('[data-target="task-modal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                setTimeout(loadUsersForTaskModal, 100);
            });
        });
    }
    
    function loadUsersForTaskModal() {
        fetch('{{ route("admin.users.list") }}')
            .then(response => response.json())
            .then(users => {
                const select = document.getElementById('task-user-id');
                if (select) {
                    select.innerHTML = '<option value="">Select User</option>';
                    users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.name;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading users:', error);
            });
    }
    
    // Toggle reminder details
    const reminderCheckbox = document.getElementById('task-reminder-checkbox');
    const reminderDetails = document.getElementById('reminder-details');
    
    if (reminderCheckbox && reminderDetails) {
        reminderCheckbox.addEventListener('change', function() {
            reminderDetails.style.display = this.checked ? 'block' : 'none';
        });
    }
});
</script>