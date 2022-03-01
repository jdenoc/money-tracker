<!DOCTYPE html>
<html lang="en">
<head>
    @component('head-component')
        Money Tracker | HOME
    @endcomponent
</head>
<body>
    <div id="app-home">
        <navbar page-name="home"></navbar>
        <div class="columns is-gapless">
            <div id="institutions-panel-column" class="column is-one-quarter">
                <institutions-panel></institutions-panel>
            </div>
            <div class="column">
                <entries-table></entries-table>
            </div>
            <div v-hotkey="keymap">
                <!-- modal components -->
                <entry-modal></entry-modal>
                <transfer-modal></transfer-modal>
                <filter-modal></filter-modal>
                <!--
                loading-modal must ALWAYS be on the bottom.
                This way if the loading-modal is active and another modal is active,
                then it will look like the loading-modal is still active.
                -->
                <loading-modal></loading-modal>
                <!-- notifications are the only exception -->
                <notification></notification>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{mix('vue/js/app-home.js')}}"></script>
</body>
</html>