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
                    <option 
                        v-for="product in products.filter(p => p.type !== 'material')" 
                        :value="product.id">
                        {{ product.title }}
                    </option>
                </select>
            </div>

            <!-- Product/Service Details Section -->
            <div v-if="taskProduct.selectedProductDetails" class="bg-white shadow-lg rounded-lg p-6 mt-4">

                <!-- Conditional Section for Products -->
                <div v-if="taskProduct.selectedProductDetails.type === 'product'">
                    <p class="text-2xl font-semibold text-blue-700 mb-3">Product Details</p>
                    <div class="flex flex-col space-y-4 mb-4">
                        <div>
                            <p class="text-lg font-bold">Price:</p>
                            <p class="text-xl text-green-700">{{ taskProduct.selectedProductDetails.derivedPrice }} kr</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold">Quantity in Stock:</p>
                            <p class="text-xl" :class="{'text-red-600': taskProduct.selectedProductDetails.derivedStock < 0, 'text-green-700': taskProduct.selectedProductDetails.derivedStock >= 0}">
                                {{ taskProduct.selectedProductDetails.derivedStock }}
                            </p>
                            <p v-if="taskProduct.selectedProductDetails.derivedStock < 0" class="text-sm text-gray-500 mt-1 italic">
                                Note: Negative values are allowed, and you can adjust your stock later.
                            </p>
                        </div>
                    </div>

                    <!-- Optional Description for Product -->
                    <div v-if="taskProduct.selectedProductDetails.description" class="mb-4">
                        <p class="text-lg font-bold">Description:</p>
                        <p class="text-gray-600">{{ taskProduct.selectedProductDetails.description }}</p>
                    </div>

                    <!-- Product Quantity Input -->
                    <div class="mt-6">
                        <label for="quantity" class="block text-gray-700 font-semibold mb-2">Quantity:</label>
                        <input type="number" id="quantity" v-model="taskProduct.quantity" min="0" @input="onProductQuantityChange(index)"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                    </div>
                </div>

                <!-- Conditional Section for Services -->
                <div v-if="taskProduct.selectedProductDetails.type === 'service'" class="p-6 bg-gray-50 rounded-lg shadow-lg space-y-4 mt-6">
                    <h3 class="text-2xl font-semibold text-blue-700">Service Details</h3>
                    
                    <!-- Base Price -->
                    <div class="flex items-center justify-between">
                        <p class="text-lg font-bold text-gray-700">Base Price:</p>
                        <p class="text-xl text-green-700">{{ taskProduct.selectedProductDetails.price }} kr</p>
                    </div>
                    
                    <!-- Description -->
                    <div v-if="taskProduct.selectedProductDetails.description" class="text-gray-600 mt-2">
                        <p class="font-medium text-gray-700">Description:</p>
                        <p>{{ taskProduct.selectedProductDetails.description }}</p>
                    </div>

                    <!-- Available Attributes Section -->
                    <div class="mt-4">
                        <h4 class="text-lg font-semibold text-gray-700 mb-3">Available Attributes</h4>
                        <div class="overflow-y-auto max-h-64 space-y-4">
                            <div v-for="(attribute, attrIndex) in taskProduct.selectedProductDetails.attributes" :key="attrIndex" 
                                class="flex flex-col md:flex-row items-center justify-between p-4 bg-white rounded-lg shadow-md transition duration-300 ease-in-out hover:bg-gray-50">
                                
                                <!-- Attribute Title and Price -->
                                <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-4 w-full">
                                    <div class="flex items-center justify-between w-full md:w-auto">
                                        <p class="font-semibold text-gray-800">{{ attribute.key }}</p>
                                        <span class="ml-4 text-gray-600">{{ attribute.value }} kr</span>
                                    </div>
                                    
                                    <!-- Toggle for Attribute Selection -->
                                    <div class="flex items-center mt-2 md:mt-0 md:ml-4">
                                        <button @click.prevent="toggleAttribute(taskProduct, attribute)" 
                                            class="flex items-center bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-2 px-4 rounded-md transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400">
                                            <span v-if="taskProduct.selectedAttributes[attribute.key]">Deselect</span>
                                            <span v-else>Select</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Attribute Quantity if selected -->
                                <div v-if="taskProduct.selectedAttributes[attribute.key]" class="flex items-center mt-2 md:mt-0 md:ml-6">
                                    <label class="text-gray-600 mr-2">Quantity:</label>
                                    <input type="number" v-model="taskProduct.selectedAttributesQuantities[attribute.key]" min="1"
                                        class="shadow border rounded py-1 px-3 w-20 text-center text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 transition duration-300 ease-in-out">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Shared Materials Section -->
                <div v-if="taskProduct.linkedMaterials && taskProduct.linkedMaterials.length > 0" class="mt-6">
                    <p class="text-lg font-semibold text-blue-700 mb-4">Select Materials:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div 
                            v-for="material in taskProduct.linkedMaterials" 
                            :key="material.id" 
                            class="p-4 border border-gray-200 rounded-lg shadow-md bg-white hover:shadow-lg transition-shadow duration-300">
                            
                            <!-- Material Header -->
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xl font-semibold text-gray-800">{{ material.title }}</p>
                                <input type="checkbox" v-model="material.selected" @change="onMaterialSelectionChange(index)" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500" />
                            </div>

                            <!-- Material Details -->
                            <div class="text-sm text-gray-700 space-y-2">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Unit Type:</span>
                                    <span>{{ material.unit_type }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Stock:</span>
                                    <span :class="{'text-red-600': material.displayStock <= material.minimum_stock_alert || material.displayStock < 0, 'text-green-600': material.displayStock > material.minimum_stock_alert}">
                                        {{ material.displayStock }}
                                    </span>
                                </div>
                                <p v-if="material.displayStock < 0" class="text-sm text-gray-500 italic mt-1">
                                    Negative values are allowed, and you can update stock levels later.
                                </p>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Usage per Unit:</span>
                                    <span>{{ material.usage_per_unit }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Price per Unit:</span>
                                    <span>{{ material.price_per_unit }} kr</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Min Stock Alert:</span>
                                    <span v-if="material.minimum_stock_alert" class="text-orange-500">{{ Number(material.minimum_stock_alert).toFixed(2) }}</span>
                                    <span v-else class="italic text-gray-400">None</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Product/Service Button -->
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

          <!-- Display Selected Materials for Dynamic Products -->
          <div v-if="product.selectedMaterials && product.selectedMaterials.length > 0" class="mt-4">
            <p class="text-gray-700 font-semibold">Selected Materials:</p>
            <ul class="list-disc pl-5 mt-2">
              <li v-for="(material, matIndex) in product.selectedMaterials" :key="matIndex">
                <span>
                  {{ material.title }} ({{ material.unitType }}) - 
                  Used: {{ material.quantityUsed }} {{ material.unitType }}, 
                  Cost: {{ material.totalCost }} kr
                </span>
              </li>
            </ul>
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

        <!-- Material Stock Overview Section -->
        <div v-if="materialStockOverview.length > 0" class="mt-6 bg-gray-50 p-4 rounded-lg shadow-md">
          <h3 class="text-xl font-bold text-gray-800 mb-3">Material Stock Overview</h3>

          <!-- Info Message -->
          <p class="text-sm text-gray-500 italic mb-3">
              Note: Negative stock levels are allowed. You can proceed and update stock levels later if needed.
          </p>

          <!-- Overall Summary Section -->
          <div class="mb-4 p-3 bg-gray-100 rounded-md shadow-inner">
              <h4 class="text-lg font-semibold text-gray-700">Overall Summary</h4>
              <p class="text-gray-800"><strong>Total Price:</strong> {{ totalProductSummary.totalPrice }} kr</p>
              <p class="text-gray-800"><strong>Total Quantity:</strong> {{ totalProductSummary.totalQuantity }}</p>

              <!-- List each product with title, quantity, and price -->
              <ul class="mt-2 space-y-2">
                  <li v-for="(product, index) in totalProductSummary.products" :key="index" class="text-gray-700">
                      <p><strong>Product:</strong> {{ product.title }}</p>
                      <p><strong>Quantity:</strong> {{ product.quantity }}</p>
                      <p><strong>Product Total Price:</strong> {{ product.totalPrice }} kr</p>
                  </li>
              </ul>
          </div>

          <ul class="list-disc pl-5 space-y-4">
              <li v-for="(material, index) in materialStockOverview" :key="index" class="space-y-1">
                  
                  <!-- Material Title with Remaining Stock Label -->
                  <div :class="{'text-red-600': material.remainingStock < 0, 'text-green-700': material.remainingStock >= 0}">
                      <strong>{{ material.title }}</strong> - <span>Remaining Stock: </span> 
                      <span>{{ material.remainingStock }} {{ material.unitType }}</span>
                  </div>
                  
                  <!-- Progress Bar for Stock Levels -->
                  <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden mt-1">
                      <div 
                          :style="{ width: Math.max((material.remainingStock / material.initialStock) * 100, 0) + '%' }"
                          :class="{
                              'bg-green-500': material.remainingStock >= material.initialStock * 0.2,
                              'bg-orange-400': material.remainingStock < material.initialStock * 0.2 && material.remainingStock >= 0,
                              'bg-red-500': material.remainingStock < 0
                          }"
                          class="h-full transition-width duration-300">
                      </div>
                  </div>

                  <!-- Warning Message for Low Stock -->
                  <p v-if="material.remainingStock <= material.minimumStockAlert" class="text-orange-500 text-sm font-semibold mt-1">
                      Warning: Stock is low (minimum threshold: {{ material.minimumStockAlert }} {{ material.unitType }})
                  </p>

                  <!-- Usage Summary Below Each Material with a Clear Label -->
                  <p class="text-gray-600 text-sm mt-1">
                      <strong>Total Used:</strong> {{ material.initialStock - material.remainingStock }} {{ material.unitType }}
                  </p>
              </li>
          </ul>
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
      materialsCache: {}, // To store preloaded materials by product ID
      productChangedFlag: false, // Flag to track if the product was changed
      previousProductType: null, // Track the type of the previously selected product
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
          quantity: 0,
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
    materialStockOverview() {
        const stockMap = {};

        // Loop through each added product/service and accumulate material usage
        this.addedProducts.forEach(product => {
            if (product.selectedMaterials && product.selectedMaterials.length > 0) {
                product.selectedMaterials.forEach(material => {
                    if (!stockMap[material.title]) {
                        // Initialize material in stockMap with initial values
                        stockMap[material.title] = {
                            title: material.title,
                            unitType: material.unitType,
                            initialStock: parseFloat(material.initialStock) || 0,
                            remainingStock: parseFloat(material.initialStock) - parseFloat(material.quantityUsed),
                            minimumStockAlert: parseFloat(material.minimumStockAlert) || 0
                        };
                    } else {
                        // Deduct used quantity from remaining stock for the material
                        stockMap[material.title].remainingStock -= parseFloat(material.quantityUsed) || 0;
                    }
                });
            }
        });

        // Return as an array for rendering
        return Object.values(stockMap);
    },

    totalProductSummary() {
        const summary = {
            totalPrice: 0,
            totalQuantity: 0,
            products: []
        };

        this.addedProducts.forEach(product => {
            let productTotalPrice = parseFloat(product.totalPrice) || 0;
            let quantity = 0;

            if (product.type === 'product') {
                // Use the already calculated totalPrice from when the product was added
                productTotalPrice = parseFloat(product.totalPrice) || 0;
                quantity = product.quantity || 0;
            } else if (product.type === 'service') {
                // For services, accumulate based on attribute quantities
                quantity = product.selectedAttributes.reduce((sum, attr) => sum + (attr.quantity || 0), 0);
            }

            // Accumulate total values
            summary.totalPrice += productTotalPrice;
            summary.totalQuantity += quantity;

            // Add each product/service instance to the products array with independent quantities
            summary.products.push({
                title: product.title,
                quantity: quantity,
                totalPrice: productTotalPrice.toFixed(2)
            });
        });

        // Round total price for display consistency
        summary.totalPrice = Math.round(summary.totalPrice * 100) / 100;

        return summary;
    }
},

