import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["container", "cell", "question", "feedback", "prevButton", "nextButton", "submitButton"];
    static values = {
        mode: String // 'training' or 'Exam'
    }

    connect() {
        this.currentIndex = 0;
        this.showQuestion(0);
    }

    showQuestion(index) {
        index = parseInt(index);
        this.currentIndex = index;

        // Toggle visibility
        this.questionTargets.forEach((el, i) => {
            if (i === index) {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });

        // Update Active Cell State
        this.cellTargets.forEach((cell, i) => {
            if (i === index) {
                cell.classList.add('scale-125', 'bg-indigo-600', 'ring-2', 'ring-indigo-300');
                cell.classList.remove('bg-slate-200');
            } else {
                cell.classList.remove('scale-125', 'bg-indigo-600', 'ring-2', 'ring-indigo-300');
                // Restore status color if validated, or default
                if (!cell.classList.contains('bg-emerald-500') && !cell.classList.contains('bg-rose-500') && !cell.classList.contains('bg-amber-400') && !cell.classList.contains('bg-sky-500')) {
                    cell.classList.add('bg-slate-200');
                }
            }
        });

        this.updateNavigation();
    }

    jumpToQuestion(event) {
        const index = event.currentTarget.dataset.index;
        this.showQuestion(index);
    }

    next() {
        if (this.currentIndex < this.questionTargets.length - 1) {
            this.showQuestion(this.currentIndex + 1);
        }
    }

    previous() {
        if (this.currentIndex > 0) {
            this.showQuestion(this.currentIndex - 1);
        }
    }

    updateNavigation() {
        // Toggle Prev Button
        if (this.hasPrevButtonTarget) {
            this.prevButtonTarget.classList.toggle('invisible', this.currentIndex === 0);
        }

        // Toggle Next/Submit
        const isLast = this.currentIndex === this.questionTargets.length - 1;
        if (this.hasNextButtonTarget) {
            this.nextButtonTarget.classList.toggle('hidden', isLast);
        }
        if (this.hasSubmitButtonTarget) {
            this.submitButtonTarget.classList.toggle('hidden', !isLast);
        }
    }

    validate(event) {
        const input = event.target;
        const questionId = input.name.replace('q_', '').replace('[]', '');
        const questionContainer = input.closest('[data-quiz-target="question"]');
        const index = this.questionTargets.indexOf(questionContainer);
        const cell = this.cellTargets[index];

        if (this.modeValue === 'Exam') {
            // Exam Mode: Just mark as answered (Blue)
            cell.classList.remove('bg-slate-200');
            cell.classList.add('bg-sky-500', 'shadow-[0_0_15px_rgba(14,165,233,0.6)]', 'border-sky-300');
        } else {
            // Training Mode
            this.handleTrainingValidation(questionContainer, questionId, cell);
        }
    }

    handleTrainingValidation(container, questionId, cell) {
        // Find all inputs for this question
        const inputs = container.querySelectorAll('input[type="checkbox"]');
        const feedbackBlocks = container.querySelectorAll('[data-quiz-target="feedback"]');
        
        let allCorrect = true;     // Flag: Did user make ANY mistake?
        let userHasSelectedAllCorrect = true; // Flag: Did user find ALL correct answers?
        let anySelected = false;

        // 1. Check user selections
        inputs.forEach(input => {
            const isCorrect = input.dataset.isCorrect === "true";
            const label = input.closest('label');
            
            // Reset basic classes
            label.classList.remove('bg-emerald-100', 'border-emerald-500', 'bg-rose-100', 'border-rose-500', 'text-emerald-800', 'text-rose-800');
            
            if (input.checked) {
                anySelected = true;
                if (isCorrect) {
                     label.classList.add('bg-emerald-100', 'border-emerald-500', 'text-emerald-800');
                } else {
                     label.classList.add('bg-rose-100', 'border-rose-500', 'text-rose-800');
                     allCorrect = false;
                }
            }
        });

        // 2. Check if all correct answers are found
        const totalCorrectAnswers = container.querySelectorAll('input[data-is-correct="true"]').length;
        const userSelectedCorrectAnswers = container.querySelectorAll('input[data-is-correct="true"]:checked').length;
        
        if (userSelectedCorrectAnswers < totalCorrectAnswers) {
            userHasSelectedAllCorrect = false;
        }

        // Determine Cell Color & Feedback Visibility
        cell.classList.remove('bg-slate-200', 'bg-emerald-500', 'bg-rose-500', 'bg-amber-400', 'shadow-[0_0_10px_rgba(244,63,94,0.6)]', 'shadow-[0_0_10px_rgba(52,211,153,0.6)]');
        
        if (!anySelected) {
            cell.classList.add('bg-slate-200'); 
            feedbackBlocks.forEach(fb => fb.classList.add('hidden'));
            return;
        }

        // Logic for feedback display:
        // Show feedback if user made a mistake OR if user found everything.
        // Don't show if partially correct but no mistakes (unless you want to guide them).
        
        if (allCorrect && userHasSelectedAllCorrect) {
            // PERFECT
            cell.classList.add('bg-emerald-500', 'shadow-[0_0_10px_rgba(52,211,153,0.6)]');
            feedbackBlocks.forEach(fb => fb.classList.remove('hidden'));
        } 
        else if (!allCorrect) {
            // MISTAKE MADE
            cell.classList.add('bg-rose-500', 'shadow-[0_0_10px_rgba(244,63,94,0.6)]');
            feedbackBlocks.forEach(fb => fb.classList.remove('hidden'));
            
            // Reveal missed correct answers
            inputs.forEach(input => {
                 if (input.dataset.isCorrect === "true" && !input.checked) {
                     input.closest('label').classList.add('border-emerald-400', 'border-dashed', 'bg-emerald-50/50');
                 }
            });
        } 
        else {
            // PARTIAL (Correct so far, but missing some)
            cell.classList.add('bg-amber-400');
            // Hide feedback until finished? Or show hint? Let's hide to encourage finding the rest.
            feedbackBlocks.forEach(fb => fb.classList.add('hidden')); 
        }
    }
}
