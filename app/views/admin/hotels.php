<?php
$currentPage = 'admin-hotels';
ob_start();
?>

<div class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="section-title">Hotel Management</h1>
            <a href="/index.php?page=admin" class="btn btn-secondary">← Back to Admin</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <!-- Add New Hotel -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">Add New Hotel</div>
            <div class="card-body">
                <form method="POST" action="/index.php?page=admin-hotels&action=create-hotel">
                    <?php echo CSRF::field(); ?>
                    
                    <div class="form-group">
                        <label class="form-label" for="name">Hotel Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="location">Address/Location *</label>
                        <input type="text" class="form-control" id="location" name="location" required>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label" for="contact_phone">Contact Phone</label>
                                <input type="tel" class="form-control" id="contact_phone" name="contact_phone">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label" for="contact_email">Contact Email</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">Add Hotel</button>
                </form>
            </div>
        </div>

        <!-- Hotels List -->
        <?php if (!empty($hotels)): ?>
            <?php foreach ($hotels as $hotel): ?>
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong><?php echo e($hotel['name']); ?></strong>
                            <span style="margin-left: 1rem; color: #545454;"><?php echo e($hotel['address']); ?></span>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-sm btn-primary" onclick="editHotel(<?php echo $hotel['id']; ?>)">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteHotel(<?php echo $hotel['id']; ?>, '<?php echo e($hotel['name']); ?>')">Delete</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <p><?php echo e($hotel['description']); ?></p>
                        <?php if ($hotel['address'] || $hotel['website']): ?>
                            <p style="font-size: 0.875rem; color: #545454; margin-bottom: 1rem;">
                                <?php if ($hotel['address']): ?>
                                    📍 <?php echo e($hotel['address']); ?>
                                <?php endif; ?>
                                <?php if ($hotel['address'] && $hotel['website']): ?> | <?php endif; ?>
                                <?php if ($hotel['website']): ?>
                                    🌐 <a href="<?php echo e($hotel['website']); ?>" target="_blank"><?php echo e($hotel['website']); ?></a>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>

                        <!-- Add Room Form -->
                        <div style="background: #FDE5B7; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                            <h5 style="margin: 0 0 1rem 0;">Add Room</h5>
                            <form method="POST" action="/index.php?page=admin-hotels&action=create-room" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: end;">
                                <?php echo CSRF::field(); ?>
                                <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                                
                                <div style="flex: 1; min-width: 150px;">
                                    <label class="form-label" style="font-size: 0.875rem;">Room Type *</label>
                                    <input type="text" class="form-control" name="room_type" placeholder="e.g. Single, Double" required>
                                </div>

                                <div style="flex: 0 0 100px;">
                                    <label class="form-label" style="font-size: 0.875rem;">Capacity *</label>
                                    <input type="number" class="form-control" name="capacity" min="1" required>
                                </div>

                                <div style="flex: 0 0 120px;">
                                    <label class="form-label" style="font-size: 0.875rem;">Price (£) *</label>
                                    <input type="number" class="form-control" name="price_per_night" min="0" step="0.01" required>
                                </div>

                                <div style="flex: 0 0 100px;">
                                    <label class="form-label" style="font-size: 0.875rem;">Quantity *</label>
                                    <input type="number" class="form-control" name="quantity_available" min="1" required>
                                </div>

                                <button type="submit" class="btn btn-primary">Add Room</button>
                            </form>
                        </div>

                        <!-- Rooms List -->
                        <?php
                        $rooms = $hotelModel->getRoomsByHotel($hotel['id']);
                        if (empty($rooms)):
                        ?>
                            <p style="text-align: center; color: #545454; padding: 1rem;">No rooms added yet.</p>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                                    <thead>
                                        <tr style="border-bottom: 2px solid #dee2e6;">
                                            <th style="padding: 0.75rem; text-align: left;">Room Type</th>
                                            <th style="padding: 0.75rem; text-align: center;">Capacity</th>
                                            <th style="padding: 0.75rem; text-align: right;">Price/Night</th>
                                            <th style="padding: 0.75rem; text-align: center;">Available</th>
                                            <th style="padding: 0.75rem; text-align: center;">Reserved</th>
                                            <th style="padding: 0.75rem; text-align: center;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rooms as $room): ?>
                                            <tr style="border-bottom: 1px solid #dee2e6;">
                                                <td style="padding: 0.75rem;"><?php echo e($room['room_type']); ?></td>
                                                <td style="padding: 0.75rem; text-align: center;"><?php echo $room['capacity']; ?> guests</td>
                                                <td style="padding: 0.75rem; text-align: right;">£<?php echo number_format($room['price_per_night'], 2); ?></td>
                                                <td style="padding: 0.75rem; text-align: center;"><?php echo $room['available_rooms']; ?></td>
                                                <td style="padding: 0.75rem; text-align: center;"><?php echo $room['total_rooms'] - $room['available_rooms']; ?></td>
                                                <td style="padding: 0.75rem; text-align: center;">
                                                    <button class="btn btn-sm btn-secondary" onclick="viewReservations(<?php echo $room['id']; ?>)">Reservations</button>
                                                    <button class="btn btn-sm btn-primary" onclick="editRoom(<?php echo $room['id']; ?>)">Edit</button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteRoom(<?php echo $room['id']; ?>, '<?php echo e($room['room_type']); ?>')">Delete</button>
                                                </td>
                                            </tr>
                                            <tr id="reservations-<?php echo $room['id']; ?>" style="display: none;">
                                                <td colspan="6" style="padding: 1rem; background: #f8f9fa;">
                                                    <?php
                                                    $reservations = $room['reservations'] ?? [];
                                                    if (empty($reservations)):
                                                    ?>
                                                        <p style="margin: 0;">No reservations yet.</p>
                                                    <?php else: ?>
                                                        <table style="width: 100%; font-size: 0.875rem;">
                                                            <thead>
                                                                <tr style="border-bottom: 1px solid #dee2e6;">
                                                                    <th style="padding: 0.5rem; text-align: left;">Guest</th>
                                                                    <th style="padding: 0.5rem; text-align: left;">Check-in</th>
                                                                    <th style="padding: 0.5rem; text-align: left;">Check-out</th>
                                                                    <th style="padding: 0.5rem; text-align: center;">Guests</th>
                                                                    <th style="padding: 0.5rem; text-align: right;">Total</th>
                                                                    <th style="padding: 0.5rem; text-align: center;">Payment</th>
                                                                    <th style="padding: 0.5rem; text-align: center;">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($reservations as $reservation): ?>
                                                                    <tr style="border-bottom: 1px solid #eee;">
                                                                        <td style="padding: 0.5rem;"><?php echo e($reservation['discord_name']); ?></td>
                                                                        <td style="padding: 0.5rem;"><?php echo date('M j', strtotime($reservation['check_in_date'])); ?></td>
                                                                        <td style="padding: 0.5rem;"><?php echo date('M j', strtotime($reservation['check_out_date'])); ?></td>
                                                                        <td style="padding: 0.5rem; text-align: center;"><?php echo $reservation['num_guests']; ?></td>
                                                                        <td style="padding: 0.5rem; text-align: right;">£<?php echo number_format($reservation['total_price'], 2); ?></td>
                                                                        <td style="padding: 0.5rem; text-align: center;">
                                                                            <form method="POST" action="/index.php?page=admin-hotels&action=payment" style="display: inline;">
                                                                                <?php echo CSRF::field(); ?>
                                                                                <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                                                <input type="hidden" name="status" value="<?php echo $reservation['payment_status'] === 'received' ? 'pending' : 'received'; ?>">
                                                                                <label>
                                                                                    <input type="checkbox" <?php echo $reservation['payment_status'] === 'received' ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                                                    <?php echo $reservation['payment_status'] === 'received' ? 'Received' : 'Pending'; ?>
                                                                                </label>
                                                                            </form>
                                                                        </td>
                                                                        <td style="padding: 0.5rem; text-align: center;">
                                                                            <button class="btn btn-sm btn-danger" onclick="cancelReservation(<?php echo $reservation['id']; ?>)">Cancel</button>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center" style="padding: 3rem;">
                    <p>No hotels added yet. Add your first hotel above!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Hotel Modal -->
