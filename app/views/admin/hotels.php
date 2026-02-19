<?php
$currentPage = 'admin';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Manage Hotels & Rooms</h1>
            <div>
                <button onclick="openCreateHotelModal()" class="btn btn-primary" style="margin-right: 1rem;">Add Hotel</button>
                <a href="/index.php?page=admin" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>

        <?php if (empty($hotels)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No hotels created yet.</p>
                    <button onclick="openCreateHotelModal()" class="btn btn-primary">Create First Hotel</button>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($hotels as $hotel): ?>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="margin: 0; font-weight: 400;"><?php echo e($hotel['name']); ?></h3>
                            <?php if ($hotel['location']): ?>
                                <small><?php echo e($hotel['location']); ?></small>
                            <?php endif; ?>
                        </div>
                        <div>
                            <button onclick='editHotel(<?php echo json_encode($hotel); ?>)' 
                                    class="btn btn-sm btn-primary" style="margin-right: 0.5rem;">Edit Hotel</button>
                            <button onclick="addRoom(<?php echo $hotel['id']; ?>)" 
                                    class="btn btn-sm btn-success" style="margin-right: 0.5rem;">Add Room</button>
                            <button onclick="deleteHotel(<?php echo $hotel['id']; ?>)" 
                                    class="btn btn-sm btn-danger">Delete Hotel</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($hotel['description'])): ?>
                            <p><?php echo e($hotel['description']); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($hotel['link'])): ?>
                            <p style="margin: 0.5rem 0;">
                                <strong>Website:</strong> <a href="<?php echo e($hotel['link']); ?>" target="_blank"><?php echo e($hotel['link']); ?></a>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (empty($hotel['rooms'])): ?>
                            <div class="text-center" style="padding: 1rem; background: #f8f9fa; border-radius: 0.25rem; margin-top: 1rem;">
                                <p>No rooms added yet.</p>
                                <button onclick="addRoom(<?php echo $hotel['id']; ?>)" class="btn btn-sm btn-primary">Add First Room</button>
                            </div>
                        <?php else: ?>
                            <h4 style="margin-top: 1rem; margin-bottom: 0.5rem;">Rooms</h4>
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 2px solid #dee2e6;">
                                        <th style="padding: 0.5rem; text-align: left;">Room Type</th>
                                        <th style="padding: 0.5rem; text-align: center;">Capacity</th>
                                        <th style="padding: 0.5rem; text-align: center;">Available</th>
                                        <th style="padding: 0.5rem; text-align: center;">Reservations</th>
                                        <th style="padding: 0.5rem; text-align: center;">Price/Night</th>
                                        <th style="padding: 0.5rem; text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hotel['rooms'] as $room): ?>
                                        <tr style="border-bottom: 1px solid #dee2e6;">
                                            <td style="padding: 0.5rem;"><?php echo e($room['room_type']); ?></td>
                                            <td style="padding: 0.5rem; text-align: center;"><?php echo e($room['capacity']); ?></td>
                                            <td style="padding: 0.5rem; text-align: center;"><?php echo e($room['quantity_available']); ?></td>
                                            <td style="padding: 0.5rem; text-align: center;"><?php echo e($room['reservation_count']); ?></td>
                                            <td style="padding: 0.5rem; text-align: center;">£<?php echo number_format($room['price'], 2); ?></td>
                                            <td style="padding: 0.5rem; text-align: right;">
                                                <button onclick='editRoom(<?php echo json_encode($room); ?>)' 
                                                        class="btn btn-xs btn-primary" style="margin-right: 0.25rem;">Edit</button>
                                                <button onclick="deleteRoom(<?php echo $room['id']; ?>)" 
                                                        class="btn btn-xs btn-danger">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Hotel Modal -->
<div id="hotelModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <span class="modal-close">&times;</span>
        <h2 id="hotelModalTitle">Add Hotel</h2>
        
        <form id="hotelForm">
            <input type="hidden" id="hotel_id" name="id">
            
            <div class="form-group">
                <label for="hotel_name">Hotel Name *</label>
                <input type="text" id="hotel_name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="hotel_location">Location</label>
                <input type="text" id="hotel_location" name="location" class="form-control" placeholder="City or area">
            </div>
            
            <div class="form-group">
                <label for="hotel_phone">Contact Phone</label>
                <input type="tel" id="hotel_phone" name="contact_phone" class="form-control" placeholder="+44 123 456 7890">
            </div>
            
            <div class="form-group">
                <label for="hotel_email">Contact Email</label>
                <input type="email" id="hotel_email" name="contact_email" class="form-control" placeholder="contact@hotel.com">
            </div>
            
            <div class="form-group">
                <label for="hotel_description">Description</label>
                <textarea id="hotel_description" name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="hotel_link">Link (Optional)</label>
                <input type="url" id="hotel_link" name="link" class="form-control" placeholder="https://example.com">
                <small class="form-text">External link for more information</small>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Save Hotel</button>
                <button type="button" onclick="modalManager.close('hotelModal')" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Room Modal -->
