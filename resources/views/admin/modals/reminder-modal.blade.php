<div class="modal" id="reminder-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="reminder-modal-title">Add New Reminder</h2>
            <button class="close-modal" data-modal="reminder-modal">×</button>
        </div>
        <form id="reminder-form" method="POST">
            @csrf
            <input type="hidden" id="reminder-id" name="id">
            
            <div class="form-group">
                <label for="reminder-user-id">User *</label>
                <select class="form-control" id="reminder-user-id" name="user_id" required>
                    <option value="">Select User</option>
                    <!-- Users will be loaded via AJAX -->
                </select>
            </div>
            
            <div class="form-group">
                <label for="reminder-title">Title *</label>
                <input type="text" class="form-control" id="reminder-title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="reminder-description">Description</label>
                <textarea class="form-control" id="reminder-description" name="description" rows="3"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="reminder-date">Date *</label>
                    <input type="date" class="form-control" id="reminder-date" name="date" required>
                </div>
                
                <div class="form-group">
                    <label for="reminder-time">Time *</label>
                    <input type="time" class="form-control" id="reminder-time" name="time" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="reminder-task-id">Related Task (Optional)</label>
                <select class="form-control" id="reminder-task-id" name="task_id">
                    <option value="">No Task</option>
                    <!-- Tasks will be loaded via AJAX -->
                </select>
            </div>
            
            <div class="form-group">
                <label for="reminder-priority">Priority</label>
                <select class="form-control" id="reminder-priority" name="priority">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="reminder-recurring">Recurring</label>
                <select class="form-control" id="reminder-recurring" name="recurring">
                    <option value="none" selected>None</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
            
            <div class="modal-actions">
                <button type="submit" class="btn" id="save-reminder">Save Reminder</button>
                <button type="button" class="btn btn-danger" id="delete-reminder" style="display: none;">Delete Reminder</button>
                <button type="button" class="btn" onclick="Admin.closeModal('reminder-modal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load data when reminder modal opens
    const reminderModal = document.getElementById('reminder-modal');
    if (reminderModal) {
        // Listen for modal show event
        reminderModal.addEventListener('show', function() {
            loadDataForReminderModal();
        });
        
        // Also trigger on click if using custom modal system
        document.querySelectorAll('[data-target="reminder-modal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                setTimeout(loadDataForReminderModal, 100);
            });
        });
    }
    
    function loadDataForReminderModal() {
        // Load users
        fetch('{{ route("admin.users.list") }}')
            .then(response => response.json())
            .then(users => {
                const userSelect = document.getElementById('reminder-user-id');
                if (userSelect) {
                    userSelect.innerHTML = '<option value="">Select User</option>';
                    users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.name;
                        userSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading users:', error);
            });
        
        // Load tasks
        fetch('{{ route("admin.tasks.list") }}')
            .then(response => response.json())
            .then(tasks => {
                const taskSelect = document.getElementById('reminder-task-id');
                if (taskSelect) {
                    taskSelect.innerHTML = '<option value="">No Task</option>';
                    tasks.forEach(task => {
                        const option = document.createElement('option');
                        option.value = task.id;
                        option.textContent = task.title;
                        taskSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading tasks:', error);
            });
    }
});
</script>