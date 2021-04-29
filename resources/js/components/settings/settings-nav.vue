<template>
  <nav class="panel">
    <p class="panel-heading">Settings</p>
    <a class="panel-block"
       v-bind:class="{'is-active': isVisibleSettings.institutions}"
       v-on:click="showInstitutionsSettings"
    >
      <span class="panel-icon">
        <i class="fas fa-university" aria-hidden="true"></i>
      </span> Institutions
    </a>
    <a class="panel-block"
       v-bind:class="{'is-active': isVisibleSettings.accounts}"
       v-on:click="showAccountsSettings"
    >
      <span class="panel-icon">
        <i class="fas fa-file-invoice-dollar" aria-hidden="true"></i>
      </span> Accounts
    </a>
    <a class="panel-block"
       v-bind:class="{'is-active': isVisibleSettings.accountTypes}"
       v-on:click="showAccountTypesSettings"
    >
      <span class="panel-icon">
          <i class="far fa-credit-card" aria-hidden="true"></i>
      </span> Account-types
    </a>
    <a class="panel-block"
       v-bind:class="{'is-active': isVisibleSettings.tags}"
       v-on:click="showTagsSettings"
    >
      <span class="panel-icon">
          <i class="fas fa-tags" aria-hidden="true"></i>
      </span> Tags
    </a>
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
  },
  mounted: function(){
    // // check which chart is supposed to be visible, then broadcast which chart should be visible
    // let visibleChart = Object.keys(this.isVisibleChart).filter(function(chartName){
    //   return this.isVisibleChart[chartName] === true
    // }.bind(this))[0];
    // this['show'+visibleChart.charAt(0).toUpperCase()+visibleChart.slice(1)+'Chart']();
  }
}
</script>

<style scoped>

</style>