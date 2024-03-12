<template>
    <div class="container mx-auto px-4">
      <!-- <h1 class="text-2xl font-bold mb-6">Add New Task</h1> -->
  
      <form @submit.prevent="handleFormSubmission">
        <div class="mb-4">
          <p>{{ project.name }}</p>
          <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
          <input type="text" id="title" v-model="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
  
        <div class="mb-4">
          <label for="task_type" class="block text-gray-700 text-sm font-bold mb-2">Type:</label>
          <select id="task_type" v-model="task_type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
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
                    <select v-model="currency" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline ml-2 w-32">
                        <option value="DKK">DKK </option>
                        <option value="EUR">EUR </option>
                        <option value="USD">USD </option>
                        <!-- Add more options for other currencies as needed -->
                    </select>
                    <div class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <input type="number" id="projectPrice" v-model="projectPrice" class="w-full">
                    </div>
                </div>

            <label for="startDate" class="block text-gray-700 text-sm font-bold mb-2">Start Date:</label>
            <input type="date" id="startDate" v-model="startDate" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            <input type="checkbox" id="hasEndDate" v-model="hasEndDate">
            <label for="hasEndDate">Does this task have an end date?</label>
            <div v-if="hasEndDate">
                <input type="date" id="endDate" v-model="endDate" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <label for="location" class="block text-gray-700 text-sm font-bold mb-2">Location:</label>
            <input type="text" id="location" v-model="location" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div v-if="task_type === 'hourly'" class="mb-4">
            <label for="hourlyPrice" class="block text-gray-700 text-sm font-bold mb-2">Hourly Price:</label>
            <input type="number" id="hourlyPrice" v-model="hourlyPrice" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            <label for="hoursWorked" class="block text-gray-700 text-sm font-bold mb-2">Hours Worked:</label>
            <input type="number" id="hoursWorked" v-model="hoursWorked" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            <label for="workDate" class="block text-gray-700 text-sm font-bold mb-2">Date:</label>
            <input type="date" id="workDate" v-model="workDate" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            <label for="note" class="block text-gray-700 text-sm font-bold mb-2">Note:</label>
            <textarea id="note" v-model="note" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
        </div>

        <div v-if="task_type === 'product'" class="mb-4">
            <!-- You'll need to fetch the list of products from your database and store it in your component's data -->
            <label for="product" class="block text-gray-700 text-sm font-bold mb-2">Product:</label>
            <select id="product" v-model="product" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option v-for="product in products" :value="product.id">{{ product.name }}</option>
            </select>

            <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">Quantity:</label>
            <input type="number" id="quantity" v-model="quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div v-if="task_type === 'distance'" class="mb-4">
            <label for="distance" class="block text-gray-700 text-sm font-bold mb-2">Distance (KM):</label>
            <input type="number" id="distance" v-model="distance" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            <label for="pricePerKm" class="block text-gray-700 text-sm font-bold mb-2">Price per KM:</label>
            <input type="number" id="pricePerKm" v-model="pricePerKm" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div v-if="task_type === 'other'" class="mb-4">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
            <textarea id="description" v-model="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
        </div>
        
        <input type="hidden" id="hiddenInput" v-model="formData">
          <div class="flex items-center justify-between">
              <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-96">
                  Add Task
              </button>
          </div>
      </form>
    </div>
  </template>
  
  <script>
  import axios from 'axios';

  export default {
    props: ['project'],
 
  // props: {
  //   project: {
  //     type: String,
  //     required: true,
  // },
  // },
  data() {
    return {
      // localProject: JSON.parse(this.project),
      localProject: this.project ? JSON.parse(this.project) : {},
      task_type: 'project_based',
      title: '',
      projectPrice: '',
      currency: 'DKK',
      startDate: new Date().toISOString().substr(0, 10),
      hasEndDate: false,
      endDate: '',
      location: '',
      description: '',
      hourlyPrice: '',
      hoursWorked: '',
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
    };
  },
  watch: {
    localProject: {
      handler(newValue) {
        this.formData = JSON.stringify(newValue);
      },
      deep: true,
    },
  },
  mounted() {
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
    console.log('Project:', this.project);
    console.log('localProject:', this.localProject);
    console.log('Type:', typeof this.project);
    console.log('Type local:', typeof this.localProject);
    console.log('Value id:', this.localProject.id);
    console.log('Type id:', typeof this.localProject.id);
  },
    methods: {
      handleFormSubmission() {
        console.log('handleFormSubmission called');
        let route = '';
        let data = {};
        let formDataString = this.formData;
        let formDataObject = null;
        // Check if formDataString is not empty
        if (formDataString) {
          try {
            formDataObject = JSON.parse(formDataString);
            console.log('formDataObject:', formDataObject);
          } catch (error) {
            console.error('Invalid JSON:', error);
            return; // If the JSON is invalid, return early
          }
        } else {
          console.error('Empty JSON string. Type:', typeof formDataString, 'Value:', formDataString);
          return; // If the JSON string is empty, return early
        }

        // Check if this.localProject and this.localProject.id are defined
        if (this.localProject && this.localProject.id) {
        switch (this.task_type) {
          case 'project_based':
            route = `/projects/${this.localProject.id}/tasks/store-project`;
            data = {
              title: this.title,
              task_type: this.task_type,
              user_id: this.localProject.user_id,
              project_id: this.localProject.id,
              name: this.localProject.title,
              price: this.projectPrice,
              currency: this.currency,
              start_date: this.startDate,
              // hasEndDate: this.hasEndDate,
              end_date: this.endDate,
              project_location: this.location,
            };
            break;
          case 'hourly':
            route = `/projects/${this.localProject.id}/tasks/store-hourly`;
            data = {
              title: this.title,
              task_type: this.task_type,
              hourlyPrice: this.hourlyPrice,
              hoursWorked: this.hoursWorked,
              workDate: this.workDate,
              note: this.note,
            };
            break;
          //   following not implemented yet
          //   case 'product':
          //   route = `/projects/${this.localProject.id}/tasks/store-product`;
          //   data = {
          //     title: this.title,
          //     task_type: this.task_type,
          //     product: this.product,
          //     quantity: this.quantity,
          //   };
          //   break;
          // case 'distance':
          //   route = `/projects/${this.localProject.id}/tasks/store-distance`;
          //   data = {
          //     title: this.title,
          //     task_type: this.task_type,
          //     distance: this.distance,
          //     pricePerKm: this.pricePerKm,
          //   };
          //   break;
          // case 'other':
          //   route = `/projects/${this.localProject.id}/tasks/store-other`;
          //   data = {
          //     title: this.title,
          //     task_type: this.task_type,
          //     description: this.description,
          //   };
          //   break;
          // Add other cases for 'product', 'distance', 'other'
        }
      } else {
      console.error('localProject or localProject.id is undefined');
      return; // If localProject or localProject.id is undefined, return early
    }

        // Emit the formSubmitted event with the form data as payload
        this.$emit('formSubmitted', { route, data });

        axios.post(route, data)
          .then(response => {
            // Handle success
            console.log('Request was successful', response.data);
            this.formSubmitted = true; // You might set a data property to indicate the form was submitted
            this.errorMessage = ''; // Clear any previous error messages
          })
          .catch(error => {
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