<div class="modal" id="edit-hotel-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Hotel</h3>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-hotel-form" method="POST" action="/index.php?page=admin-hotels&action=edit-hotel">
                <?php echo CSRF::field(); ?>
                <input type="hidden" id="edit-hotel-id" name="hotel_id">
                
                <div class="form-group">
                    <label class="form-label" for="edit-hotel-name">Hotel Name *</label>
                    <input type="text" class="form-control" id="edit-hotel-name" name="name" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-hotel-description">Description *</label>
                    <textarea class="form-control" id="edit-hotel-description" name="description" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-hotel-location">Address/Location *</label>
                    <input type="text" class="form-control" id="edit-hotel-location" name="location" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-hotel-website">Website</label>
                    <input type="url" class="form-control" id="edit-hotel-website" name="website">
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
<div class="modal" id="edit-room-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Room</h3>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-room-form" method="POST" action="/index.php?page=admin-hotels&action=edit-room">
                <?php echo CSRF::field(); ?>
                <input type="hidden" id="edit-room-id" name="room_id">
                
                <div class="form-group">
                    <label class="form-label" for="edit-room-type">Room Type *</label>
                    <input type="text" class="form-control" id="edit-room-type" name="room_type" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-room-capacity">Capacity *</label>
                    <input type="number" class="form-control" id="edit-room-capacity" name="capacity" min="1" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-room-price">Price per Night (£) *</label>
                    <input type="number" class="form-control" id="edit-room-price" name="price_per_night" min="0" step="0.01" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-room-quantity">Total Rooms Available *</label>
                    <input type="number" class="form-control" id="edit-room-quantity" name="quantity_available" min="1" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<script>
