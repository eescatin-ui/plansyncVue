<div class="modal" id="note-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="note-modal-title">Add New Note</h2>
            <button class="close-modal" data-modal="note-modal">×</button>
        </div>
        <form id="note-form" method="POST">
            @csrf
            <input type="hidden" id="note-id" name="id">
            
            <div class="form-group">
                <label for="note-user-id">User *</label>
                <select class="form-control" id="note-user-id" name="user_id" required>
                    <option value="">Select User</option>
                    <!-- Users will be loaded via AJAX -->
                </select>
            </div>
            
            <div class="form-group">
                <label for="note-title">Title *</label>
                <input type="text" class="form-control" id="note-title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="note-content">Content *</label>
                <textarea class="form-control" id="note-content" name="content" rows="6" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="note-category">Category</label>
                    <select class="form-control" id="note-category" name="category">
                        <option value="">Select Category</option>
                        <option value="personal">Personal</option>
                        <option value="work">Work</option>
                        <option value="study">Study</option>
                        <option value="ideas">Ideas</option>
                        <option value="reference">Reference</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="note-color">Color Tag</label>
                    <div class="color-options">
                        <input type="radio" name="color" value="blue" id="color-blue" checked>
                        <label for="color-blue" class="color-option blue" title="Blue"></label>
                        
                        <input type="radio" name="color" value="green" id="color-green">
                        <label for="color-green" class="color-option green" title="Green"></label>
                        
                        <input type="radio" name="color" value="yellow" id="color-yellow">
                        <label for="color-yellow" class="color-option yellow" title="Yellow"></label>
                        
                        <input type="radio" name="color" value="red" id="color-red">
                        <label for="color-red" class="color-option red" title="Red"></label>
                        
                        <input type="radio" name="color" value="purple" id="color-purple">
                        <label for="color-purple" class="color-option purple" title="Purple"></label>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="note-tags">Tags</label>
                <input type="text" class="form-control" id="note-tags" name="tags" placeholder="e.g., important, todo, meeting">
                <small class="text-muted">Separate tags with commas</small>
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="note-pinned" name="pinned">
                    <label for="note-pinned">Pin this note (show at top)</label>
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="submit" class="btn" id="save-note">Save Note</button>
                <button type="button" class="btn btn-danger" id="delete-note" style="display: none;">Delete Note</button>
                <button type="button" class="btn" onclick="Admin.closeModal('note-modal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.color-options {
    display: flex;
    gap: 10px;
    margin-top: 5px;
}
.color-option {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid transparent;
}
.color-option:hover {
    border-color: #333;
}
.color-option.blue { background-color: #4cc9f0; }
.color-option.green { background-color: #4caf50; }
.color-option.yellow { background-color: #ffeb3b; }
.color-option.red { background-color: #f44336; }
.color-option.purple { background-color: #9c27b0; }
input[type="radio"]:checked + .color-option {
    border-color: #333;
    transform: scale(1.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load users when note modal opens
    const noteModal = document.getElementById('note-modal');
    if (noteModal) {
        noteModal.addEventListener('show', function() {
            loadUsersForNoteModal();
        });
        
        document.querySelectorAll('[data-target="note-modal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                setTimeout(loadUsersForNoteModal, 100);
            });
        });
    }
    
    function loadUsersForNoteModal() {
        fetch('{{ route("admin.users.list") }}')
            .then(response => response.json())
            .then(users => {
                const select = document.getElementById('note-user-id');
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
});
</script>