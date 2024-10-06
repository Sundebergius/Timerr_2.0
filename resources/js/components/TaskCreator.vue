<template>
    <div class="task-creator">
  <div class="container mx-auto px-4">
    <!-- <h1 class="text-2xl font-bold mb-6">Add New Task</h1> -->
    <!-- @submit.prevent="handleFormSubmission" -->
    <form> 
      <div class="mb-4">
        <p>{{ project.title }}</p>
        <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
        <input ref="titleInput" type="text" id="title" v-model="task_title"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          <div v-if="titleError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error:</strong>
            <span class="block sm:inline">Please fill out this field.</span>
            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="titleError = false">
              <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.348 5.652a1 1 0 00-1.414 0L10 8.586 7.066 5.652a1 1 0 10-1.414 1.414L8.586 10l-2.934 2.934a1 1 0 101.414 1.414L10 11.414l2.934 2.934a1 1 0 001.414-1.414L11.414 10l2.934-2.934a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
            </button>
          </div>
      
      </div>

      <div class="mb-4">
        <label for="task_type" class="block text-gray-700 text-sm font-bold mb-2">Type:</label>
        <select id="task_type" v-model="task_type"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          required>
          <option value="project_based">Project Based</option>
          <option value="hourly">Hourly</option>
          <option value="product">Product</option>
          <option value="distance">Distance</option>
          <option value="other">Other</option>
        </select>
      </div>

      <div v-if="task_type === 'project_based'" class="mb-4">
        <label for="projectPrice" class="block text-gray-700 text-sm font-bold mb-2">Project Price:</label>
        <div class="flex">
          <!-- <select v-model="currency"
            class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline ml-2 w-32">
            <option value="DKK">DKK </option>
            <option value="EUR">EUR </option>
            <option value="USD">USD </option> -->
            <!-- Add more options for other currencies as needed -->
          <!-- </select> -->
          <div
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <input type="number" id="projectPrice" v-model="projectPrice" class="w-full">
          </div>
        </div>

        <label for="startDate" class="block text-gray-700 text-sm font-bold mb-2">Start Date:</label>
        <input type="date" id="startDate" v-model="startDate"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

        <input type="checkbox" id="hasEndDate" v-model="hasEndDate">
        <label for="hasEndDate">Does this task have an end date?</label>
        <div v-if="hasEndDate">
          <input type="date" id="endDate" v-model="endDate"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <label for="location" class="block text-gray-700 text-sm font-bold mb-2">Location:</label>
        <input type="text" id="location" v-model="location"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
      </div>

      <div v-if="task_type === 'hourly'" class="mb-4">
        <label for="rate_per_hour" class="block text-gray-700 text-sm font-bold mb-2">Hourly rate:</label>
        <input type="number" id="rate_per_hour" v-model="rate_per_hour"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
      </div>
        <!-- <label for="hoursWorked" class="block text-gray-700 text-sm font-bold mb-2">Hours Worked:</label>
            <input type="number" id="hoursWorked" v-model="hoursWorked" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            <label for="workDate" class="block text-gray-700 text-sm font-bold mb-2">Date:</label>
            <input type="date" id="workDate" v-model="workDate" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            <label for="note" class="block text-gray-700 text-sm font-bold mb-2">Note:</label>
            <textarea id="note" v-model="note" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea> -->
      
      <div v-if="task_type === 'product'" class="mb-8">
        <!-- No products warning -->
        <div v-if="products.length === 0" class="mb-6 text-red-500">
          <p>No products found. Please <a :href="productUrl" class="text-blue-500 underline">create a product or service</a> first.</p>
        </div>

        <!-- Product dropdown -->
        <div v-for="(taskProduct, index) in taskProducts" :key="index" class="mb-6" v-if="products.length > 0">
          <label for="product" class="block text-lg font-bold mb-2 text-gray-700">Select Product or Service:</label>
          <div class="relative">
            <select id="product" v-model="taskProduct.selectedProduct" @change="onProductChange(index)"
              class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
              <option v-for="product in products" :value="product.id">{{ product.title }}</option>
            </select>
          </div>

          <!-- Product/Service Details Section -->
          <div v-if="taskProduct.selectedProductDetails" class="bg-white shadow-lg rounded-lg p-6 mt-4">
            <!-- For Physical Product -->
            <div v-if="taskProduct.selectedProductDetails && taskProduct.selectedProductDetails.type === 'product'">
              <p class="text-xl font-semibold text-blue-700 mb-2">Product Details</p>
              <p><strong>Price:</strong> {{ taskProduct.selectedProductDetails.price }} kr</p>
              <p><strong>Quantity in Stock:</strong> {{ taskProduct.selectedProductDetails.quantity_in_stock }}</p>
              <p v-if="taskProduct.selectedProductDetails.description"><strong>Description:</strong> {{ taskProduct.selectedProductDetails.description }}</p>
            </div>

            <!-- For Service -->
            <div v-if="taskProduct.selectedProductDetails && taskProduct.selectedProductDetails.type === 'service'">
              <p class="text-xl font-semibold text-blue-700 mb-2">Service Details</p>
              <p><strong>Base Price:</strong> {{ taskProduct.selectedProductDetails.price }} kr</p>
              <p v-if="taskProduct.selectedProductDetails.description"><strong>Description:</strong> {{ taskProduct.selectedProductDetails.description }}</p>

              <!-- Interactive Attribute Selection -->
              <p class="mt-4 font-semibold">Available Attributes:</p>
              
              <!-- Stacked attributes with scrollable section -->
              <div class="overflow-y-auto max-h-64 space-y-4">
                <div v-for="(attribute, attrIndex) in taskProduct.selectedProductDetails.attributes" :key="attrIndex" class="flex flex-col md:flex-row md:items-center mb-2">
                  
                  <!-- Select button -->
                  <div
                    @click="toggleAttribute(taskProduct, attribute)"
                    class="cursor-pointer bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out mb-2 md:mb-0 md:mr-4">
                    {{ taskProduct.selectedAttributes[attribute.key] ? 'Selected' : 'Select' }} {{ attribute.key }}
                  </div>

                  <!-- Attribute price -->
                  <span class="ml-2 text-gray-600">{{ attribute.value }} kr</span>

                  <!-- Attribute Quantity if selected -->
                  <div v-if="taskProduct.selectedAttributes[attribute.key]" class="flex items-center ml-0 mt-2 md:ml-4 md:mt-0">
                    <label class="text-gray-600 mr-2">Quantity:</label>
                    <input type="number" v-model="taskProduct.selectedAttributesQuantities[attribute.key]" min="1"
                      class="shadow border rounded py-1 px-2 w-16 text-center text-gray-700 focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                  </div>
                </div>
              </div>
            </div>

            <!-- Product Quantity Input for Physical Products -->
            <div v-if="taskProduct.selectedProductDetails && taskProduct.selectedProductDetails.type === 'product'" class="mt-6">
              <label for="quantity" class="block text-gray-700 font-semibold mb-2">Quantity:</label>
              <input type="number" id="quantity" v-model="taskProduct.quantity" min="1"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
            </div>

            <!-- Add Product Button -->
            <div class="mt-4">
              <button type="button" @click="validateAndAddProduct(index)"
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                Add {{ taskProduct.selectedProductDetails.type === 'product' ? 'Product' : 'Service' }}
              </button>
            </div>
          </div>
        </div>

        <!-- List of Added Products -->
        <div v-for="(product, index) in addedProducts" :key="'addedProduct' + index" class="mb-6 bg-white shadow-md rounded-lg p-6">
          <h2 class="text-xl font-bold mb-2 text-gray-800">{{ product.title }}</h2>

          <!-- Editable quantity for physical products -->
          <div v-if="product.type === 'product'" class="mb-4">
            <label for="quantity" class="block text-gray-700 font-semibold mb-2">Quantity:</label>
            <input type="number" v-model="product.quantity" min="1"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
            <p class="text-gray-700 mt-2"><strong>Total Price:</strong> {{ calculateTotalPrice(product) }} kr</p>
          </div>

          <!-- Editable attributes and prices for services -->
          <div v-if="product.type === 'service'">
            <p class="text-gray-700 font-semibold">Selected Attributes:</p>
            <ul class="list-disc pl-5 mt-2">
              <li v-for="(attr, attrIndex) in product.selectedAttributes" :key="attrIndex">
                <label>{{ attr.attribute }} - Quantity: {{ attr.quantity }} ({{ attr.price }} kr each)</label>
              </li>
            </ul>
            <p class="text-gray-700 mt-2"><strong>Total Price:</strong> {{ product.totalPrice }} kr</p>
          </div>

          <!-- Remove Button -->
          <button type="button" @click="removeProduct(index)"
            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300 ease-in-out mt-4">
            Remove
          </button>
        </div>
      </div>

      <div v-if="task_type === 'distance'" class="mb-4">
        <!-- <label for="distance" class="block text-gray-700 text-sm font-bold mb-2">Distance (KM):</label>
            <input type="number" id="distance" v-model="distance" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"> -->

        <label for="pricePerKm" class="block text-gray-700 text-sm font-bold mb-2">Price per KM:</label>
        <input type="number" id="pricePerKm" v-model="pricePerKm"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
      </div>

      <div v-if="task_type === 'other'" class="mb-4">
        <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
        <textarea id="description" v-model="description"
          class="form-input mt-1 block w-full rounded shadow appearance-none py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
        <p class="text-gray-600 text-sm mt-2">Use the description to provide a general overview of the task.</p>

        <button type="button" @click="showCustomFields = !showCustomFields"
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-4">
          Toggle Custom Fields
        </button>

        <button type="button" @click="toggleChecklistSection"
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mt-4 mb-8">
          Toggle Checklist Section
        </button>

        <div v-if="showCustomFields">
          <p class="text-gray-600 text-sm mt-2">Use custom fields to capture additional information about the task that isn't covered by the standard fields.</p>
          <div v-for="(field, index) in customFields" :key="index" class="mb-4">
            <label :for="'customField' + index" class="block text-gray-700 text-sm font-bold mb-2">Custom Field:</label>
            <input :id="'customField' + index" v-model="field.value"
              class="form-input mt-1 block w-full rounded shadow appearance-none py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <button v-if="customFields.length > 1" type="button" @click="deleteCustomField(index)"
              class="mt-2 text-red-500 hover:text-red-700 text-sm py-1 px-2 rounded focus:outline-none focus:shadow-outline">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>

          <button type="button" @click="addCustomField"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Add Custom Field
          </button>
          
        </div>
                

        <div v-if="showChecklistSection" class="mt-4">
          <p class="text-gray-600 text-sm mt-2">Use checklist sections to group related items together. Each item in a
            section represents a step or requirement for completing the task.</p>
          <div v-for="(section, index) in checklistSections" :key="'section' + index" class="mb-4 p-4 rounded bg-gray-200">
            <label :for="'sectionTitle' + index" class="block text-gray-700 text-sm font-bold mb-2">Section Title:</label>
            <input :id="'sectionTitle' + index" v-model="section.title"
              class="form-input mt-1 block w-full rounded shadow appearance-none py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
              <button v-if="checklistSections.length > 1" type="button" @click="deleteChecklistSection(index)"
                class="mt-2 text-red-500 hover:text-red-700 text-sm py-1 px-2 rounded focus:outline-none focus:shadow-outline">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-5 w-5">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            <div v-for="(item, itemIndex) in section.items" :key="'item' + itemIndex" class="mb-4 mt-2">
              <label :for="'checklistItem' + index + 'Item' + itemIndex"
                class="block text-gray-700 text-sm mb-2">Checklist Item:</label>
              <input :id="'checklistItem' + index + 'Item' + itemIndex" v-model="item.value"
                class="form-input mt-1 block w-full rounded shadow appearance-none py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button v-if="section.items.length > 1" type="button" @click="deleteChecklistItem(index, itemIndex)"
                  class="mt-2 text-red-500 hover:text-red-700 text-sm py-1 px-2 rounded focus:outline-none focus:shadow-outline">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
            </div>
            
            <button type="button" @click="addChecklistItem(index)"
              class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
              Add Checklist Item
            </button>
          </div>
          <button type="button" @click="addChecklistSection"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Add Checklist Section
          </button>
          
        </div>
      </div>

      <!-- <input type="hidden" id="hiddenInput" v-model="formData"> -->
      <div class="flex items-center justify-between mt-8">
        <button type="button" @click="handleFormSubmission"
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-96">
          Add Task
        </button>
      </div>

    </form>
  </div>
  <product-modal v-if="showModal && localProject" :project_id="localProject.id" :userId="localProject.user_id"
    @close="showModal = false" @product-created="handleProductEvent"></product-modal>
  <!-- @product-created="updateProductList"   -->
