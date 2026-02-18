/**
 * Dicksord Fest 2026 - Newcastle Event Management System
 * Scripts for Modal Management and UI Interactions
 */

(function() {
    'use strict';

    // ========================================================================
    // MODAL MANAGER
    // ========================================================================
    
    const modals = {};
    let escapeListenerAdded = false;

    // Initialize modals
    function initModals() {
        const modalElements = document.querySelectorAll('.modal');
        
        modalElements.forEach(function(modal) {
            const modalId = modal.id;
            if (modalId) {
                modals[modalId] = modal;
                
                // Close button
                const closeBtn = modal.querySelector('.modal-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        closeModal(modalId);
                    });
                }
                
                // Click outside to close
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal(modalId);
                    }
                });
            }
        });
        
        // Single document-level escape key listener for all modals (add only once)
        if (!escapeListenerAdded) {
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    // Close all active modals
                    Object.keys(modals).forEach(function(modalId) {
                        const modal = modals[modalId];
                        if (modal && modal.classList.contains('active')) {
                            closeModal(modalId);
                        }
                    });
                }
            });
            escapeListenerAdded = true;
        }
    }

    // Open modal
    function openModal(modalId) {
        const modal = modals[modalId] || document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    // Close modal
    function closeModal(modalId) {
        const modal = modals[modalId] || document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // Close all modals
    function closeAllModals() {
        Object.keys(modals).forEach(function(modalId) {
            closeModal(modalId);
        });
    }

    // Expose modalManager globally
    window.modalManager = {
        init: initModals,
        open: openModal,
        close: closeModal,
        closeAll: closeAllModals
    };

    // ========================================================================
    // NAVIGATION TOGGLE
    // ========================================================================
    
    function initNavigation() {
        const navToggle = document.querySelector('.nav-toggle');
        const nav = document.querySelector('.nav');

        if (navToggle && nav) {
            navToggle.addEventListener('click', function() {
                nav.classList.toggle('active');
            });

            // Close nav on link click (mobile)
            const navLinks = nav.querySelectorAll('a');
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        nav.classList.remove('active');
                    }
                });
            });
        }
    }

    // ========================================================================
    // ALERTS
    // ========================================================================
    
    function initAlerts() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    }

    // Show alert
    window.showAlert = function(message, type) {
        type = type || 'info';
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-' + type;
        alertDiv.textContent = message;
        
        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(function() {
                alertDiv.style.transition = 'opacity 0.3s ease';
                alertDiv.style.opacity = '0';
                setTimeout(function() {
                    alertDiv.remove();
                }, 300);
            }, 5000);
        }
    };

    // ========================================================================
    // UTILITY FUNCTIONS
    // ========================================================================
    
    // Utility function for API calls
    window.apiCall = function(url, method, data, callback) {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        
        if (method === 'POST') {
            xhr.setRequestHeader('Content-Type', 'application/json');
        }
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        callback(null, response);
                    } catch (e) {
                        callback({message: 'Invalid response format', error: e.message});
                    }
                } else {
                    try {
                        const error = JSON.parse(xhr.responseText);
                        callback(error);
                    } catch (e) {
                        callback({message: 'Request failed'});
                    }
                }
            }
        };
        
        if (method === 'POST' && data) {
            xhr.send(JSON.stringify(data));
        } else {
            xhr.send();
        }
    };

    // Confirm action
    window.confirmAction = function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    };

    // ========================================================================
    // INITIALIZATION
    // ========================================================================
    
    // Initialize everything when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initModals();
        initNavigation();
        initAlerts();
    });

})();
