<div class="modal" id="user-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="user-modal-title">Add New User</h2>
            <button class="close-modal" data-modal="user-modal">×</button>
        </div>
        <form id="user-form" method="POST">
            @csrf
            <input type="hidden" id="user-id" name="id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="user-name">Full Name *</label>
                    <input type="text" class="form-control" id="user-name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="user-email">Email *</label>
                    <input type="email" class="form-control" id="user-email" name="email" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="user-password">Password</label>
                    <input type="password" class="form-control" id="user-password" name="password">
                    <small class="text-muted">Leave empty to keep current password</small>
                </div>
                
                <div class="form-group">
                    <label for="user-password-confirmation">Confirm Password</label>
                    <input type="password" class="form-control" id="user-password-confirmation" name="password_confirmation">
                </div>
            </div>
            
            <div class="form-group">
                <label for="user-role">Role *</label>
                <select class="form-control" id="user-role" name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="user-status">Status</label>
                <select class="form-control" id="user-status" name="status">
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="user-phone">Phone Number</label>
                <input type="tel" class="form-control" id="user-phone" name="phone">
            </div>
            
            <div class="form-group">
                <label for="user-address">Address</label>
                <textarea class="form-control" id="user-address" name="address" rows="2"></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="submit" class="btn" id="save-user">Save User</button>
                <button type="button" class="btn btn-danger" id="delete-user" style="display: none;">Delete User</button>
                <button type="button" class="btn" onclick="Admin.closeModal('user-modal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// User modal doesn't need AJAX loading since it doesn't have dynamic selects
// All data is entered manually
</script>