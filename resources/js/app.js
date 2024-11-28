/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import { createApp } from 'vue/dist/vue.esm-bundler.js';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faChevronUp, faChevronDown } from '@fortawesome/free-solid-svg-icons';

// Add icons to the library
library.add(faChevronUp, faChevronDown);


//import './bootstrap';
//import Alpine from 'alpinejs' ;
//window.Alpine = Alpine;
//Alpine.start();

import '../css/app.css';

// import vue components
import TaskCreator from './components/TaskCreator.vue'; // Import your new component
import ProductModal from './components/productModal.vue'; // Import your new component
import LinkMaterialsModal from './components/LinkMaterialsModal.vue'; // Import your new LinkMaterialsModal component

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

// Create the Vue application instance
const app = createApp({
    data() {
        return {
            showModal: false,
            showLinkMaterialsModal: false, // Add this for managing the LinkMaterialsModal state
            selectedProductId: null, // Track the selected product ID
            userId: null,
            teamId: null,
        };
    },
    async created() {
        // Get user ID and team ID from the DOM (assuming they are added as data attributes)
        const appElement = document.querySelector("#app");
        if (appElement) {
            this.userId = Number(appElement.getAttribute('data-user-id'));
            this.teamId = Number(appElement.getAttribute('data-team-id'));
        }
    },
    methods: {
        // Handles the product creation modal
        handleProductCreated(newProduct) {
            console.log('New product created:', newProduct);
            this.showModal = false;
            // Handle further actions after product creation
        },
        toggleModal() {
            this.showModal = !this.showModal;
        },
        // New method to handle closing the LinkMaterialsModal
        handleMaterialsLinked() {
            console.log('Materials linked successfully');
            this.showLinkMaterialsModal = false;
            this.refreshPage(); // Refresh the page after linking materials
            // Perform any additional actions after linking materials
        },
        openLinkMaterialsModal(productId) {
            if (productId) {
                this.selectedProductId = productId; // Set the selected product ID
                this.showLinkMaterialsModal = true; // Show the modal
            } else {
                alert('No product selected for linking materials.');
            }
        },
        refreshPage() {
            console.log('Refreshing the page...');
            window.location.reload();
        },

        // New submitForm method to handle form submissions from task-creator
        submitForm({ route, data }) {
            console.log('submitForm called with data:', data);

            // Perform the actual form submission
            axios.post(route, data)
                .then(response => {
                    console.log('Form submitted successfully:', response.data);
                    // Redirect or handle success
                    window.location.href = `/projects/${data.project_id}`;
                })
                .catch(error => {
                    console.error('Error submitting form:', error);
                });
        }
    }
});

// Register components globally
app.component('task-creator', TaskCreator);
app.component('product-modal', ProductModal);
app.component('link-materials-modal', LinkMaterialsModal); // Register your new LinkMaterialsModal component
app.component('font-awesome-icon', FontAwesomeIcon);

// Mount the Vue instance if #app exists
if (document.querySelector("#app")) {
    app.mount("#app");
}
