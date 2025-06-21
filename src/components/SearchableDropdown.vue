<template>
  <div class="searchable-dropdown">
    <!-- Search Input -->
    <input 
      type="text" 
      v-model="searchQuery"
      @input="handleSearch"
      @focus="showDropdown = true"
      @blur="handleBlur"
      :placeholder="selectedItemName || placeholder"
      :class="['search-input-dropdown', { 'has-selection': selectedItem }]"
      :readonly="readonly"
    >
    
    <!-- Clear Selection Button -->
    <button 
      v-if="selectedItem && !readonly" 
      type="button" 
      @click="clearSelection" 
      class="clear-selection"
    >
      ×
    </button>
    
    <!-- Dropdown Results -->
    <div v-if="showDropdown && filteredItems.length > 0" class="dropdown-results">
      <div class="dropdown-header">
        <span>{{ filteredItems.length }} {{ itemType }}{{ filteredItems.length > 1 ? 's' : '' }} found</span>
        <button type="button" @click="showDropdown = false" class="close-dropdown">×</button>
      </div>
      <div class="dropdown-list">
        <div 
          v-for="item in paginatedItems" 
          :key="item[itemKey]"
          @mousedown="selectItem(item)"
          class="dropdown-item"
          :class="{ 'selected': selectedItem && selectedItem[itemKey] === item[itemKey] }"
        >
          <div class="item-info">
            <div class="item-name">{{ item[displayField] }}</div>
            <div v-if="item[subDisplayField]" class="item-details">{{ item[subDisplayField] }}</div>
          </div>
        </div>
      </div>
      
      <!-- Pagination -->
      <div v-if="totalPages > 1" class="dropdown-pagination">
        <button 
          type="button"
          @click="previousPage" 
          :disabled="currentPage === 1"
          class="pagination-btn"
        >
          ← Previous
        </button>
        <span class="page-info">{{ currentPage }} of {{ totalPages }}</span>
        <button 
          type="button"
          @click="nextPage" 
          :disabled="currentPage === totalPages"
          class="pagination-btn"
        >
          Next →
        </button>
      </div>
    </div>
    
    <!-- No Results -->
    <div v-if="showDropdown && searchQuery && filteredItems.length === 0" class="dropdown-no-results">
      <p>No {{ itemType }}s found matching "{{ searchQuery }}"</p>
    </div>
    
    <!-- Empty State -->
    <div v-if="showDropdown && !searchQuery && items.length === 0" class="dropdown-no-results">
      <p>No {{ itemType }}s available.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SearchableDropdown',
  props: {
    items: {
      type: Array,
      required: true
    },
    selectedItem: {
      type: Object,
      default: null
    },
    placeholder: {
      type: String,
      default: 'Type to search...'
    },
    itemKey: {
      type: String,
      default: 'id'
    },
    displayField: {
      type: String,
      default: 'name'
    },
    subDisplayField: {
      type: String,
      default: null
    },
    itemType: {
      type: String,
      default: 'item'
    },
    searchFields: {
      type: Array,
      default: () => ['name']
    },
    pageSize: {
      type: Number,
      default: 10
    },
    readonly: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      searchQuery: '',
      showDropdown: false,
      currentPage: 1
    }
  },
  computed: {
    selectedItemName() {
      return this.selectedItem ? this.selectedItem[this.displayField] : '';
    },
    filteredItems() {
      if (!this.searchQuery.trim()) {
        return this.items;
      }
      
      const query = this.searchQuery.toLowerCase();
      return this.items.filter(item => {
        return this.searchFields.some(field => {
          const value = item[field];
          return value && value.toString().toLowerCase().includes(query);
        });
      });
    },
    totalPages() {
      return Math.ceil(this.filteredItems.length / this.pageSize);
    },
    paginatedItems() {
      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return this.filteredItems.slice(start, end);
    }
  },
  watch: {
    searchQuery() {
      this.currentPage = 1; // Reset to first page when searching
    },
    selectedItem(newVal) {
      if (newVal) {
        this.searchQuery = newVal[this.displayField];
      } else {
        this.searchQuery = '';
      }
    }
  },
  methods: {
    handleSearch() {
      this.showDropdown = true;
      this.$emit('search', this.searchQuery);
    },
    handleBlur() {
      // Delay hiding dropdown to allow for click events
      setTimeout(() => {
        this.showDropdown = false;
      }, 200);
    },
    selectItem(item) {
      this.$emit('select', item);
      this.showDropdown = false;
    },
    clearSelection() {
      this.$emit('select', null);
      this.searchQuery = '';
      this.showDropdown = false;
    },
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    }
  }
}
</script>

