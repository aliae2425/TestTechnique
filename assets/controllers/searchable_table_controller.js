import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'group', 'sortIcon'];

    connect() {
        this.sortDirection = 1;
        this.lastSortedColumn = null;
        
        // Prevent collapse from disappearing when sorting/searching by re-initializing bootstrap collapse if needed,
        // but primarily the issue is often that Stimulus disconnects/reconnects or DOM manipulation breaks event listeners.
        // However, with standard bootstrap data-bs attributes, it should work fine unless the row is removed from DOM.
        // The issue "preview disappears" likely means the content is not rendered correctly or CSS issue.
        // Or if Turbo is involved, ensure it's compatible.
    }

    search(event) {
        const query = this.inputTarget.value.toLowerCase();
        
        this.groupTargets.forEach(group => {
            // Search only in the first row (summary) to avoid matching implementation details if hidden
            const firstRow = group.querySelector('tr');
            if (!firstRow) return;

            const text = firstRow.textContent.toLowerCase();
            if (text.includes(query)) {
                group.style.display = '';
            } else {
                group.style.display = 'none';
            }
        });
    }

    sort(event) {
        const th = event.currentTarget;
        const index = Array.from(th.parentNode.children).indexOf(th);
        const table = this.element.querySelector('table');
        
        // Toggle direction if clicking same column
        if (this.lastSortedColumn === index) {
            this.sortDirection *= -1;
        } else {
            this.sortDirection = 1;
            this.lastSortedColumn = index;
        }

        this.updateSortIcons(th, this.sortDirection);

        const groups = Array.from(this.groupTargets);
        
        // Store open states
        const openStates = {};
        groups.forEach(group => {
            const collapse = group.querySelector('.collapse');
            if (collapse && collapse.classList.contains('show')) {
                openStates[collapse.id] = true;
            }
        });

        const sortedGroups = groups.sort((a, b) => {
            const rowA = a.querySelector('tr');
            const rowB = b.querySelector('tr');

            // If no rows (shouldn't happen), treat as equal
            if (!rowA || !rowB) return 0;

            const cellA = rowA.children[index].textContent.trim().toLowerCase();
            const cellB = rowB.children[index].textContent.trim().toLowerCase();

            // Check if date (DD/MM/YYYY HH:MM)
            const dateA = this.parseDate(cellA);
            const dateB = this.parseDate(cellB);

            if (dateA && dateB) {
                return (dateA - dateB) * this.sortDirection;
            }

            // Check if number (Score %)
            const numA = parseFloat(cellA);
            const numB = parseFloat(cellB);
            
            if (!isNaN(numA) && !isNaN(numB)) {
                return (numA - numB) * this.sortDirection;
            }

            return cellA.localeCompare(cellB) * this.sortDirection;
        });

        // Re-append to DOM
        sortedGroups.forEach(group => table.appendChild(group));

        // Restore open states is not strictly necessary if we just move elements, 
        // but if Bootstrap loses listeners, we might need to re-initialize or trust data-bs attributes.
        // Moving DOM nodes usually preserves their state (including classList 'show').
    }

    parseDate(str) {
        // Format: d/m/Y H:i
        const parts = str.match(/(\d{2})\/(\d{2})\/(\d{4})\s(\d{2}):(\d{2})/);
        if (parts) {
            return new Date(parts[3], parts[2] - 1, parts[1], parts[4], parts[5]);
        }
        return null;
    }

    updateSortIcons(activeTh, direction) {
        const thead = activeTh.closest('thead');
        thead.querySelectorAll('th').forEach(th => {
            const icon = th.querySelector('.sort-icon');
            if(icon) icon.textContent = '⇅'; 
        });

        const icon = activeTh.querySelector('.sort-icon');
        if(icon) icon.textContent = direction === 1 ? '↑' : '↓';
    }
}
