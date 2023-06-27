<template>
  <section id="settings-institutions" class="max-w-lg">
    <h3 class="text-2xl mb-5 scroll-mt-16">Institutions</h3>
    <form class="grid grid-cols-6 gap-2">
      <label for="settings-institution-name" class="font-medium justify-self-end py-2 col-span-2">Name:</label>
      <input id="settings-institution-name" name="name" type="text" class="rounded text-gray-700 col-span-4" autocomplete="off" v-model="form.name" v-bind:readonly="!form.active" />

      <label class="font-medium justify-self-end py-2 col-span-2">Active State:</label>
      <div class="col-span-4">
        <toggle-button
          v-bind:toggle-state.sync="form.active"
          toggle-id="settings-institution-active"
          v-bind="toggleButtonProperties"
          v-on:click.native="resetFormAfterActiveStateToggle(form.id)"
        ></toggle-button>
      </div>

      <div class="font-medium" v-show="isDataInForm">Created:</div>
      <div class="col-span-5 italic text-sm self-center leading-none justify-self-end" v-show="isDataInForm" v-text="makeDateReadable(form.createStamp)"></div>

      <div class="font-medium" v-show="isDataInForm">Modified:</div>
      <div class="col-span-5 italic text-sm self-center leading-none justify-self-end" v-show="isDataInForm" v-text="makeDateReadable(form.modifiedStamp)"></div>

      <div class="font-medium" v-show="isDataInForm">Disabled:</div>
      <div class="col-span-5 italic text-sm self-center leading-none justify-self-end" v-show="isDataInForm" v-text="makeDateReadable(form.disabledStamp)"></div>

      <button type="button" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 mx-1 mt-6 bg-gray-50 hover:bg-white col-span-3" v-on:click="setFormDefaults">Clear</button>
      <button type="button" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 ml-1 mt-6 text-white bg-green-500 opacity-90 hover:opacity-100 col-span-3 disabled:opacity-50 disabled:cursor-not-allowed"
              v-on:click="save"
              v-bind:disabled="!canSave"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-px mr-1" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
        Save
      </button>
    </form>

    <hr class="my-6"/>

    <spinner v-if="!institutionsStore.isSet" id="loading-settings-institutions"></spinner>

    <ul class="mt-4 mr-8 mb-2 ml-2 text-sm" v-else>
      <li
          class="list-none p-4 mb-2 border"
          v-for="institution in institutionsStore.list"
          v-bind:key="institution.id"
          v-bind:id="'settings-institution-'+institution.id"
          v-bind:class="{
            'border-l-4': form.id===institution.id,
            'text-blue-400 border-blue-400 hover:border-blue-500 is-active': institution.active,
            'text-gray-500 border-gray-500 hover:border-gray-700 is-disabled': !institution.active
          }"
      >
        <span
            class="cursor-pointer"
            v-text="institution.name"
            v-on:click="retrieveUpToDateInstitutionData(institution.id)"
        ></span>
      </li>
    </ul>
  </section>
</template>

<script>
// utilities
import _ from "lodash";
// objects
import {Institution} from "../../institution";  // TODO: figure out how to integrate this into stores
// mixins
import {settingsMixin} from "../../mixins/settings-mixin";
// components
import Spinner from 'vue-spinner-component/src/Spinner.vue';
import ToggleButton from "../toggle-button";
// stores
import {useInstitutionsStore} from "../../stores/institutions";

