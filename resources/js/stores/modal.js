import { defineStore } from 'pinia'
import _ from 'lodash'

export const useModalStore = defineStore('modal', {

  state: function() {
    return {
      activeModal: null,
    }
  },

  getters: {
    MODAL_ENTRY() {
      return 'modal-entry';
    },
    MODAL_FILTER(){
      return 'modal-filter';
    },
    MODAL_NONE: function(){
      return null;
    },
    MODAL_TRANSFER(){
      return 'modal-transfer';
    },
    isSet(state) {
      return !_.isNull(state.activeModal)
    },
  },

  actions: {
    $reset(){
      this.activeModal = this.MODAL_NONE
    }
  }

})