import _ from "lodash";
import {tailwindColorsMixin} from "./tailwind-colors-mixin";

export const settingsMixin = {
  mixins: [tailwindColorsMixin],
  data: function(){
    return {
      form: {}    // this is set when component is mounted
    };
  },

  computed: {
    defaultFormData(){
      console.warn("Using a default defaultFormData(). Should be overridden");
      return {};
    },
    isDataInForm(){
      return !_.isNull(this.form.id) && _.isNumber(this.form.id);
    },
    defaultToggleButtonProperties(){
      return {
        colorChecked: this.tailwindColors.blue[600],
        colorUnchecked: this.tailwindColors.gray[400],
        fontSize: 14, // px
        labelChecked: 'Active',
        labelUnchecked: 'Inactive',
        height: 40, // px
        width: 250, // px
        transitionSpeed: 75, // ms
        reverse: true,
      };
    },
    /** @deprecated **/
    altDefaultToggleButtonProperties(){
      let properties = _.cloneDeep(this.defaultToggleButtonProperties);
      properties.colorChecked = this.tailwindColors.gray[400];
      properties.colorUnchecked = this.tailwindColors.blue[600];
      properties.labelChecked = 'Inactive';
      properties.labelUnchecked = 'Active';
      properties.reverse = false;
      return properties;
    }
  },

  methods: {
    afterSaveDisplayNotificationIfNeeded(notification){
      // show a notification if needed
      if(!_.isEmpty(notification)){
        this.$eventHub.broadcast(
          this.$eventHub.EVENT_NOTIFICATION,
          {type: notification.type, message: notification.message}
        );
      }
    },
    debugOutput(msg){   // TODO: remove
      console.debug(new Date().getTime()+' '+msg);
    },
    featureUnavailable(){
      console.debug("This feature is currently available");
    },
    fillForm(formData){
      formData = _.cloneDeep(formData);
      this.form = this.sanitiseData(formData);
    },
    makeDateReadable(isoDateString){
      if(_.isNull(isoDateString)){
        return isoDateString;
      } else {
        return new Date(isoDateString).toString();
      }
    },
    sanitiseData(formData){
      console.warn("Using a default sanitiseData(). It should be overridden.");
      return formData;
    },
    setFormDefaults(){
      this.fillForm(this.defaultFormData);
    },
  }
}

