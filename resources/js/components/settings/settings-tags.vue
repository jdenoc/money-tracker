<template>
  <section id="settings-accounts" class="container">
    <form>
      <!-- id |  int(10) unsigned -->

      <div class="field is-horizontal">
        <!-- name -->
        <div class="field-label is-normal">
          <label class="label" for="settings-tag-name">Tag:</label>
        </div>
        <div class="field-body"><div class="field"><div class="control">
          <input id="settings-tag-name" name="name" type="text" class="input" v-model="form.name" />
        </div></div></div>
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

    <div class="tags are-medium">
      <span class="tag is-info"
        v-for="tag in listTags"
        v-text="tag.name"
        v-bind:class="{'is-light': form.id !== tag.id}"
        v-on:click="fillForm(tag)"
      ></span>
    </div>
  </section>
</template>

<script>
import _ from "lodash";
import {tagsObjectMixin} from "../../mixins/tags-object-mixin";
import {settingsMixin} from "../../mixins/settings-mixin";

export default {
  name: "settings-tags",
  mixins: [settingsMixin, tagsObjectMixin],
  data: function(){
    return {

    }
  },
  computed: {
    listTags: function(){
      return _.orderBy(this.rawTagsData);
    },
    formDefaultData: function(){
      return {
        id: null,
        name: '',
      };
    },
  },
  methods: {
    fillForm: function(account){
      this.form = _.clone(account);
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
    },
    setFormDefaults: function(){
      this.fillForm(this.formDefaultData);
    },

    retrieveUpToDateTagData: function(tagId = null){
      if(_.isNumber(tagId)){
      //   this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
      //
      //   new Promise(function(resolve, reject){
      //     let accountData = this.accountObject.find(accountId);
      //     if(this.accountObject.isDataUpToDate(accountData)){
      //       resolve(accountData);
      //     } else {
      //       reject(accountId);
      //     }
      //   }.bind(this))
      //     .then(this.fillForm.bind(this))
      //     .catch(function(accountId){
      //       this.accountObject.fetch(accountId)
      //         .then(function(fetchResult){
      //           let freshlyFetchedAccountData = {};
      //           if(fetchResult.fetched){
      //             freshlyFetchedAccountData = this.accountObject.find(accountId);
      //           }
      //           this.fillForm(freshlyFetchedAccountData);
      //           if(!_.isEmpty(fetchResult.notification)){
      //             this.$eventHub.broadcast(
      //               this.$eventHub.EVENT_NOTIFICATION,
      //               {type: fetchResult.notification.type, message: fetchResult.notification.message}
      //             );
      //           }
      //         }.bind(this));
      //     }.bind(this));
      } else {
        this.setFormDefaults();
      }
    },
    save: function(){
      // this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
      // TODO: make API call to save (insert/update) account details
      console.log("this feature is not yet ready");
    }
  },
  mounted: function(){
    this.setFormDefaults();
  }
}
</script>

<style lang="scss" scoped>
@import "../../../sass/settings";
.tags{
  margin: 1rem;
}
</style>