<!-- Entity Comments Section - Reusable Partial -->
<!-- Used by all entity maintenance screens -->

<?php
/**
 * Entity Comments Partial
 *
 * Required variables:
 * - $entityName (string): e.g., 'Agent', 'Driver', 'Owner'
 * - $entityKey (string): e.g., 'AgentKey', 'DriverKey'
 * - $entity (array): Current entity data
 */
$entityLower = strtolower($entityName);
$entityKeyLower = strtolower($entityKey);
?>

<div class="tls-form-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi-chat-left-text me-2"></i>Comments
        </h5>
        <button type="button" class="btn btn-sm tls-btn-primary" onclick="showCommentModal()">
            <i class="bi-plus me-1"></i> Add Comment
        </button>
    </div>
    <div class="card-body">
        <div id="comments-list">
            <p class="text-muted">Loading comments...</p>
        </div>
    </div>
</div>

<!-- Comment Modal -->
<div class="modal fade" id="commentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentModalLabel">Add Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="comment-modal-form">
                    <input type="hidden" id="comment-key" value="0">
                    <div class="mb-3">
                        <label for="comment-text" class="form-label">Comment</label>
                        <textarea class="form-control" id="comment-text" rows="4" maxlength="255" required></textarea>
                        <small class="form-text text-muted">Maximum 255 characters</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn tls-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn tls-btn-primary" onclick="saveComment()">Save Comment</button>
            </div>
        </div>
    </div>
</div>
