export default {
    install: function(Vue, options){
        Vue.prototype.$eventHub = new Vue({
            computed: {
                /**
                 * @returns {string}
                 */
                EVENT_LOADING_SHOW: function(){ return "loading-true"; },
                /**
                 * @returns {string}
                 */
                EVENT_LOADING_HIDE: function(){ return "loading-false"; },
                /**
                 * @returns {string}
                 */
                EVENT_NOTIFICATION: function(){ return "notification"; },
                /**
                 * @returns {string}
                 */
                EVENT_ACCOUNT_UPDATE: function(){ return 'update-accounts'; },
                /**
                 * @returns {string}
                 */
                EVENT_ENTRY_TABLE_UPDATE: function(){ return "update-entry-table"; },
                /**
                 * @returns {string}
                 */
                EVENT_ENTRY_MODAL_OPEN: function(){ return "open-entry-modal";},
                /**
                 * @returns {string}
                 */
                EVENT_ENTRY_MODAL_CLOSE: function(){ return "close-entry-modal"; },
                /**
                 * @returns {string}
                 */
                EVENT_ENTRY_MODAL_UPDATE_DATA: function(){ return "update-data-in-entry-modal"; },
                /**
                 * @returns {string}
                 */
                EVENT_FILTER_MODAL_OPEN: function(){ return "open-filter-modal"; },
                /**
                 * @returns {string}
                 */
                EVENT_FILTER_MODAL_CLOSE: function(){ return "close-filter-modal"; },
                /**
                 * @returns {string}
                 */
                EVENT_TRANSFER_MODAL_OPEN: function(){ return "open-transfer-model"; },
                /**
                 * @returns {string}
                 */
                EVENT_TRANSFER_MODAL_CLOSE: function(){ return "close-transfer-model"; },
                /**
                 * @return {string}
                 */
                EVENT_STATS_SUMMARY: function(){ return "stats-display-summary-chart"; },
                /**
                 * @returns {string}
                 */
                EVENT_STATS_TRENDING: function(){ return 'stats-display-trending-chart'},
                /**
                 * @returns {string}
                 */
                EVENT_STATS_TAGS: function(){ return 'stats-display-tags-chart' },
                /**
                 * @return {string}
                 */
                EVENT_STATS_DISTRIBUTION: function(){ return 'stats-display-distribution-char' },
            },
            methods: {
                broadcast(event, data = null){
                    this.$emit(event, data);
                },
                listen(event, callback){
                    this.$on(event, callback);
                }
            }
        });
    }
}

