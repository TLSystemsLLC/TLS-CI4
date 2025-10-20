/**
 * TLS Autocomplete Component
 * CodeIgniter 4 Version
 *
 * Provides autocomplete functionality for TLS forms with dropdown selection,
 * keyboard navigation, and API integration.
 *
 * Usage:
 * const autocomplete = new TLSAutocomplete(inputElement, apiType, onSelectCallback);
 */
class TLSAutocomplete {
    constructor(inputElement, apiType, onSelect = null) {
        this.input = inputElement;
        this.apiType = apiType;
        this.onSelect = onSelect;
        this.resultsContainer = null;
        this.currentIndex = -1;
        this.searchTimeout = null;

        this.init();
    }
    
    init() {
        console.log('Initializing autocomplete for:', this.input.id, 'API type:', this.apiType);
        this.createResultsContainer();
        this.input.addEventListener('input', (e) => this.handleInput(e));
        this.input.addEventListener('keydown', (e) => this.handleKeydown(e));
        this.input.addEventListener('blur', (e) => this.handleBlur(e));
        this.input.addEventListener('focus', (e) => this.handleFocus(e));
    }
    
    createResultsContainer() {
        this.resultsContainer = document.createElement('div');
        this.resultsContainer.className = 'autocomplete-results position-absolute bg-white border rounded shadow-sm w-100';
        this.resultsContainer.style.top = '100%'; // Position below the input
        this.resultsContainer.style.left = '0';
        this.resultsContainer.style.zIndex = '1050';
        this.resultsContainer.style.maxHeight = '300px';
        this.resultsContainer.style.overflowY = 'auto';
        this.resultsContainer.style.display = 'none';

        // Find the nearest position-relative parent, or use input's parent
        let positionParent = this.input.closest('.position-relative');
        if (!positionParent) {
            positionParent = this.input.parentNode;
            positionParent.style.position = 'relative';
        }

        positionParent.appendChild(this.resultsContainer);
    }
    
    handleInput(e) {
        const query = e.target.value.trim();
        console.log('Input event for:', this.input.id, 'Query:', query);
        clearTimeout(this.searchTimeout);
        
        // Allow single character searches for numeric keys
        if (query.length < 1) {
            console.log('Empty query, hiding results');
            this.hideResults();
            this.updateStatus(this.getEmptyStatusMessage());
            return;
        }
        
        console.log('Setting timeout for search...');
        this.searchTimeout = setTimeout(() => {
            console.log('Executing search for:', query);
            this.search(query);
        }, 300);
    }
    
