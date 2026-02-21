<?php
$currentPage = 'travel_advice';
ob_start();

require_once BASE_PATH . '/app/models/TravelAdvice.php';
$travelAdviceModel = new TravelAdvice();
$adviceList = $travelAdviceModel->getAll($event['id']);
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <h1 class="section-title" style="margin-bottom: 0;">Travel Advice</h1>
            <?php if (isAdmin()): ?>
            <button class="btn btn-primary" onclick="modalManager.open('add-travel-advice-modal')">+ Add Advice</button>
            <?php endif; ?>
        </div>

        <p style="color: #666; margin-bottom: 1.5rem;">Travel options researched by the group for getting to Newcastle.</p>

        <?php if (empty($adviceList)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No travel advice entries yet. Check back later!</p>
                </div>
            </div>
        <?php else: ?>
            <?php
            // Group by travel type
            $grouped = [];
            foreach ($adviceList as $advice) {
                $grouped[$advice['travel_type']][] = $advice;
            }
            ?>
            <?php foreach ($grouped as $type => $entries): ?>
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header"><?php echo e($type); ?></div>
                <div class="card-body" style="padding: 0;">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; min-width: 500px;">
                            <thead>
                                <tr style="background: #f8f9fa;">
                                    <th style="padding: 0.75rem 1rem; text-align: left; border-bottom: 2px solid #FDDC62; color: #613704;">From</th>
                                    <th style="padding: 0.75rem 1rem; text-align: left; border-bottom: 2px solid #FDDC62; color: #613704;">To</th>
                                    <th style="padding: 0.75rem 1rem; text-align: left; border-bottom: 2px solid #FDDC62; color: #613704;">Supplier</th>
                                    <th style="padding: 0.75rem 1rem; text-align: left; border-bottom: 2px solid #FDDC62; color: #613704;">Researched</th>
                                    <th style="padding: 0.75rem 1rem; text-align: left; border-bottom: 2px solid #FDDC62; color: #613704;">Notes</th>
                                    <?php if (isAdmin()): ?>
                                    <th style="padding: 0.75rem 1rem; text-align: left; border-bottom: 2px solid #FDDC62; color: #613704;"></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entries as $advice): ?>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="padding: 0.75rem 1rem;"><?php echo e($advice['from_location']); ?></td>
                                    <td style="padding: 0.75rem 1rem;"><?php echo e($advice['to_location']); ?></td>
                                    <td style="padding: 0.75rem 1rem;"><?php echo e($advice['supplier'] ?: '—'); ?></td>
                                    <td style="padding: 0.75rem 1rem;"><?php echo $advice['date_researched'] ? e(formatDisplayDate($advice['date_researched'])) : '—'; ?></td>
                                    <td style="padding: 0.75rem 1rem; max-width: 250px; white-space: pre-wrap;"><?php echo e($advice['notes'] ?: '—'); ?></td>
                                    <?php if (isAdmin()): ?>
                                    <td style="padding: 0.75rem 1rem; white-space: nowrap;">
                                        <button class="btn btn-sm btn-secondary"
                                                data-id="<?php echo $advice['id']; ?>"
                                                data-type="<?php echo e($advice['travel_type']); ?>"
                                                data-from="<?php echo e($advice['from_location']); ?>"
                                                data-to="<?php echo e($advice['to_location']); ?>"
                                                data-supplier="<?php echo e($advice['supplier']); ?>"
                                                data-date="<?php echo e($advice['date_researched'] ?? ''); ?>"
                                                data-notes="<?php echo e($advice['notes'] ?? ''); ?>"
                                                onclick="editTravelAdvice(this)">Edit</button>
                                        <button class="btn btn-sm btn-danger"
                                                data-id="<?php echo $advice['id']; ?>"
                                                onclick="deleteTravelAdvice(this.dataset.id)">Delete</button>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php if (isAdmin()): ?>
<!-- Add Travel Advice Modal -->
<div class="modal" id="add-travel-advice-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add Travel Advice</h3>
            <button class="modal-close" onclick="modalManager.close('add-travel-advice-modal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="add-travel-advice-form">
                <?php echo CSRF::field(); ?>
                <div class="form-group">
                    <label class="form-label">Type of Travel *</label>
                    <select class="form-control" name="travel_type" required>
                        <option value="">Select...</option>
                        <option value="Train">Train</option>
                        <option value="Bus">Bus</option>
                        <option value="Flight">Flight</option>
                        <option value="Car">Car</option>
                        <option value="Coach">Coach</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">From *</label>
                    <input type="text" class="form-control" name="from_location" required>
                </div>
                <div class="form-group">
                    <label class="form-label">To *</label>
                    <input type="text" class="form-control" name="to_location" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Supplier / Operator</label>
                    <input type="text" class="form-control" name="supplier" placeholder="e.g. LNER, National Express">
                </div>
                <div class="form-group">
                    <label class="form-label">Date Researched</label>
                    <input type="date" class="form-control" name="date_researched">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="3" placeholder="Price range, tips, links, etc."></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Add Travel Advice</button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Travel Advice Modal -->
