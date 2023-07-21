<template>
  <div id="settings-display" class="ml-2 w-4/5 px-12 pt-4 mt-4 scroll-mt-16">
    <settings-home v-if="noSettingsAreSelected"></settings-home>
    <settings-institutions v-if="isVisibleSettings.institutions"></settings-institutions>
    <settings-accounts v-if="isVisibleSettings.accounts"></settings-accounts>
    <settings-account-types v-if="isVisibleSettings.accountTypes"></settings-account-types>
    <settings-tags v-if="isVisibleSettings.tags"></settings-tags>
  </div>
</template>

<script>
import SettingsAccounts from "./settings-accounts";
import SettingsAccountTypes from "./settings-account-types";
import SettingsHome from "./settings-home";
import SettingsInstitutions from "./settings-institutions";
import SettingsTags from "./settings-tags";

import {settingsNavMixin} from "../../mixins/settings-nav-mixin";
import _ from "lodash";

export default {
  name: "settings-display",
  components: {
    SettingsAccounts,
    SettingsAccountTypes,
    SettingsInstitutions,
    SettingsHome,
    SettingsTags,
  },
  mixins: [settingsNavMixin],
  computed: {
    noSettingsAreSelected: function(){
      let isVisibleSettingsUniqueState = Object.values(this.isVisibleSettings).filter(function (value, index, self) {
        return self.indexOf(value) === index;
      });
      return isVisibleSettingsUniqueState.length === 1 && isVisibleSettingsUniqueState[0] === false;
    }
  },
  created: function(){
    this.$eventHub.listen(this.$eventHub.EVENT_SETTINGS_NAV_CHANGE, function(payload){
      if(!_.isEmpty(payload)){
        if(_.isObject(payload)){
          this.makeSettingsVisible(payload[0]);
        } else {
          this.makeSettingsVisible(payload);
        }
      }
    }.bind(this));
  }
}
</script>

<style scoped>
</style>