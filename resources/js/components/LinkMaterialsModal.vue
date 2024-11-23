<template>
    <div
      class="fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center z-50"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
      @click.self="$emit('close')"
    >
      <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl m-4 flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="p-4 border-b">
          <h2 class="text-xl font-bold text-gray-800">Link Parent Materials</h2>
          <p class="text-sm text-gray-600 mt-2">
            Select two parent materials, and define the usage relationships between their child materials. For example, 
            you can define how many grams of wax are used for each size of glass jar.
          </p>
        </div>
  
        <!-- Modal Content (Scrollable) -->
        <div class="overflow-y-auto p-4 flex-grow" style="max-height: 70vh;">
          <!-- Warning Section -->
          <div v-if="isUsageReversed" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
            <p>
              Warning: The selected configuration may result in unusual usage patterns (e.g., consuming jars for grams of wax). 
              Please double-check your inputs.
            </p>
          </div>
  
          <!-- Select Parent Materials -->
          <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
              <label for="parentMaterial1" class="block text-gray-700 font-semibold">
                Parent Material 1
                <span
                  class="ml-1 text-gray-400 cursor-pointer"
                  title="Select the first parent material to define its usage relationships."
                >
                  <i class="fas fa-question-circle"></i>
                </span>
              </label>
              <select v-model="selectedParentMaterial1" id="parentMaterial1" class="w-full border rounded-lg p-2">
                <option v-for="material in parentMaterials" :value="material.id" :key="material.id">
                  {{ material.title }}
                </option>
              </select>
            </div>
            <div>
              <label for="parentMaterial2" class="block text-gray-700 font-semibold">
                Parent Material 2
                <span
                  class="ml-1 text-gray-400 cursor-pointer"
                  title="Select the second parent material to define its usage relationships."
                >
                  <i class="fas fa-question-circle"></i>
                </span>
              </label>
              <select v-model="selectedParentMaterial2" id="parentMaterial2" class="w-full border rounded-lg p-2">
                <option v-for="material in parentMaterials" :value="material.id" :key="material.id">
                  {{ material.title }}
                </option>
              </select>
            </div>
          </div>

          <!-- Parent Material Details Cards -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <!-- Parent Material 1 -->
            <div v-if="selectedParentMaterial1" class="p-4 border rounded-lg shadow bg-gray-50">
                <h4 class="text-lg font-semibold text-gray-700">Parent Material 1</h4>
                <p><strong>Title:</strong> {{ parentMaterials.find(m => m.id === selectedParentMaterial1).title }}</p>
                <p><strong>Unit Type:</strong> {{ parentMaterials.find(m => m.id === selectedParentMaterial1).unit_type }}</p>
            </div>

            <!-- Parent Material 2 -->
            <div v-if="selectedParentMaterial2" class="p-4 border rounded-lg shadow bg-gray-50">
                <h4 class="text-lg font-semibold text-gray-700">Parent Material 2</h4>
                <p><strong>Title:</strong> {{ parentMaterials.find(m => m.id === selectedParentMaterial2).title }}</p>
                <p><strong>Unit Type:</strong> {{ parentMaterials.find(m => m.id === selectedParentMaterial2).unit_type }}</p>
            </div>
        </div>
  
          <!-- Define Usage Per Unit -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Define Usage Per Unit</h3>
            <div class="grid grid-cols-3 gap-4">
                <div v-for="child1 in childMaterials1" :key="child1.id" class="p-4 border rounded-lg">
                    <!-- Display Parent Material 1 Title -->
                    <h4 class="font-bold text-gray-700">{{ child1.title }}</h4>
                    <div v-for="child2 in childMaterials2" :key="child2.id" class="mt-2">
                        <!-- Display Parent Material 2 Title -->
                        <label class="text-sm text-gray-600">
                        Usage of 
                        <span class="font-semibold">{{ child2.title }}</span> 
                        for 
                        <span class="font-semibold">{{ child1.title }}</span>:
                        <span
                            class="ml-1 text-gray-400 cursor-pointer"
                            :title="`Define how many units of ${child2.title} are consumed for one unit of ${child1.title}.
                            Usage Per Unit: ${child2.usage_per_unit || 'N/A'}, 
                            Cost Per Unit: ${child2.cost_per_unit || 'N/A'}, 
                            Price Per Unit: ${child2.price_per_unit || 'N/A'}.`"
                        >
                            <i class="fas fa-question-circle"></i>
                        </span>
                        </label>
                        <!-- Input for Defining Usage -->
                        <input
                        type="number"
                        v-model="linkages[child1.id][child2.id]"
                        min="0"
                        step="0.01"
                        class="w-full border rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., 100"
                        />
                    </div>
                </div>
            </div>
        </div>
  
        <!-- Preview Section -->
            <div class="mt-6 max-w-screen-lg mx-auto">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center">Preview Relationships</h3>

                <!-- Relationships Cards -->
                <div class="space-y-6">
                    <div 
                        v-for="child1 in childMaterials1" 
                        :key="child1.id" 
                        class="border rounded-lg shadow bg-gray-50 p-6 text-center"
                    >
                        <!-- Child1 Title -->
                        <h4 class="text-lg font-bold text-gray-700">
                            {{ child1.title }} ({{ child1.unit_type }})
                        </h4>
                        <p class="text-sm text-gray-600">
                            Define relationships for this material:
                        </p>

                        <!-- Linked Child2 Materials -->
                        <div class="space-y-4 mt-4">
                            <div 
                                v-for="child2 in childMaterials2" 
                                :key="child2.id" 
                                class="p-4 border rounded-lg bg-white shadow-sm text-center"
                            >
                                <!-- Child2 Title -->
                                <h5 class="text-md font-semibold text-gray-800">
                                    Linked Material: {{ child2.title }} ({{ child2.unit_type }})
                                </h5>
                                <p class="text-sm text-gray-600">
                                    {{ linkages[child1.id][child2.id] || 0 }} {{ child2.unit_type }} used per {{ child1.unit_type }}
                                </p>

                                <!-- Cost and Price Impacts -->
                                <div class="mt-4 flex flex-col items-center gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">
                                            <i class="fas fa-coins text-yellow-500 mr-1"></i>
                                            Cost Impact:
                                        </p>
                                        <p class="text-sm text-gray-700">
                                            {{ calculateCostImpact(linkages[child1.id][child2.id], child2.cost_per_unit) || 'N/A' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">
                                            <i class="fas fa-tag text-green-500 mr-1"></i>
                                            Price Impact:
                                        </p>
                                        <p class="text-sm text-gray-700">
                                            {{ calculatePriceImpact(linkages[child1.id][child2.id], child2.price_per_unit) || 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
  
        <!-- Fixed Footer -->
        <div class="p-4 border-t flex justify-end bg-white">
          <button
            @click="saveLinkages"
            :disabled="!isValid"
            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-700 mr-2 disabled:bg-gray-400 disabled:cursor-not-allowed"
          >
            Save Links
          </button>
          <button @click="$emit('close')" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-700">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  export default {
    props: {
        productId: {
            type: Number,
            required: true,
            },
        },
    data() {
      return {
        parentMaterials: [], // Load all parent materials
        childMaterials1: [],
        childMaterials2: [],
        selectedParentMaterial1: null,
        selectedParentMaterial2: null,
        linkages: {}, // Store usage per unit
      };
    },
    computed: {
      isValid() {
        return (
          this.selectedParentMaterial1 &&
          this.selectedParentMaterial2 &&
          Object.keys(this.linkages).length > 0
        );
      },
      isUsageReversed() {
        return Object.values(this.linkages).some(child1 =>
          Object.values(child1).some(value => value > 100) // Example threshold
        );
      },
    },
    methods: {
      fetchMaterials() {
        axios
          .get('/api/parent-materials')
          .then(response => {
            this.parentMaterials = response.data;
          })
          .catch(error => {
            console.error('Error fetching parent materials:', error);
          });
      },
      loadChildMaterials(parentId, target) {
        axios
            .get(`/api/materials/${parentId}/children`)
            .then((response) => {
            this[target] = response.data;
            if (target === "childMaterials1") {
                this.linkages = response.data.reduce((acc, child1) => {
                acc[child1.id] = {};
                this.childMaterials2.forEach(
                    (child2) => (acc[child1.id][child2.id] = 0)
                );
                return acc;
                }, {});
            }
            })
            .catch((error) => {
            console.error(`Error loading child materials for ${target}:`, error);
            });
        },
        saveLinkages() {
        axios
            .post(`/products/${this.productId}/linked-materials`, {
            parent_material_1_id: this.selectedParentMaterial1,
            parent_material_2_id: this.selectedParentMaterial2,
            child_material_relationships: this.linkages,
            })
            .then(() => {
            this.$emit('materials-linked');
            this.$emit('close');
            // this.$notify({ type: 'success', text: 'Linked materials saved successfully!' });
            })
            .catch((error) => {
            console.error('Error saving linked materials:', error);
            // this.$notify({ type: 'error', text: 'Failed to save linked materials. Please try again.' });
            });
        },
      calculateCostImpact(usage, costPerUnit) {
            if (!usage || !costPerUnit) return null;
            return (usage * costPerUnit).toFixed(2);
        },
        calculatePriceImpact(usage, pricePerUnit) {
            if (!usage || !pricePerUnit) return null;
            return (usage * pricePerUnit).toFixed(2);
        },
    },
    watch: {
      selectedParentMaterial1(newVal) {
        if (newVal) this.loadChildMaterials(newVal, 'childMaterials1');
      },
      selectedParentMaterial2(newVal) {
        if (newVal) this.loadChildMaterials(newVal, 'childMaterials2');
      },
    },
    mounted() {
      this.fetchMaterials();
    },
  };
  </script>
  