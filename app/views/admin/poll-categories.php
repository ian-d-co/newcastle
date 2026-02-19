<?php
$currentPage = 'admin';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Manage Poll Categories</h1>
            <div>
                <button onclick="openCreateModal()" class="btn btn-primary" style="margin-right: 1rem;">Add Category</button>
                <a href="/index.php?page=admin_polls" class="btn btn-secondary" style="margin-right: 1rem;">Manage Polls</a>
                <a href="/index.php?page=admin" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>

        <?php if (empty($categories)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No poll categories created yet.</p>
                    <button onclick="openCreateModal()" class="btn btn-primary">Create First Category</button>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <th style="padding: 0.75rem; text-align: left;">Name</th>
                                <th style="padding: 0.75rem; text-align: center;">Display Order</th>
                                <th style="padding: 0.75rem; text-align: center;">Polls</th>
                                <th style="padding: 0.75rem; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 0.75rem;"><?php echo e($category['name']); ?></td>
                                    <td style="padding: 0.75rem; text-align: center;"><?php echo (int)$category['display_order']; ?></td>
                                    <td style="padding: 0.75rem; text-align: center;"><?php echo (int)$category['poll_count']; ?></td>
                                    <td style="padding: 0.75rem; text-align: right;">
                                        <button onclick='editCategory(<?php echo json_encode($category); ?>)'
                                                class="btn btn-sm btn-primary" style="margin-right: 0.5rem;">Edit</button>
                                        <button onclick="deleteCategory(<?php echo (int)$category['id']; ?>)"
                                                class="btn btn-sm btn-danger"
                                                <?php if ($category['poll_count'] > 0): ?>disabled title="Cannot delete — has polls assigned"<?php endif; ?>>
                                            Delete
                                        </button>
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
<div id="categoryModal" class="modal">
    <div class="modal-content" style="max-width: 480px;">
        <span class="modal-close">&times;</span>
        <h2 id="modalTitle">Add Category</h2>

        <form id="categoryForm">
            <input type="hidden" id="category_id" name="id">

            <div class="form-group">
                <label for="category_name">Category Name *</label>
                <input type="text" id="category_name" name="name" class="form-control" required maxlength="100">
            </div>

            <div class="form-group">
                <label for="display_order">Display Order</label>
                <input type="number" id="display_order" name="display_order" class="form-control" value="999" min="0">
                <small class="form-text">Lower numbers appear first</small>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Save Category</button>
                <button type="button" onclick="modalManager.close('categoryModal')" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
let editingId = null;

function openCreateModal() {
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('categoryForm').reset();
    document.getElementById('category_id').value = '';
    document.getElementById('display_order').value = '999';
    modalManager.open('categoryModal');
}

function editCategory(category) {
    editingId = category.id;
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('category_id').value = category.id;
    document.getElementById('category_name').value = category.name;
    document.getElementById('display_order').value = category.display_order;
    modalManager.open('categoryModal');
}

function deleteCategory(id) {
    if (!confirm('Are you sure you want to delete this category?')) {
        return;
    }
    
    fetch('/index.php?page=admin_poll_categories&action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showAlert('Category deleted successfully!', 'success');
            setTimeout(function() { location.reload(); }, 1000);
        } else {
            showAlert(data.message || 'Failed to delete category', 'danger');
        }
    })
    .catch(function() { showAlert('An error occurred', 'danger'); });
}

document.getElementById('categoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var submitBtn = this.querySelector('button[type="submit"]');
    var originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    var formData = {
        name: document.getElementById('category_name').value,
        display_order: parseInt(document.getElementById('display_order').value, 10) || 999
    };
    
    var action = editingId ? 'update' : 'create';
    if (editingId) {
        formData.id = editingId;
    }
    
    fetch('/index.php?page=admin_poll_categories&action=' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        
        if (data.success) {
            showAlert(data.message, 'success');
            modalManager.close('categoryModal');
            setTimeout(function() { location.reload(); }, 1000);
        } else {
            showAlert(data.message || 'Failed to save category', 'danger');
        }
    })
    .catch(function() {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
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
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