</div>
</template>

<script>
import axios from 'axios';
import productModal from './productModal.vue';

export default {
  components: {
    'product-modal': productModal,
  },
  props: {
    project: {
      type: Object,
      required: true
    },
    productUrl: {
      type: String,
      required: true // Make sure this prop is required so the warning is cleared
    },
    userId: {
      type: [Number, String], // Accept both types
      required: true,
      coerce(value) {
        return Number(value); // Convert it to a number
      }
    },
  },
  emits: ['formSubmitted'], // Declare the custom event here

  data() {
    return {
      // localProject: JSON.parse(this.project),
      // userId: this.userId,
      localProject: this.project || {}, // No need to parse, it's already an object
      task_type: 'project_based',
      initialTaskType: 'project_based',
      task_title: '',
      titleError: false,
      project_title: '',
      title: '',
      projectPrice: '',
      currency: 'DKK',
      startDate: new Date().toISOString().substr(0, 10),
      hasEndDate: false,
      endDate: '',
      location: '',
      description: '',
      rate_per_hour: '',
      hourly_rate: '',
      workDate: '',
      note: '',
      products: [],
      product: '',
      quantity: '',
      distance: '',
      pricePerKm: '',
      formSubmitted: false,
      errorMessage: '',
      formData: '',
      titleError: false,
      showModal: false,
      selectedProduct: null,
      customFields: [{ value: '' }],
      position: 0,
      showChecklistSection: false,
      showCustomFields: false,
      checklistSections: [{ title: '', items: [{ value: '' }] }],
      checklistItems: [{ value: '' }],
      taskProducts: [
        {
          selectedProduct: null,
          quantity: 1,
          type: 'product', // or 'service'
          attributes: {}, // { 'size': 'Large', 'color': 'Red' }
          selectedAttributes: {}, // { 'size': 0, 'color': 0 }
          selectedAttributesQuantities: {} // { 'size': 0, 'color': 0 }
        }
      ],
      addedProducts: [],
      showEditModal: false,
      editProductIndex: null,
      editProductDetails: {
        quantity: 1,
        // Add other fields for editing if needed
    },
    };
  },
  computed: {
  },
  watch: {
    localProject: {
      handler(newValue) {
        this.formData = JSON.stringify(newValue);
      },
      deep: true,
    },
    // initialTaskType(newType) {
    //   this.task_type = newType;
    // },
  },
  mounted() {
    // Directly assign the project prop to localProject
    if (this.project) {
      this.localProject = this.project;

      console.log('Project in TaskCreator:', this.localProject);

      // Fetch products only if localProject is available and has user_id
      if (this.localProject && this.localProject.user_id) {
        this.fetchProducts();
      } else {
        console.warn('User ID is not available in the project data');
      }
    }

    // console.log('Project:', this.project);
    // console.log('localProject:', this.localProject);
    // console.log('Type:', typeof this.project);
    // console.log('Type local:', typeof this.localProject);
    // console.log('Value id:', this.localProject.id);
    // console.log('Type id:', typeof this.localProject.id);
  },
  methods: {
    addCustomField() {
      this.customFields.push({ value: '' });
    },

    handleFileUpload(event) {
      this.attachment = event.target.files[0];
    },

    toggleChecklistSection() {
      this.showChecklistSection = !this.showChecklistSection;
    },

    addChecklistItem(sectionIndex) {
      this.checklistSections[sectionIndex].items = [...this.checklistSections[sectionIndex].items, { value: '' }];
    },

    addChecklistSection() {
      this.checklistSections.push({ title: '', items: [{ value: '' }] });
    },

    deleteChecklistSection(index) {
      this.checklistSections.splice(index, 1);
    },

    deleteChecklistItem(sectionIndex, itemIndex) {
      this.checklistSections[sectionIndex].items.splice(itemIndex, 1);
    },

    onProductChange(index) {
        const selectedProduct = this.products.find(product => product.id === this.taskProducts[index].selectedProduct);

        if (selectedProduct) {
            const attributes = typeof selectedProduct.attributes === 'string'
                ? JSON.parse(selectedProduct.attributes)
                : selectedProduct.attributes || {}; // Ensure attributes is always an object

            const selectedAttributes = {};
            const selectedAttributesQuantities = {};

            if (selectedProduct.type === 'service') {
                attributes.forEach(attr => {
                    selectedAttributes[attr.key] = false;
                    selectedAttributesQuantities[attr.key] = 1;
                });
            }

            // Directly modify the `taskProducts` array using Vue's automatic reactivity
            this.taskProducts[index] = {
                ...this.taskProducts[index],
                type: selectedProduct.type || 'unknown', // Default to 'unknown' if type is not available
                attributes: selectedProduct.type === 'service' ? attributes : [],
                selectedAttributes: selectedAttributes,
                selectedAttributesQuantities: selectedAttributesQuantities,
                quantity: 1,
                selectedProductDetails: selectedProduct, // Ensure details are set correctly
            };
        } else {
            console.error('Selected product not found.');
        }
    },

    toggleAttribute(taskProduct, attribute) {
      if (taskProduct.selectedAttributes[attribute.key]) {
        // Deselect the attribute
        taskProduct.selectedAttributes[attribute.key] = false;
        taskProduct.selectedAttributesQuantities[attribute.key] = 1; // Reset quantity to 1
      } else {
        // Select the attribute
        taskProduct.selectedAttributes[attribute.key] = true;
        taskProduct.selectedAttributesQuantities[attribute.key] = 1; // Set default quantity to 1
      }
    },

    fetchProducts() {
      this.initialTaskType = 'product';
      if (this.localProject && this.localProject.user_id) {
        axios.get(`/api/products/${this.localProject.user_id}`)
          .then(response => {
            this.products = response.data.products;
          })
          .catch(error => {
            console.error('Error fetching products:', error);
          });
      } else {
        console.error('Cannot fetch products because user_id is not available');
      }
    },

    handleProductEvent(newProduct) {
      this.handleProductCreated(newProduct);
      this.updateProductList(newProduct);
    },

    // handleProductCreated(newProduct) {
    //   // Handle the new product here
    // },
    updateProductList(newProduct) {
    // Check if the product already exists in the list
    const exists = this.products.some(product => product.id === newProduct.id);
    if (!exists) {
      // If the product doesn't exist, add it to the list
      this.products.push(newProduct);
    }
  },

  addProduct(index) {
    const selectedProduct = this.products.find(product => product.id === this.taskProducts[index].selectedProduct);
    
    if (selectedProduct) {
        let basePrice = parseFloat(selectedProduct.price || 0); // Default to 0 if no price is set
        let totalPrice = 0; // Initialize total price as 0

        const selectedAttributes = Object.keys(this.taskProducts[index].selectedAttributes)
            .filter(attrKey => this.taskProducts[index].selectedAttributes[attrKey] > 0)
            .map(attrKey => {
                const quantity = this.taskProducts[index].selectedAttributesQuantities[attrKey] || 0;
                const attribute = selectedProduct.attributes?.find(attr => attr.key === attrKey);
                const price = attribute ? parseFloat(attribute.value) : 0;
                
                // Calculate price for each attribute: (standard price + attribute price) * quantity
                let attributeTotal = (basePrice + price) * quantity;

                // Round the result to 2 decimal places to avoid floating-point precision issues
                totalPrice += Math.round(attributeTotal * 100) / 100;

                return {
                    attribute: attrKey,
                    quantity: quantity,
                    price: price
                };
            });

        // If it's a service, process the total differently
        if (this.taskProducts[index].type === 'service') {
            this.addedProducts.push({
                ...selectedProduct,
                selectedAttributes: selectedAttributes, // Attach selected attributes
                totalPrice: totalPrice.toFixed(2), // Final total price, rounded
                quantity: this.taskProducts[index].quantity || 1
            });
        } 
        // For physical products
        else if (this.taskProducts[index].type === 'product') {
            totalPrice = basePrice * (this.taskProducts[index].quantity || 1); // Product price * quantity
            this.addedProducts.push({
                ...selectedProduct,
                totalPrice: totalPrice.toFixed(2), // Final total for product, rounded
                quantity: this.taskProducts[index].quantity || 1
            });
        }

        // Clear input after adding the product
        this.taskProducts[index] = {
            selectedProduct: null,
            quantity: 1,
            type: 'product',
            attributes: [],
            selectedAttributes: {},
            selectedAttributesQuantities: {}
        };
    }

    console.log('Added Products: ', this.addedProducts);
},

    handleProductCreated(product) {
      console.log(product);
      // Emit an event to notify the parent component about the creation of the product
      this.$emit('productCreated', product);
      this.showModal = false;
    },
    validateForm() {
      if (!this.task_title) {
        this.titleError = true;
      } else {
        this.titleError = false;
        // Here you can call handleFormSubmission
        this.handleFormSubmission();
      }
    },
    validateAndAddProduct(index) {
      const taskProduct = this.taskProducts[index];

      // Validate for physical products
      if (taskProduct.type === 'product' && taskProduct.quantity <= 0) {
        alert("Please specify a valid quantity for the product.");
        return;
      }

      // Validate for service products with attributes
      if (taskProduct.type === 'service') {
        for (const key in taskProduct.selectedAttributes) {
          if (taskProduct.selectedAttributes[key] && (!taskProduct.selectedAttributesQuantities[key] || taskProduct.selectedAttributesQuantities[key] <= 0)) {
            alert(`Please specify a valid quantity for the attribute: ${key}`);
            return;
          }
        }
      }

      this.addProduct(index);
    },
    editProduct(index) {
      this.editProductIndex = index;
      this.editProductDetails = {
        quantity: this.addedProducts[index].quantity,
        // Set other fields for editing if needed
      };
      this.showEditModal = true;
    },
    updateProduct() {
      if (this.editProductIndex !== null) {
        // Update the selected product with the new details
        this.addedProducts[this.editProductIndex] = {
          ...this.addedProducts[this.editProductIndex],
          quantity: this.editProductDetails.quantity,
          // Update other fields as needed
        };

        // Recalculate total price if needed
        this.addedProducts[this.editProductIndex].totalPrice = this.calculateTotalPrice(this.addedProducts[this.editProductIndex]);

        this.showEditModal = false;
        this.editProductIndex = null;
      }
    },
    removeProduct(index) {
      // Check if the index is valid for addedProducts
      if (index >= 0 && index < this.addedProducts.length) {
        this.addedProducts.splice(index, 1);
        console.log('Product removed from addedProducts:', index);
      } else {
        console.error('Invalid index for removing product from addedProducts:', index);
      }

      // Check if the index is valid for taskProducts
      if (index >= 0 && index < this.taskProducts.length) {
        // Reset the taskProducts entry at the correct index
        this.$set(this.taskProducts, index, {
          selectedProduct: null,
          quantity: 1,
          type: 'product',
          attributes: [],
          selectedAttributes: {},
          selectedAttributesQuantities: {}
        });
        console.log('Task product reset at index:', index);
      } else {
        console.error('Invalid index for resetting taskProducts:', index);
      }

      console.log('Removed Product: ', this.addedProducts);
      console.log('Task Products After Removal: ', this.taskProducts);
      console.log('Added Products Length:', this.addedProducts.length);
      console.log('Task Products Length:', this.taskProducts.length);
    },
    calculateTotalPrice(product) {
    let totalPrice = 0;

    // For physical products, calculate price * quantity
    if (product.type === 'product') {
        totalPrice += product.price * product.quantity;
    }

    // For services, calculate the standard price and attributes' price
    if (product.type === 'service') {
        // Add the standard price if it exists
        if (product.price > 0) {
            totalPrice += parseFloat(product.price);
        }

        // Loop through selected attributes and calculate their prices based on quantity
        if (product.selectedAttributes) {
            product.selectedAttributes.forEach(attr => {
                totalPrice += parseFloat((product.price + attr.price) * attr.quantity);
            });
        }
    }

    return Math.round(totalPrice.toFixed(2));
},

    handleFormSubmission() {
      console.log('handleFormSubmission called');

      if (!this.task_title) {
      this.titleError = true;
      this.$refs.titleInput.focus();
      return;
    } else {
      this.titleError = false;
    }

    // Set the common route for task creation
    let route = `/projects/${this.localProject.id}/tasks`;

      const nonEmptyChecklistSections = this.checklistSections
        .filter(section => section.title.trim() !== '' || section.items.length > 0)
        .map(section => ({
          ...section,
          items: section.items
            .filter(item => item.value && item.value.trim() !== '')
            .map(item => item.value)
        }));

        const nonEmptyCustomFields = this.customFields
          .filter(field => field.value.trim() !== '')
          .map(field => ({
            value: field.value,
            position: this.position++ // Increment position for each field
          }));

      let data = {
        user_id: this.localProject.user_id,
        task_title: this.task_title,
        project_title: this.localProject.title,
        project_id: this.localProject.id,
        customFields: nonEmptyCustomFields,
      };

      if (!this.localProject || !this.localProject.id) {
        console.error('localProject or localProject.id is not defined');
        return;
      }

      switch (this.task_type) {
        case 'project_based':
          // Validate the projectPrice and set it to 0 if empty or invalid
          if (!this.projectPrice || isNaN(this.projectPrice) || this.projectPrice <= 0) {
              this.projectPrice = 0;
          }

          data = {
            ...data,
            price: this.projectPrice,
            currency: this.currency,
            start_date: this.startDate,
            end_date: this.endDate,
            project_location: this.location,
            task_type: 'project_based',
          };
          break;
        case 'hourly':
          // Check if rate_per_hour is empty or invalid, and set it to 0 if needed
          if (!this.rate_per_hour || isNaN(this.rate_per_hour) || this.rate_per_hour <= 0) {
              this.rate_per_hour = 0;
          }

          data = {
            ...data,
            task_hourly_id: this.task_hourly_id,
            earnings: this.earnings,
            rate_per_hour: this.rate_per_hour,
            rate_per_minute: this.rate_per_minute,
            task_type: 'hourly',
          };
          break;
          case 'product':
            data = {
              ...data,
              task_type: 'product',
              products: this.addedProducts.map(product => {
                // Create a base structure for the product data
                let productData = {
                  product_id: product.id,
                  quantity: product.type === 'product' 
                            ? product.quantity  // For physical products, take the provided quantity
                            : product.selectedAttributes.reduce((total, attr) => total + attr.quantity, 0),  // Sum of quantities for service attributes
                  type: product.type,  // 'product' or 'service'
                  total_price: product.totalPrice,  // Include the totalPrice in the payload
                };

                // Only add attributes for services
                if (product.type === 'service') {
                  productData.attributes = product.selectedAttributes.map(attr => ({
                    attribute: attr.attribute,  // Name or key of the attribute
                    quantity: attr.quantity,    // Quantity for this attribute
                    price: attr.price,          // Price per unit for this attribute
                  }));
                  
                  // Optionally, include the base price for the service if relevant
                  productData.basePrice = product.price; 
                }

                return productData;
              })
            };
            break;

        case 'distance':
          // Check if pricePerKm is empty or invalid, and set it to 0 if needed
          if (!this.pricePerKm || isNaN(this.pricePerKm) || this.pricePerKm <= 0) {
              this.pricePerKm = 0;
          }

          data = {
            ...data,
            price_per_km: this.pricePerKm,
            task_type: 'distance',
          };
          break;
        case 'other':
          data = {
            ...data,
            description: this.description,
            customFields: nonEmptyCustomFields,
            checklistSections: nonEmptyChecklistSections,
            task_type: 'other',
          };
          break;
      }

      // Emit the formSubmitted event with the form data as payload
      this.$emit('formSubmitted', { route, data });

      axios.post(route, data)
        .then(response => {
          // Handle success
          console.log('Request was successful', response.data);
          this.formSubmitted = true; // You might set a data property to indicate the form was submitted
          this.errorMessage = ''; // Clear any previous error messages

          // Navigate to the project's tasks page
          window.location.href = `/projects/${this.localProject.id}`;
        })
        .catch(error => {
          console.log('Error details:', error.response.data.errors);
          console.log(error.response.data); // Logs the data from the response
          console.log(error.response.status); // Logs the status code
          console.log(error.response.headers); // Logs the headers
          // Handle error
          console.log('An error occurred', error);
          this.formSubmitted = false; // Indicate that the form was not submitted
          if (error.response && error.response.data) {
            // If the server responded with a specific error message, display it
            this.errorMessage = error.response.data.message;
          } else {
            // If the server did not respond with a specific error message, display a generic one
            this.errorMessage = 'An error occurred while submitting the form';
          }
        });
    },
  },
};
</script>