    handleKeydown(e) {
        const items = this.resultsContainer.querySelectorAll('.autocomplete-item');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.currentIndex = Math.min(this.currentIndex + 1, items.length - 1);
                this.updateSelection(items);
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.currentIndex = Math.max(this.currentIndex - 1, -1);
                this.updateSelection(items);
                break;
            case 'Enter':
                e.preventDefault();
                if (this.currentIndex >= 0 && items[this.currentIndex]) {
                    this.selectItem(JSON.parse(items[this.currentIndex].dataset.item));
                }
                break;
            case 'Escape':
                this.hideResults();
                break;
        }
    }
    
    handleBlur(e) {
        setTimeout(() => this.hideResults(), 150);
    }
    
    handleFocus(e) {
        if (e.target.value.trim().length >= 2) {
            this.search(e.target.value.trim());
        }
    }
    
    updateSelection(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === this.currentIndex);
        });
    }
    
    search(query) {
        // Build API URL based on type - CI4 routes
        let url = '';
        switch (this.apiType) {
            case 'users':
                url = `/tls-ci4/systems/user-maintenance/autocomplete?term=${encodeURIComponent(query)}`;
                break;
            case 'agents':
                url = `/tls-ci4/safety/agent-maintenance/autocomplete?term=${encodeURIComponent(query)}`;
                break;
            default:
                console.error('Unknown API type:', this.apiType);
                return;
        }

        // Add include_inactive parameter if checkbox is checked
        const includeInactive = document.getElementById('includeInactive')?.checked || false;
        if (includeInactive) {
            url += '&include_inactive=1';
        }

        // Show loading indicator
        this.showLoading(true);
        this.updateStatus('Searching...');

        console.log('Fetching:', url);
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Search results:', data);
                this.showLoading(false);
                if (data.length === 0) {
                    this.updateStatus(this.getNoResultsMessage());
                } else {
                    this.updateStatus(this.getResultsMessage(data.length));
                }
                this.displayResults(data);
            })
            .catch(error => {
                console.error('Search error:', error);
                this.showLoading(false);
                this.updateStatus('Search error occurred');
                this.hideResults();
            });
    }
    
    displayResults(results) {
        this.resultsContainer.innerHTML = '';
        this.currentIndex = -1;

        if (results.length === 0) {
            const div = document.createElement('div');
            div.className = 'autocomplete-item p-2 text-muted';
            div.innerHTML = 'No results found';
            this.resultsContainer.appendChild(div);
            this.showResults();
            return;
        }

        results.forEach((item, index) => {
            const div = document.createElement('div');
            div.className = 'autocomplete-item p-2 border-bottom';
            div.style.cursor = 'pointer';
            // Show label with inactive badge if applicable
            div.innerHTML = item.label + (item.active === false ? ' <span class="badge bg-secondary">Inactive</span>' : '');
            div.dataset.item = JSON.stringify(item);

            div.addEventListener('click', () => {
                this.selectItem(item);
            });

            div.addEventListener('mouseenter', () => {
                this.currentIndex = index;
                this.updateSelection(this.resultsContainer.querySelectorAll('.autocomplete-item'));
            });

            this.resultsContainer.appendChild(div);
        });

        this.showResults();
    }

    selectItem(item) {
        console.log('Selecting item:', item);
        this.input.value = item.value || item.id;
        this.hideResults();
        this.updateStatus(this.getSelectedMessage(item));

        if (this.onSelect) {
            this.onSelect(item);
        }
    }
    
    showLoading(show) {
        const spinner = document.getElementById('search-spinner');
        if (spinner) {
            spinner.style.display = show ? 'block' : 'none';
        }
    }
    
    updateStatus(message) {
        const status = document.getElementById('search-status');
        if (status) {
            status.textContent = message;
        }
    }

    showResults() {
        this.resultsContainer.style.display = 'block';
    }
    
    hideResults() {
        this.resultsContainer.style.display = 'none';
        this.showLoading(false);
    }
    
    // Helper methods to generate appropriate messages based on API type
    getEmptyStatusMessage() {
        const typeMessages = {
            'drivers': 'Type driver name or DriverKey',
            'owners': 'Type owner name or OwnerKey',
            'customers': 'Type customer name or CustomerKey',
            'units': 'Type unit number or UnitKey',
            'agents': 'Type agent name or AgentKey'
        };
        return typeMessages[this.apiType] || 'Type to search';
    }

    getNoResultsMessage() {
        const typeMessages = {
            'drivers': 'No drivers found',
            'owners': 'No owners found',
            'customers': 'No customers found',
            'units': 'No units found',
            'agents': 'No agents found'
        };
        return typeMessages[this.apiType] || 'No results found';
    }

    getResultsMessage(count) {
        const typeMessages = {
            'drivers': `Found ${count} drivers`,
            'owners': `Found ${count} owners`,
            'customers': `Found ${count} customers`,
            'units': `Found ${count} units`,
            'agents': `Found ${count} agents`
        };
        return typeMessages[this.apiType] || `Found ${count} results`;
    }

    getSelectedMessage(item) {
        const typeMessages = {
            'drivers': `Driver selected: ${item.full_name || item.label}`,
            'owners': `Owner selected: ${item.full_name || item.label}`,
            'customers': `Customer selected: ${item.full_name || item.label}`,
            'units': `Unit selected: ${item.full_name || item.label}`,
            'agents': `Agent selected: ${item.label}`
        };
        return typeMessages[this.apiType] || `Selected: ${item.full_name || item.label}`;
    }
}