<template>
  <div class="container mx-auto px-4">
    <!-- <h1 class="text-2xl font-bold mb-6">Add New Task</h1> -->
    <!-- @submit.prevent="handleFormSubmission" -->
    <form> 
      <div class="mb-4">
        <p>{{ project.title }}</p>
        <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
        <input type="text" id="title" v-model="task_title"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        <p v-if="titleError" class="text-red-500 text-xs italic">Please fill out this field.</p>
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
          <select v-model="currency"
            class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline ml-2 w-32">
            <option value="DKK">DKK </option>
            <option value="EUR">EUR </option>
            <option value="USD">USD </option>
            <!-- Add more options for other currencies as needed -->
          </select>
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

        <!-- <label for="hoursWorked" class="block text-gray-700 text-sm font-bold mb-2">Hours Worked:</label>
            <input type="number" id="hoursWorked" v-model="hoursWorked" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            <label for="workDate" class="block text-gray-700 text-sm font-bold mb-2">Date:</label>
            <input type="date" id="workDate" v-model="workDate" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            <label for="note" class="block text-gray-700 text-sm font-bold mb-2">Note:</label>
            <textarea id="note" v-model="note" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea> -->
      </div>

      <div v-if="task_type === 'product'" class="mb-4">
        <div v-for="(taskProduct, index) in taskProducts" :key="index" class="mb-4">
          <label for="product" class="block text-gray-700 text-sm font-bold mb-2">Product:</label>
          <div class="flex items-center">
            <button type="button" @click="showModal = true"
              class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
              <i class="fas fa-plus"></i>
            </button>
            <select id="product" v-model="taskProduct.selectedProduct"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2">
              <option v-for="product in products" :value="product.id">{{ product.title }}</option>
            </select>
          </div>
          <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">Quantity:</label>
          <input type="number" id="quantity" v-model="taskProduct.quantity"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            min="1" />
          <button type="button" @click="addProduct(index)"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Add this product
          </button>
        </div>
        <div v-for="(product, index) in addedProducts" :key="'addedProduct' + index"
          class="mb-4 p-4 bg-white rounded shadow">
          <h2 class="text-xl font-bold mb-2">{{ product.title }}</h2>
          <p class="text-gray-700 mb-1"><span class="font-bold">Quantity in Stock:</span> {{ product.quantityInStock }}
          </p>
          <p class="text-gray-700 mb-1"><span class="font-bold">Quantity Sold:</span> {{ product.quantitySold }}</p>
          <p class="text-gray-700 mb-1"><span class="font-bold">Price:</span> {{ product.price }}</p>
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

      <input type="hidden" id="hiddenInput" v-model="formData">
      <div class="flex items-center justify-between mt-8">
        <button type="button" @click="handleFormSubmission"
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-96">
          Add Task
        </button>
      </div>

    </form>
  </div>
  <product-modal v-if="showModal && localProject" :project_id="localProject.id" :userId="localProject.user_id"
    @close="showModal = false"></product-modal>
  <!-- @product-created="updateProductList"   -->
</template>

<script>
import axios from 'axios';
import productModal from './productModal.vue';

export default {
  components: {
    'product-modal': productModal,
  },
  props: ['project', 'user_id'],

  // props: {
  //   project: {
  //     type: String,
  //     required: true,
  // },
  // },
  data() {
    return {
      // localProject: JSON.parse(this.project),
      userId: '',
      localProject: this.project ? JSON.parse(this.project) : {},
      task_type: 'project_based',
      initialTaskType: 'project_based',
      task_title: '',
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
        }
      ],
      addedProducts: [],
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
    // Fetch the products
    this.fetchProducts();
    if (this.project) {
      try {
        this.localProject = JSON.parse(this.project);
      } catch (e) {
        console.error('Error parsing project prop:', e);
      }
      let hiddenInput = document.getElementById('hiddenInput');
      if (hiddenInput) {
        console.log('hiddenInput exists. Value:', hiddenInput.value);
      } else {
        console.log('hiddenInput does not exist');
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
    fetchProducts() {
      this.initialTaskType = 'product';
      axios.get(`/api/products/${this.localProject.user_id}`)
        .then(response => {
          this.products = response.data;
        })
        .catch(error => {
          console.error('Error fetching products:', error);
        });
    },
    updateProductList(newProduct) {
      this.products.push(newProduct);
    },
    addProduct(index) {
      console.log('addProduct method called');
      console.log('this.taskProducts[index].selectedProduct:', this.taskProducts[index].selectedProduct);
      console.log('this.products:', this.products);
      const selectedProduct = this.products.find(product => product.id === this.taskProducts[index].selectedProduct);
      console.log('selectedProduct:', selectedProduct);
      if (selectedProduct) {
        this.taskProducts[index] = {
          selectedProduct: selectedProduct.id,
          quantity: this.taskProducts[index].quantity,
        };
        // Include quantity when pushing to addedProducts
        this.addedProducts.push({
          ...selectedProduct,
          quantity: this.taskProducts[index].quantity
        });
        console.log('Added Products: ', this.addedProducts); // new console log statement
      }
      console.log('Product: ', this.taskProducts);
    },
    handleProductCreated(product) {
      console.log(product);
      this.products.push(product);
      this.selectedProduct = product.id;
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
    handleFormSubmission() {
      console.log('handleFormSubmission called');
      let route = '';
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
          route = `/projects/${this.localProject.id}/tasks/store-project`;
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
          route = `/projects/${this.localProject.id}/tasks/store-hourly`;
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
          route = `/projects/${this.localProject.id}/tasks/store-product`;
          data = {
            ...data,
            products: this.addedProducts.map(product => ({
              product_id: product.id,
              quantity: product.quantity
            })),
            task_type: 'product',
          };
          break;
        case 'distance':
          route = `/projects/${this.localProject.id}/tasks/store-distance`;
          data = {
            ...data,
            price_per_km: this.pricePerKm,
            task_type: 'distance',
          };
          break;
        case 'other':
          route = `/projects/${this.localProject.id}/tasks/store-other`;
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