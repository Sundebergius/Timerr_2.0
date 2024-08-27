<template>
  <div class="fixed z-10 inset-0 overflow-y-auto flex items-center justify-center"
    style="background-color: rgba(0,0,0,0.5);" aria-labelledby="modal-title" role="dialog" aria-modal="true"
    @click.self="closeModal">
    <div class="bg-white rounded-lg w-96 p-6 m-4">
      <!-- ...modal content... -->



      <form @submit.prevent="createProduct">
        <div class="mb-4">
          <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
          <input type="text" id="title" v-model="title" required
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="mb-4">
          <label for="category" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
          <input type="text" id="category" v-model="category"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="mb-4">
          <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
          <textarea id="description" v-model="description"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
        </div>

        <div class="mb-4">
          <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price:</label>
          <input type="number" id="price" v-model="price"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="mb-4">
          <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">Quantity:</label>
          <input type="number" id="quantity" v-model="quantity"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <!-- Image should be stored in cloud storage like AWS S3, Google Cloud Storage, or Firebase Storage. -->

        <!-- <div class="mb-4">
            <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Image:</label>
            <input type="file" id="image" v-model="image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div> -->

        <!-- Parent Product Dropdown -->
        <div class="mb-4">
          <label for="parent_id" class="block text-gray-700 text-sm font-bold mb-2">Parent Product (Optional):</label>
          <select id="parent_id" v-model="parent_id"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="">None</option>
            <option v-for="product in products" :key="product.id" :value="product.id">{{ product.title }}</option>
          </select>
        </div>

        <!-- Dynamic Attributes Section -->
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Attributes:</label>
          <div v-for="(attribute, index) in attributes" :key="index" class="mb-2">
            <input type="text" v-model="attribute.key" placeholder="Attribute Name"
              class="shadow appearance-none border rounded w-1/3 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2">
            <input type="text" v-model="attribute.value" placeholder="Attribute Value"
              class="shadow appearance-none border rounded w-1/2 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2">
            <button type="button" @click="removeAttribute(index)" class="text-red-500">Remove</button>
          </div>
          <button type="button" @click="addAttribute"
            class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline">
            Add Attribute
          </button>
        </div>

        <!-- ...other form fields... -->

        <!-- Submit Button -->
        <div class="flex justify-center">
          <button type="submit"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Create Product
          </button>
        </div>

        <!-- Success and Error Messages -->
        <div v-if="successMessage" class="alert alert-success mt-4">{{ successMessage }}</div>
        <div v-if="errorMessage" class="alert alert-danger mt-4">{{ errorMessage }}</div>
      </form>

    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    project_id: {
      type: Number,
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
      parent_id: '', // For storing the selected parent product ID
      attributes: [], // Dynamic attributes array
      successMessage: '',
      errorMessage: '',
      products: [],
      localProject: this.project ? JSON.parse(this.project) : {},
      // ...other data properties...
    };
  },
  created() {
    // console.log('userId:', this.userId);
    // console.log('projectId:', this.projectId);
    this.fetchProducts();
  },

  methods: {
    closeModal() {
      this.$emit('close');
    },
    addProduct(newProduct) {
      this.products.push(newProduct);
    },
    fetchProducts() {
      // Fetch existing products to populate the parent product dropdown
      axios.get(`/api/products/${this.userId}`)
        .then(response => {
          this.products = response.data.products;
        })
        .catch(error => {
          console.error('Error fetching products:', error);
        });
    },
    addAttribute() {
      this.attributes.push({ key: '', value: '' });
    },
    removeAttribute(index) {
      this.attributes.splice(index, 1);
    },
    createProduct() {
  const productData = {
    title: this.title,
    category: this.category,
    description: this.description,
    price: this.price,
    quantityInStock: this.quantity,
    user_id: this.userId,
    image: null,
    active: true,
    parent_id: this.parent_id,
    attributes: this.attributes.reduce((acc, attribute) => {
      acc[attribute.key] = attribute.value;
      return acc;
    }, {})
  };

  console.log('Product data being sent:', productData);

  axios.post('/api/products', productData)
    .then(response => {
      console.log('Full server response:', response);
      console.log('Server response data:', response.data);

      if (response.data && response.data.product) {
        this.addProduct(response.data.product);
        this.$emit('product-created', response.data.product);
        this.resetForm();
        this.successMessage = 'Product created successfully!';
        this.errorMessage = '';  // Clear any previous error messages
      } else {
        console.error('Product not defined in server response');
        this.errorMessage = 'An error occurred while creating the product.';
      }
    })
    .catch(error => {
      console.error('Error caught in catch block:', error);
      // Error could be due to network issues, etc. Handle it more broadly
      if (error.response) {
        if (error.response.status === 422) {
          console.log('Validation errors:', error.response.data);
          this.errorMessage = 'Validation error: ' + JSON.stringify(error.response.data);
        } else {
          this.errorMessage = 'Error: ' + error.response.status + ' - ' + error.response.data;
        }
      } else if (error.request) {
        // The request was made but no response was received
        console.error('No response received:', error.request);
        this.errorMessage = 'No response received from server.';
      } else {
        // Something happened in setting up the request that triggered an Error
        console.error('Error setting up request:', error.message);
        this.errorMessage = 'An error occurred: ' + error.message;
      }
      this.successMessage = '';  // Clear any previous success messages
    });
},

    resetForm() {
      // Reset the form after successful creation
      this.title = '';
      this.category = '';
      this.description = '';
      this.price = '';
      this.quantity = '';
      this.parent_id = '';
      this.attributes = [];
    }
  },
};
</script>