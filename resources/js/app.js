/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import { createApp } from 'vue/dist/vue.esm-bundler.js';

//import './bootstrap';
//import Alpine from 'alpinejs' ;
import axios from 'axios';
//window.Alpine = Alpine;
//Alpine.start();

import 'select2';
import '../css/app.css';

// import vue components
import TagEditor from './components/TagEditor.vue'; // Import your component
import TaskCreator from './components/TaskCreator.vue'; // Import your new component
import ProductModal from './components/productModal.vue'; // Import your new component

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

const app = createApp({
     data() {
         return {
             showModal: false,
         // products: [],
            userId: null,
            teamId: null, // Add teamId here
            products: [],
         };
     },
    async created() {
        console.log('created() called from app.js');
        const appElement = document.querySelector('#app');
        if (appElement) {
            this.userId = Number(appElement.getAttribute('data-user-id'));
            this.teamId = Number(appElement.getAttribute('data-team-id')); // Retrieve teamId

            console.log('User ID from app.js:', this.userId);
            console.log('Team ID from app.js:', this.teamId); // Log teamId

            if (this.userId) {
                try {
                    console.log('Fetching products from app.js...');
                    const response = await axios.get(`/api/products/${this.userId}`);
                    console.log('Products fetched successfully from app.js:', response.data);
                    this.products = response.data;
                } catch (error) {
                    console.error('Error fetching products from app.js:', error);
                }
            } else {
                console.error('User ID is null from app.js');
            }
        } else {
            console.error('App element not found from app.js');
        }

    },
    
     methods: {
        
         handleProductCreated(newProduct) {
        //     // Add the newly created product to the products array
        //     this.products.push(newProduct);
            
        //     // Fetch updated list of products after a new product is created
        //     this.fetchProducts();
         },
         fetchProducts() {
            console.log('Fetching products from app.js...');
            axios.get(`/api/products/${this.userId}`)
                .then(response => {
                    console.log('Products fetched successfully from app.js:', response.data);
                    // Filter out any undefined or null values
                    const validProducts = response.data.filter(product => product != null);
                    this.products = validProducts;
                })
                .catch(error => {
                    console.error('Error fetching products from app.js:', error);
                });
         },
     }
});

app.component('tag-editor', TagEditor); // Register your component

app.component('task-creator', TaskCreator, {
    methods: {
        // Other methods...
        handleProductCreated(newProduct) {
            // Add the newly created product to the products array
            this.products.push(newProduct);
            
            // Fetch updated list of products after a new product is created
            this.fetchProducts();
        },
        fetchProducts() {
            console.log('Fetching products from app.js...');
            axios.get(`/api/products/${this.userId}`)
                .then(response => {
                    console.log('Products fetched successfully from app.js:', response.data);
                    // Filter out any undefined or null values
                    const validProducts = response.data.filter(product => product != null);
                    this.products = validProducts;
                })
                .catch(error => {
                    console.error('Error fetching products from app.js:', error);
                });
        },
    },
    created() {
        // Other created lifecycle hook code...
        this.$on('fetchProducts', this.fetchProducts);
        this.$on('productCreated', this.handleProductCreated);
    },
}); // Register your new component

// Handle the product-created event emitted by the ProductModal component
app.component('product-modal', ProductModal, {
    // Register event handler for the product-created event
    // emits: ['product-created']
});

import ExampleComponent from './components/ExampleComponent.vue';
app.component('example-component', ExampleComponent);


/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
// });

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

// Check if the "#app" element exists before trying to mount the app
if (document.querySelector("#app")) {
    app.mount("#app");
}
