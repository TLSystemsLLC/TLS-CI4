<!-- Entity Contacts Section - Reusable Partial -->
<!-- Used by all entity maintenance screens -->

<?php
/**
 * Entity Contacts Partial
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
            <i class="bi-people me-2"></i>Contacts
            <span class="badge bg-primary ms-2" id="contact-count">0</span>
        </h5>
        <button type="button" class="btn btn-sm tls-btn-primary" onclick="showContactModal()">
            <i class="bi-plus me-1"></i> Add Contact
        </button>
    </div>
    <div class="card-body">
        <div id="contacts-grid">
            <p class="text-muted">Loading contacts...</p>
        </div>
    </div>
</div>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">Add Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="contact-modal-form">
                    <input type="hidden" id="contact-key" value="0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="contact-name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact-name" maxlength="50" required>
                        </div>
                        <div class="col-md-6">
                            <label for="contact-function" class="form-label">Function</label>
                            <select class="form-select" id="contact-function">
                                <option value="">-- Select --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="contact-telephone" class="form-label">Telephone</label>
                            <input type="tel" class="form-control" id="contact-telephone" maxlength="20">
                        </div>
                        <div class="col-md-6">
                            <label for="contact-cell" class="form-label">Cell Phone</label>
                            <input type="tel" class="form-control" id="contact-cell" maxlength="20">
                        </div>
                        <div class="col-md-12">
                            <label for="contact-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="contact-email" maxlength="100">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="contact-primary">
                                <label class="form-check-label" for="contact-primary">Primary Contact</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn tls-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn tls-btn-primary" onclick="saveContact()">Save Contact</button>
            </div>
        </div>
    </div>
</div>
