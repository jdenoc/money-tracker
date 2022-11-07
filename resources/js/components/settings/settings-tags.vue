<template>
  <section id="settings-tags" class="max-w-lg">
    <h3 class="text-2xl mb-5 scroll-mt-16">Tags</h3>
    <form class="grid grid-cols-6 gap-2">
      <!-- name -->
      <label for="settings-tag-name" class="font-medium justify-self-end py-2">Tag:</label>
      <input id="settings-tag-name" name="name" type="text" class="rounded text-gray-700 col-span-5" autocomplete="off" v-model="form.name" />

      <button type="button" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 mx-1 mt-6 bg-gray-50 hover:bg-white col-span-3" v-on:click="setFormDefaults()">Clear</button>
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

    <spinner v-if="!areTagsSet" id="loading-settings-tags"></spinner>

    <div class="tags flex flex-wrap gap-y-1 gap-x-1.5" v-else>
      <span class="tag rounded-2xl px-2 py-1.5 text-base cursor-pointer border border-gray-300"
            v-for="tag in listTags"
            v-bind:key="tag.id"
            v-bind:id="'settings-tag-'+tag.id"
            v-text="tag.name"
            v-bind:class="{
              'bg-white text-gray-700 hover:bg-gray-100 ' : form.id !== tag.id,
              'bg-blue-500 text-white opacity-90 hover:opacity-100 ': form.id === tag.id
            }"
            v-on:click="fillForm(tag)"
      >
      </span>
      <!-- TODO: add "delete" functionality -->
    </div>
  </section>
</template>

<script>
// components
import Spinner from "vue-spinner-component/src/Spinner.vue";
// mixins
import {settingsMixin} from "../../mixins/settings-mixin";
import {tagsObjectMixin} from "../../mixins/tags-object-mixin";
// utilities
import _ from "lodash";
// objects
import {Tag} from "../../tag";

export default {
  name: "settings-tags",
  mixins: [settingsMixin, tagsObjectMixin],
  components: {
    Spinner
  },
  data: function(){
    return { };
  },
  computed: {
    canSave: function(){
      if(!_.isNull(this.form.id)){
        let tagData = this.tagsObject.find(this.form.id);
        return !_.isEqual(tagData, this.form);
      } else {
        return !_.isEmpty(this.form.name);
      }
    },
    defaultFormData: function(){
      return {
        id: null,
        name: '',
      };
    },
    tagObject: function(){
      return new Tag();
    }
  },
  methods: {
    fillForm: function(tagData){
      this.form = _.cloneDeep(tagData);
    },
    save: function(){
      // TODO: consider putting logic in place to stop duplicate tag names
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
      let tagData = {};
      Object.keys(this.form).forEach(function(formDatumKey){
        switch (formDatumKey){
          case 'id':
          case 'name':
            tagData[formDatumKey] = this.form[formDatumKey];
            break;
          default:
            // do nothing...
            break;
        }
      }.bind(this));

      this.tagsObject.setFetchedState = false
      this.tagObject.save(tagData)
        .then(function(notification){
          // show a notification if needed
          if(!_.isEmpty(notification)){
            this.$eventHub.broadcast(
              this.$eventHub.EVENT_NOTIFICATION,
              notification
            );
          }
        }.bind(this))
        .finally(function(){
          this.setFormDefaults();
          this.tagsObject.fetch().finally(function(){
            this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
          }.bind(this));
        }.bind(this));
    }
  },
  mounted: function(){
    this.setFormDefaults();
  }
}
</script>

<style lang="scss" scoped>
</style>