<div id="roomModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <span class="modal-close">&times;</span>
        <h2 id="roomModalTitle">Add Room</h2>
        
        <form id="roomForm">
            <input type="hidden" id="room_id" name="id">
            <input type="hidden" id="room_hotel_id" name="hotel_id">
            
            <div class="form-group">
                <label for="room_type">Room Type *</label>
                <input type="text" id="room_type" name="room_type" class="form-control" 
                       placeholder="e.g., Single, Double, Twin, Suite" required>
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="capacity">Capacity *</label>
                        <input type="number" id="capacity" name="capacity" class="form-control" 
                               min="1" value="2" required>
                    </div>
                </div>
                
                <div class="col">
                    <div class="form-group">
                        <label for="quantity_available">Available Rooms *</label>
                        <input type="number" id="quantity_available" name="quantity_available" class="form-control" 
                               min="1" value="1" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="price">Price Per Night (£) *</label>
                <input type="number" id="price" name="price" class="form-control" 
                       min="0" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="room_confirmation_deadline">Confirmation Deadline (Optional)</label>
                <input type="datetime-local" id="room_confirmation_deadline" name="confirmation_deadline" class="form-control">
                <small class="form-text">Last date to confirm booking</small>
            </div>

            <div class="form-group">
                <label for="room_payment_deadline">Payment Deadline (Optional)</label>
                <input type="datetime-local" id="room_payment_deadline" name="payment_deadline" class="form-control">
                <small class="form-text">Last date to complete payment</small>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Save Room</button>
                <button type="button" onclick="modalManager.close('roomModal')" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
let editingHotelId = null;
let editingRoomId = null;

// Hotel functions
function openCreateHotelModal() {
    editingHotelId = null;
    document.getElementById('hotelModalTitle').textContent = 'Add Hotel';
    document.getElementById('hotelForm').reset();
    document.getElementById('hotel_id').value = '';
    modalManager.open('hotelModal');
}

function editHotel(hotel) {
    editingHotelId = hotel.id;
    document.getElementById('hotelModalTitle').textContent = 'Edit Hotel';
    document.getElementById('hotel_id').value = hotel.id;
    document.getElementById('hotel_name').value = hotel.name;
    document.getElementById('hotel_location').value = hotel.location || '';
    document.getElementById('hotel_phone').value = hotel.contact_phone || '';
    document.getElementById('hotel_email').value = hotel.contact_email || '';
    document.getElementById('hotel_description').value = hotel.description || '';
    document.getElementById('hotel_link').value = hotel.link || '';
    modalManager.open('hotelModal');
}

function deleteHotel(id) {
    if (!confirm('Are you sure you want to delete this hotel? All rooms and reservations will be removed.')) {
        return;
    }
    
    fetch('/index.php?page=admin_hotels&action=delete_hotel', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Hotel deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to delete hotel', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
}

document.getElementById('hotelForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Disable submit button to prevent double submission
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = {
        name: this.hotel_name.value,
        location: this.hotel_location.value,
        contact_phone: this.hotel_phone.value,
        contact_email: this.hotel_email.value,
        description: this.hotel_description.value,
        link: this.hotel_link.value || null
    };
    
    if (editingHotelId) {
        formData.id = editingHotelId;
    }
    
    const action = editingHotelId ? 'update_hotel' : 'create_hotel';
    
    fetch('/index.php?page=admin_hotels&action=' + action, {
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
            modalManager.close('hotelModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to save hotel', 'danger');
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
});

// Room functions
function addRoom(hotelId) {
    editingRoomId = null;
    document.getElementById('roomModalTitle').textContent = 'Add Room';
    document.getElementById('roomForm').reset();
    document.getElementById('room_id').value = '';
    document.getElementById('room_hotel_id').value = hotelId;
    modalManager.open('roomModal');
}

function editRoom(room) {
    editingRoomId = room.id;
    document.getElementById('roomModalTitle').textContent = 'Edit Room';
    document.getElementById('room_id').value = room.id;
    document.getElementById('room_hotel_id').value = room.hotel_id;
    document.getElementById('room_type').value = room.room_type;
    document.getElementById('capacity').value = room.capacity;
    document.getElementById('quantity_available').value = room.quantity_available;
    document.getElementById('price').value = room.price;
    document.getElementById('room_confirmation_deadline').value = room.confirmation_deadline ? room.confirmation_deadline.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('room_payment_deadline').value = room.payment_deadline ? room.payment_deadline.replace(' ', 'T').substring(0, 16) : '';
    modalManager.open('roomModal');
}

function deleteRoom(id) {
    if (!confirm('Are you sure you want to delete this room? All reservations will be removed.')) {
        return;
    }
    
    fetch('/index.php?page=admin_hotels&action=delete_room', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Room deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to delete room', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
}

document.getElementById('roomForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Disable submit button to prevent double submission
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = {
        hotel_id: this.room_hotel_id.value,
        room_type: this.room_type.value,
        capacity: parseInt(this.capacity.value),
        quantity_available: parseInt(this.quantity_available.value),
        price: parseFloat(this.price.value),
        confirmation_deadline: document.getElementById('room_confirmation_deadline').value || null,
        payment_deadline: document.getElementById('room_payment_deadline').value || null
    };
    
    if (editingRoomId) {
        formData.id = editingRoomId;
    }
    
    const action = editingRoomId ? 'update_room' : 'create_room';
    
    fetch('/index.php?page=admin_hotels&action=' + action, {
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
            modalManager.close('roomModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to save room', 'danger');
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
.btn-xs {
    padding: 0.125rem 0.5rem;
    font-size: 0.75rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
