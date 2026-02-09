import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["container", "cell", "question", "feedback", "prevButton", "nextButton", "submitButton", "timer"];
    static values = {
        mode: String, // 'training' or 'Exam'
        timeLimit: Number
    }

    connect() {
        this.currentIndex = 0;
        this.showQuestion(0);
        
        if (this.hasTimeLimitValue && this.timeLimitValue > 0) {
            this.startTimer(this.timeLimitValue);
        }
    }

    startTimer(seconds) {
        this.updateTimerDisplay(seconds);
        
        this.timerInterval = setInterval(() => {
            seconds--;
            this.updateTimerDisplay(seconds);
            
            if (seconds <= 0) {
                clearInterval(this.timerInterval);
                this.forceSubmit();
            }
            // Warning style when less than 1 minute
            if (seconds <= 60 && this.hasTimerTarget) {
                this.timerTarget.classList.add('text-red-600', 'animate-pulse', 'font-bold');
            }
        }, 1000);
    }

    updateTimerDisplay(seconds) {
        if (!this.hasTimerTarget) return;
        
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        this.timerTarget.textContent = `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
    }

    forceSubmit() {
        // Find the form and submit it
        const form = this.element.querySelector('form');
        if (form) {
             // Create a hidden input to indicate forced submission implies timeout if needed, 
             // but strictly just submitting is enough usually.
            form.requestSubmit();
        }
    }

    disconnect() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
        }
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
            
            // Auto-advance to next question
            setTimeout(() => {
                this.next();
            }, 300);
        } else {
            // Training Mode
            this.handleTrainingValidation(questionContainer, questionId, cell);
            
            // Lock inputs for this question to prevent changing answer
            const inputs = questionContainer.querySelectorAll('input[type="checkbox"], input[type="radio"]');
            inputs.forEach(inp => {
                // Prevent further changes without disabling (so POST data remains)
                inp.addEventListener('click', (e) => e.preventDefault());
                inp.closest('label').classList.add('cursor-not-allowed', 'opacity-75');
            });
        }
    }

    handleTrainingValidation(container, questionId, cell) {
        // Find all inputs for this question (support both checkbox and radio)
        const inputs = container.querySelectorAll('input[type="checkbox"], input[type="radio"]');
        const feedbackBlocks = container.querySelectorAll('[data-quiz-target="feedback"]');

        // Reset all feedback blocks to hidden initially and remove color classes
        feedbackBlocks.forEach(fb => {
            fb.classList.add('hidden');
            fb.classList.remove('text-red-600', 'bg-red-50', 'border-red-100', 'text-emerald-600', 'bg-emerald-50', 'border-emerald-100');
        });
        
        let allCorrect = true;     // Flag: Did user make ANY mistake?
        let userHasSelectedAllCorrect = true; // Flag: Did user find ALL correct answers?
        let anySelected = false;

        // 1. Check user selections
        inputs.forEach(input => {
            const label = input.closest('label');
            // Clean up potentially conflicting classes from previous runs
            label.classList.remove(
                'bg-emerald-50', 'bg-emerald-100', 'border-emerald-500', 'ring-emerald-500', 'text-emerald-800', 'text-emerald-900',
                'bg-rose-50', 'bg-rose-100', 'border-rose-500', 'ring-rose-500', 'text-rose-800', 'text-rose-900',
                'ring-2'
            );
            
            if (input.checked) {
                anySelected = true;
                if (input.dataset.isCorrect !== "true") {
                    allCorrect = false;
                }
            }
        });

        // Show feedback if any answer is selected
        if (anySelected) {
             inputs.forEach(input => {
                 const isCorrect = input.dataset.isCorrect === "true";
                 const label = input.closest('label');
                 const feedback = label.querySelector('[data-quiz-target="feedback"]');
                 
                 // Reset base ring/bg classes first to ensure clean state
                 label.classList.remove('ring-1', 'ring-slate-200', 'hover:bg-slate-50', 'bg-white');

                 // Apply Label Color for Correct OR Wrong-but-Selected
                 if (isCorrect) {
                     // GREEN: Correct Answer
                     label.classList.add('bg-emerald-50', 'ring-2', 'ring-emerald-500', 'text-emerald-900');
                     if (feedback) feedback.classList.remove('hidden');
                 } 
                 else if (input.checked) {
                     // RED: Wrong Choice
                     label.classList.add('bg-rose-50', 'ring-2', 'ring-rose-500', 'text-rose-900');
                     if (feedback) feedback.classList.remove('hidden');
                 } else {
                     // Default unused state (fix for non-selected wrong answers staying "white")
                     label.classList.add('bg-white', 'ring-1', 'ring-slate-200');
                 }
             });
        }

        // 2. Check if all correct answers are found
        const totalCorrectAnswers = container.querySelectorAll('input[data-is-correct="true"]').length;
        const userSelectedCorrectAnswers = container.querySelectorAll('input[data-is-correct="true"]:checked').length;
        
        if (userSelectedCorrectAnswers < totalCorrectAnswers) {
            userHasSelectedAllCorrect = false;
        }

        // Determine Cell Color
        cell.classList.remove('bg-slate-200', 'bg-emerald-500', 'bg-rose-500', 'bg-amber-400', 'shadow-[0_0_10px_rgba(244,63,94,0.6)]', 'shadow-[0_0_10px_rgba(52,211,153,0.6)]');
        
        if (!anySelected) {
            cell.classList.add('bg-slate-200'); 
            return;
        }


        if (allCorrect && userHasSelectedAllCorrect) {
            // PERFECT
            cell.classList.add('bg-emerald-500', 'shadow-[0_0_10px_rgba(52,211,153,0.6)]');
        } 
        else if (!allCorrect) {
            // MISTAKE MADE
            cell.classList.add('bg-rose-500', 'shadow-[0_0_10px_rgba(244,63,94,0.6)]');
            
            // Reveal missed correct answers (Visual Hint)
            inputs.forEach(input => {
                 if (input.dataset.isCorrect === "true" && !input.checked) {
                     input.closest('label').classList.add('border-emerald-400', 'border-dashed', 'bg-emerald-50/50');
                 }
            });
        } 
        else {
            // PARTIAL
            cell.classList.add('bg-amber-400');
        }
    }
}
