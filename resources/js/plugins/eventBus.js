import mitt from 'mitt';

export default {
    install: function(app, options){

        const emitter = mitt();

        app.config.globalProperties.$eventBus = {};

        app.config.globalProperties.$eventBus.broadcast = function(event, data){
            emitter.emit(event, data);
        }

        app.config.globalProperties.$eventBus.listen = function(event, callback){
            emitter.on(event, callback);
        }

        app.config.globalProperties.$eventBus.EVENT_LOADING_SHOW = function(){ return "loading:true"; }
        app.config.globalProperties.$eventBus.EVENT_LOADING_HIDE = function(){ return "loading:false"; }
        app.config.globalProperties.$eventBus.EVENT_NOTIFICATION = function(){ return "notification"; }
        app.config.globalProperties.$eventBus.EVENT_ACCOUNT_UPDATE = function(){ return 'accounts:update'; }
        app.config.globalProperties.$eventBus.EVENT_ENTRY_TABLE_UPDATE = function(){ return "entry-table:update"; }
        app.config.globalProperties.$eventBus.EVENT_ENTRY_MODAL_OPEN = function(){ return "entry-modal:open";}
        app.config.globalProperties.$eventBus.EVENT_ENTRY_MODAL_CLOSE = function(){ return "entry-modal:close"; }
        app.config.globalProperties.$eventBus.EVENT_ENTRY_MODAL_UPDATE_DATA = function(){ return "update-data-in-entry-modal"; }
        app.config.globalProperties.$eventBus.EVENT_FILTER_MODAL_OPEN = function(){ return "filter-modal:open"; }
        app.config.globalProperties.$eventBus.EVENT_FILTER_MODAL_CLOSE = function(){ return "filter-modal:close"; }
        app.config.globalProperties.$eventBus.EVENT_TRANSFER_MODAL_OPEN = function(){ return "transfer-model:open"; }
        app.config.globalProperties.$eventBus.EVENT_TRANSFER_MODAL_CLOSE = function(){ return "transfer-model:close"; }
        app.config.globalProperties.$eventBus.EVENT_FILE_DROP_UPDATE = function(){ return 'file-pond:update'; }
        app.config.globalProperties.$eventBus.EVENT_STATS_SUMMARY = function(){ return "stats-display:summary-chart"; }
        app.config.globalProperties.$eventBus.EVENT_STATS_TRENDING = function(){ return 'stats-display:trending-chart'}
        app.config.globalProperties.$eventBus.EVENT_STATS_TAGS = function(){ return 'stats-display:tags-chart' }
        app.config.globalProperties.$eventBus.EVENT_STATS_DISTRIBUTION = function(){ return 'stats-display:distribution-chart' }
    }
}