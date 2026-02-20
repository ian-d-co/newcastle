// Form Handling and Conditional Logic
(function() {
    'use strict';

    // Attendance form conditional logic
    function initAttendanceForm() {
        const form = document.getElementById('attendance-form');
        if (!form) return;

        const carCheckbox = form.querySelector('input[value="Car"]');
        const carshareSection = document.getElementById('carshare-section');
        const canCarshareYes = document.getElementById('can-carshare-yes');
        const canCarshareNo = document.getElementById('can-carshare-no');
        const carshareDetails = document.getElementById('carshare-details');

        const hostingSection = document.getElementById('hosting-section');
        const canHostYes = document.getElementById('can-host-yes');
        const canHostNo = document.getElementById('can-host-no');
        const hostingDetails = document.getElementById('hosting-details');

        // Car checkbox logic
        if (carCheckbox && carshareSection) {
            carCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    carshareSection.style.display = 'block';
                } else {
                    carshareSection.style.display = 'none';
                    if (carshareDetails) carshareDetails.style.display = 'none';
                }
            });
        }

        // Carshare Yes/No logic
        if (canCarshareYes && canCarshareNo && carshareDetails) {
            canCarshareYes.addEventListener('change', function() {
                if (this.checked) {
                    carshareDetails.style.display = 'block';
                }
            });

            canCarshareNo.addEventListener('change', function() {
                if (this.checked) {
                    carshareDetails.style.display = 'none';
                }
            });
        }

        // Hosting Yes/No logic
        if (canHostYes && canHostNo && hostingDetails) {
            canHostYes.addEventListener('change', function() {
                if (this.checked) {
                    hostingDetails.style.display = 'block';
                }
            });

            canHostNo.addEventListener('change', function() {
                if (this.checked) {
                    hostingDetails.style.display = 'none';
                }
            });
        }

        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const data = {
                discord_name: formData.get('discord_name'),
                name: formData.get('name'),
                pin: formData.get('pin'),
                days_attending: Array.from(form.querySelectorAll('input[name="days_attending[]"]:checked')).map(function(cb) { return cb.value; }),
                travel_method: Array.from(form.querySelectorAll('input[name="travel_method[]"]:checked')).map(function(cb) { return cb.value; }),
                csrf_token: formData.get('csrf_token')
            };

            // Add carshare data if applicable
            if (carCheckbox && carCheckbox.checked && canCarshareYes && canCarshareYes.checked) {
                data.carshare_origin = formData.get('carshare_origin');
                data.carshare_capacity = formData.get('carshare_capacity');
            }

            // Add hosting data if applicable
            if (canHostYes && canHostYes.checked) {
                data.hosting_capacity = formData.get('hosting_capacity');
                data.hosting_notes = formData.get('hosting_notes');
            }

            // Validate
            if (!data.discord_name || !data.name) {
                showAlert('Please fill in all required fields', 'danger');
                return;
            }

            if (data.days_attending.length === 0) {
                showAlert('Please select at least one day', 'danger');
                return;
            }

            if (data.travel_method.length === 0) {
                showAlert('Please select at least one travel method', 'danger');
                return;
            }

            // Submit
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            apiCall('/api/attendance.php', 'POST', data, function(err, response) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Register Attendance';

                if (err) {
                    showAlert(err.message || 'Failed to register attendance', 'danger');
                } else {
                    showAlert('Attendance registered successfully!', 'success');
                    modalManager.close('attendance-modal');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                }
            });
        });
    }

    // Activity booking
    window.bookActivity = function(activityId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        apiCall('/api/activity-book.php', 'POST', {
            activity_id: activityId,
            csrf_token: csrfToken
        }, function(err, response) {
            if (err) {
                showAlert(err.message || 'Failed to book activity', 'danger');
            } else {
                showAlert('Activity booked successfully!', 'success');
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            }
        });
    };

    // Activity cancellation
    window.cancelActivity = function(activityId) {
        confirmAction('Are you sure you want to cancel this booking?', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            apiCall('/api/activity-cancel.php', 'POST', {
                activity_id: activityId,
                csrf_token: csrfToken
            }, function(err, response) {
                if (err) {
                    showAlert(err.message || 'Failed to cancel activity', 'danger');
                } else {
                    showAlert('Activity cancelled successfully!', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                }
            });
        });
    };

    // Meal booking (similar to activity)
    window.bookMeal = function(mealId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        apiCall('/api/meal-book.php', 'POST', {
            meal_id: mealId,
            csrf_token: csrfToken
        }, function(err, response) {
            if (err) {
                showAlert(err.message || 'Failed to book meal', 'danger');
            } else {
                showAlert('Meal booked successfully!', 'success');
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            }
        });
    };

    window.cancelMeal = function(mealId) {
        confirmAction('Are you sure you want to cancel this booking?', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            apiCall('/api/meal-cancel.php', 'POST', {
                meal_id: mealId,
                csrf_token: csrfToken
            }, function(err, response) {
                if (err) {
                    showAlert(err.message || 'Failed to cancel meal', 'danger');
                } else {
                    showAlert('Meal cancelled successfully!', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                }
            });
        });
    };

    // Carshare booking
    window.bookCarshare = function(offerId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        apiCall('/api/carshare-book.php', 'POST', {
            offer_id: offerId,
            csrf_token: csrfToken
        }, function(err, response) {
            if (err) {
                showAlert(err.message || 'Failed to book carshare', 'danger');
            } else {
                showAlert('Carshare booked successfully!', 'success');
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            }
        });
    };

    window.cancelCarshare = function(offerId) {
        confirmAction('Are you sure you want to cancel this booking?', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            apiCall('/api/carshare-cancel.php', 'POST', {
                offer_id: offerId,
                csrf_token: csrfToken
            }, function(err, response) {
                if (err) {
                    showAlert(err.message || 'Failed to cancel carshare', 'danger');
                } else {
                    showAlert('Carshare cancelled successfully!', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                }
            });
        });
    };

    // Hosting booking
    window.bookHosting = function(offerId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        apiCall('/api/hosting-book.php', 'POST', {
            offer_id: offerId,
            csrf_token: csrfToken
        }, function(err, response) {
            if (err) {
                showAlert(err.message || 'Failed to book hosting', 'danger');
            } else {
                showAlert('Hosting booked successfully!', 'success');
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            }
        });
    };

    window.cancelHosting = function(offerId) {
        confirmAction('Are you sure you want to cancel this booking?', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            apiCall('/api/hosting-cancel.php', 'POST', {
                offer_id: offerId,
                csrf_token: csrfToken
            }, function(err, response) {
                if (err) {
                    showAlert(err.message || 'Failed to cancel hosting', 'danger');
                } else {
                    showAlert('Hosting cancelled successfully!', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                }
            });
        });
    };

    // Hotel reservation
    window.reserveRoom = function(roomId, checkIn, checkOut, occupancyData) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        const payload = {
            room_id: roomId,
            csrf_token: csrfToken
        };

        if (occupancyData) {
            payload.occupancy_type = occupancyData.occupancy_type;
            payload.nights = occupancyData.nights;
            payload.book_direct = occupancyData.book_direct || false;
            payload.book_with_group = occupancyData.book_with_group || false;
        } else {
            payload.check_in = checkIn;
            payload.check_out = checkOut;
        }
        
        apiCall('/api/hotel-reserve.php', 'POST', payload, function(err, response) {
            if (err) {
                showAlert(err.message || 'Failed to reserve room', 'danger');
            } else {
                showAlert('Room reserved successfully!', 'success');
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            }
        });
    };

    window.cancelReservation = function(reservationId) {
        confirmAction('Are you sure you want to cancel this reservation?', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            apiCall('/api/hotel-cancel.php', 'POST', {
                reservation_id: reservationId,
                csrf_token: csrfToken
            }, function(err, response) {
                if (err) {
                    showAlert(err.message || 'Failed to cancel reservation', 'danger');
                } else {
                    showAlert('Reservation cancelled successfully!', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                }
            });
        });
    };

    window.markAttending = function(itemType, itemId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        apiCall('/api/mark-attending.php', 'POST', {
            item_type: itemType,
            item_id: itemId,
            csrf_token: csrfToken
        }, function(err, response) {
            if (err) {
                showAlert(err.message || 'Failed to mark attendance', 'danger');
            } else {
                showAlert('Marked as attending!', 'success');
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            }
        });
    };

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        initAttendanceForm();
    });

})();
