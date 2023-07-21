import { defineStore } from 'pinia'

export const usePaginationStore = defineStore('pagination', {

  state: function() {
    return {
      currentPage: 0,
      currentFilter: {}
    }
  },

  getters: {

  },

  actions: {
    $reset(){
      this.currentPage = 0
      this.currentFilter = {}
    }
  }

})