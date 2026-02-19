<?php
$currentPage = 'polls';
ob_start();
?>

<div class="section">
    <div class="container">
        <h1 class="section-title">Polls</h1>

        <?php if (empty($polls)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <p>No active polls at the moment. Check back later!</p>
                </div>
            </div>
        <?php else: ?>
            <?php
            // Separate active and closed polls
            $activePolls = array_values(array_filter($polls, function($poll) {
                return $poll['is_active'] && (!$poll['expires_at'] || strtotime($poll['expires_at']) > time());
            }));

            $closedPolls = array_values(array_filter($polls, function($poll) {
                return !$poll['is_active'] || ($poll['expires_at'] && strtotime($poll['expires_at']) <= time());
            }));
            ?>

            <?php if (!empty($activePolls)): ?>
            <?php
            $currentCategory = null;
            foreach ($activePolls as $poll):
                $hasVoted = $poll['has_voted'] ?? false;
                $userVotes = $poll['user_votes'] ?? [];
                $isExpired = $poll['expires_at'] && strtotime($poll['expires_at']) < time();
                $categoryName = $poll['category_name'] ?? null;

                // Show category header when category changes
                if ($categoryName !== $currentCategory):
                    if ($currentCategory !== null): ?>
                        </div><!-- /.poll-category-group -->
                    <?php endif; ?>
                    <h2 class="section-title mt-4">
                        <?php echo e($categoryName ?? 'Uncategorized'); ?>
                    </h2>
                    <div class="poll-category-group">
                    <?php $currentCategory = $categoryName;
                endif;
            ?>
                
                <div class="poll" id="poll-<?php echo $poll['id']; ?>" data-poll-id="<?php echo $poll['id']; ?>">
                    <div class="poll-question"><?php echo e($poll['question']); ?></div>
                    
                    <?php if (!$hasVoted && !$isExpired): ?>
                        <!-- Voting Form -->
                        <form class="poll-form" data-poll-id="<?php echo $poll['id']; ?>">
                            <?php echo CSRF::field(); ?>
                            <div class="poll-options">
                                <?php foreach ($poll['options'] as $option): ?>
                                    <div class="form-check">
                                        <?php if ($poll['is_multiple_choice']): ?>
                                            <input class="form-check-input" type="checkbox" id="option-<?php echo $option['id']; ?>" name="poll_option[]" value="<?php echo $option['id']; ?>">
                                        <?php else: ?>
                                            <input class="form-check-input" type="radio" id="option-<?php echo $option['id']; ?>" name="poll_option[]" value="<?php echo $option['id']; ?>">
                                        <?php endif; ?>
                                        <label class="form-check-label" for="option-<?php echo $option['id']; ?>">
                                            <?php echo e($option['option_text']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (isGuestMode()): ?>
                                <button type="button" class="btn btn-primary btn-block mt-3" disabled>Vote (Login Required)</button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-primary btn-block mt-3">Vote</button>
                            <?php endif; ?>
                        </form>
                    <?php else: ?>
                        <!-- Results -->
                        <div class="poll-results">
                            <?php
                            $totalVotes = array_sum(array_column($poll['options'], 'vote_count'));
                            foreach ($poll['options'] as $option):
                                $pct = $totalVotes > 0 ? round(($option['vote_count'] / $totalVotes) * 100) : 0;
                                $isUserVote = in_array($option['id'], $userVotes);
                                $voteLabel = $option['vote_count'] === 1 ? 'vote' : 'votes';
                            ?>
                                <div class="poll-result-option" style="margin-bottom: 0.75rem;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span><?php echo e($option['option_text']); ?><?php if ($isUserVote): ?> <strong>✓</strong><?php endif; ?></span>
                                        <span><?php echo $option['vote_count']; ?> <?php echo $voteLabel; ?> (<?php echo $pct; ?>%)</span>
                                    </div>
                                    <div style="background: #e9ecef; border-radius: 0.25rem; height: 8px;">
                                        <div style="background: <?php echo $isUserVote ? '#28a745' : '#007bff'; ?>; width: <?php echo $pct; ?>%; height: 8px; border-radius: 0.25rem; transition: width 0.3s;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <p style="font-size: 0.875rem; color: #666; margin-top: 0.5rem;"><?php echo $totalVotes; ?> total <?php echo $totalVotes === 1 ? 'vote' : 'votes'; ?></p>
                        </div>
                        <?php if ($hasVoted): ?>
                            <div class="badge badge-success mt-2">✓ You voted</div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="poll-meta">
                        <div>
                            <?php if ($poll['is_multiple_choice']): ?>
                                <span class="badge badge-info">Multiple Choice</span>
                            <?php else: ?>
                                <span class="badge badge-info">Single Choice</span>
                            <?php endif; ?>
                            <?php if ($poll['is_anonymous']): ?>
                                <span class="badge badge-secondary">Anonymous</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($poll['expires_at']): ?>
                            <div>
                                <?php if ($isExpired): ?>
                                    <span class="badge badge-danger">Expired</span>
                                <?php else: ?>
                                    <span>Expires: <?php echo formatDisplayDate($poll['expires_at']); ?> <?php echo formatDisplayTime($poll['expires_at']); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if ($currentCategory !== null): ?>
                </div><!-- /.poll-category-group -->
            <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($closedPolls)): ?>
            <div class="expander" style="margin-top: 2rem;">
                <div class="expander-header" onclick="toggleExpander(this)">
                    <h2>Closed Polls (<?php echo count($closedPolls); ?>)</h2>
                    <span class="expander-icon">▼</span>
                </div>
                <div class="expander-content">
                    <?php foreach ($closedPolls as $poll): ?>
                        <?php
                        $userVotes = $poll['user_votes'] ?? [];
                        $totalVotes = array_sum(array_column($poll['options'], 'vote_count'));
                        ?>
                        <div class="poll" style="opacity: 0.85;">
                            <div class="poll-question"><?php echo e($poll['question']); ?></div>
                            <?php if ($poll['expires_at']): ?>
                                <p style="color: #999; font-size: 0.875rem; margin-bottom: 0.75rem;">
                                    Closed on <?php echo formatDisplayDate($poll['expires_at']); ?>
                                </p>
                            <?php endif; ?>
                            <div class="poll-results">
                                <?php foreach ($poll['options'] as $option):
                                    $pct = $totalVotes > 0 ? round(($option['vote_count'] / $totalVotes) * 100) : 0;
                                    $isUserVote = in_array($option['id'], $userVotes);
                                    $voteLabel = $option['vote_count'] === 1 ? 'vote' : 'votes';
                                ?>
                                    <div class="poll-result-option" style="margin-bottom: 0.75rem;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                            <span><?php echo e($option['option_text']); ?><?php if ($isUserVote): ?> <strong>✓</strong><?php endif; ?></span>
                                            <span><?php echo $option['vote_count']; ?> <?php echo $voteLabel; ?> (<?php echo $pct; ?>%)</span>
                                        </div>
                                        <div style="background: #e9ecef; border-radius: 0.25rem; height: 8px;">
                                            <div style="background: <?php echo $isUserVote ? '#28a745' : '#6c757d'; ?>; width: <?php echo $pct; ?>%; height: 8px; border-radius: 0.25rem;"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <p style="font-size: 0.875rem; color: #666; margin-top: 0.5rem;"><?php echo $totalVotes; ?> total <?php echo $totalVotes === 1 ? 'vote' : 'votes'; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleExpander(header) {
    header.classList.toggle('active');
    var content = header.nextElementSibling;
    content.classList.toggle('active');
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.poll-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            var pollId = this.getAttribute('data-poll-id');
            var csrfToken = this.querySelector('input[name="csrf_token"]').value;
            var selectedOptions = Array.from(this.querySelectorAll('input[name="poll_option[]"]:checked')).map(function(el) {
                return parseInt(el.value, 10);
            });

            if (selectedOptions.length === 0) {
                showAlert('Please select an option before voting.', 'danger');
                return;
            }

            var submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            var self = this;
            apiCall('/api/poll-vote.php', 'POST', {
                poll_id: parseInt(pollId, 10),
                option_ids: selectedOptions,
                csrf_token: csrfToken
            }, function(err, response) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Vote';

                if (err) {
                    showAlert(err.message || 'Failed to submit vote.', 'danger');
                } else {
                    showAlert('Vote submitted successfully!', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            });
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
