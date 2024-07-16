<template>
    <div>
      <div class="flex flex-col sm:flex-row items-center">
        <input v-model="newTag" type="text" id="tag-input"
          class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:max-w-xs lg:max-w-lg xl:max-w-xl border-gray-300 rounded-md sm:mr-3 mb-3 sm:mb-0 sm:flex-grow">
        <select v-model="newTagColor" id="tag-color"
          class="block focus:ring-indigo-500 focus:border-indigo-500 w-32 shadow-sm sm:max-w-xs sm:text-sm border-gray-300 rounded-md sm:mr-3 mb-3 sm:mb-0">
          <option value ="neutral">Black</option>
          <option value="rose">Red</option>
          <option value="sky">Blue</option>
          <option value="emerald">Green</option>
        </select>
      </div>
      <button @click="addTag" id="add-tag" type="button"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mt-3">Add
        tag</button>
      <div id="tags-container" class="mt-2 space-y-1">
        <span v-for="(tag, index) in tags" :key="index" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium m-1 border border-transparent focus:outline-none focus:ring-2 focus:ring-offset-2"
          :class="tagClasses[index]">
          
          {{ tag.name }}
          <button @click.prevent="removeTag(index)" class="flex-shrink-0 ml-2.5 h-4 w-4 rounded-full inline-flex items-center justify-center"
            :class="getColorClass(tag)">
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
import config from '../config'; // Adjust relative path from TagEditor.vue to config.js

export default {
  props: {
    client: {
      type: String,
      // type: Object, // Adjusted to accept an object as props
      required: true
    }
  },
  data() {
    return {
      newTag: '',
      newTagColor: 'neutral',
      tags: [],
      tagColors: [],
      clientData: null
    }
  },
  mounted() {
      console.log('Value:', this.client);
      console.log('Type:', typeof this.client);
      console.log('Value id:', this.client.id);
      console.log('Type id:', typeof this.client.id);
    console.log('Type of client:', typeof this.client);
    this.clientData = JSON.parse(this.client);
    console.log('Client:', this.clientData);
    this.fetchTags();
  },
  // mounted() {
  //   this.clientData = JSON.parse(this.client); // Parse the client data passed as props
  //   this.fetchTags(); // Fetch initial tags for the client
  // },
  computed: {
  tagClasses() {
    return this.tags.map(tag => {
      switch (tag.color) {
        case 'rose':
          return 'border-rose-500 bg-rose-100 text-rose-500';
        case 'sky':
          return 'border-sky-500 bg-sky-100 text-sky-500';
        case 'emerald':
          return 'border-emerald-500 bg-emerald-100 text-emerald-500';
        default:
          return 'border-gray-500 bg-gray-100 text-gray-500';
      }
    });
  }
},
  methods: {
    async addTag() {
      try {
        const response = await fetch(`${config[process.env.NODE_ENV].apiUrl}/tag`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ 
            name: this.newTag, 
            color: this.newTagColor,
            client_id: this.clientData.id
          }),
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.tags.push({ name: this.newTag, color: this.newTagColor });
          this.newTag = '';
          this.newTagColor = 'neutral';
        } else {
          console.error('Error:', data.error);
        }
      } catch (error) {
        console.error('Error:', error);
      }
    },
    async removeTag(index) {
      const tag = this.tags[index];
      try {
        const response = await fetch(`${config[process.env.NODE_ENV].apiUrl}/tag/${tag.id}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.tags.splice(index, 1);
        } else {
          console.error('Error:', data.error);
        }
      } catch (error) {
        console.error('Error:', error);
      }
    },
    async fetchTags() {
      try {
        const response = await fetch(`${config[process.env.NODE_ENV].apiUrl}/clients/${this.clientData.id}/tags`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
        });
        
        const data = await response.json();
        this.tags = data;
      } catch (error) {
        console.error('Error:', error);
      }
    },
    getColorClass(tag) {
      switch (tag.color) {
        case 'rose':
          return 'text-rose-500 bg-rose-100 hover:bg-rose-200';
        case 'sky':
          return 'text-sky-500 bg-sky-100 hover:bg-sky-200';
        case 'emerald':
          return 'text-emerald-500 bg-emerald-100 hover:bg-emerald-200';
        default:
          return 'text-gray-500 bg-gray-100 hover:bg-gray-200';
      }
    },
  }
}
</script>

<style>
.tag.neutral {
  @apply border-neutral-500;
}
.tag.red {
  @apply border-red-500;
}
.tag.blue {
  @apply border-blue-500;
}
.tag.green {
  @apply border-green-500;
}
</style>