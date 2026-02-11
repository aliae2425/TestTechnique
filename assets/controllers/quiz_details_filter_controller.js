import { Controller } from '@hotwired/stimulus';

const CLS_DEFAULT = ['bg-primary', 'text-primary-foreground', 'hover:bg-primary/90'];
const CLS_OUTLINE = ['border', 'bg-background', 'shadow-xs', 'hover:bg-accent', 'hover:text-accent-foreground'];

export default class extends Controller {
    static targets = ['item', 'filterBtn'];

    connect() {
        console.log('Quiz Details Filter Controller connected');
        // Default filter: all (managed by template for initial state, but let's consistency check?)
        // Actually, the template sets the initial classes. 
        // We just need to ensure javascript filtering logic aligns.
        // this.filter('all'); // This might hide things if DOM isn't ready or logic differs.
        
        // Let's not force 'filter' on connect if the HTML is already rendered correctly? 
        // But the items need to be filtered initially if not all are shown.
        // In our case, "all" are shown by default. So it's fine.
        this.filter('all');
    }

    filterAll(event) {
        event.preventDefault();
        this.setActive(event.currentTarget);
        this.filter('all');
    }

    filterCorrect(event) {
        event.preventDefault();
        this.setActive(event.currentTarget);
        this.filter('correct');
    }

    filterIncorrect(event) {
        event.preventDefault();
        this.setActive(event.currentTarget);
        this.filter('incorrect');
    }

    filter(status) {
        this.itemTargets.forEach(item => {
            if (status === 'all' || item.dataset.status === status) {
                // Remove 'hidden' class if using Tailwind, or display property
                // item.style.display = 'block';
                item.classList.remove('hidden');
            } else {
                // item.style.display = 'none';
                item.classList.add('hidden');
            }
        });
    }

    setActive(clickedBtn) {
        this.filterBtnTargets.forEach(btn => {
            // Reset all to Outline
            btn.classList.add(...CLS_OUTLINE);
            btn.classList.remove(...CLS_DEFAULT);
        });
        
        // Set clicked to Default
        clickedBtn.classList.remove(...CLS_OUTLINE);
        clickedBtn.classList.add(...CLS_DEFAULT);
    }
}
