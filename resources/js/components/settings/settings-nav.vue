<template>
  <!-- settings - sidebar -->
  <nav id="settings-nav" class="w-80 top-16 left-0 inset-y-0 fixed border-r">
    <div id="settings-panel-header" class="block py-4 px-3 font-semibold text-xl bg-gray-100">Settings</div>

    <ul>
      <li class="py-2 px-3 cursor-pointer border-b settings-nav-option"
          v-on:click="showInstitutionsSettings"
          v-bind:class="{'bg-blue-600 text-white is-active': isVisibleSettings.institutions, 'bg-white hover:bg-gray-50 text-black hover:text-blue-600': !isVisibleSettings.institutions}"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block -mt-1 mr-0.5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10.496 2.132a1 1 0 00-.992 0l-7 4A1 1 0 003 8v7a1 1 0 100 2h14a1 1 0 100-2V8a1 1 0 00.496-1.868l-7-4zM6 9a1 1 0 00-1 1v3a1 1 0 102 0v-3a1 1 0 00-1-1zm3 1a1 1 0 012 0v3a1 1 0 11-2 0v-3zm5-1a1 1 0 00-1 1v3a1 1 0 102 0v-3a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        Institutions
      </li>
      <li class="py-2 px-3 cursor-pointer border-b settings-nav-option"
          v-on:click="showAccountsSettings"
          v-bind:class="{'bg-blue-600 text-white is-active': isVisibleSettings.accounts, 'bg-white hover:bg-gray-50 text-black hover:text-blue-600': !isVisibleSettings.accounts}"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block -mt-1 mr-0.5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
        </svg>
        Accounts
      </li>

      <li class="py-2 px-3 cursor-pointer border-b settings-nav-option"
          v-on:click="showAccountTypesSettings"
          v-bind:class="{'bg-blue-600 text-white is-active': isVisibleSettings.accountTypes, 'bg-white hover:bg-gray-50 text-black hover:text-blue-600': !isVisibleSettings.accountTypes}"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block -mt-1 mr-0.5" viewBox="0 0 20 20" fill="currentColor">
          <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
          <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
        </svg>
        Account-types
      </li>
      <li class="py-2 px-3 cursor-pointer border-b settings-nav-option"
          v-on:click="showTagsSettings"
          v-bind:class="{'bg-blue-600 text-white is-active': isVisibleSettings.tags, 'bg-white hover:bg-gray-50 text-black hover:text-blue-600': !isVisibleSettings.tags}"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block -mt-1 mr-0.5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
        </svg>
        Tags
      </li>
    </ul>
  </nav>
</template>

<script>
import {settingsNavMixin} from "../../mixins/settings-nav-mixin";

export default {
  name: "settings-nav",
  mixins: [settingsNavMixin],
  methods:{
    showInstitutionsSettings: function(){
      this.broadcastChange(this.settingsNameInstitutions);
    },
    showAccountsSettings: function(){
      this.broadcastChange(this.settingsNameAccounts);
    },
    showAccountTypesSettings: function(){
      this.broadcastChange(this.settingsNameAccountTypes);
    },
    showTagsSettings: function(){
      this.broadcastChange(this.settingsNameTags);
    },
    broadcastChange: function(settingsName){
      this.makeSettingsVisible(settingsName);
      this.$eventHub.broadcast(this.$eventHub.EVENT_SETTINGS_NAV_CHANGE, settingsName);
    }
  }
}
</script>

<style scoped>
</style>