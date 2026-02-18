<?php
$currentPage = 'admin-polls';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Poll Management</h1>
            <a href="/index.php?page=admin" class="btn btn-secondary">← Back to Admin</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <!-- Add New Poll -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">Create New Poll</div>
            <div class="card-body">
                <form method="POST" action="/index.php?page=admin-polls&action=create" id="poll-form">
                    <?php echo CSRF::field(); ?>
                    
                    <div class="form-group">
                        <label class="form-label" for="question">Poll Question *</label>
                        <input type="text" class="form-control" id="question" name="question" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Options *</label>
                        <div id="poll-options">
                            <div class="poll-option-input" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <input type="text" class="form-control" name="options[]" placeholder="Option 1" required>
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeOption(this)" style="min-width: 44px;">✕</button>
                            </div>
                            <div class="poll-option-input" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <input type="text" class="form-control" name="options[]" placeholder="Option 2" required>
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeOption(this)" style="min-width: 44px;">✕</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addOption()">+ Add Option</button>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allow_multiple" name="allow_multiple" value="1">
                            <label class="form-check-label" for="allow_multiple">
                                Allow multiple votes
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="anonymous" name="anonymous" value="1">
                            <label class="form-check-label" for="anonymous">
                                Allow anonymous voting
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="expires_at">Expiry Date/Time (optional)</label>
                        <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                        <small>Leave blank for no expiry</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">Create Poll</button>
                </form>
            </div>
        </div>

        <!-- Existing Polls -->
        <div class="card">
            <div class="card-header">
                Existing Polls (<?php echo count($polls); ?>)
            </div>
            <div class="card-body">
                <?php if (empty($polls)): ?>
                    <p class="text-center" style="padding: 2rem;">No polls created yet.</p>
                <?php else: ?>
                    <?php foreach ($polls as $poll): ?>
                        <div class="card" style="margin-bottom: 1.5rem;">
                            <div class="card-body">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <div>
                                        <h4 style="margin: 0 0 0.5rem 0;"><?php echo e($poll['question']); ?></h4>
                                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; font-size: 0.875rem;">
                                            <?php if ($poll['is_active']): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Closed</span>
                                            <?php endif; ?>
                                            <?php if ($poll['allow_multiple']): ?>
                                                <span class="badge badge-info">Multiple Votes</span>
                                            <?php endif; ?>
                                            <?php if ($poll['anonymous']): ?>
                                                <span class="badge badge-warning">Anonymous</span>
                                            <?php endif; ?>
                                            <?php if ($poll['expires_at']): ?>
                                                <span class="badge <?php echo strtotime($poll['expires_at']) < time() ? 'badge-danger' : 'badge-secondary'; ?>">
                                                    Expires: <?php echo date('M j, Y g:i A', strtotime($poll['expires_at'])); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <button class="btn btn-sm btn-<?php echo $poll['is_active'] ? 'warning' : 'success'; ?>" 
                                                onclick="togglePoll(<?php echo $poll['id']; ?>, <?php echo $poll['is_active'] ? 0 : 1; ?>)">
                                            <?php echo $poll['is_active'] ? 'Close' : 'Activate'; ?>
                                        </button>
                                        <button class="btn btn-sm btn-primary" onclick="editPoll(<?php echo $poll['id']; ?>)">Edit</button>
                                        <button class="btn btn-sm btn-danger" onclick="deletePoll(<?php echo $poll['id']; ?>, '<?php echo e($poll['question']); ?>')">Delete</button>
                                    </div>
                                </div>

                                <!-- Poll Results -->
                                <div style="margin-top: 1rem;">
                                    <?php
                                    $totalVotes = 0;
                                    foreach ($poll['options'] as $option) {
                                        $totalVotes += $option['votes'];
                                    }
                                    ?>
                                    <p style="margin-bottom: 0.5rem; font-weight: bold;">
                                        Results (<?php echo $totalVotes; ?> total votes)
                                    </p>
                                    <?php foreach ($poll['options'] as $option): ?>
                                        <?php
                                        $percentage = $totalVotes > 0 ? ($option['votes'] / $totalVotes * 100) : 0;
                                        $isLeading = $totalVotes > 0 && $option['votes'] == max(array_column($poll['options'], 'votes'));
                                        ?>
                                        <div style="margin-bottom: 0.75rem;">
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                                <span><?php echo e($option['option_text']); ?></span>
                                                <span style="font-weight: bold;"><?php echo $option['votes']; ?> (<?php echo number_format($percentage, 1); ?>%)</span>
                                            </div>
                                            <div style="height: 24px; background: #f0f0f0; border-radius: 4px; overflow: hidden;">
                                                <div style="height: 100%; background: <?php echo $isLeading ? '#FDDC62' : '#D46300'; ?>; width: <?php echo $percentage; ?>%; transition: width 0.3s;"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Poll Modal -->
