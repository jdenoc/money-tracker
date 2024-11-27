<!DOCTYPE html>
<html lang="en">
<head>
    @component('head-component')
        Money Tracker | HOME
    @endcomponent
    @vite('resources/js/app-home.js')
</head>
<body>
<div id="app-home" class="py-16">
    <nav-bar page-name="home"></nav-bar>
    <institutions-panel></institutions-panel>
    <div class="ml-80">
        <entries-table></entries-table>
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
</body>
</html>