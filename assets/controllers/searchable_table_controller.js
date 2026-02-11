import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'row', 'sortIcon'];

    connect() {
        this.sortDirection = 1;
        this.lastSortedColumn = null;
    }

    search(event) {
        const query = this.inputTarget.value.toLowerCase();
        
        this.rowTargets.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    sort(event) {
        const th = event.currentTarget;
        const index = Array.from(th.parentNode.children).indexOf(th);
        const table = this.element.querySelector('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Toggle direction if clicking same column
        if (this.lastSortedColumn === index) {
            this.sortDirection *= -1;
        } else {
            this.sortDirection = 1;
            this.lastSortedColumn = index;
        }

        // Update icons (optional, basic text arrow)
        this.updateSortIcons(th, this.sortDirection);

        rows.sort((a, b) => {
            const cellA = a.children[index].textContent.trim().toLowerCase();
            const cellB = b.children[index].textContent.trim().toLowerCase();

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

        rows.forEach(row => tbody.appendChild(row));
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
        // Reset all headers
        this.element.querySelectorAll('th').forEach(th => {
            const icon = th.querySelector('.sort-icon');
            if(icon) icon.textContent = '⇅'; // Default
        });

        // Set active header
        const icon = activeTh.querySelector('.sort-icon');
        if(icon) icon.textContent = direction === 1 ? '↑' : '↓';
    }
}
