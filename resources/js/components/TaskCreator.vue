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
                                </div>

                                <!-- Attribute Quantity Input -->
                                <div class="flex items-center mt-2 md:mt-0 md:ml-6">
                                    <label class="text-gray-600 mr-2">Quantity:</label>
                                    <input 
                                        type="number" 
                                        v-model="taskProduct.selectedAttributesQuantities[attribute.key]" 
                                        min="0"
                                        @input="onAttributeQuantityChange(index, attribute.key)" 
                                        class="shadow border rounded py-1 px-3 w-20 text-center text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 transition duration-300 ease-in-out">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Shared Materials Section -->
                <div v-if="taskProduct.linkedMaterials && taskProduct.linkedMaterials.length > 0" class="mt-6">
                    <p class="text-lg font-semibold text-blue-700 mb-4">Select Materials:</p>

                    <!-- Simple Materials Section -->
                    <div v-if="simpleMaterials[index]?.length > 0">
                      <p class="text-md font-semibold text-gray-800 mb-2">Simple Materials:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div 
                                v-for="material in simpleMaterials[index]" 
                                :key="material.id" 
                                class="p-4 border border-gray-200 rounded-lg shadow-md bg-white hover:shadow-lg transition-shadow duration-300">

                                <!-- Material Header -->
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-xl font-semibold text-gray-800">{{ material.title }}</p>
                                    <input 
                                        type="checkbox" 
                                        v-model="material.selected" 
                                        @change="onMaterialSelectionChange(index)" 
                                        class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500" />
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
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Usage per Unit:</span>
                                        <span>{{ material.usage_per_unit }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Price per Unit:</span>
                                        <span>{{ material.price_per_unit }} kr</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Materials Section -->
                    <div v-if="advancedMaterials[index]?.length > 0" class="mt-6">
                      <p class="text-md font-semibold text-gray-800 mb-2">Advanced Materials:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div 
                                v-for="material in advancedMaterials[index]" 
                                :key="material.id" 
                                class="p-4 border border-gray-200 rounded-lg shadow-md bg-white hover:shadow-lg transition-shadow duration-300">

                                <!-- Parent Material Header -->
                                <p class="text-xl font-semibold text-gray-800 mb-3">{{ material.title }}</p>

                                <!-- Child Materials Section -->
                                <div class="text-sm text-gray-700 space-y-2">
                                    <p class="font-medium text-blue-600 mb-2">Child Materials:</p>
                                    <div 
                                        v-for="child in material.child_materials" 
                                        :key="child.id" 
                                        class="p-2 border border-gray-300 rounded-lg shadow-sm bg-gray-50 mb-2">

                                        <!-- Child Material Header -->
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-600">{{ child.title }}</span>
                                            <input 
                                                type="checkbox" 
                                                v-model="child.selected" 
                                                @change="onChildMaterialSelectionChange(index, material, child)" 
                                                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500" />
                                        </div>

                                        <!-- Child Material Details -->
                                        <div class="flex justify-between mt-1">
                                            <span class="font-medium text-gray-600">Stock:</span>
                                            <span :class="{'text-red-600': child.quantity_in_stock <= child.minimum_stock_alert, 'text-green-600': child.quantity_in_stock > child.minimum_stock_alert}">
                                                {{ child.quantity_in_stock }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-600">Price per Unit:</span>
                                            <span>{{ child.price_per_unit }} kr</span>
                                        </div>

                                        <!-- Quantity Input for Advanced Materials -->
                                        <div class="flex justify-between mt-2">
                                            <span class="font-medium text-gray-600">Quantity:</span>
                                            <input 
                                                type="number" 
                                                min="0" 
                                                v-model="child.quantity" 
                                                class="w-16 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 text-right" 
                                                placeholder="0" />
                                        </div>

                                        <!-- Predefined Usage Section -->
                                        <div v-if="child.predefined_usage" class="text-sm mt-2">
                                            <span class="italic text-gray-500">Predefined Usage: {{ child.predefined_usage }} units</span>
                                        </div>
                                    </div>
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
                  @input="recalculateProductTotal(index)"
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
            <p class="text-gray-700 mt-2"><strong>Total Price:</strong> {{ product.totalPrice }} kr</p>
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
          selectedAttributesQuantities: {}, // { 'size': 0, 'color': 0 }
          selectedProductDetails: null, // Initialize as null or an empty object
          linkedMaterials: [], // Array of materials linked to the product
        }
      ],
      addedProducts: [],
      showEditModal: false,
      editProductIndex: null,
      editProductDetails: {
        quantity: 0,
        // Add other fields for editing if needed
    },
    };
  },
  computed: {
    simpleMaterials() {
        return this.taskProducts.map(taskProduct =>
            taskProduct?.linkedMaterials?.filter(material => !material.is_parent_material) || []
        );
    },
    advancedMaterials() {
        return this.taskProducts.map(taskProduct =>
            taskProduct?.linkedMaterials?.filter(material => material.is_parent_material) || []
        );
    },
    materialStockOverview() {
        const stockMap = {};

        this.addedProducts.forEach(product => {
            (product.selectedMaterials || []).forEach(material => {
                const initialStock = Number(material.initialStock) || 0;
                const quantityUsed = Number(material.quantityUsed) || 0;
                const usagePerUnit = Number(material.usage_per_unit) || 0;
                const minimumStockAlert = Number(material.minimumStockAlert) || 0;

                if (!stockMap[material.title]) {
                    stockMap[material.title] = {
                        title: material.title,
                        unitType: material.unitType,
                        initialStock,
                        remainingStock: initialStock - quantityUsed, // Ensure subtraction is numeric
                        minimumStockAlert
                    };
                } else {
                    stockMap[material.title].remainingStock -= quantityUsed; // Ensure numeric subtraction
                }

                // Adjust for service attributes if it's a service
                if (product.type === 'service') {
                    Object.keys(product.selectedAttributesQuantities || {}).forEach(attrKey => {
                        const attrQuantity = parseFloat(product.selectedAttributesQuantities[attrKey]) || 0;
                        // stockMap[material.title].remainingStock -= attrQuantity * usagePerUnit; // Ensure numeric subtraction
                    });
                }

                // Safeguard against negative or NaN remaining stock
                stockMap[material.title].remainingStock = isNaN(stockMap[material.title].remainingStock)
                    ? 0
                    : stockMap[material.title].remainingStock;
            });
        });

        // Return array of materials with all numbers validated
        return Object.values(stockMap).map(material => ({
            ...material,
            remainingStock: isNaN(material.remainingStock) ? 0 : material.remainingStock, // Fallback to 0 if NaN
            initialStock: isNaN(material.initialStock) ? 0 : material.initialStock // Ensure initialStock is valid
        }));
    },

    totalProductSummary() {
        let totalPrice = 0;
        let totalQuantity = 0;

        this.addedProducts.forEach(product => {
            totalPrice += parseFloat(product.totalPrice) || 0;
            totalQuantity += product.type === 'product'
                ? (parseFloat(product.quantity) || 0) // Ensure numeric value for quantity
                : this.getAttributeTotalQuantity(product);
        });

        return {
            totalPrice: isNaN(totalPrice) ? '0.00' : totalPrice.toFixed(2), // Ensure valid total price
            totalQuantity: isNaN(totalQuantity) ? 0 : totalQuantity, // Ensure valid total quantity
            products: this.addedProducts.map(product => ({
                title: product.title,
                quantity: isNaN(product.quantity) ? 0 : product.type === 'product' ? product.quantity : this.getAttributeTotalQuantity(product),
                totalPrice: isNaN(product.totalPrice) ? '0.00' : parseFloat(product.totalPrice).toFixed(2) // Ensure valid product total price
            }))
        };
    }
},

  watch: {
    taskProducts: {
        handler(newTaskProducts, oldTaskProducts) {
            newTaskProducts.forEach((taskProduct, index) => {
              const oldTaskProduct = oldTaskProducts?.[index];

                if (taskProduct.selectedProduct !== oldTaskProducts[index]?.selectedProduct) {
                    // Only call updateDerivedPriceAndStock if materials have been selected
                    if (taskProduct.linkedMaterials.some(material => material.selected)) {
                        this.updateDerivedPriceAndStock(index);
                    } else {
                        // Reset price if no materials are selected
                        taskProduct.selectedProductDetails.derivedPrice = '0.00';
                    }
                }

                // Watch for changes in advanced material child quantities
                taskProduct.linkedMaterials.forEach(material => {
                    if (material.is_parent_material) {
                        material.child_materials.forEach((child, childIndex) => {
                            const oldChild = oldTaskProduct.linkedMaterials?.[index]?.child_materials?.[childIndex];
                            if (child.selected !== oldChild?.selected || child.quantity !== oldChild?.quantity) {
                                this.updateDerivedPriceAndStock(index);
                            }
                        });
                    }
                });

                // Watch for changes in service attributes quantities
                if (taskProduct.type === 'service') {
                    Object.keys(taskProduct.selectedAttributesQuantities || {}).forEach(attrKey => {
                        if (taskProduct.selectedAttributesQuantities[attrKey] !== oldTaskProducts[index]?.selectedAttributesQuantities?.[attrKey]) {
                            // Recalculate the total price for the service when attributes change
                            taskProduct.selectedProductDetails.derivedPrice = this.calculateTotalPrice(taskProduct, taskProduct.linkedMaterials);
                        }
                    });
                }
            });
        },
        deep: true
    },

    addedProducts: {
        handler(newProducts, oldProducts) {
            newProducts.forEach((product, index) => {
                if (product !== oldProducts[index]) {
                    if (!product.selectedProductDetails) {
                        const selectedProduct = this.products.find(p => p.id === product.id);
                        if (selectedProduct) {
                            product.selectedProductDetails = { ...selectedProduct };
                        } else {
                            console.warn('No selected product details found for added product from Watch:', product);
                            return; // Skip further processing if selectedProductDetails cannot be set
                        }
                    }

                    product.totalPrice = this.calculateTotalPrice(product, product.selectedMaterials);

                    // Ensure materials stock is updated if a product or service changes
                    if (product.type === 'product' || product.type === 'service') {
                        this.updateProductWithMaterials(index, product, product.selectedMaterials);
                    }
                }

                // Handle advanced materials specifically
                product.selectedMaterials?.forEach((material, materialIndex) => {
                    if (material.is_parent_material) {
                        material.child_materials.forEach((child, childIndex) => {
                            const oldChild = oldProduct.selectedMaterials?.[materialIndex]?.child_materials?.[childIndex];
                            if (child.selected !== oldChild?.selected || child.quantity !== oldChild?.quantity) {
                                this.updateDerivedPriceAndStock(index);
                            }
                        });
                    }
                });
            });
        },
        deep: true
    }
},

  mounted() {
    if (this.localProject.user_id) {
        this.fetchProducts()
            .then(() => this.fetchAllMaterials())
            .catch(error => console.error('Error initializing materials:', error));
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

    //Product based methods for task creation: 

    // Fetch all products from the API old method

    // preloadMaterialsForProducts() {
    //     this.products.forEach(product => {
    //         if (product.type !== 'material') { // Skip material type products
    //             axios.get(`/api/products/${product.id}/materials`)
    //                 .then(response => {
    //                     // Directly assign the cached materials without using this.$set
    //                     this.materialsCache[product.id] = response.data || [];
    //                 })
    //                 .catch(error => {
    //                     console.error(`Error preloading materials for product ${product.id}:`, error);
    //                 });
    //         }
    //     });
    // },

    simulatedMaterialStock(materials = [], quantity, attributes = {}) {
      // Create a lookup for each material's remaining stock from the overall stock overview
      const materialStockOverviewLookup = this.materialStockOverview.reduce((acc, material) => {
          acc[material.title] = material.remainingStock;
          return acc;
      }, {});

      return materials.map(material => {
          const usagePerUnit = parseFloat(material.usage_per_unit) || 0; 
          const initialStock = !Number.isNaN(Number(materialStockOverviewLookup[material.title]))
              ? Number(materialStockOverviewLookup[material.title])
              : parseFloat(material.quantity_in_stock) || 0; 

          let totalQuantityUsed = 0;

          if (material.selected) {
              if (quantity > 0) {
                  // Calculate base quantity usage for products
                  totalQuantityUsed = usagePerUnit * quantity;
              }

              // Add usage from each selected attribute for services
              Object.keys(attributes).forEach(attrKey => {
                  if (attributes[attrKey]) { 
                      const attrQuantity = parseFloat(this.taskProducts[0].selectedAttributesQuantities[attrKey]) || 0;
                      totalQuantityUsed += usagePerUnit * attrQuantity;
                  }
              });
          }

          totalQuantityUsed = isNaN(totalQuantityUsed) ? 0 : totalQuantityUsed; 

          return {
              ...material,
              initialStock: isNaN(initialStock) ? 0 : initialStock,
              displayStock: initialStock - totalQuantityUsed,
              minimumStockAlert: material.minimum_stock_alert || 0,
          };
      });
  },

  getAttributeTotalQuantity(product) {
      return Object.values(product.selectedAttributesQuantities || {}).reduce((sum, qty) => sum + qty, 0);
    },

    onProductChange(index) {
        this.updateTaskProductDetails(index);

        const taskProduct = this.taskProducts[index];

        // Ensure taskProduct and selectedProduct exist before proceeding
        if (!taskProduct || !taskProduct.selectedProduct) {
            console.error("Selected product is not defined in taskProduct:", taskProduct);
            return;
        }

        // Reset fields that need clearing but retain selectedProduct value
        this.resetTaskProductFields(index);

        // Find the selected product in the products list
        const selectedProduct = this.products.find(p => p.id === taskProduct.selectedProduct);

        if (!selectedProduct) {
            console.error("Selected product not found in products list for id:", taskProduct.selectedProduct);
            return;
        }

        // Set selected product details with initial derivedPrice and derivedStock
        taskProduct.selectedProductDetails = {
            ...selectedProduct,
            derivedPrice: parseFloat(selectedProduct.price) || 0, // Set initial price
            derivedStock: parseInt(selectedProduct.quantity_in_stock) || 0, // Set initial stock
        };

        // Set the type explicitly
        taskProduct.type = selectedProduct.type;

        // Initialize attributes for services
        taskProduct.selectedAttributes = {};
        taskProduct.selectedAttributesQuantities = {};
        const attributes = selectedProduct.attributes || [];

        if (selectedProduct.type === 'service') {
            attributes.forEach(attr => {
                taskProduct.selectedAttributes[attr.key] = false;
                taskProduct.selectedAttributesQuantities[attr.key] = 0;
            });
        }

        // Load materials from cache, initializing each material's `selected` state to false
        const cachedMaterials = (this.materialsCache[selectedProduct.id] || []).map(material => ({
            ...material,
            initialStock: parseFloat(material.quantity_in_stock) || 0, // Ensure initial stock is set as a valid number
            selected: false // Ensure each material is initially not selected
        }));

        this.updateProductWithMaterials(index, selectedProduct, cachedMaterials);

        // Run updateDerivedPriceAndStock to set initial price and stock based on non-selected materials
        this.updateDerivedPriceAndStock(index);
    },

    // Update task product with materials and attributes
    updateProductWithMaterials(index, product, materials) {
        const taskProduct = this.taskProducts[index];
        taskProduct.selectedProductDetails = { ...product };
        taskProduct.quantity = taskProduct.quantity || 0;

        taskProduct.linkedMaterials = materials.map(material => ({
            ...material,
            initialStock: isNaN(material.initialStock) ? parseFloat(material.quantity_in_stock) || 0 : material.initialStock // Safeguard for valid `initialStock`
        }));

        // Simulate stock for materials based on initial quantity and attributes (even if zero)
        taskProduct.linkedMaterials = this.simulatedMaterialStock(taskProduct.linkedMaterials, taskProduct.quantity);


        this.taskProducts[index] = taskProduct;
    },

    // New method to handle material selection changes
    onMaterialSelectionChange(index) {
        const taskProduct = this.taskProducts[index];
        if (!taskProduct || !taskProduct.linkedMaterials) return;

        taskProduct.linkedMaterials.forEach(material => {
            if (!material.is_parent_material && material.selected) {
                // Simple material selection logic
                const materialEffectiveStock = Math.floor(material.displayStock / (material.usage_per_unit || 1));
                material.derivedStock = materialEffectiveStock;
            } else if (material.is_parent_material) {
                // Parent material logic: Ignore its `selected` state and focus on child materials
                material.child_materials.forEach(child => {
                    if (child.selected) {
                        // Update parent material stock or price based on child selections
                        const childEffectiveStock = Math.floor(child.quantity_in_stock / (child.usage_per_unit || 1));
                        material.derivedStock = childEffectiveStock;
                    }
                });
            }
        });

        // Recalculate overall price and stock
        this.updateDerivedPriceAndStock(index);
    },

    onChildMaterialSelectionChange(index, parentMaterial, childMaterial) {
        const taskProduct = this.taskProducts[index];
        if (!taskProduct || !parentMaterial || !childMaterial) return;

        // If all children are selected, mark the parent as selected; otherwise, deselect it
        parentMaterial.selected = parentMaterial.child_materials.every(child => child.selected);

        // Ensure derived stock and price are recalculated for the updated child quantity
        this.updateDerivedPriceAndStock(index);
    },

    onProductQuantityChange(index) {
        const taskProduct = this.taskProducts[index];
        if (!taskProduct) return;

        // Recalculate the material stock based on the updated quantity
        taskProduct.linkedMaterials = this.simulatedMaterialStock(
            taskProduct.linkedMaterials, 
            taskProduct.quantity
        );

        // Recalculate price and stock based on the new quantity
        this.updateDerivedPriceAndStock(index);
    },

    onAttributeQuantityChange(index, attrKey) {
        const taskProduct = this.taskProducts[index];
        if (!taskProduct) return;

        // If the attribute quantity is greater than 0, it will be considered in calculations
        const attrQuantity = parseFloat(taskProduct.selectedAttributesQuantities[attrKey]) || 0;

        if (attrQuantity > 0) {
            taskProduct.selectedAttributes[attrKey] = true; // Mark as active if quantity > 0
        } else {
            delete taskProduct.selectedAttributes[attrKey]; // Remove if quantity is 0
        }

        // Recalculate the material stock and price when attribute quantity changes
        taskProduct.linkedMaterials = this.simulatedMaterialStock(
            taskProduct.linkedMaterials,
            taskProduct.quantity,
            taskProduct.selectedAttributes
        );

        this.updateDerivedPriceAndStock(index);
    },

    updateDerivedPriceAndStock(index) {
        const taskProduct = this.taskProducts[index];
        if (!taskProduct || !taskProduct.selectedProductDetails) return;

        // Initialize total price and stock
        let totalPrice = 0;
        let derivedStock = Infinity; // Start with Infinity to ensure we get the lowest stock of selected materials

        // Iterate through all linked materials
        taskProduct.linkedMaterials.forEach(material => {
            if (material.selected && !material.is_parent_material) {
                // Simple material price and stock calculation
                const materialTotalPrice = material.price_per_unit * (material.usage_per_unit * (taskProduct.quantity || 0));
                totalPrice += materialTotalPrice;

                const materialEffectiveStock = Math.floor(material.displayStock / (material.usage_per_unit || 1));
                derivedStock = Math.min(derivedStock, materialEffectiveStock);
            } else if (material.is_parent_material) {
                // Advanced materials: Calculate price and stock based on child materials
                material.child_materials.forEach(child => {
                    if (child.selected) {
                        // Use child-specific quantity for calculations
                        const childQuantity = parseFloat(child.quantity) || 0;

                        // Calculate price for the selected child material
                        const childTotalPrice = child.price_per_unit * childQuantity;
                        totalPrice += childTotalPrice;

                        // Calculate stock based on the selected quantity and usage per unit
                        const childEffectiveStock = Math.floor(child.quantity_in_stock / (child.usage_per_unit || 1));
                        derivedStock = Math.min(derivedStock, childEffectiveStock);
                    }
                });
            }
        });

        // Handle attribute-specific calculations for services
        if (taskProduct.type === 'service') {
            Object.keys(taskProduct.selectedAttributesQuantities || {}).forEach(attrKey => {
                const attrQuantity = parseFloat(taskProduct.selectedAttributesQuantities[attrKey]) || 0;
                const attribute = taskProduct.selectedProductDetails.attributes?.find(a => a.key === attrKey);

                if (attribute && attrQuantity > 0) {
                    totalPrice += attrQuantity * parseFloat(attribute.value);
                }
            });
        }

        // If no materials are selected, fallback to base product stock
        if (derivedStock === Infinity) {
            derivedStock = parseInt(taskProduct.selectedProductDetails.quantity_in_stock) || 0;
        }

        // Update the task product's derived properties
        taskProduct.selectedProductDetails.derivedPrice = totalPrice.toFixed(2);
        taskProduct.selectedProductDetails.derivedStock = derivedStock;

        this.$forceUpdate(); // Ensure the UI updates
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

    fetchAllMaterials() {
        if (!this.products || this.products.length === 0) {
            console.error("No products found for the current user.");
            return;
        }

        this.products.forEach(product => {
            if (product.type !== 'material') { // Skip material type products
                axios.get(`/api/products/${product.id}/materials`)
                    .then(async response => {
                        const materials = await Promise.all(response.data.map(async material => {
                            if (material.is_parent_material) {
                                const childResponse = await axios.get(`/api/materials/${material.id}/children`);
                                material.childMaterials = childResponse.data;
                            }
                            material.selected = false; // Ensure each material is initially not selected
                            return material;
                        }));

                        this.materialsCache[product.id] = materials;
                    })
                    .catch(error => {
                        console.error(`Error fetching materials for product ${product.id}:`, error);
                    });
            }
        });
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
    const selectedProduct = this.products.find(p => p.id === taskProduct.selectedProduct);

    if (!selectedProduct) {
        console.warn('Selected product not found when adding:', taskProduct.selectedProduct);
        return;
    }

    if (!taskProduct.selectedProductDetails) {
        taskProduct.selectedProductDetails = { ...selectedProduct };
    }

    console.log('Task Product before calculating price:', taskProduct);

    // Calculate quantity for services outside the material mapping
    let serviceAttributeQuantity = 0;

    if (taskProduct.selectedProductDetails.type === 'service') {
        // Calculate total quantity based on selected attributes for services
        serviceAttributeQuantity = Object.values(taskProduct.selectedAttributesQuantities).reduce((sum, qty) => {
            return sum + (parseFloat(qty) || 0);
        }, 0);
    }

    // Filter and calculate selected materials with the pre-calculated quantity
    const selectedMaterials = taskProduct.linkedMaterials
        .filter(m => m.selected)
        .map(material => {
            // Use serviceAttributeQuantity if it's a service, else calculate based on product quantity
            const quantityUsed = taskProduct.selectedProductDetails.type === 'service'
                ? serviceAttributeQuantity
                : parseFloat(material.usage_per_unit) * (taskProduct.quantity || 1);

            const totalCost = quantityUsed * parseFloat(material.price_per_unit);

            return {
                ...material,
                quantityUsed: quantityUsed.toFixed(2),
                totalCost: totalCost.toFixed(2),
            };
        });

    if (!taskProduct.selectedProductDetails) {
        console.error('selectedProductDetails missing before price calculation:', taskProduct);
        return;
    }

    // Calculate total price, including materials
    const totalPrice = this.calculateTotalPrice(taskProduct, selectedMaterials);

    // Add the product or service to the list of added products
    this.addedProducts.push({
        ...selectedProduct,
        quantity: taskProduct.quantity,
        totalPrice: totalPrice.toFixed(2),
        selectedMaterials: JSON.parse(JSON.stringify(selectedMaterials)), // Deep copy
        selectedAttributesQuantities: JSON.parse(JSON.stringify(taskProduct.selectedAttributesQuantities)),
    });

    console.log('Product added to addedProducts:', this.addedProducts[this.addedProducts.length - 1]);

    this.resetTaskProduct(index);
},

    // Reset the task product to its initial state
    resetTaskProduct(index) {
      this.taskProducts[index] = {
        selectedProduct: null,
        quantity: 0,
        type: 'product',
        selectedProductDetails: null, // Clear any previously loaded product details
        linkedMaterials: [],
        attributes: {},
        selectedAttributes: {},
        selectedAttributesQuantities: {},
      };
    },

    // Reset specific fields for task product without overriding the selected product itself
    resetTaskProductFields(index) {
        const taskProduct = this.taskProducts[index];
        taskProduct.quantity = 0;
        taskProduct.selectedProductDetails = null;
        taskProduct.linkedMaterials = [];
        taskProduct.selectedAttributes = {};
        taskProduct.selectedAttributesQuantities = {};
    },

    updateTaskProductDetails(index) {
        const taskProduct = this.taskProducts[index];
        if (!taskProduct) return;

        console.log('Updating task product details for index:', index);
        console.log('Current task product:', taskProduct);

        // Find the selected product details
        const selectedProduct = this.products.find(p => p.id === taskProduct.selectedProduct);
        if (selectedProduct) {
            taskProduct.selectedProductDetails = { ...selectedProduct };
            console.log('Updated selectedProductDetails:', taskProduct.selectedProductDetails);
        } else {
            console.warn('Selected product not found:', taskProduct.selectedProduct);
        }
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

        // Check the type from selectedProductDetails instead of taskProduct directly
        if (taskProduct.selectedProductDetails.type === 'product' && taskProduct.quantity <= 0) {
            alert("Please specify a valid quantity for the product.");
            return;
        }

        // Validate for service products with attributes
        if (taskProduct.selectedProductDetails.type === 'service') {
            for (const key in taskProduct.selectedAttributes) {
                if (taskProduct.selectedAttributes[key] && (!taskProduct.selectedAttributesQuantities[key] || taskProduct.selectedAttributesQuantities[key] <= 0)) {
                    alert(`Please specify a valid quantity for the attribute: ${key}`);
                    return;
                }
            }
        }

        // Proceed to add product if validation passes
        if (!taskProduct.hasValidated) {  // Avoid re-validation or multiple triggers
            taskProduct.hasValidated = true;  // Set a temporary validation flag
            this.addProduct(index);
        }
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

    updatePriceAndStockWithTotalCalculation(index) {
        const taskProduct = this.taskProducts[index];

        if (!taskProduct || !taskProduct.selectedProductDetails) return;

        // Recalculate the derived price based on selected materials
        taskProduct.selectedProductDetails.derivedPrice = this.calculateTotalPrice(
            taskProduct,
            taskProduct.linkedMaterials
        );

        // Recalculate the derived stock by checking material stock
        taskProduct.selectedProductDetails.derivedStock = taskProduct.linkedMaterials.reduce(
          (totalStock, material) => totalStock + (material.selected ? material.displayStock : 0),
          parseInt(taskProduct.selectedProductDetails.quantity_in_stock) || 0
      );
    },

    calculateTotalPrice(taskProduct, selectedMaterials) {
        // Ensure selectedProductDetails is populated
        if (!taskProduct.selectedProductDetails) {
            console.warn('selectedProductDetails is missing; attempting to update.');
            this.updateTaskProductDetails(this.taskProducts.indexOf(taskProduct));
        }

        if (!taskProduct.selectedProductDetails) {
            console.error('No selected product details found for taskProduct from CalTotalPrice:', taskProduct);
            return 0;
        }

        let totalPrice = 0;

        const { type, price, attributes } = taskProduct.selectedProductDetails;
        
        // Handle products
        if (type === 'product') {
            // Calculate the base product price multiplied by the quantity
            const productBasePrice = parseFloat(price) || 0;
            totalPrice = productBasePrice * (taskProduct.quantity || 0); // Ensure the quantity is factored in

            // Add selected material costs
            selectedMaterials.forEach(material => {
                const materialCostPerUnit = parseFloat(material.price_per_unit) || 0;
                const materialUsed = parseFloat(material.usage_per_unit) * (taskProduct.quantity || 0);
                const materialCost = materialCostPerUnit * materialUsed;
                totalPrice += materialCost; // Add material cost to the total price
            });

            return isNaN(totalPrice) ? 0 : parseFloat(totalPrice.toFixed(2)); // Ensure valid number
          }

        // Handle services
        if (type === 'service') {
            totalPrice = parseFloat(price) || 0;

            // Calculate attribute prices based on quantities
            Object.keys(taskProduct.selectedAttributesQuantities).forEach(attrKey => {
                const attrQuantity = parseFloat(taskProduct.selectedAttributesQuantities[attrKey]) || 0;
                const attribute = attributes?.find(a => a.key === attrKey);
                const attrPrice = parseFloat(attribute?.value) || 0;
                totalPrice += attrQuantity * attrPrice;
            });

            // Calculate material costs for each attribute quantity individually
            Object.keys(taskProduct.selectedAttributesQuantities).forEach(attrKey => {
                const attrQuantity = parseFloat(taskProduct.selectedAttributesQuantities[attrKey]) || 0;
                selectedMaterials.forEach(material => {
                    const materialCostPerUnit = parseFloat(material.price_per_unit) || 0;
                    const materialUsed = parseFloat(material.usage_per_unit) * attrQuantity;
                    totalPrice += materialCostPerUnit * materialUsed;
                });
            });

            return isNaN(totalPrice) ? 0 : parseFloat(totalPrice.toFixed(2));
        }

        return isNaN(totalPrice) ? 0 : parseFloat(totalPrice.toFixed(2));
    },

    recalculateProductTotal(index) {
        const product = this.addedProducts[index];

        // Ensure selectedProductDetails is available
        if (!product.selectedProductDetails) {
            const selectedProduct = this.products.find(p => p.id === product.id);
            if (selectedProduct) {
                product.selectedProductDetails = { ...selectedProduct };
            } else {
                console.warn('Product details not found during recalculation:', product.id);
                return;
            }
        }

        // Recalculate total price and update materials
        const updatedMaterials = product.selectedMaterials.map(material => {
            const quantityUsed = parseFloat(material.usage_per_unit) * (product.quantity || 1);
            const totalCost = quantityUsed * parseFloat(material.price_per_unit);

            return {
                ...material,
                quantityUsed: quantityUsed.toFixed(2),
                totalCost: totalCost.toFixed(2),
            };
        });

        const newTotalPrice = this.calculateTotalPrice(product, updatedMaterials);

        // Update product with new values directly
        this.addedProducts[index] = {
            ...product,
            totalPrice: newTotalPrice.toFixed(2),
            selectedMaterials: JSON.parse(JSON.stringify(updatedMaterials)), // Ensure deep copy
        };
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

                // Add materials data if present
                productData.materials = product.selectedMaterials.map(material => ({
                        material_id: material.id,
                        title: material.title,
                        quantity_used: material.quantityUsed,
                        total_cost: material.totalCost
                    }));

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

       // Log the final data object for review before sending it to the server
      console.log("Data payload to be sent:", JSON.stringify(data, null, 2));

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