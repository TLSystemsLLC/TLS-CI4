/**
 * TLS Entity Maintenance - Common JavaScript
 *
 * Generic JavaScript that works for ALL entity maintenance screens.
 * Configurable via TLSEntityMaintenance.init({ entityName, entityKey, baseUrl })
 *
 * Handles:
 * - Autocomplete search
 * - Form tracking
 * - Address management (AJAX)
 * - Contact management (AJAX)
 * - Comment management (AJAX)
 * - New entity creation
 *
 * @version 1.0
 */

const TLSEntityMaintenance = {
    // Configuration
    config: {
        entityName: null,      // e.g., 'Driver', 'Agent', 'Owner'
        entityKey: null,       // e.g., 'DriverKey', 'AgentKey'
        baseUrl: null,         // e.g., 'safety/driver-maintenance'
        apiType: null          // e.g., 'drivers', 'agents'
    },

    // State
    currentEntityKey: 0,
    autocomplete: null,
    formTracker: null,
    contactModal: null,
    commentModal: null,
    contactFunctionOptions: {},

    /**
     * Initialize the entity maintenance screen
     */
    init(options) {
        // If baseUrl is a full URL (starts with http), extract just the path
        let baseUrl = options.baseUrl;
        if (baseUrl && baseUrl.startsWith('http')) {
            try {
                const url = new URL(baseUrl);
                baseUrl = url.pathname; // Extract just the path part
            } catch (e) {
                console.error('Failed to parse baseUrl:', e);
            }
        }

        this.config = {
            ...this.config,
            ...options,
            baseUrl: baseUrl,
            apiType: options.apiType || (options.entityName.toLowerCase() + 's')
        };

        console.log('TLSEntityMaintenance initialized:', this.config);

        // Wait for DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    },

    /**
     * Setup after DOM ready
     */
    setup() {
        // Initialize modals
        const contactModalEl = document.getElementById('contactModal');
        const commentModalEl = document.getElementById('commentModal');

        if (contactModalEl) {
            this.contactModal = new bootstrap.Modal(contactModalEl);
        }
        if (commentModalEl) {
            this.commentModal = new bootstrap.Modal(commentModalEl);
        }

        // Initialize autocomplete
        this.initAutocomplete();

        // Initialize form tracker (only if entity form exists)
        const entityForm = document.getElementById('entity-form');
        if (entityForm && typeof TLSFormTracker !== 'undefined') {
            this.formTracker = new TLSFormTracker({
                formSelector: '#entity-form',
                saveButtonId: 'tls-save-btn',
                resetButtonId: 'tls-reset-btn',
                saveIndicatorId: 'unsaved-changes-alert',
                excludeFields: [],
                onSave: () => {
                    entityForm.submit();
                }
            });
        }

        // Load contact function options
        this.loadContactFunctionOptions();

        // Load entity data if entity is loaded
        const entityKeyField = document.querySelector(`input[name="${this.config.entityKey.toLowerCase()}"]`);
        if (entityKeyField) {
            this.currentEntityKey = parseInt(entityKeyField.value) || 0;
            if (this.currentEntityKey > 0) {
                this.loadEntityData();
            }
        }
    },

    /**
     * Initialize autocomplete for entity search
     */
    initAutocomplete() {
        const searchField = document.getElementById(this.config.entityKey.toLowerCase());
        if (searchField && typeof TLSAutocomplete !== 'undefined') {
            this.autocomplete = new TLSAutocomplete(
                searchField,
                this.config.apiType,
                (entity) => {
                    console.log(this.config.entityName + ' selected:', entity);
                    searchField.value = entity.value;
                    document.getElementById('searchForm').submit();
                }
            );
        }
    },

    /**
     * Load all entity data (address, contacts, comments)
     */
    loadEntityData() {
        console.log('Loading data for ' + this.config.entityName + ':', this.currentEntityKey);
        this.loadAddress();
        this.loadContacts();
        this.loadComments();
    },

    // ==================== NEW ENTITY ====================

    /**
     * Create new entity
     */
    newEntity() {
        if (this.formTracker && this.formTracker.hasChanges()) {
            if (!confirm('You have unsaved changes. Are you sure you want to create a new ' + this.config.entityName.toLowerCase() + '? All unsaved changes will be lost.')) {
                return;
            }
        }

        if (!confirm('Create a new ' + this.config.entityName.toLowerCase() + '?')) {
            return;
        }

        fetch(`${this.config.baseUrl}/create-new`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data[this.config.entityKey.toLowerCase()]) {
                window.location.href = `/${this.config.baseUrl}/load/${data[this.config.entityKey.toLowerCase()]}`;
            } else {
                alert('Failed to create new ' + this.config.entityName.toLowerCase() + ': ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error creating new ' + this.config.entityName.toLowerCase() + ':', error);
            alert('Error creating new ' + this.config.entityName.toLowerCase());
        });
    },

    // ==================== ADDRESS MANAGEMENT ====================

    /**
     * Load address
     */
    loadAddress() {
        const url = `/${this.config.baseUrl}/get-address?${this.config.entityKey.toLowerCase()}=${this.currentEntityKey}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.address) {
                    this.displayAddress(data.address);
                } else {
                    document.getElementById('address-display').innerHTML = '<p class="text-muted"><em>No address on file</em></p>';
                }
            })
            .catch(error => {
                console.error('Error loading address:', error);
                document.getElementById('address-display').innerHTML = '<p class="text-danger"><em>Error loading address</em></p>';
            });
    },

    /**
     * Display address
     */
    displayAddress(address) {
        const html = `
            <div class="address-display">
                <p class="mb-1"><strong>${address.Name1 || ''}</strong></p>
                ${address.Name2 ? `<p class="mb-1">${address.Name2}</p>` : ''}
                <p class="mb-1">${address.Address1 || ''}</p>
                ${address.Address2 ? `<p class="mb-1">${address.Address2}</p>` : ''}
                <p class="mb-1">${address.City || ''}, ${address.State || ''} ${address.Zip || ''}</p>
                ${address.Phone ? `<p class="mb-0"><i class="bi-telephone me-1"></i>${address.Phone}</p>` : ''}
            </div>
        `;
        document.getElementById('address-display').innerHTML = html;

        // Store for editing
        this.currentAddress = address;
    },

    /**
     * Edit address
     */
    editAddress() {
        if (!this.currentAddress) {
            this.currentAddress = { NameKey: 0 };
        }

        document.getElementById('address-name-key').value = this.currentAddress.NameKey || 0;
        document.getElementById('address-name1').value = this.currentAddress.Name1 || '';
        document.getElementById('address-name2').value = this.currentAddress.Name2 || '';
        document.getElementById('address-address1').value = this.currentAddress.Address1 || '';
        document.getElementById('address-address2').value = this.currentAddress.Address2 || '';
        document.getElementById('address-city').value = this.currentAddress.City || '';
        document.getElementById('address-state').value = this.currentAddress.State || '';
        document.getElementById('address-zip').value = this.currentAddress.Zip || '';
        document.getElementById('address-phone').value = this.currentAddress.Phone || '';

        document.getElementById('address-display').style.display = 'none';
        document.getElementById('address-actions').style.display = 'none';
        document.getElementById('address-edit').style.display = 'block';
    },

    /**
     * Cancel edit address
     */
    cancelEditAddress() {
        document.getElementById('address-display').style.display = 'block';
        document.getElementById('address-actions').style.display = 'block';
        document.getElementById('address-edit').style.display = 'none';
    },

    /**
     * Save address
     */
    saveAddress() {
        const formData = new FormData();
        formData.append(this.config.entityKey.toLowerCase(), this.currentEntityKey);
        formData.append('name_key', document.getElementById('address-name-key').value);
        formData.append('name1', document.getElementById('address-name1').value);
        formData.append('name2', document.getElementById('address-name2').value);
        formData.append('address1', document.getElementById('address-address1').value);
        formData.append('address2', document.getElementById('address-address2').value);
        formData.append('city', document.getElementById('address-city').value);
        formData.append('state', document.getElementById('address-state').value);
        formData.append('zip', document.getElementById('address-zip').value);
        formData.append('phone', document.getElementById('address-phone').value);

        fetch(`${this.config.baseUrl}/save-address`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.cancelEditAddress();
                if (data.address) {
                    this.displayAddress(data.address);
                }
                alert('Address saved successfully');
            } else {
                alert('Error saving address: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error saving address:', error);
            alert('Error saving address');
        });
    },

    // ==================== CONTACT MANAGEMENT ====================

    /**
     * Load contact function options
     */
    loadContactFunctionOptions() {
        fetch(`${this.config.baseUrl}/get-contact-function-options`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.options) {
                    this.contactFunctionOptions = data.options;
                    this.populateContactFunctionDropdown();
                }
            })
            .catch(error => {
                console.error('Error loading contact function options:', error);
            });
    },

    /**
     * Populate contact function dropdown
     */
    populateContactFunctionDropdown() {
        const select = document.getElementById('contact-function');
        if (!select) return;

        select.innerHTML = '<option value="">-- Select --</option>';
        for (const [code, description] of Object.entries(this.contactFunctionOptions)) {
            const option = document.createElement('option');
            option.value = code;
            option.textContent = description;
            select.appendChild(option);
        }
    },

    /**
     * Load contacts
     */
    loadContacts() {
        const url = `/${this.config.baseUrl}/get-contacts?${this.config.entityKey.toLowerCase()}=${this.currentEntityKey}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.displayContacts(data.contacts || []);
                }
            })
            .catch(error => {
                console.error('Error loading contacts:', error);
                document.getElementById('contacts-grid').innerHTML = '<p class="text-danger">Error loading contacts</p>';
            });
    },

    /**
     * Display contacts
     */
    displayContacts(contacts) {
        document.getElementById('contact-count').textContent = contacts.length;

        if (contacts.length === 0) {
            document.getElementById('contacts-grid').innerHTML = '<p class="text-muted"><em>No contacts</em></p>';
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-sm table-hover"><thead><tr>';
        html += '<th>Name</th><th>Function</th><th>Phone</th><th>Email</th><th>Actions</th></tr></thead><tbody>';

        contacts.forEach(contact => {
            const functionDesc = this.contactFunctionOptions[contact.ContactFunction] || contact.ContactFunction;
            html += `<tr>
                <td>${contact.ContactName || ''} ${contact.PrimaryContact ? '<span class="badge bg-primary">Primary</span>' : ''}</td>
                <td>${functionDesc || ''}</td>
                <td>${contact.TelephoneNo || contact.CellNo || ''}</td>
                <td>${contact.Email || ''}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="TLSEntityMaintenance.editContact(${contact.ContactKey})">
                        <i class="bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="TLSEntityMaintenance.deleteContact(${contact.ContactKey})">
                        <i class="bi-trash"></i>
                    </button>
                </td>
            </tr>`;
        });

        html += '</tbody></table></div>';
        document.getElementById('contacts-grid').innerHTML = html;
    },

    /**
     * Show contact modal for new contact
     */
    showContactModal() {
        document.getElementById('contactModalLabel').textContent = 'Add Contact';
        document.getElementById('contact-key').value = '0';
        document.getElementById('contact-name').value = '';
        document.getElementById('contact-function').value = '';
        document.getElementById('contact-telephone').value = '';
        document.getElementById('contact-cell').value = '';
        document.getElementById('contact-email').value = '';
        document.getElementById('contact-primary').checked = false;

        this.contactModal.show();
    },

    /**
     * Edit contact
     */
    editContact(contactKey) {
        // Find contact in current data
        fetch(`${this.config.baseUrl}/get-contacts?${this.config.entityKey.toLowerCase()}=${this.currentEntityKey}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const contact = data.contacts.find(c => c.ContactKey == contactKey);
                    if (contact) {
                        document.getElementById('contactModalLabel').textContent = 'Edit Contact';
                        document.getElementById('contact-key').value = contact.ContactKey;
                        document.getElementById('contact-name').value = contact.ContactName || '';
                        document.getElementById('contact-function').value = contact.ContactFunction || '';
                        document.getElementById('contact-telephone').value = contact.TelephoneNo || '';
                        document.getElementById('contact-cell').value = contact.CellNo || '';
                        document.getElementById('contact-email').value = contact.Email || '';
                        document.getElementById('contact-primary').checked = contact.PrimaryContact == 1;

                        this.contactModal.show();
                    }
                }
            });
    },

    /**
     * Save contact
     */
    saveContact() {
        const contactName = document.getElementById('contact-name').value.trim();
        if (!contactName) {
            alert('Please enter a contact name');
            return;
        }

        const formData = new FormData();
        formData.append(this.config.entityKey.toLowerCase(), this.currentEntityKey);
        formData.append('contact_key', document.getElementById('contact-key').value);
        formData.append('contact_name', contactName);
        formData.append('contact_function', document.getElementById('contact-function').value);
        formData.append('telephone_no', document.getElementById('contact-telephone').value);
        formData.append('cell_no', document.getElementById('contact-cell').value);
        formData.append('email', document.getElementById('contact-email').value);
        formData.append('primary_contact', document.getElementById('contact-primary').checked ? '1' : '0');

        fetch(`${this.config.baseUrl}/save-contact`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.contactModal.hide();
                this.loadContacts();
                alert('Contact saved successfully');
            } else {
                alert('Error saving contact: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error saving contact:', error);
            alert('Error saving contact');
        });
    },

    /**
     * Delete contact
     */
    deleteContact(contactKey) {
        if (!confirm('Delete this contact?')) {
            return;
        }

        const formData = new FormData();
        formData.append('contact_key', contactKey);

        fetch(`${this.config.baseUrl}/delete-contact`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.loadContacts();
                alert('Contact deleted successfully');
            } else {
                alert('Error deleting contact: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting contact:', error);
            alert('Error deleting contact');
        });
    },

    // ==================== COMMENT MANAGEMENT ====================

    /**
     * Load comments
     */
    loadComments() {
        const url = `/${this.config.baseUrl}/get-comments?${this.config.entityKey.toLowerCase()}=${this.currentEntityKey}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.displayComments(data.comments || []);
                }
            })
            .catch(error => {
                console.error('Error loading comments:', error);
                document.getElementById('comments-list').innerHTML = '<p class="text-danger">Error loading comments</p>';
            });
    },

    /**
     * Display comments
     */
    displayComments(comments) {
        if (comments.length === 0) {
            document.getElementById('comments-list').innerHTML = '<p class="text-muted"><em>No comments</em></p>';
            return;
        }

        let html = '';
        comments.forEach(comment => {
            html += `
                <div class="card mb-2">
                    <div class="card-body">
                        <p class="card-text">${comment.Comment || ''}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                By ${comment.CommentBy || 'Unknown'} on ${comment.CommentDate || ''}
                                ${comment.EditedBy ? `<br>Edited by ${comment.EditedBy} on ${comment.EditedDate}` : ''}
                            </small>
                            <div>
                                <button class="btn btn-sm btn-outline-primary" onclick="TLSEntityMaintenance.editComment(${comment.CommentKey})">
                                    <i class="bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="TLSEntityMaintenance.deleteComment(${comment.CommentKey})">
                                    <i class="bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        document.getElementById('comments-list').innerHTML = html;
    },

    /**
     * Show comment modal for new comment
     */
    showCommentModal() {
        document.getElementById('commentModalLabel').textContent = 'Add Comment';
        document.getElementById('comment-key').value = '0';
        document.getElementById('comment-text').value = '';

        this.commentModal.show();
    },

    /**
     * Edit comment
     */
    editComment(commentKey) {
        fetch(`${this.config.baseUrl}/get-comments?${this.config.entityKey.toLowerCase()}=${this.currentEntityKey}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const comment = data.comments.find(c => c.CommentKey == commentKey);
                    if (comment) {
                        document.getElementById('commentModalLabel').textContent = 'Edit Comment';
                        document.getElementById('comment-key').value = comment.CommentKey;
                        document.getElementById('comment-text').value = comment.Comment || '';

                        this.commentModal.show();
                    }
                }
            });
    },

    /**
     * Save comment
     */
    saveComment() {
        const commentText = document.getElementById('comment-text').value.trim();
        if (!commentText) {
            alert('Please enter a comment');
            return;
        }

        const formData = new FormData();
        formData.append(this.config.entityKey.toLowerCase(), this.currentEntityKey);
        formData.append('comment_key', document.getElementById('comment-key').value);
        formData.append('comment', commentText);

        fetch(`${this.config.baseUrl}/save-comment`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.commentModal.hide();
                this.loadComments();
                alert('Comment saved successfully');
            } else {
                alert('Error saving comment: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error saving comment:', error);
            alert('Error saving comment');
        });
    },

    /**
     * Delete comment
     */
    deleteComment(commentKey) {
        if (!confirm('Delete this comment?')) {
            return;
        }

        const formData = new FormData();
        formData.append('comment_key', commentKey);

        fetch(`${this.config.baseUrl}/delete-comment`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.loadComments();
                alert('Comment deleted successfully');
            } else {
                alert('Error deleting comment: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting comment:', error);
            alert('Error deleting comment');
        });
    }
};

// Global functions for onclick handlers
function newEntity() { TLSEntityMaintenance.newEntity(); }
function editAddress() { TLSEntityMaintenance.editAddress(); }
function cancelEditAddress() { TLSEntityMaintenance.cancelEditAddress(); }
function saveAddress() { TLSEntityMaintenance.saveAddress(); }
function showContactModal() { TLSEntityMaintenance.showContactModal(); }
function saveContact() { TLSEntityMaintenance.saveContact(); }
function showCommentModal() { TLSEntityMaintenance.showCommentModal(); }
function saveComment() { TLSEntityMaintenance.saveComment(); }
