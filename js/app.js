// Dicksord Fest 2026 - Newcastle - Main JavaScript
(function() {
    'use strict';

    // Initialize app
    document.addEventListener('DOMContentLoaded', function() {
        initAlerts();
        
        // Auto-close expired polls
        if (typeof pollManager !== 'undefined') {
            setInterval(function() {
                pollManager.checkExpired();
            }, 60000); // Check every minute
        }
    });

    // Auto-dismiss alerts
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
                        callback(null, {success: true});
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

    // Confirm action
    window.confirmAction = function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    };

})();
