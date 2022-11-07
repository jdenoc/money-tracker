<template>
  <vue-snotify></vue-snotify>
</template>

<script>
import _ from 'lodash';
import { SnotifyStyle } from 'vue-snotify';

export default {
  name: "notification-item",
  methods: {
    displayNotification: function(notification){
      if(!_.isEmpty(notification)){
        switch(notification.type){
          case SnotifyStyle.error:
            this.$snotify.error(notification.message);
            break;
          case SnotifyStyle.info:
          default:
            this.$snotify.info(notification.message);
            break;
          case SnotifyStyle.success:
            this.$snotify.success(notification.message);
            break;
          case SnotifyStyle.warning:
            this.$snotify.warning(notification.message);
            break;
        }
      }
    }
  },
  created: function(){
    this.$eventHub.listen(this.$eventHub.EVENT_NOTIFICATION, this.displayNotification);
  },
}
</script>

<style scoped>
@import "~vue-snotify/styles/material";

.snotifyToast__body{
  font-size: 1.1em;
}
.snotify-success .snotifyToast__body{
  color: #e1f1e1;
}
</style>