export default {
  name: "settings-institutions",
  mixins: [settingsMixin],
  components: {
    Spinner,
    ToggleButton,
  },
  data: function(){
    return { };
  },
  computed: {
    canSave: function(){
      if(!_.isNull(this.form.id)){
        let institutionData = this.institutionsStore.find(this.form.id);
        institutionData = this.sanitiseData(institutionData);
        return !_.isEqual(institutionData, this.form);
      } else {
        return !_.isEmpty(this.form.name);
      }
    },
    defaultFormData: function(){
      return {
        id: null,
        name: '',
        active: true,
        createStamp: '',
        modifiedStamp: '',
        disabledStamp: '',
        // accounts: [],
      };
    },
    institutionObject: function(){
      return new Institution();
    },
    institutionsStore(){
      return useInstitutionsStore();
    },
    toggleButtonProperties: function(){
      return _.cloneDeep(this.defaultToggleButtonProperties);
    }
  },
  methods: {
    afterSaveResetFormAndHideLoading(){
      this.setFormDefaults();
      this.institutionsStore.fetch().finally(function(){
        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
      }.bind(this));
    },
    resetFormAfterActiveStateToggle(institutionId){
      // this is called AFTER toggle-state has been updated
      let institutionData = {}
      if(_.isNull(institutionId)){
        institutionData = _.clone(this.defaultFormData)
      } else {
        institutionData = _.clone(this.institutionObject.find(institutionId));
      }
      institutionData.active = this.form.active;
      this.fillForm(institutionData);
    },
    retrieveUpToDateInstitutionData: function(institutionId = null){
      if(_.isNumber(institutionId)){
        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

        // let institutionData = this.institutionsStore.find(institutionId);
        // if(this.institutionsObject.isDataUpToDate(institutionData)){
        //   this.fillForm(institutionData);
        //   this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
        // } else {
          this.institutionObject
            .fetch(institutionId)
            .then(function(fetchResult){
              if(fetchResult.fetched){
                let freshlyFetchedInstitutionData = this.institutionObject.find(institutionId);
                this.fillForm(freshlyFetchedInstitutionData);
              } else {
                this.setFormDefaults();
              }

              if(!_.isEmpty(fetchResult.notification)){
                this.$eventHub.broadcast(
                  this.$eventHub.EVENT_NOTIFICATION,
                  {type: fetchResult.notification.type, message: fetchResult.notification.message}
                );
              }
            }.bind(this))
            .finally(function(){
              this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
            }.bind(this));
        // }
      } else {
        this.setFormDefaults();
      }
    },
    sanitiseData(originalData){
      let data = _.clone(originalData)
      Object.keys(data).forEach(function(k){
        switch(k){
          case 'create_stamp':
          case 'modified_stamp':
          case 'disabled_stamp': {
            let camelCasedKey = _.camelCase(k);
            data[camelCasedKey] = data[k];
            delete data[k];
            break;
          }
          default:
            // do nothing
        }
      });
      return data;
    },
    save: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

      let institutionData = {};
      Object.keys(this.form).forEach(function(formDatumKey){
        switch (formDatumKey){
          case 'id':
          case 'name':
            institutionData[formDatumKey] = this.form[formDatumKey];
            break;
          default:
            // do nothing...
            break;
        }
      }.bind(this));

      this.institutionObject.setFetchedState = false
      if(this.form.active){
        let upsertInstitution = function(institutionData){
          this.institutionObject.save(institutionData)
            .then(this.afterSaveDisplayNotificationIfNeeded)
            .finally(this.afterSaveResetFormAndHideLoading);
        }.bind(this);

        let existingInstitution = this.institutionObject.find(institutionData['id']);
        if(_.isEmpty(existingInstitution)){
          // new institution record
          upsertInstitution(institutionData);
        } else {
          // existing institution record
          if(existingInstitution.active){
            upsertInstitution(institutionData);
          }  else {
            this.institutionObject.enable(institutionData['id'])
              .then(this.afterSaveDisplayNotificationIfNeeded)
              .finally(function(){
                upsertInstitution(institutionData)
              });
          }
        }
      } else {
        this.institutionObject.disable(institutionData['id'])
          .then(this.afterSaveDisplayNotificationIfNeeded)
          .finally(this.afterSaveResetFormAndHideLoading);
      }
    },
  },
  mounted: function(){
    this.setFormDefaults();
  }
}
</script>

<style lang="scss" scoped>
</style>