const hotelsData = <?php echo json_encode($hotels); ?>;
const roomsData = <?php
$allRooms = [];
foreach ($hotels as $hotel) {
    $rooms = $hotelModel->getRoomsByHotel($hotel['id']);
    $allRooms = array_merge($allRooms, $rooms);
}
echo json_encode($allRooms);
?>;

function viewReservations(roomId) {
    const row = document.getElementById('reservations-' + roomId);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}

function editHotel(hotelId) {
    const hotel = hotelsData.find(h => h.id == hotelId);
    if (!hotel) return;
    
    document.getElementById('edit-hotel-id').value = hotel.id;
    document.getElementById('edit-hotel-name').value = hotel.name;
    document.getElementById('edit-hotel-description').value = hotel.description;
    document.getElementById('edit-hotel-location').value = hotel.address || '';
    document.getElementById('edit-hotel-website').value = hotel.website || '';
    
    modalManager.open('edit-hotel-modal');
}

function deleteHotel(hotelId, name) {
    if (!confirm(`Are you sure you want to delete "${name}"? This will also delete all rooms and reservations.`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/index.php?page=admin-hotels&action=delete-hotel';
    
    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_csrf_token';
    csrfField.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfField);
    
    const hotelIdField = document.createElement('input');
    hotelIdField.type = 'hidden';
    hotelIdField.name = 'hotel_id';
    hotelIdField.value = hotelId;
    form.appendChild(hotelIdField);
    
    document.body.appendChild(form);
    form.submit();
}

function editRoom(roomId) {
    const room = roomsData.find(r => r.id == roomId);
    if (!room) return;
    
    document.getElementById('edit-room-id').value = room.id;
    document.getElementById('edit-room-type').value = room.room_type;
    document.getElementById('edit-room-capacity').value = room.capacity;
    document.getElementById('edit-room-price').value = room.price_per_night;
    document.getElementById('edit-room-quantity').value = room.total_rooms;
    
    modalManager.open('edit-room-modal');
}

function deleteRoom(roomId, roomType) {
    if (!confirm(`Are you sure you want to delete "${roomType}" room? This will also cancel all reservations.`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/index.php?page=admin-hotels&action=delete-room';
    
    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_csrf_token';
    csrfField.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfField);
    
    const roomIdField = document.createElement('input');
    roomIdField.type = 'hidden';
    roomIdField.name = 'room_id';
    roomIdField.value = roomId;
    form.appendChild(roomIdField);
    
    document.body.appendChild(form);
    form.submit();
}

function cancelReservation(reservationId) {
    if (!confirm('Are you sure you want to cancel this reservation?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/index.php?page=admin-hotels&action=cancel-reservation';
    
    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_csrf_token';
    csrfField.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfField);
    
    const reservationIdField = document.createElement('input');
    reservationIdField.type = 'hidden';
    reservationIdField.name = 'reservation_id';
    reservationIdField.value = reservationId;
    form.appendChild(reservationIdField);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
