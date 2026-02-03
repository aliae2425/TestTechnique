import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["slide", "progress", "prevButton", "nextButton", "submitButton", "counter"];

    connect() {
        this.currentIndex = 0;
        this.showSlide(this.currentIndex);
        this.updateProgress();
    }

    next() {
        if (this.currentIndex < this.slideTargets.length - 1) {
            this.currentIndex++;
            this.showSlide(this.currentIndex);
            this.updateProgress();
        }
    }

    previous() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.showSlide(this.currentIndex);
            this.updateProgress();
        }
    }

    showSlide(index) {
        this.slideTargets.forEach((slide, i) => {
            slide.classList.toggle('hidden', i !== index);
        });

        // Update buttons visibility
        this.prevButtonTarget.classList.toggle('invisible', index === 0);
        
        if (index === this.slideTargets.length - 1) {
            this.nextButtonTarget.classList.add('hidden');
            this.submitButtonTarget.classList.remove('hidden');
        } else {
            this.nextButtonTarget.classList.remove('hidden');
            this.submitButtonTarget.classList.add('hidden');
        }
        
        // Update counter
        if (this.hasCounterTarget) {
            this.counterTarget.textContent = `Question ${index + 1} / ${this.slideTargets.length}`;
        }
    }

    updateProgress() {
        if (this.hasProgressTarget) {
            const progress = ((this.currentIndex + 1) / this.slideTargets.length) * 100;
            this.progressTarget.style.width = `${progress}%`;
        }
    }
}
