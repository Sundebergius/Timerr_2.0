<template>
    <div class="container mx-auto px-4">
      <!-- <h1 class="text-2xl font-bold mb-6">Add New Task</h1> -->
  
      <form @submit.prevent="submitForm">
        <!-- <div class="mb-4">
          <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
          <input type="text" id="title" v-model="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div> -->
  
        <div class="mb-4">
          <label for="type" class="block text-gray-700 text-sm font-bold mb-2">Type:</label>
          <select id="type" v-model="type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            <option value="project_based">Project Based</option>
            <option value="hourly">Hourly</option>
            <option value="product">Product</option>
            <option value="distance">Distance</option>
            <option value="other">Other</option>
          </select>
        </div>
  
        <div v-if="type === 'project_based'" class="mb-4">
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

        <div v-if="type === 'hourly'" class="mb-4">
            <label for="hourlyPrice" class="block text-gray-700 text-sm font-bold mb-2">Hourly Price:</label>
            <input type="number" id="hourlyPrice" v-model="hourlyPrice" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            <label for="hoursWorked" class="block text-gray-700 text-sm font-bold mb-2">Hours Worked:</label>
            <input type="number" id="hoursWorked" v-model="hoursWorked" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            <label for="workDate" class="block text-gray-700 text-sm font-bold mb-2">Date:</label>
            <input type="date" id="workDate" v-model="workDate" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            <label for="note" class="block text-gray-700 text-sm font-bold mb-2">Note:</label>
            <textarea id="note" v-model="note" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
        </div>

        <div v-if="type === 'product'" class="mb-4">
            <!-- You'll need to fetch the list of products from your database and store it in your component's data -->
            <label for="product" class="block text-gray-700 text-sm font-bold mb-2">Product:</label>
            <select id="product" v-model="product" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option v-for="product in products" :value="product.id">{{ product.name }}</option>
            </select>

            <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">Quantity:</label>
            <input type="number" id="quantity" v-model="quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div v-if="type === 'distance'" class="mb-4">
            <label for="distance" class="block text-gray-700 text-sm font-bold mb-2">Distance (KM):</label>
            <input type="number" id="distance" v-model="distance" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            <label for="pricePerKm" class="block text-gray-700 text-sm font-bold mb-2">Price per KM:</label>
            <input type="number" id="pricePerKm" v-model="pricePerKm" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div v-if="type === 'other'" class="mb-4">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
            <textarea id="description" v-model="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
        </div>
        
        <!-- <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
          Add Task
        </button> -->
      </form>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        title: '',
        type: '',
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
        products: [], // You'll need to fetch this data from your database
        product: '',
        quantity: '',
        distance: '',
        pricePerKm: '',
      };
    },
    methods: {
      submitForm() {
        // Handle form submission here
      },
    },
  };
  </script>