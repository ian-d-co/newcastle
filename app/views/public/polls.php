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
            <?php foreach ($polls as $poll): ?>
                <?php
                $hasVoted = $poll['has_voted'] ?? false;
                $userVotes = $poll['user_votes'] ?? [];
                $isExpired = $poll['expires_at'] && strtotime($poll['expires_at']) < time();
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
                            <button type="submit" class="btn btn-primary btn-block mt-3">Vote</button>
                        </form>
                    <?php else: ?>
                        <!-- Results -->
                        <div class="poll-results"></div>
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
                                    <span>Expires: <?php echo date('M j, Y g:i A', strtotime($poll['expires_at'])); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