<div class="modal" id="edit-poll-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Poll</h3>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-poll-form" method="POST" action="/index.php?page=admin-polls&action=edit">
                <?php echo CSRF::field(); ?>
                <input type="hidden" id="edit-poll-id" name="poll_id">
                
                <div class="form-group">
                    <label class="form-label" for="edit-question">Poll Question *</label>
                    <input type="text" class="form-control" id="edit-question" name="question" required>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit-allow-multiple" name="allow_multiple" value="1">
                        <label class="form-check-label" for="edit-allow-multiple">
                            Allow multiple votes
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit-anonymous" name="anonymous" value="1">
                        <label class="form-check-label" for="edit-anonymous">
                            Allow anonymous voting
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-expires-at">Expiry Date/Time (optional)</label>
                    <input type="datetime-local" class="form-control" id="edit-expires-at" name="expires_at">
                </div>

                <p style="margin-top: 1rem;"><small>Note: Options cannot be edited after votes have been cast. To change options, create a new poll.</small></p>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<script>
const pollsData = <?php echo json_encode($polls); ?>;
let optionCount = 2;

function addOption() {
    optionCount++;
    const container = document.getElementById('poll-options');
    const div = document.createElement('div');
    div.className = 'poll-option-input';
    div.style.cssText = 'display: flex; gap: 0.5rem; margin-bottom: 0.5rem;';
    div.innerHTML = `
        <input type="text" class="form-control" name="options[]" placeholder="Option ${optionCount}" required>
        <button type="button" class="btn btn-sm btn-danger" onclick="removeOption(this)" style="min-width: 44px;">✕</button>
    `;
    container.appendChild(div);
}

function removeOption(button) {
    const container = document.getElementById('poll-options');
    if (container.children.length > 2) {
        button.parentElement.remove();
    } else {
        alert('Poll must have at least 2 options');
    }
}

function editPoll(pollId) {
    const poll = pollsData.find(p => p.id == pollId);
    if (!poll) return;
    
    document.getElementById('edit-poll-id').value = poll.id;
    document.getElementById('edit-question').value = poll.question;
    document.getElementById('edit-allow-multiple').checked = poll.allow_multiple == 1;
    document.getElementById('edit-anonymous').checked = poll.anonymous == 1;
    
    if (poll.expires_at) {
        const date = new Date(poll.expires_at);
        const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
        document.getElementById('edit-expires-at').value = localDate.toISOString().slice(0, 16);
    } else {
        document.getElementById('edit-expires-at').value = '';
    }
    
    modalManager.open('edit-poll-modal');
}

function togglePoll(pollId, isActive) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/index.php?page=admin-polls&action=toggle';
    
    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_csrf_token';
    csrfField.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfField);
    
    const pollIdField = document.createElement('input');
    pollIdField.type = 'hidden';
    pollIdField.name = 'poll_id';
    pollIdField.value = pollId;
    form.appendChild(pollIdField);
    
    const isActiveField = document.createElement('input');
    isActiveField.type = 'hidden';
    isActiveField.name = 'is_active';
    isActiveField.value = isActive;
    form.appendChild(isActiveField);
    
    document.body.appendChild(form);
    form.submit();
}

function deletePoll(pollId, question) {
    if (!confirm(`Are you sure you want to delete "${question}"? This will also delete all votes.`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/index.php?page=admin-polls&action=delete';
    
    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_csrf_token';
    csrfField.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfField);
    
    const pollIdField = document.createElement('input');
    pollIdField.type = 'hidden';
    pollIdField.name = 'poll_id';
    pollIdField.value = pollId;
    form.appendChild(pollIdField);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
