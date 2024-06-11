import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        console.error(this.element);
        this.element.querySelectorAll('.dropdown-item')
            .forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.element.querySelector('#address_address').value = btn.dataset.address;
                    this.element.querySelector('#address_zipCode').value = btn.dataset.zipCode;
                    this.element.querySelector('#address_city').value = btn.dataset.city;
                })

            }
            )
    }
}
