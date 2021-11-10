<template>
  <notifications position="bottom center" width="300"></notifications>
</template>

<script>
    import _ from 'lodash';
    import {uuid} from 'vue-uuid';

    export default {
      name: "notification",
      computed: {
        uuid: function(){
          return uuid.v4();
        }
      },
      methods: {
        displayNotification: function(notification) {
          if (!_.isEmpty(notification)) {
            this.$notify({
              id: this.uuid,
              type: notification.type,
              title: notification.message,
              duration: 5500, // 5.5 seconds
            });
          }
        }
      },
      created: function(){
        this.$eventBus.listen(this.$eventBus.EVENT_NOTIFICATION(), this.displayNotification);
      },
    }
</script>

<style scoped>

</style>