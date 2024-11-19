<template>
  <div
    class="fixed z-10 inset-0 flex items-center justify-center"
    style="background-color: rgba(0,0,0,0.5);"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
    @click.self="closeModal"
  >
  <div class="bg-white rounded-lg w-full max-w-lg m-4 shadow-lg overflow-hidden">
  <!-- Modal Header -->
  <div class="p-4 border-b">
    <h2 class="text-xl font-bold text-gray-800 text-center">
      {{ type 
          ? (type === 'product' ? 'Create Product' 
          : type === 'service' ? 'Create Service' 
          : 'Create Material') 
          : 'Create a New Item' }}
    </h2>
    <p class="text-gray-600 text-center">
      {{ type 
          ? (type === 'product' 
              ? 'Define product details including stock and pricing.' 
              : type === 'service' 
              ? 'Set up your service with base price and customizable options.' 
              : 'Define the material for inventory tracking and usage management.') 
          : 'Please select a type to get started.' }}
    </p>
    <p v-if="type" class="text-sm text-gray-500 text-center mt-2">
      {{ type === 'product' 
          ? 'Only the title is required for products; all other fields are optional.' 
          : type === 'service' 
          ? 'For services, both the title and at least one option are required.' 
          : 'For materials, specify details such as cost per unit and inventory management.' }}
    </p>
  </div>

     <!-- Form with Scrollable Content and Fixed Footer -->
     <form @submit.prevent="createProduct">
        <!-- Modal Content (Scrollable) -->
        <div class="p-8 overflow-y-auto" style="max-height: 60vh;">
          <!-- Type Selection -->
          <div class="mb-4">
            <label for="type" class="block text-gray-700 font-semibold mb-2">Type:</label>
            <select
              id="type"
              v-model="type"
              @change="handleTypeChange"
              class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option disabled value="">Select Type</option>
              <option value="product">Product</option>
              <option value="service">Service</option>
              <option value="material">Material</option>
            </select>
          </div>

          <!-- Title Field -->
          <div v-if="type" class="mb-4">
            <label for="title" class="block text-gray-700 font-semibold mb-2">Title (Required):</label>
            <input
              type="text"
              id="title"
              v-model="title"
              required
              class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              :placeholder="`Enter a name for the ${type}`"
            />
          </div>

          <!-- Material-Specific Fields -->
          <div v-if="type === 'material'">
            <!-- Link to Product/Service/Material -->
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
              <h3 class="text-gray-700 font-semibold mb-2">Link material</h3>
              <p class="text-gray-500 text-sm mb-2">
                Choose an existing item to link this material with.
              </p>
              <select
                id="parentMaterial"
                v-model="parentMaterial"
                class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select Linked Item</option>
                <option v-for="item in availableMaterials" :key="item.id" :value="item.id">
                  {{ item.title }}
                </option>
              </select>
            </div>

            <!-- Show Additional Fields Only if parentMaterial is Selected -->
            <div v-if="parentMaterial">
              <!-- Parent Material Toggle -->
              <div class="flex items-start space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition mb-4">
                <input
                  type="checkbox"
                  id="isParentMaterial"
                  v-model="isParentMaterial"
                  class="h-5 w-5 text-blue-600 border-gray-300 rounded mt-1"
                />
                <span>
                  <label for="isParentMaterial" class="text-gray-700 font-medium">Set as Parent Material</label>
                  <p class="text-gray-500 text-sm mt-1">Enable this option to add child materials.</p>
                </span>
              </div>

              <!-- Child Materials Section (Only if isParentMaterial is true) -->
              <div v-if="isParentMaterial" class="bg-gray-50 p-4 rounded-lg mb-4">
                <h3 class="text-gray-700 font-semibold mb-2">Add Child Materials</h3>

                <!-- List of Child Materials -->
                <div v-for="(child, index) in childMaterials" :key="index" class="bg-white p-3 rounded-lg mb-2 border">
                  <div class="flex justify-between items-center mb-2">
                    <h4 class="text-gray-700">Child Material {{ index + 1 }}</h4>
                    <button @click="removeChildMaterial(index)" class="text-red-500 text-sm">Remove</button>
                  </div>

                  <!-- Child Material Fields -->
                  <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                      <label class="block text-gray-700 font-semibold">Child Material Name:</label>
                      <input
                        type="text"
                        v-model="child.title"
                        class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none"
                        :placeholder="`e.g., Small, Medium, Large for '${title}'`"
                        required
                      />
                      <p v-if="!child.title" class="text-red-500 text-sm mt-1">This field is required.</p>
                      <p class="text-gray-500 text-sm mt-1">
                        This is the specific name for the variation of the parent material "{{ title }}".
                      </p>
                    </div>
                    <div>
                      <label class="block text-gray-700 font-semibold">Unit Type:</label>
                      <input
                        type="text"
                        v-model="child.unitType"
                        class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none"
                        placeholder="e.g., grams, pieces"
                        required
                      />
                      <p v-if="!child.unitType" class="text-red-500 text-sm mt-1">This field is required.</p>
                      <p class="text-gray-500 text-sm mt-1">
                        Inherits "{{ unitType }}" by default. Edit if different.
                      </p>
                    </div>
                    <div>
                      <label class="block text-gray-700 font-semibold">Stock Quantity:</label>
                      <input
                        type="number"
                        v-model="child.quantity"
                        min="0"
                        step="1"
                        class="shadow border rounded w-full py-2 px-3 text-gray-700"
                        placeholder="Total stock"
                      />
                    </div>
                    <div>
                      <label class="block text-gray-700 font-semibold">Minimum Stock Alert:</label>
                      <input
                        type="number"
                        v-model="child.minimumStockAlert"
                        min="0"
                        step="1"
                        class="shadow border rounded w-full py-2 px-3 text-gray-700"
                        placeholder="Alert level"
                      />
                    </div>
                    <div>
                      <label class="block text-gray-700 font-semibold">Usage per Unit:</label>
                      <input
                        type="number"
                        v-model="child.usagePerUnit"
                        min="0"
                        step="0.01"
                        class="shadow border rounded w-full py-2 px-3 text-gray-700"
                        placeholder="Usage per unit"
                        required
                      />
                      <p v-if="!child.usagePerUnit" class="text-red-500 text-sm mt-1">This field is required.</p>
                      <p v-if="child.unitType" class="text-gray-500 text-sm mt-1">
                        This defines how much of this material (in {{ child.unitType }}) is used per item.
                      </p>
                    </div>
                    <div>
                      <label class="block text-gray-700 font-semibold">Cost per Unit:</label>
                      <input
                        type="number"
                        v-model="child.costPerUnit"
                        min="0"
                        step="0.01"
                        class="shadow border rounded w-full py-2 px-3 text-gray-700"
                        placeholder="Cost per unit"
                      />
                    </div>
                    <div>
                      <label class="block text-gray-700 font-semibold">Price per Unit:</label>
                      <input
                        type="number"
                        v-model="child.pricePerUnit"
                        min="0"
                        step="0.01"
                        class="shadow border rounded w-full py-2 px-3 text-gray-700"
                        placeholder="Price per unit"
                      />
                    </div>
                  </div>
                </div>

                <!-- Add Child Material Button -->
                <div class="flex justify-center mt-4">
                  <button @click.prevent="addChildMaterial" type="button" class="px-4 py-2 bg-blue-500 text-white rounded-lg">
                    Add Child Material
                  </button>
                </div>
              </div>

              <!-- Inventory Details Section (First) -->
              <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <h3 @click="toggleInventorySection" class="text-gray-700 font-semibold mb-2 cursor-pointer flex items-center">
                  Inventory Details
                  <font-awesome-icon :icon="inventorySectionVisible ? 'chevron-up' : 'chevron-down'" class="ml-2" />
                </h3>
                
                <transition name="fade">
                  <div v-if="inventorySectionVisible">
                    <!-- Unit Type and Total Quantity Fields -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                      <div>
                        <label for="unitType" class="block text-gray-700 font-semibold">Unit Type:</label>
                        <input
                          type="text"
                          id="unitType"
                          v-model="unitType"
                          :class="['shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none', unitType ? '' : 'border-red-500']"
                          placeholder="e.g., grams, pieces"
                          required
                        />
                        <p v-if="!unitType" class="text-red-500 text-sm mt-1">This field is required.</p>
                        <p class="text-gray-500 text-sm mt-1">
                          The unit type will impact how the cost and price calculations work.
                        </p>
                      </div>
                      <div>
                      <label for="totalQuantity" class="block text-gray-700 font-semibold">Total Quantity:</label>
                      <input
                        type="number"
                        id="totalQuantity"
                        v-model="totalQuantity"
                        min="0"
                        step="1"
                        class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none"
                        placeholder="Total in stock"
                      />
                      <p class="text-gray-500 text-sm mt-1">
                        Current available stock amount.
                        <span class="text-blue-500 cursor-pointer" @click="showQuantityInfo = !showQuantityInfo">(What's this?)</span>
                      </p>
                      
                      <!-- Tooltip for Total Quantity -->
                      <transition name="fade">
                        <p v-if="showQuantityInfo" class="text-gray-500 text-sm mt-2 bg-gray-100 p-2 rounded">
                          <strong>Note:</strong> Enter the total available quantity of this material based on your chosen unit type (e.g., 1000 grams, 1.5 kg). This value represents the full stock amount currently on hand.
                        </p>
                      </transition>
                    </div>
                  </div>

                    <!-- Usage per Unit and Minimum Stock Alert -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                      <div>
                        <label for="usagePerUnit" class="block text-gray-700 font-semibold">Usage per Unit:</label>
                        <input
                          type="number"
                          id="usagePerUnit"
                          v-model="usagePerUnit"
                          min="0"
                          step="0.01"
                          :class="['shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none', usagePerUnit ? '' : 'border-red-500']"
                          placeholder="Usage per unit"
                          required
                        />
                        <p v-if="!usagePerUnit" class="text-red-500 text-sm mt-1">This field is required.</p>
                        <!-- Conditional paragraph based on the unitType field -->
                        <p v-if="unitType" class="text-gray-500 text-sm mt-1">
                          This defines how much of this material (in {{ unitType }}) is used per item.
                        </p>
                      </div>
                      <div>
                        <label for="minimumStockAlert" class="block text-gray-700 font-semibold">Min. Stock Alert:</label>
                        <input
                          type="number"
                          id="minimumStockAlert"
                          v-model="minimumStockAlert"
                          min="0"
                          step="0.01"
                          class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none"
                          placeholder="Alert level"
                        />
                        <p class="text-gray-500 text-sm mt-1">
                          Set an alert threshold.
                          <span class="text-blue-500 cursor-pointer" @click="showMinStockInfo = !showMinStockInfo">(What's this?)</span>
                        </p>
                        
                        <!-- Tooltip for Minimum Stock Alert -->
                        <transition name="fade">
                          <p v-if="showMinStockInfo" class="text-gray-500 text-sm mt-2 bg-gray-100 p-2 rounded">
                            <strong>Note:</strong> This sets a threshold to alert you when stock reaches or falls below a certain level. You can enter fractional values if applicable (e.g., 0.5 kg).
                          </p>
                        </transition>
                      </div>
                    </div>
                  </div>
                </transition>
              </div>

              <!-- Material Details (Only if not a Parent Material) -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-gray-700 font-semibold mb-2">Material Details</h3>

                <!-- Cost per Unit (User Cost) -->
                <label class="block text-gray-700 font-semibold">Cost per Unit:</label>
                <input
                  type="number"
                  v-model="costPerUnit"
                  min="0"
                  step="0.01"
                  class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none"
                  placeholder="Cost per unit for your expense"
                />
                <p class="text-gray-500 text-sm mt-1">
                  This is the amount you pay for each unit of this material in {{ unitType }}.
                </p>
                <!-- Price per Unit (Selling Price) -->
                <label class="block text-gray-700 font-semibold mt-4">Price per Unit:</label>
                <input
                  type="number"
                  v-model="pricePerUnit"
                  min="0"
                  step="0.01"
                  class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none"
                  placeholder="Price per unit to charge customers"
                />
                <p class="text-gray-500 text-sm mt-1">This is the price you intend to charge customers for each unit of this material.</p>
                <!-- Conditional Paragraph if Linked Product Has a Fixed Price -->
                <p v-if="isLinkedProductFixedPrice" class="text-yellow-600 text-sm mt-1">
                  Note: The selected product has a fixed price. Changes to this field will not affect the product's price. 
                  To allow this material to influence the product price, set the product price to 0.
                </p>
                <!-- Real-Time Summary (Based on Inventory Details) -->
                <div v-if="usagePerUnit && costPerUnit && pricePerUnit" class="bg-gray-100 p-4 rounded mt-4">
                  <p class="text-gray-700 font-semibold">Summary</p>
                  <p class="text-gray-500 text-sm">
                    Based on the provided usage per unit ({{ usagePerUnit }} {{ unitType }}), the total cost for each usage is:
                    <strong>{{ (usagePerUnit * costPerUnit).toFixed(2) }}</strong> and the total price is:
                    <strong>{{ (usagePerUnit * pricePerUnit).toFixed(2) }}</strong>.
                  </p>
                  <p class="text-gray-500 text-sm mt-1">
                    Note: Ensure the values accurately reflect the total material cost and price for each item.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Service-Specific Fields -->
          <div v-if="type === 'service'">
            <div class="mb-4">
              <label for="price" class="block text-gray-700 font-semibold mb-2">Base Price (Optional):</label>
              <input
                type="number"
                id="price"
                v-model="price"
                min="0"
                class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter a base price for the service"
                />
                <small class="text-gray-500">
                  The base price is the foundational charge for this service. Prices for each option (e.g., print size or format) will be added on top of this amount.
                </small>
              </div>

            <!-- Attributes Section -->
            <div class="mb-4">
              <label class="block text-gray-700 font-semibold mb-2">Customizable Options (At least one required):</label>
              <p class="text-gray-600 mb-2">
                Add options for this service, such as sizes or formats, each with their own price.
              </p>
              <div v-for="(attribute, index) in attributes" :key="index" class="mb-2 flex items-center space-x-2">
                <input
                  type="text"
                  v-model="attribute.key"
                  placeholder="Option Name (e.g., Print Size)"
                  class="shadow border rounded w-1/2 py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <input
                  type="number"
                  v-model="attribute.value"
                  min="0"
                  placeholder="Price"
                  class="shadow border rounded w-1/3 py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button
                  type="button"
                  @click="removeAttribute(index)"
                  class="inline-flex items-center bg-red-500 text-white rounded-full p-2 hover:bg-red-700 focus:outline-none"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>

              <!-- Add Attribute Button -->
              <button
                type="button"
                @click="addAttribute"
                class="bg-green-500 text-white font-semibold py-1 px-3 rounded w-full text-center hover:bg-green-700 focus:outline-none mt-2"
              >
                Add Option
              </button>
            </div>
          </div>

          <!-- Product-Specific Fields with Enhanced Guidance -->
          <div v-if="type === 'product'">
            <div class="mb-4">
              <label for="price" class="block text-gray-700 font-semibold mb-2">Price (Optional):</label>
              <input
                type="number"
                id="price"
                v-model="price"
                min="0"
                class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter price per unit"
              />
              <p class="text-gray-500 text-sm mt-2">Leave blank if price should be dynamically calculated based on materials.</p>
            </div>

            <!-- Manage Inventory Toggle with Guidance -->
            <div class="flex items-start space-x-3 bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition mb-4">
              <input
                type="checkbox"
                id="manageInventory"
                v-model="manageInventory"
                class="h-5 w-5 text-blue-600 border-gray-300 rounded mt-1"
              />
              <span>
                <span class="text-gray-700 font-medium">Manage Inventory</span>
                <p class="text-gray-500 text-sm mt-1">
                  Enable if you want to track inventory directly for this product. 
                  If this product relies on materials for inventory, or no inventory at all, you can skip this.
                  <span class="text-blue-500 cursor-pointer" @click="showInfo = !showInfo">(What's this?)</span>
                </p>
              </span>
            </div>

            <!-- Tooltip for Additional Guidance -->
            <transition name="fade">
              <p v-if="showInfo" class="text-gray-500 text-sm mt-2 bg-gray-100 p-2 rounded">
                <strong>Note:</strong> If you manage this product through linked materials (e.g., prints, frames), leave this unchecked. You can also set a quantity here if you prefer direct management.
              </p>
            </transition>

            <!-- Quantity Field: Only Show if Manage Inventory is Enabled -->
            <div v-if="manageInventory" class="mb-4">
              <label for="quantity" class="block text-gray-700 font-semibold mb-2">Stock Quantity (Optional):</label>
              <input
                type="number"
                id="quantity"
                v-model="quantity"
                min="0"
                class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter available stock (leave blank for no tracking)"
              />
            </div>
          </div>
        </div>
        

         <!-- Modal Footer (Fixed within Form) -->
          <div class="p-4 border-t flex justify-center">
            <button
              type="submit"
              class="font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2"
              :class="{
                'bg-blue-500 text-white hover:bg-blue-700 focus:ring-blue-500': type,
                'bg-gray-300 text-gray-500 cursor-not-allowed': !type
              }"
              :disabled="!type"
            >
              Create {{ type === 'product' ? 'Product' : type === 'service' ? 'Service' : 'Material' }}
            </button>
          </div>
      </form>

        <!-- Success and Error Messages -->
        <div v-if="successMessage" class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded text-center">
          <p class="font-semibold">{{ successMessage }}</p>
        </div>

        <div v-if="errorMessage" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded text-center">
          <p class="font-semibold">{{ errorMessage }}</p>
        </div>
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
    teamId: { // Add this prop if the team_id is available via props
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
      manageInventory: false, // Initialize manageInventory here
      unitType: '',
      totalQuantity: '',
      costPerUnit: '',
      pricePerUnit: '',
      usagePerUnit: '',
      minimumStockAlert: '',
      availableMaterials: [], // Array to store fetched materials/products
      parentMaterial: '', // Selected parent material or product
      isParentMaterial: false, // New property for tracking if the material is a parent material
      childMaterials: [],
      inventorySectionVisible: true,
      linkingSectionVisible: false,
      attributes: [], // Dynamic attributes array
      successMessage: '',
      errorMessage: '',
      products: [],
      localProject: this.project ? JSON.parse(this.project) : {},
      showInfo: false, // Added showInfo property to control the tooltip visibility
      showQuantityInfo: false, // Added showQuantityInfo property to control the tooltip visibility
      showMinStockInfo: false, // Added showMinStockInfo property to control the tooltip visibility
      // Initialize any other necessary data properties here
    };
  },
  created() {
    // Fetch products when the component is created
    this.fetchProducts();
  },
  computed: {
    usageCost() {
      return (this.usagePerUnit * this.costPerUnit).toFixed(2);
    },
    usagePrice() {
      return (this.usagePerUnit * this.pricePerUnit).toFixed(2);
    },
    // Calculate total price of all child materials
    totalMaterialPrice() {
      return this.childMaterials.reduce((total, child) => {
        return total + (child.pricePerUnit || 0) * (child.quantity || 0);
      }, 0);
    },
    isLinkedProductFixedPrice() {
      const selectedProduct = this.availableMaterials.find(item => item.id === this.parentMaterial);
      return selectedProduct && selectedProduct.price > 0;
    },
  },
  watch: {
    unitType(newVal) {
      this.childMaterials.forEach((child) => {
        if (!child.unitType) {
          child.unitType = newVal;
        }
      });
    },
    totalQuantity(newVal) {
      const totalChildStock = this.childMaterials.reduce(
        (total, child) => total + (child.quantity || 0),
        0
      );
      if (newVal < totalChildStock) {
        console.warn("Total parent stock is less than the sum of child stock!");
      }
    },
    usagePerUnit(newVal) {
      this.childMaterials.forEach((child) => {
        if (!child.usagePerUnit) {
          child.usagePerUnit = newVal;
        }
      });
    },
    costPerUnit(newVal) {
      this.childMaterials.forEach((child) => {
        if (!child.costPerUnit) {
          child.costPerUnit = newVal;
        }
      });
    },
    pricePerUnit(newVal) {
      this.childMaterials.forEach((child) => {
        if (!child.pricePerUnit) {
          child.pricePerUnit = newVal;
        }
      });
    },
  },
  methods: {
    closeModal() {
      this.$emit('close');
    },
    toggleInventorySection() {
      this.inventorySectionVisible = !this.inventorySectionVisible;
    },
    toggleLinkingSection() {
      this.linkingSectionVisible = !this.linkingSectionVisible;
    },
    handleTypeChange() {
      // Reset fields that are specific to each type
      this.title = ''; // Clear title when switching types
      this.manageInventory = false; // Reset inventory management toggle
      this.attributes = []; // Clear attributes array for services
      this.quantity = ''; // Clear quantity when switching types

      // Optional: reset other fields if needed when switching types
      this.price = ''; 
      this.description = ''; 
      this.category = '';
    },
    addProduct(newProduct) {
      this.products.push(newProduct);
    },
    fetchProducts() {
        axios.get(`/api/products/${this.userId}`)
            .then(response => {
                this.availableMaterials = response.data.products.filter(product => 
                    !product.manage_inventory && (product.type !== 'material' || product.is_parent_material)
                );
            })
            .catch(error => {
                console.error('Error fetching products:', error);
            });
    },
    addChildMaterial() {
      this.childMaterials.push({
        title: '',
        unitType: this.unitType || '', // Use parent's unitType
        quantity: '', // User will input stock quantity
        usagePerUnit: this.usagePerUnit || 1, // Use parent's usagePerUnit
        minimumStockAlert: this.minimumStockAlert || '', // Use parent's minimum stock alert
        costPerUnit: this.costPerUnit || '', // Use parent's cost per unit
        pricePerUnit: this.pricePerUnit || '', // Use parent's price per unit
      });
    },
    removeChildMaterial(index) {
      this.childMaterials.splice(index, 1);
    },
    addAttribute() {
      // Check for empty attribute name
      if (this.attributes.some(attr => !attr.key.trim())) {
        this.errorMessage = 'Attribute name cannot be empty.';
        return;
      }
      this.attributes.push({ key: '', value: '' });
    },
    removeAttribute(index) {
      this.attributes.splice(index, 1);
    },
    createProduct() {
      let attributes = [];

      if (this.type === 'service') {
        attributes = this.attributes.map(attribute => {
          // Trim key and ensure it is not empty
          const key = attribute.key.trim();
          // Ensure the attribute value is numeric, default to 0 if empty or invalid
          const value = isNaN(parseFloat(attribute.value)) ? 0 : parseFloat(attribute.value);
          return { key, value }; // Format as array of objects
        }).filter(attr => attr.key); // Remove any attributes with empty keys

        // Check if attributes are valid before sending
        if (attributes.length === 0) {
          this.errorMessage = 'At least one valid attribute must be provided for services.';
          return;
        }
      }

      const productData = {
        title: this.title,
        description: this.description,
        price: this.price || 0,
        quantity_in_stock: this.type === 'material' ? this.totalQuantity || 0 : this.quantity || 0,  // Use totalQuantity for materials
        user_id: this.userId,
        team_id: this.teamId,  // Include team_id in the product data
        image: null,
        active: true,
        parent_id: this.parentMaterial || null,
        type: this.type,
        attributes: this.type === 'service' ? attributes : [], // Send attributes as an array
        manage_inventory: this.manageInventory, // Include new field in the product data
        unit_type: this.unitType || null,           // Unit type for materials
        usage_per_unit: this.usagePerUnit  || null,  // Usage per unit
        minimum_stock_alert: this.minimumStockAlert  || null, // Minimum stock alert
        cost_per_unit: this.costPerUnit  || null,     // Cost per unit for material calculation
        price_per_unit: this.pricePerUnit  || null,   // Price per unit for material calculation
        is_parent_material: this.isParentMaterial, // Add the is_parent_material field here
        // children: this.isParentMaterial ? this.childMaterials : [],
      };

      // Determine if the product is created for a team or personal account
      // if (this.teamId) {
      //      productData.team_id = this.teamId; // Set team_id if it exists
      //  } else {
      //     productData.user_id = this.userId; // Otherwise set user_id
      //  }

      console.log('Product data being sent:', productData);

      axios.post('/api/products', productData)
        .then(response => {
          const parentProduct = response.data.product;

          if (this.isParentMaterial && this.childMaterials.length > 0) {
            const childData = this.childMaterials.map(child => ({
              title: child.title || null,
              unit_type: child.unitType || null,
              quantity_in_stock: child.quantity || 0,
              usage_per_unit: child.usagePerUnit || 0,
              minimum_stock_alert: child.minimumStockAlert || 0,
              cost_per_unit: child.costPerUnit || 0,
              price_per_unit: child.pricePerUnit || 0,
              parent_id: parentProduct.id,
              user_id: this.userId,
              team_id: this.teamId,
              type: 'material',
            }));

            console.log('Child materials data being sent:', { materials: childData });

            axios.post('/api/products/batch', { materials: childData })
              .then(() => {
                this.resetForm();
                this.successMessage = 'Parent product and child materials created successfully!';
                this.$emit('product-created', parentProduct);
                window.location.reload();
              })
              .catch(error => {
                console.error('Error creating child materials:', error);
                this.errorMessage = 'Error creating child materials.';
              });
          } else {
            this.resetForm();
            this.successMessage = 'Product created successfully!';
            this.$emit('product-created', parentProduct);
            window.location.reload();
          }
        })
        .catch(error => {
          console.error('Error creating product:', error);
          this.errorMessage = 'Error creating product.';
        });
    },
    resetForm() {
      // Reset the form after successful creation
      this.title = '';
      this.type = '';
      this.category = '';
      this.description = '';
      this.price = '';
      this.quantity = '';
      this.parent_id = '';
      this.unit_type = '';
      this.usage_per_unit = null;
      this.minimum_stock_alert = null;
      this.cost_per_unit = '';
      this.attributes = [];
      this.manageInventory = false;
    }
  },
};

</script>