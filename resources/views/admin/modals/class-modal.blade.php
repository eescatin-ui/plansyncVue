<div class="modal" id="class-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="class-modal-title">Add New Class</h2>
            <button class="close-modal" data-modal="class-modal">×</button>
        </div>
        <form id="class-form" method="POST">
            @csrf
            <input type="hidden" id="class-id" name="id">
            
            <div class="form-group">
                <label for="class-user-id">User *</label>
                <select class="form-control" id="class-user-id" name="user_id" required>
                    <option value="">Select User</option>
                    <!-- Users will be loaded via AJAX -->
                </select>
            </div>
            
            <div class="form-group">
                <label for="class-name">Class Name *</label>
                <input type="text" class="form-control" id="class-name" name="name" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="class-day">Day *</label>
                    <select class="form-control" id="class-day" name="day" required>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="class-start-time">Start Time *</label>
                    <input type="time" class="form-control" id="class-start-time" name="start_time" required>
                </div>
                
                <div class="form-group">
                    <label for="class-end-time">End Time *</label>
                    <input type="time" class="form-control" id="class-end-time" name="end_time" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="class-location">Location</label>
                <input type="text" class="form-control" id="class-location" name="location">
            </div>
            
            <div class="form-group">
                <label for="class-instructor">Instructor Name</label>
                <input type="text" class="form-control" id="class-instructor" name="instructor_name">
            </div>
            
            <div class="form-group">
                <label for="class-description">Description</label>
                <textarea class="form-control" id="class-description" name="description" rows="3"></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="submit" class="btn" id="save-class">Save Class</button>
                <button type="button" class="btn btn-danger" id="delete-class" style="display: none;">Delete Class</button>
                <button type="button" class="btn" onclick="Admin.closeModal('class-modal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load users when class modal opens
    const classModal = document.getElementById('class-modal');
    if (classModal) {
        // Listen for modal show event
        classModal.addEventListener('show', function() {
            loadUsersForClassModal();
        });
        
        // Also trigger on click if using custom modal system
        document.querySelectorAll('[data-target="class-modal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                setTimeout(loadUsersForClassModal, 100);
            });
        });
    }
    
    function loadUsersForClassModal() {
        fetch('{{ route("admin.users.list") }}')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(users => {
                const select = document.getElementById('class-user-id');
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
                // Fallback: show error in select
                const select = document.getElementById('class-user-id');
                if (select) {
                    select.innerHTML = '<option value="">Error loading users. Please refresh.</option>';
                }
            });
    }
});
</script>