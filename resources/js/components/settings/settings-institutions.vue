<template>
  <section id="settings-institutions" class="container">
    <form>
      <div class="field is-horizontal">
        <div class="field-label"><label class="label" for="settings-institution-name">Name:</label></div>
        <div class="field-body"><div class="field"><div class="control">
          <input id="settings-institution-name" class="input" name="name" type="text" v-model="form.name" />
        </div></div></div>
      </div>

      <div class="field is-horizontal">
        <div class="field-label is-normal"><label class="label" for="settings-institution-active">Active State:</label></div>
        <div class="field-body"><div class="field"><div class="control">
          <toggle-button
              id="settings-institution-active"
              v-model="form.active"
              v-bind:value="form.active"
              v-bind:labels="toggleButtonProperties.labels"
              v-bind:color="toggleButtonProperties.colors"
              v-bind:height="toggleButtonProperties.height"
              v-bind:width="toggleButtonProperties.width"
              v-bind:sync="true"
          />
        </div></div></div>
      </div>

      <div class="field is-horizontal" v-if="isDataInForm">
        <div class="field-label">Created:</div>
        <div class="field-body" v-text="form.createStamp"></div>
      </div>

      <div class="field is-horizontal" v-if="isDataInForm">
        <div class="field-label">Modified:</div>
        <div class="field-body" v-text="form.modifiedStamp"></div>
      </div>

      <div class="field is-grouped is-grouped-centered">
        <div class="control">
          <button class="button is-primary" type="button" v-on:click="save()"><i class="fas fa-save"></i> Save</button>
        </div>
        <div class="control">
          <button class="button" type="button" v-on:click="setFormDefaults()">Clear</button>
        </div>
      </div>
    </form>

    <hr/>

    <ul class="block-list is-small is-info">
      <li
          v-for="institution in listInstitutions"
          v-bind:key="institution.id"
          v-bind:id="institution.id"
          v-bind:class="{'is-highlighted': form.id===institution.id, 'is-outlined': institution.active, 'has-background-white-bis': !institution.active}"
      >
        <span
            v-text="institution.name"
            v-bind:class="{'has-text-grey': !institution.active}"
            v-on:click="retrieveUpToDateInstitutionData(institution.id)"
        ></span>
      </li>
    </ul>
  </section>
</template>

<script>
import {Institutions} from "../../institutions";
import {settingsMixin} from "../../mixins/settings-mixin";
import {ToggleButton} from 'vue-js-toggle-button';
import _ from "lodash";

export default {
  name: "settings-institutions",
  mixins: [settingsMixin],
  components: {
    ToggleButton
  },
  data: function(){
    return {
      institutionsObject: new Institutions(),
    };
  },
  computed: {
    defaultFormData: function(){
      return {
        id: null,
        name: '',
        active: '',
        createStamp: '',
        modifiedStamp: '',
        // "accounts": []
      };
    },
    listInstitutions: function(){
      return _.orderBy(this.institutionsObject.retrieve, ['name', 'active'], ['asc', 'desc']);
    },
    toggleButtonProperties: function(){
      let toggleProperties = settingsMixin.computed.toggleButtonProperties();
      toggleProperties.labels = {checked: 'Active', unchecked: 'Inactive'};
      toggleProperties.colors = {checked: this.colorInfo, unchecked: this.colorGreyLight};
      return toggleProperties;
    }
  },
  methods: {
    fillForm: function(institution){
      this.form = _.clone(institution);

      Object.keys(this.form).forEach(function(k){
        switch(k){
          case 'create_stamp':
          case 'modified_stamp':
            let camelCasedKey = _.camelCase(k);
            this.form[camelCasedKey] = this.form[k];
            delete this.form[k];
            break;
        }
      }.bind(this));
    },
    setFormDefaults: function(){
      this.fillForm(this.defaultFormData);
    },
    save: function(){
      // TODO: make API call to save (insert/update) institution details
      console.log("this feature is not yet ready");
    },
    retrieveUpToDateInstitutionData: function(institutionId = null){
      if(_.isNumber(institutionId)){
        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

        new Promise(function(resolve, reject){
          // TODO: figure out a way to get most up to date version of institution data
          let institutionData = this.institutionObject.find(institutionId);
          if(this.institutionObject.isDataUpToDate(institutionData)){
            resolve(institutionData);
          } else {
            reject(institutionId);
          }
        }.bind(this))
          .then(this.fillForm.bind(this))
          .catch(function(institutionId){
            this.institutionObject.fetch(institutionId)
              .then(function(fetchResult){
                let freshlyFetchedInstitutionData = {};
                if(fetchResult.fetched){
                  freshlyFetchedInstitutionData = this.institutionObject.find(institutionId);
                }
                this.fillForm(freshlyFetchedInstitutionData);
                if(!_.isEmpty(fetchResult.notification)){
                  this.$eventHub.broadcast(
                      this.$eventHub.EVENT_NOTIFICATION,
                      {type: fetchResult.notification.type, message: fetchResult.notification.message}
                  );
                }
              }.bind(this));
          }.bind(this));
      } else {
        this.setFormDefaults();
      }
    }
  },
  mounted: function(){
    this.setFormDefaults();
  }
}
</script>

<style lang="scss" scoped>
@import "../../../sass/settings";
</style>