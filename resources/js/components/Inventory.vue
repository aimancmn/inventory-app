<template>
  <div>
    <input type="file" ref="fileInput" @change="handleFileChange" />
    <button @click="uploadFile">Upload</button>

    <table v-if="data.length > 0">
      <thead>
        <tr>
          <th v-for="(value, key) in data[0]" :key="key">{{ key }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in paginatedData" :key="index">
          <td v-for="(value, key) in item" :key="key">{{ value }}</td>
        </tr>
      </tbody>
    </table>

    <div v-if="totalPages > 1">
      <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
      <span>{{ currentPage }}</span>
      <button @click="nextPage" :disabled="currentPage === totalPages">Next</button>
    </div>

    <input type="text" v-model="searchTerm" @input="searchData" placeholder="Search" />
  </div>
</template>

<script>
export default {
  data() {
    return {
      file: null,
      data: [],
      paginatedData: [],
      currentPage: 1,
      itemsPerPage: 10,
      totalPages: 1,
      searchTerm: '',
    };
  },
  
  methods: {
    handleFileChange(event) {
      this.file = event.target.files[0];
    },

    uploadFile() {
      const formData = new FormData();
      formData.append('file', this.file);

      axios
        .post('/api/upload', formData)
        .then(response => {
          this.data = response.data;
          this.paginateData();
        })
        .catch(error => {
          console.log(error);
        });
    },

    paginateData() {
      const startIndex = (this.currentPage - 1) * this.itemsPerPage;
      const endIndex = startIndex + this.itemsPerPage;
      this.paginatedData = this.data.slice(startIndex, endIndex);
      this.totalPages = Math.ceil(this.data.length / this.itemsPerPage);
    },

    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
        this.paginateData();
      }
    },

    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
        this.paginateData();
      }
    },

    searchData() {
      this.currentPage = 1;
      const searchResults = this.data.filter(item => {
        for (let value of Object.values(item)) {
          if (
            value
              .toString()
              .toLowerCase()
              .includes(this.searchTerm.toLowerCase())
          ) {
            return true;
          }
        }
        return false;
      });
      this.data = searchResults;
      this.paginateData();
    },
  },
};
</script>
