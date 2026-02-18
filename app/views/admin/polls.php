<?php
$currentPage = 'admin';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Manage Polls</h1>
            <div>
                <button onclick="openCreateModal()" class="btn btn-primary" style="margin-right: 1rem;">Add Poll</button>
                <a href="/index.php?page=admin" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>

        <?php if (empty($polls)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No polls created yet.</p>
                    <button onclick="openCreateModal()" class="btn btn-primary">Create First Poll</button>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <th style="padding: 0.75rem; text-align: left;">Question</th>
                                <th style="padding: 0.75rem; text-align: center;">Options</th>
                                <th style="padding: 0.75rem; text-align: center;">Votes</th>
                                <th style="padding: 0.75rem; text-align: center;">Multi</th>
                                <th style="padding: 0.75rem; text-align: center;">Anon</th>
                                <th style="padding: 0.75rem; text-align: center;">Status</th>
                                <th style="padding: 0.75rem; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($polls as $poll): ?>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 0.75rem;">
                                        <?php echo e($poll['question']); ?>
                                        <?php if ($poll['expires_at']): ?>
                                            <br><small style="color: #666;">Expires: <?php echo formatDisplayDate($poll['expires_at']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <?php echo count($poll['options']); ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <?php echo e($poll['vote_count']); ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <?php if ($poll['is_multiple_choice']): ?>
                                            <span style="color: #28a745;">✓</span>
                                        <?php else: ?>
                                            <span style="color: #999;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <?php if ($poll['is_anonymous']): ?>
                                            <span style="color: #28a745;">✓</span>
                                        <?php else: ?>
                                            <span style="color: #999;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <?php if ($poll['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Closed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: right;">
                                        <button onclick='editPoll(<?php echo json_encode($poll); ?>)' 
                                                class="btn btn-sm btn-primary" style="margin-right: 0.5rem;">Edit</button>
                                        <button onclick="togglePollStatus(<?php echo $poll['id']; ?>, <?php echo $poll['is_active'] ? 0 : 1; ?>)" 
                                                class="btn btn-sm btn-warning" style="margin-right: 0.5rem;">
                                            <?php echo $poll['is_active'] ? 'Close' : 'Open'; ?>
                                        </button>
                                        <button onclick="deletePoll(<?php echo $poll['id']; ?>)" 
                                                class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="pollModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <span class="modal-close">&times;</span>
        <h2 id="modalTitle">Add Poll</h2>
        
        <form id="pollForm">
            <input type="hidden" id="poll_id" name="id">
            
            <div class="form-group">
                <label for="question">Poll Question *</label>
                <textarea id="question" name="question" class="form-control" rows="2" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Poll Options</label>
                <div id="optionsContainer">
                    <div class="option-row" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <input type="text" class="form-control poll-option" placeholder="Option 1" required>
                        <button type="button" onclick="removeOption(this)" class="btn btn-sm btn-danger" style="display: none;">×</button>
                    </div>
                    <div class="option-row" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <input type="text" class="form-control poll-option" placeholder="Option 2" required>
                        <button type="button" onclick="removeOption(this)" class="btn btn-sm btn-danger">×</button>
                    </div>
                </div>
                <button type="button" onclick="addOption()" class="btn btn-sm btn-secondary">+ Add Option</button>
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="expires_at">Expires At (Optional)</label>
                        <input type="datetime-local" id="expires_at" name="expires_at" class="form-control">
                        <small class="form-text">Leave blank for no expiration</small>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; cursor: pointer; margin-bottom: 0.5rem;">
                    <input type="checkbox" id="is_multiple_choice" name="is_multiple_choice" 
                           value="1" style="margin-right: 0.5rem;">
                    Allow Multiple Selections
                </label>
                <small class="form-text">Allow users to select multiple options</small>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; cursor: pointer; margin-bottom: 0.5rem;">
                    <input type="checkbox" id="is_anonymous" name="is_anonymous" 
                           value="1" style="margin-right: 0.5rem;">
                    Anonymous Voting
                </label>
                <small class="form-text">Hide who voted for what (results still visible)</small>
            </div>
            
            <div class="form-group" id="activeStatusGroup" style="display: none;">
                <label style="display: flex; align-items: center; cursor: pointer; margin-bottom: 0.5rem;">
                    <input type="checkbox" id="is_active" name="is_active" 
                           value="1" checked style="margin-right: 0.5rem;">
                    Active
                </label>
                <small class="form-text">Uncheck to close poll to new votes</small>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Save Poll</button>
                <button type="button" onclick="modalManager.close('pollModal')" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
let editingId = null;
let optionCounter = 2;

function addOption() {
    optionCounter++;
    const container = document.getElementById('optionsContainer');
    const div = document.createElement('div');
    div.className = 'option-row';
    div.style.cssText = 'display: flex; gap: 0.5rem; margin-bottom: 0.5rem;';
    div.innerHTML = `
        <input type="text" class="form-control poll-option" placeholder="Option ${optionCounter}">
        <button type="button" onclick="removeOption(this)" class="btn btn-sm btn-danger">×</button>
    `;
    container.appendChild(div);
}

function removeOption(button) {
    button.parentElement.remove();
}

function openCreateModal() {
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Add Poll';
    document.getElementById('pollForm').reset();
    document.getElementById('poll_id').value = '';
    document.getElementById('activeStatusGroup').style.display = 'none';
    
    // Reset to 2 options
    const container = document.getElementById('optionsContainer');
    container.innerHTML = `
        <div class="option-row" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
            <input type="text" class="form-control poll-option" placeholder="Option 1" required>
            <button type="button" onclick="removeOption(this)" class="btn btn-sm btn-danger" style="display: none;">×</button>
        </div>
        <div class="option-row" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
            <input type="text" class="form-control poll-option" placeholder="Option 2" required>
            <button type="button" onclick="removeOption(this)" class="btn btn-sm btn-danger">×</button>
        </div>
    `;
    optionCounter = 2;
    
    modalManager.open('pollModal');
}

function editPoll(poll) {
    editingId = poll.id;
    document.getElementById('modalTitle').textContent = 'Edit Poll';
    document.getElementById('poll_id').value = poll.id;
    document.getElementById('question').value = poll.question;
    document.getElementById('is_multiple_choice').checked = poll.is_multiple_choice == 1;
    document.getElementById('is_anonymous').checked = poll.is_anonymous == 1;
    document.getElementById('is_active').checked = poll.is_active == 1;
    document.getElementById('activeStatusGroup').style.display = 'block';
    
    // Set expires_at if exists
    if (poll.expires_at) {
        const date = new Date(poll.expires_at);
        const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
        document.getElementById('expires_at').value = localDate.toISOString().slice(0, 16);
    } else {
        document.getElementById('expires_at').value = '';
    }
    
    // Load existing options
    const container = document.getElementById('optionsContainer');
    container.innerHTML = '';
    poll.options.forEach((option, index) => {
        const div = document.createElement('div');
        div.className = 'option-row';
        div.style.cssText = 'display: flex; gap: 0.5rem; margin-bottom: 0.5rem;';
        
        // Create input and set value safely
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control poll-option';
        input.placeholder = 'Option ' + (index + 1);
        input.value = option.option_text;
        input.required = true;
        input.disabled = true; // Disable editing of existing options
        
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-sm btn-danger';
        button.textContent = '×';
        button.onclick = function() { removeOption(this); };
        if (index === 0) {
            button.style.display = 'none';
        }
        button.disabled = true; // Disable deletion of existing options
        
        div.appendChild(input);
        div.appendChild(button);
        container.appendChild(div);
    });
    optionCounter = poll.options.length;
    
    // Add notice that options cannot be edited
    const notice = document.createElement('p');
    notice.style.cssText = 'color: #666; font-size: 0.875rem; margin-top: 0.5rem;';
    notice.textContent = 'Note: Poll options cannot be edited after creation.';
    container.appendChild(notice);
    
    modalManager.open('pollModal');
}

function togglePollStatus(id, newStatus) {
    const formData = { id: id, is_active: newStatus };
    
    fetch('/index.php?page=admin_polls&action=update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Poll status updated!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to update poll', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
}

function deletePoll(id) {
    if (!confirm('Are you sure you want to delete this poll? All votes will be removed.')) {
        return;
    }
    
    fetch('/index.php?page=admin_polls&action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Poll deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to delete poll', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
}

document.getElementById('pollForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Collect options
    const options = [];
    document.querySelectorAll('.poll-option').forEach(input => {
        if (input.value.trim()) {
            options.push(input.value.trim());
        }
    });
    
    if (options.length < 2) {
        showAlert('Please provide at least 2 options', 'danger');
        return;
    }
    
    // Disable submit button to prevent double submission
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = {
        question: this.question.value,
        is_multiple_choice: this.is_multiple_choice.checked ? 1 : 0,
        is_anonymous: this.is_anonymous.checked ? 1 : 0,
        expires_at: this.expires_at.value || null,
        options: options
    };
    
    if (editingId) {
        formData.id = editingId;
        formData.is_active = this.is_active.checked ? 1 : 0;
        // Options cannot be edited after creation - only poll metadata
        delete formData.options;
    }
    
    const action = editingId ? 'update' : 'create';
    
    fetch('/index.php?page=admin_polls&action=' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        
        if (data.success) {
            showAlert(data.message, 'success');
            modalManager.close('pollModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to save poll', 'danger');
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
});
</script>

<style>
.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
}
.badge-success {
    color: #fff;
    background-color: #28a745;
}
.badge-secondary {
    color: #fff;
    background-color: #6c757d;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
