// Poll Management and Chart Rendering
(function() {
    'use strict';

    // Poll voting
    function initPolls() {
        const pollForms = document.querySelectorAll('.poll-form');
        
        pollForms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const pollId = form.dataset.pollId;
                const formData = new FormData(form);
                const optionIds = formData.getAll('poll_option');
                const csrfToken = formData.get('csrf_token');
                
                if (optionIds.length === 0) {
                    showAlert('Please select at least one option', 'warning');
                    return;
                }
                
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Voting...';
                
                apiCall('/api/poll-vote.php', 'POST', {
                    poll_id: pollId,
                    option_ids: optionIds,
                    csrf_token: csrfToken
                }, function(err, response) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Vote';
                    
                    if (err) {
                        showAlert(err.message || 'Failed to submit vote', 'danger');
                    } else {
                        showAlert('Vote submitted successfully!', 'success');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    }
                });
            });
        });
    }

    // Render poll results as bar chart
    function renderPollChart(pollId) {
        const pollElement = document.getElementById('poll-' + pollId);
        if (!pollElement) return;
        
        apiCall('/api/poll-results.php?poll_id=' + pollId, 'GET', null, function(err, response) {
            if (err) {
                console.error('Failed to load poll results', err);
                return;
            }
            
            const resultsContainer = pollElement.querySelector('.poll-results');
            if (!resultsContainer) return;
            
            resultsContainer.innerHTML = '';
            
            let maxVotes = 0;
            let leadingOption = null;
            
            response.options.forEach(function(option) {
                if (option.vote_count > maxVotes) {
                    maxVotes = option.vote_count;
                    leadingOption = option;
                }
            });
            
            response.options.forEach(function(option) {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'poll-option';
                
                if (leadingOption && option.id === leadingOption.id) {
                    optionDiv.classList.add('poll-leading');
                }
                
                const label = document.createElement('div');
                label.className = 'poll-option-label';
                label.textContent = option.option_text;
                
                const barContainer = document.createElement('div');
                barContainer.className = 'poll-bar';
                
                const bar = document.createElement('div');
                bar.className = 'poll-bar-fill';
                bar.style.width = option.percentage + '%';
                
                const barText = document.createElement('div');
                barText.className = 'poll-bar-text';
                barText.textContent = option.vote_count + ' votes (' + Math.round(option.percentage) + '%)';
                
                barContainer.appendChild(bar);
                barContainer.appendChild(barText);
                
                optionDiv.appendChild(label);
                optionDiv.appendChild(barContainer);
                
                resultsContainer.appendChild(optionDiv);
            });
        });
    }

    // Real-time poll updates
    function startPollUpdates() {
        const polls = document.querySelectorAll('[data-poll-id]');
        
        polls.forEach(function(poll) {
            const pollId = poll.dataset.pollId;
            
            // Update every 10 seconds
            setInterval(function() {
                renderPollChart(pollId);
            }, 10000);
        });
    }

    // Check for expired polls
    function checkExpired() {
        apiCall('/api/poll-check-expired.php', 'POST', {}, function(err, response) {
            if (!err && response.expired > 0) {
                // Reload to reflect closed polls
                window.location.reload();
            }
        });
    }

    // Expose globally
    window.pollManager = {
        init: initPolls,
        renderChart: renderPollChart,
        startUpdates: startPollUpdates,
        checkExpired: checkExpired
    };

    // Auto-initialize
    document.addEventListener('DOMContentLoaded', function() {
        initPolls();
        
        // Render all poll charts
        const polls = document.querySelectorAll('[data-poll-id]');
        polls.forEach(function(poll) {
            const pollId = poll.dataset.pollId;
            renderPollChart(pollId);
        });
        
        // Start real-time updates
        startPollUpdates();
    });

})();
