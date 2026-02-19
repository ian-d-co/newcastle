// Modal Management
(function() {
    'use strict';

    const modals = {};

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
                
                // Escape key to close
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && modal.classList.contains('active')) {
                        closeModal(modalId);
                    }
                });
            }
        });
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

    // Expose globally
    window.modalManager = {
        init: initModals,
        open: openModal,
        close: closeModal,
        closeAll: closeAllModals
    };

    // Auto-initialize
    document.addEventListener('DOMContentLoaded', initModals);

})();