<div class="modal" id="edit-travel-advice-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Travel Advice</h3>
            <button class="modal-close" onclick="modalManager.close('edit-travel-advice-modal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-travel-advice-form">
                <?php echo CSRF::field(); ?>
                <input type="hidden" name="id" id="edit-ta-id">
                <div class="form-group">
                    <label class="form-label">Type of Travel *</label>
                    <select class="form-control" name="travel_type" id="edit-ta-type" required>
                        <option value="">Select...</option>
                        <option value="Train">Train</option>
                        <option value="Bus">Bus</option>
                        <option value="Flight">Flight</option>
                        <option value="Car">Car</option>
                        <option value="Coach">Coach</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">From *</label>
                    <input type="text" class="form-control" name="from_location" id="edit-ta-from" required>
                </div>
                <div class="form-group">
                    <label class="form-label">To *</label>
                    <input type="text" class="form-control" name="to_location" id="edit-ta-to" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Supplier / Operator</label>
                    <input type="text" class="form-control" name="supplier" id="edit-ta-supplier">
                </div>
                <div class="form-group">
                    <label class="form-label">Date Researched</label>
                    <input type="date" class="form-control" name="date_researched" id="edit-ta-date">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" id="edit-ta-notes" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<script>
var taCsrf = document.querySelector('meta[name="csrf-token"]').content;

document.getElementById('add-travel-advice-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var f = this;
    var btn = f.querySelector('[type="submit"]');
    btn.disabled = true;
    apiCall('/api/travel-advice.php', 'POST', {
        csrf_token: taCsrf,
        action: 'create',
        travel_type: f.travel_type.value,
        from_location: f.from_location.value,
        to_location: f.to_location.value,
        supplier: f.supplier.value,
        date_researched: f.date_researched.value,
        notes: f.notes.value
    }, function(err, res) {
        btn.disabled = false;
        if (err) { showAlert(err.message || 'Failed to add', 'danger'); return; }
        showAlert('Travel advice added!', 'success');
        modalManager.close('add-travel-advice-modal');
        setTimeout(function() { location.reload(); }, 800);
    });
});

function editTravelAdvice(btn) {
    document.getElementById('edit-ta-id').value       = btn.dataset.id;
    document.getElementById('edit-ta-type').value     = btn.dataset.type;
    document.getElementById('edit-ta-from').value     = btn.dataset.from;
    document.getElementById('edit-ta-to').value       = btn.dataset.to;
    document.getElementById('edit-ta-supplier').value = btn.dataset.supplier;
    document.getElementById('edit-ta-date').value     = btn.dataset.date;
    document.getElementById('edit-ta-notes').value    = btn.dataset.notes;
    modalManager.open('edit-travel-advice-modal');
}

document.getElementById('edit-travel-advice-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var f = this;
    var btn = f.querySelector('[type="submit"]');
    btn.disabled = true;
    apiCall('/api/travel-advice.php', 'POST', {
        csrf_token: taCsrf,
        action: 'update',
        id: f.id.value,
        travel_type: f.travel_type.value,
        from_location: f.from_location.value,
        to_location: f.to_location.value,
        supplier: f.supplier.value,
        date_researched: f.date_researched.value,
        notes: f.notes.value
    }, function(err, res) {
        btn.disabled = false;
        if (err) { showAlert(err.message || 'Failed to update', 'danger'); return; }
        showAlert('Travel advice updated!', 'success');
        modalManager.close('edit-travel-advice-modal');
        setTimeout(function() { location.reload(); }, 800);
    });
});

function deleteTravelAdvice(id) {
    if (!confirm('Are you sure you want to delete this entry?')) return;
    apiCall('/api/travel-advice.php', 'POST', {
        csrf_token: taCsrf,
        action: 'delete',
        id: id
    }, function(err, res) {
        if (err) { showAlert(err.message || 'Failed to delete', 'danger'); return; }
        showAlert('Deleted!', 'success');
        setTimeout(function() { location.reload(); }, 600);
    });
}
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