watch: {
    // Watch for changes in the addedProducts array and recalculate total price and material usage
    addedProducts: {
        handler(newProducts) {
            newProducts.forEach(product => {
                if (product.type === 'product') {
                    // Recalculate price and material usage for each product
                    this.calculateTotalPrice(product);
                }
            });
        },
        deep: true // Ensure deep reactivity to capture changes within nested objects/arrays
    },

    // Watch for changes in taskProducts array and track changes to selected product
    taskProducts: {
        handler(newTaskProducts) {
            newTaskProducts.forEach(taskProduct => {
                if (taskProduct.selectedProduct) {
                    // Flag that a product change has occurred
                    this.productChangedFlag = true;
                }
            });
        },
        deep: true // Ensure deep reactivity
    },

    // Watch for changes in localProject object and update form data
    localProject: {
      handler(newValue) {
        this.formData = JSON.stringify(newValue);
      },
      deep: true // Ensure deep reactivity to nested changes in localProject
    }
},
  mounted() {
      if (this.project) {
          this.localProject = this.project;
          
          if (this.localProject && this.localProject.user_id) {
              this.fetchProducts().then(() => {
                  // Preload materials only after products are fetched
                  this.preloadMaterialsForProducts();
              });
          } else {
              console.warn('User ID is not available in the project data');
          }
      }
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

    preloadMaterialsForProducts() {
        this.products.forEach(product => {
            if (product.type !== 'material') { // Skip material type products
                axios.get(`/api/products/${product.id}/materials`)
                    .then(response => {
                        // Directly assign the cached materials without using this.$set
                        this.materialsCache[product.id] = response.data || [];
                    })
                    .catch(error => {
                        console.error(`Error preloading materials for product ${product.id}:`, error);
                    });
            }
        });
    },

    simulatedMaterialStock(materials, quantity) {
    const materialStockOverviewLookup = this.materialStockOverview.reduce((acc, material) => {
        acc[material.title] = material.remainingStock;
        return acc;
    }, {});

    return materials.map(material => {
        const usagePerUnit = parseFloat(material.usage_per_unit) || 0;
        const initialStock = materialStockOverviewLookup[material.title] !== undefined
            ? materialStockOverviewLookup[material.title]
            : parseFloat(material.quantity_in_stock) || 0;

        // Calculate quantity used based on user input for each service attribute or product quantity
        const quantityUsed = material.selected ? usagePerUnit * quantity : 0;

        return {
            ...material,
            displayStock: initialStock - quantityUsed,
            minimumStockAlert: material.minimum_stock_alert || 0
        };
    });
},

onProductChange(index) {
    const selectedProduct = this.products.find(product => product.id === this.taskProducts[index].selectedProduct);

    if (selectedProduct) {
        // Reset quantity only when switching between product types
        if (this.previousProductType !== selectedProduct.type) {
            this.taskProducts[index].quantity = 0;
            this.previousProductType = selectedProduct.type;
        }

        const attributes = typeof selectedProduct.attributes === 'string'
            ? JSON.parse(selectedProduct.attributes)
            : selectedProduct.attributes || {};

        const selectedAttributes = {};
        const selectedAttributesQuantities = {};

        if (selectedProduct.type === 'service') {
            attributes.forEach(attr => {
                selectedAttributes[attr.key] = false;
                selectedAttributesQuantities[attr.key] = 0;
            });
        }

        let derivedPrice = selectedProduct.type === 'product' && selectedProduct.price === 0 ? 0 : parseFloat(selectedProduct.price) || 0;
        let derivedStock = selectedProduct.quantity_in_stock;

        // Initialize totalAttributeQuantity for services
        let totalAttributeQuantity = 0;

        // Calculate total attribute quantity for services
        if (selectedProduct.type === 'service') {
            totalAttributeQuantity = Object.values(this.taskProducts[index].selectedAttributesQuantities)
                .reduce((sum, quantity) => sum + quantity, 0);

            // Adjust derived price based on attributes for services
            derivedPrice = attributes.reduce((total, attr) => {
                const attrQuantity = this.taskProducts[index].selectedAttributesQuantities[attr.key] || 0;
                return total + (attrQuantity * (parseFloat(attr.value) || 0));
            }, derivedPrice);
        }

        const materialStockOverviewLookup = this.materialStockOverview.reduce((acc, material) => {
            acc[material.title] = material.remainingStock;
            return acc;
        }, {});

        const calculateDerivedValues = (materials) => {
            const productQuantity = this.taskProducts[index].quantity || 0;

            // Calculate derived price based on selected materials for products
            if (selectedProduct.type === 'product' && derivedPrice === 0) {
                derivedPrice = materials.reduce((total, material) => {
                    return material.selected ? total + (material.price_per_unit * material.usage_per_unit * productQuantity) : total;
                }, 0);
            }

            // Adjust stock for selected materials based on the product quantity or service attribute quantities
            const stockQuantities = materials
                .filter(material => material.selected)
                .map(material => {
                    const adjustedStock = materialStockOverviewLookup[material.title] !== undefined
                        ? materialStockOverviewLookup[material.title]
                        : material.quantity_in_stock;

                    // Directly subtract the `usage_per_unit` multiplied by the product or service quantity
                    const usageMultiplier = selectedProduct.type === 'service' ? totalAttributeQuantity : productQuantity;

                    return adjustedStock - (material.usage_per_unit * usageMultiplier);
                });

            // Set derived stock as the minimum stock available across selected materials
            derivedStock = stockQuantities.length ? Math.min(...stockQuantities) : selectedProduct.quantity_in_stock;

            const selectedStates = materials.reduce((acc, material) => {
                acc[material.id] = material.selected;
                return acc;
            }, {});

            materials.forEach(material => {
                const adjustedStock = materialStockOverviewLookup[material.title] !== undefined
                    ? materialStockOverviewLookup[material.title]
                    : material.quantity_in_stock;

                // Adjust material stock based on the exact usage, not a percentage
                const usageMultiplier = selectedProduct.type === 'service' ? totalAttributeQuantity : productQuantity;

                material.displayStock = material.selected
                    ? adjustedStock - (material.usage_per_unit * usageMultiplier)
                    : adjustedStock;

                material.selected = selectedStates[material.id] || false;
            });

            this.taskProducts[index] = {
                ...this.taskProducts[index],
                type: selectedProduct.type || 'unknown',
                attributes: selectedProduct.type === 'service' ? attributes : [],
                selectedAttributes,
                selectedAttributesQuantities,
                quantity: productQuantity,
                selectedProductDetails: { ...selectedProduct, derivedPrice, derivedStock },
                linkedMaterials: materials
            };
        };

        // Fetch materials and calculate derived values
        if (this.materialsCache[selectedProduct.id]) {
            const materials = this.materialsCache[selectedProduct.id];
            calculateDerivedValues(materials);
        } else {
            axios.get(`/api/products/${selectedProduct.id}/materials`)
                .then(response => {
                    const materials = response.data.map(material => ({
                        ...material,
                        selected: false
                    }));
                    this.materialsCache[selectedProduct.id] = materials;
                    calculateDerivedValues(materials);
                })
                .catch(error => {
                    console.error('Error fetching linked materials:', error);
                    this.taskProducts[index].linkedMaterials = [];
                    this.taskProducts[index].selectedProductDetails = {
                        ...selectedProduct,
                        derivedPrice: selectedProduct.price,
                        derivedStock: selectedProduct.quantity_in_stock
                    };
                });
        }
    } else {
        console.error('Selected product not found.');
    }
},





    // New method to handle material selection changes
    onMaterialSelectionChange(index) {
        this.onProductChange(index); // Recalculate values based on selected materials
    },

    onProductQuantityChange(index) {
        this.onProductChange(index); // Recalculate values based on the new quantity
    },

    toggleAttribute(taskProduct, attribute) {
      if (taskProduct.selectedAttributes[attribute.key]) {
        // Deselect the attribute
        taskProduct.selectedAttributes[attribute.key] = false;
        taskProduct.selectedAttributesQuantities[attribute.key] = 0; // Reset quantity to 1
      } else {
        // Select the attribute
        taskProduct.selectedAttributes[attribute.key] = true;
        taskProduct.selectedAttributesQuantities[attribute.key] = 0; // Set default quantity to 1
      }
    },

    fetchProducts() {
        this.initialTaskType = 'product';
        if (this.localProject && this.localProject.user_id) {
            return axios.get(`/api/products/${this.localProject.user_id}`)
                .then(response => {
                    this.products = response.data.products;
                })
                .catch(error => {
                    console.error('Error fetching products:', error);
                });
        } else {
            console.error('Cannot fetch products because user_id is not available');
            return Promise.reject('User ID is not available');
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
    const taskProduct = this.taskProducts[index];
    console.log("Task Product:", taskProduct);

    const selectedProduct = this.products.find(product => product.id === taskProduct.selectedProduct);
    console.log("Selected Product:", selectedProduct);

    if (selectedProduct) {
        let totalPrice = 0;
        const isService = selectedProduct.type === 'service';
        console.log("Is Service:", isService);

        // Products with materials
        if (selectedProduct.type === 'product') {
            console.log("Adding product with materials...");
            const selectedMaterials = taskProduct.linkedMaterials
                .filter(material => material.selected)
                .map(material => {
                    const usagePerUnit = parseFloat(material.usage_per_unit) || 0;
                    const pricePerUnit = parseFloat(material.price_per_unit) || 0;
                    const quantityUsed = usagePerUnit * (taskProduct.quantity || 1);
                    const materialCost = quantityUsed * pricePerUnit;

                    console.log("Material:", material.title, "Usage Per Unit:", usagePerUnit, "Price Per Unit:", pricePerUnit, "Quantity Used:", quantityUsed, "Material Cost:", materialCost);

                    totalPrice += materialCost;
                    console.log("Total Price (after material):", totalPrice);

                    return {
                        title: material.title,
                        unitType: material.unit_type,
                        quantityUsed: quantityUsed.toFixed(2),
                        totalCost: materialCost.toFixed(2),
                        initialStock: material.quantity_in_stock,
                        minimumStockAlert: material.minimum_stock_alert
                    };
                });

            console.log("Selected Materials:", selectedMaterials);

            // Add the product with the total price calculated from materials
            this.addedProducts.push({
                ...selectedProduct,
                quantity: taskProduct.quantity,
                totalPrice: totalPrice.toFixed(2),
                selectedMaterials,
            });

            console.log("Product added with total price:", totalPrice);
        }

        // Services with attributes and materials
        if (isService) {
            console.log("Adding service with attributes and materials...");
            // Calculate attribute costs and material usage
            const selectedAttributes = Object.keys(taskProduct.selectedAttributes)
                .filter(key => taskProduct.selectedAttributes[key])
                .map(key => {
                    const attribute = selectedProduct.attributes.find(attr => attr.key === key);
                    const attrQuantity = taskProduct.selectedAttributesQuantities[key];
                    const attrPrice = parseFloat(attribute.value) || 0;
                    const attributeTotal = attrQuantity * attrPrice;

                    console.log("Attribute:", attribute.key, "Quantity:", attrQuantity, "Price:", attrPrice, "Attribute Total:", attributeTotal);

                    totalPrice += attributeTotal;
                    console.log("Total Price (after attribute):", totalPrice);

                    return {
                        attribute: key,
                        quantity: attrQuantity,
                        price: attrPrice,
                        total: attributeTotal.toFixed(2),
                    };
                });

            const selectedMaterials = taskProduct.linkedMaterials
                .filter(material => material.selected)
                .map(material => {
                    const usagePerUnit = parseFloat(material.usage_per_unit) || 0;
                    const pricePerUnit = parseFloat(material.price_per_unit) || 0;
                    const totalAttributeQuantity = selectedAttributes.reduce((sum, attr) => sum + attr.quantity, 0);
                    const quantityUsed = usagePerUnit * totalAttributeQuantity;
                    const materialCost = quantityUsed * pricePerUnit;

                    console.log("Material:", material.title, "Usage Per Unit:", usagePerUnit, "Price Per Unit:", pricePerUnit, "Total Attribute Quantity:", totalAttributeQuantity, "Quantity Used:", quantityUsed, "Material Cost:", materialCost);

                    totalPrice += materialCost;
                    console.log("Total Price (after materials for service):", totalPrice);

                    return {
                        title: material.title,
                        unitType: material.unit_type,
                        quantityUsed: quantityUsed.toFixed(2),
                        totalCost: materialCost.toFixed(2),
                        initialStock: material.quantity_in_stock,
                        minimumStockAlert: material.minimum_stock_alert
                    };
                });

            console.log("Selected Attributes:", selectedAttributes);
            console.log("Selected Materials for Service:", selectedMaterials);

            // Add the service with the total price calculated from attributes and materials
            this.addedProducts.push({
                ...selectedProduct,
                totalPrice: totalPrice.toFixed(2),
                selectedAttributes,
                selectedMaterials,
            });

            console.log("Service added with total price:", totalPrice);
        }

        // Reset task product fields after adding to ongoing tasks
        this.taskProducts[index] = {
            selectedProduct: null,
            quantity: 0,
            type: 'product',
            attributes: [],
            selectedAttributes: {},
            selectedAttributesQuantities: {},
        };

        console.log("Task Product reset after addition.");
    } else {
        console.log("Selected product not found.");
    }

    console.log('Final Added Products/Services:', this.addedProducts);
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
            // Directly reset the taskProducts entry at the correct index
            this.taskProducts[index] = {
                selectedProduct: null,
                quantity: 0,
                type: 'product',
                attributes: [],
                selectedAttributes: {},
                selectedAttributesQuantities: {}
            };
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

    // Case for products
    if (product.type === 'product') {
        // Products with selected materials
        if (product.selectedMaterials && product.selectedMaterials.length > 0) {
            product.selectedMaterials.forEach(material => {
                const usagePerUnit = parseFloat(material.usage_per_unit) || 0;  // Get usage per unit
                const quantityUsed = usagePerUnit * (product.quantity || 1);    // Update quantity used based on product quantity
                const materialCost = quantityUsed * (parseFloat(material.price_per_unit) || 0); // Calculate cost based on quantity used

                // Dynamically update the material data
                material.quantityUsed = quantityUsed.toFixed(2);  // Update material's quantity used
                material.totalCost = materialCost.toFixed(2);     // Update material's total cost

                totalPrice += materialCost;
            });
        }
    }

    // Case for services
    else if (product.type === 'service') {
        // Start with the base price of the service (if any)
        totalPrice += parseFloat(product.price) || 0;

        // Add the prices of the selected attributes
        if (product.selectedAttributes && product.selectedAttributes.length > 0) {
            product.selectedAttributes.forEach(attr => {
                const attrPrice = parseFloat(attr.price) || 0;
                const attrQuantity = parseInt(attr.quantity) || 0;
                const attrTotal = attrPrice * attrQuantity;
                totalPrice += attrTotal;
            });
        }

        // Add material costs (if materials are also selected for the service)
        if (product.selectedMaterials && product.selectedMaterials.length > 0) {
            product.selectedMaterials.forEach(material => {
                const usagePerUnit = parseFloat(material.usage_per_unit) || 0;
                const quantityUsed = usagePerUnit * (product.quantity || 1);  // Update quantity used based on product quantity
                const materialCost = quantityUsed * (parseFloat(material.price_per_unit) || 0);

                // Dynamically update the material data
                material.quantityUsed = quantityUsed.toFixed(2);  // Update material's quantity used
                material.totalCost = materialCost.toFixed(2);     // Update material's total cost

                totalPrice += materialCost;
            });
        }
    }

    // Return the total price rounded to two decimal places
    return Math.round(totalPrice * 100) / 100;
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