import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
    static targets = ['modal', 'modalBody', 'title'];

    connect() {
        this.modal = new Modal(this.modalTarget);
    }

    async open(event) {
        event.preventDefault();
        
        const url = event.currentTarget.dataset.url;
        
        // Reset content and show loader
        this.modalBodyTarget.innerHTML = `
            <div class="text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
        `;
        
        this.modal.show();

        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Erreur réseau');
            
            const html = await response.text();
            this.modalBodyTarget.innerHTML = html;
        } catch (error) {
            this.modalBodyTarget.innerHTML = `
                <div class="alert alert-danger">
                    Erreur lors du chargement des détails : ${error.message}
                </div>
            `;
        }
    }
}
