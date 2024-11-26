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

<style lang="scss">
// vite/vue2 setup is broken for scoped styles stored in node_modules.
// style element order is all mixed up
@import 'vue-snotify/styles/material';
</style>

<style lang="css" scoped>
.snotifyToast__body {
  font-size: 1.1rem;
}

.snotify-success .snotifyToast__body {
  color: #e1f1e1;
}
</style>