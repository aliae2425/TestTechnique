import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["element"];
    static values = {
        index: Number,
        prototype: String,
    }

    connect() {
        this.indexValue = this.elementTargets.length;
    }

    addElement(event) {
        event.preventDefault();

        const prototype = this.prototypeValue.replace(/__name__/g, this.indexValue);
        this.indexValue++;

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = prototype;
        const newElement = tempDiv.firstElementChild;
        newElement.setAttribute('data-form-collection-target', 'element');

        event.currentTarget.insertAdjacentElement('beforebegin', newElement);
    }

    removeElement(event) {
        event.preventDefault();
        const element = event.target.closest('[data-form-collection-target="element"]');
        if (element) {
            element.remove();
        }
    }
}