<style scoped>
.searchable-dropdown {
  position: relative;
  width: 100%;
  box-sizing: border-box;
}

.search-input-dropdown {
  width: 100%;
  padding: 12px 40px 12px 16px;
  border: 2px solid #e0e6ed;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
  background-color: #fff;
  box-sizing: border-box;
}

.search-input-dropdown:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.search-input-dropdown.has-selection {
  background-color: #f8f9fa;
  color: #2c3e50;
  font-weight: 500;
}

.clear-selection {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  font-size: 18px;
  color: #95a5a6;
  cursor: pointer;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.clear-selection:hover {
  background-color: #e74c3c;
  color: white;
}

.dropdown-results {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 2px solid #e0e6ed;
  border-top: none;
  border-radius: 0 0 8px 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  z-index: 1050;
  max-height: 300px;
  overflow: hidden;
  max-width: 100%;
  box-sizing: border-box;
}

.dropdown-header {
  padding: 12px 16px;
  background-color: #f8f9fa;
  border-bottom: 1px solid #e0e6ed;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  font-weight: 600;
  color: #7f8c8d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.close-dropdown {
  background: none;
  border: none;
  font-size: 16px;
  color: #95a5a6;
  cursor: pointer;
  padding: 0;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-dropdown:hover {
  color: #e74c3c;
}

.dropdown-list {
  max-height: 280px;
  overflow-y: auto;
}

.dropdown-item {
  padding: 12px 16px;
  cursor: pointer;
  border-bottom: 1px solid #f1f2f6;
  transition: all 0.2s ease;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
}

.dropdown-item.selected {
  background-color: #3498db;
  color: white;
}

.dropdown-item:last-child {
  border-bottom: none;
}

.item-info {
  display: flex;
  flex-direction: column;
}

.item-name {
  font-weight: 500;
  color: #2c3e50;
  margin-bottom: 4px;
}

.dropdown-item.selected .item-name {
  color: white;
}

.item-details {
  font-size: 12px;
  color: #7f8c8d;
}

.dropdown-item.selected .item-details {
  color: rgba(255, 255, 255, 0.8);
}

.dropdown-pagination {
  padding: 12px 16px;
  background-color: #f8f9fa;
  border-top: 1px solid #e0e6ed;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.pagination-btn {
  background: #3498db;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: #2980b9;
}

.pagination-btn:disabled {
  background: #bdc3c7;
  cursor: not-allowed;
}

.page-info {
  font-size: 12px;
  color: #7f8c8d;
  font-weight: 500;
}

.dropdown-no-results {
  padding: 24px 16px;
  text-align: center;
  color: #7f8c8d;
  font-style: italic;
}

.dropdown-no-results p {
  margin: 0;
}

/* Scrollbar styling */
.dropdown-list::-webkit-scrollbar {
  width: 6px;
}

.dropdown-list::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.dropdown-list::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.dropdown-list::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Modal constraints - ensure dropdown doesn't overflow modal boundaries */
.modal .searchable-dropdown {
  position: relative;
  width: 100%;
}

.modal .searchable-dropdown .dropdown-results {
  max-height: 250px;
  overflow-y: auto;
  box-sizing: border-box;
  z-index: 1052;
  /* Ensure dropdown stays within modal bounds */
  position: absolute;
  left: 0;
  right: 0;
  width: 100%;
}

/* Ensure dropdown respects modal boundaries */
.modal-content .form-group .searchable-dropdown {
  position: relative;
  overflow: visible;
}

.modal-content .form-group .searchable-dropdown .dropdown-results {
  max-width: 100%;
  width: 100%;
  left: 0;
  right: 0;
  box-sizing: border-box;
  /* Prevent horizontal overflow */
  overflow-x: hidden;
}
</style>
