<template>
  <div class="fixed z-10 inset-0 overflow-y-auto flex items-center justify-center" style="background-color: rgba(0,0,0,0.5);" aria-labelledby="modal-title" role="dialog" aria-modal="true" @click.self="closeModal">
    <div class="bg-white rounded-lg w-96 p-6 m-4">
      <form @submit.prevent="createProduct">
        <div class="mb-4">
          <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
          <input type="text" id="title" v-model="title" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <!-- Type Selection -->
        <div class="mb-4">
          <label for="type" class="block text-gray-700 text-sm font-bold mb-2">Type:</label>
          <select id="type" v-model="type" @change="handleTypeChange" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option disabled value="">Select Type</option>
            <option value="product">Product</option>
            <option value="service">Service</option>
          </select>
        </div>

        <!-- Conditional Fields for Services -->
        <div v-if="type === 'service'">
          <!-- Optional Price Field for Services -->
          <div class="mb-4">
            <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price (Optional):</label>
            <input type="number" id="price" v-model="price" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          
          <div class="mb-4">
            <label for="attributes" class="block text-gray-700 text-sm font-bold mb-2">Attributes (e.g., print size - price):</label>
            <div v-for="(attribute, index) in attributes" :key="index" class="mb-2 flex items-center">
              <input type="text" v-model="attribute.key" placeholder="Attribute Name" class="shadow appearance-none border rounded w-1/3 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2">
              <input type="text" v-model="attribute.value" placeholder="Attribute Value" class="shadow appearance-none border rounded w-1/2 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2">
              <button type="button" @click="removeAttribute(index)" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:shadow-outline-gray transition ease-in-out duration-150">
                  <i class="fas fa-trash"></i>
              </button>            
            </div>

            <!-- Centered Add Attribute Button -->
            <div class="flex justify-center">
              <button type="button" @click="addAttribute" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline">Add Attribute</button>
            </div>
          </div>
        </div>


        <!-- Common Fields for Products -->
        <div v-if="type === 'product'">
          <!-- Optional Price Field for Products -->
          <div class="mb-4">
            <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price (Optional):</label>
            <input type="number" id="price" v-model="price" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>

          <!-- Quantity Field for Products -->
          <div v-if="type === 'product'" class="mb-4">
            <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">Quantity (Optional):</label>
            <input type="number" id="quantity" v-model="quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>

          <!-- Parent Product Dropdown -->
          <!-- <div class="mb-4">
            <label for="parent_id" class="block text-gray-700 text-sm font-bold mb-2">Parent Product (Optional):</label>
            <select id="parent_id" v-model="parent_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
              <option value="">None</option>
              <option v-for="product in products" :key="product.id" :value="product.id">{{ product.title }}</option>
            </select>
          </div> -->
        </div>

        <!-- Submit Button -->
        <div class="flex justify-center">
          <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Create Product</button>
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
    },
    // type: {
    //   type: String,
    //   default: ''
    // }
  },
  data() {
    return {
      showModal: false,
      title: '',
      category: '', // Ensure this is a valid option if it's being used in a dropdown
      type:'',
      description: '',
      price: '',
      quantity: '',
      parent_id: '', // For storing the selected parent product ID
      attributes: [], // Dynamic attributes array
      successMessage: '',
      errorMessage: '',
      products: [],
      localProject: this.project ? JSON.parse(this.project) : {},
      // Initialize any other necessary data properties here
    };
  },
  created() {
    // Fetch products when the component is created
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
        // category: this.category,
        description: this.description,
        price: this.price || 0, // Default to 0 if price is not provided
        quantity_in_stock: this.quantity || 0, // Default to 0 if no quantity is provided
        user_id: this.userId,
        image: null,
        active: true,
        parent_id: this.parent_id || null, // Ensure parent_id is null if not selected
        type: this.type,
        attributes: this.attributes.reduce((acc, attribute) => {
          if (attribute.key && attribute.value) {
            acc[attribute.key] = attribute.value;
          }
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

          if (error.response) {
            if (error.response.status === 422) {
              // Validation errors
              console.log('Validation errors:', error.response.data);
              this.errorMessage = 'Validation error: ' + JSON.stringify(error.response.data);
            } else {
              this.errorMessage = 'Error: ' + error.response.status + ' - ' + error.response.data;
            }
          } else if (error.request) {
            // No response received
            console.error('No response received:', error.request);
            this.errorMessage = 'No response received from server.';
          } else {
            // Request setup error
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