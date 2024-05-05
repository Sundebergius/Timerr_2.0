<template>
  <div class="fixed z-10 inset-0 overflow-y-auto flex items-center justify-center" style="background-color: rgba(0,0,0,0.5);" aria-labelledby="modal-title" role="dialog" aria-modal="true" @click.self="closeModal">
        <div class="bg-white rounded-lg w-96 p-6 m-4">    
        <!-- ...modal content... -->
  
        

      <form @submit.prevent="createProduct">
        <div class="mb-4">
          <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
          <input type="text" id="title" v-model="title" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
  
        <div class="mb-4">
          <label for="category" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
          <input type="text" id="category" v-model="category" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
            <textarea id="description" v-model="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
        </div>

        <div class="mb-4">
            <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price:</label>
            <input type="number" id="price" v-model="price" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="mb-4">
            <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">Quantity:</label>
            <input type="number" id="quantity" v-model="quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <!-- Image should be stored in cloud storage like AWS S3, Google Cloud Storage, or Firebase Storage. -->
        <!-- <div class="mb-4">
            <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Image:</label>
            <input type="file" id="image" v-model="image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div> -->
  
        <!-- ...other form fields... -->
  
        <div class="flex justify-center">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Create Product
            </button>
        </div>
        <div v-if="successMessage" class="alert alert-success mt-4">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="alert alert-danger mt-4">
        {{ errorMessage }}
      </div>
      </form>
      
    </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';

  export default {
    props: {
      projectId: {
        type: Number,
        required: true
      },
      userId: {
        type: Number,
        required: true
      }
    },
    data() {
      return {
        title: '',
        category: '',
        description: '',
        price: '',
        quantity: '',
        successMessage: '',
        errorMessage: '',
        // ...other data properties...
      };
    },
    created() {
  console.log('userId:', this.userId);
  console.log('projectId:', this.projectId);
},
  
    methods: {
        closeModal() {
            this.$emit('close');
            },
      createProduct() {
        axios.post('/api/products', {
          title: this.title,
          category: this.category,
          description: this.description,
          price: this.price,
          quantity: this.quantity,
          projectId: this.projectId,
          user_id: this.userId,
          // ...other data...
        })
        .then(response => {
          this.successMessage = response.data.message;
          this.errorMessage = '';
          })
        .catch(error => {
          this.errorMessage = 'An error occurred while creating the product.';
          this.successMessage = '';
        });
      },
    },
  };
  </script>