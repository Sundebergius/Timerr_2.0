<template>
    <div>
      <div class="flex flex-col sm:flex-row items-center">
        <input v-model="newTag" type="text" id="tag-input"
          class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:max-w-xs lg:max-w-lg xl:max-w-xl border-gray-300 rounded-md sm:mr-3 mb-3 sm:mb-0 sm:flex-grow">
        <select v-model="newTagColor" id="tag-color"
          class="block focus:ring-indigo-500 focus:border-indigo-500 w-32 shadow-sm sm:max-w-xs sm:text-sm border-gray-300 rounded-md sm:mr-3 mb-3 sm:mb-0">
          <option value="red">Red</option>
          <option value="blue">Blue</option>
          <option value="green">Green</option>
        </select>
      </div>
      <button @click="addTag" id="add-tag" type="button"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mt-3">Add
        tag</button>
      <div id="tags-container" class="mt-2 space-y-1">
        <span v-for="(tag, index) in tags" :key="index" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium m-1"
          :class="`bg-${tagColors[index]}-100 text-${tagColors[index]}-800`">
          {{ tag }}
          <button @click="removeTag(index)" class="flex-shrink-0 ml-2.5 h-4 w-4 rounded-full inline-flex items-center justify-center"
            :class="`text-${tagColors[index]}-500 bg-${tagColors[index]}-100 hover:bg-${tagColors[index]}-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-${tagColors[index]}-500`">
            <span class="sr-only">Remove</span>
            <svg class="h-2 w-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </span>
      </div>
    </div>
  </template>
  
  <script>
    export default {
    data() {
        return {
        newTag: '',
        newTagColor: 'red',
        tags: [],
        tagColors: []
        }
    },
    methods: {
        addTag() {
      fetch('http://timerr_2.0.test/api/tag', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // For CSRF protection
        },
        body: JSON.stringify({ 
                name: this.newTag, 
                color: this.newTagColor,
                client_id: this.client.id // assuming this.client is the current client
            }),
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          this.tags.push(this.newTag);
          this.tagColors.push(this.newTagColor);
          this.newTag = '';
        } else {
          console.error('Error:', data.error);
        }
      })
      .catch((error) => {
        console.error('Error:', error);
      });
    },
        removeTag(index) {
        const tag = this.tags[index];
        fetch('http://timerr_2.0.test/api/tag', {
            method: 'DELETE',
            headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // For CSRF protection
            },
            body: JSON.stringify({ 
                tag: tag,
                client_id: this.client.id // assuming this.client is the current client
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
            this.tags.splice(index, 1);
            this.tagColors.splice(index, 1);
            } else {
            console.error('Error:', data.error);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
        });
        }
    }
    }
  </script>