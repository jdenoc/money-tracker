<!DOCTYPE html>
<html lang="en">
<head>
    @component('head-component')
        Money Tracker | HOME
    @endcomponent
</head>
<body>
<div id="app-home" class="py-16">
    <nav-bar page-name="home"></nav-bar>
    <institutions-panel></institutions-panel>
    <div class="ml-80">
        <entries-table></entries-table>
        {{--            <div v-hotkey="keymap">--}}
        <div>
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
<script type="text/javascript" src="{{mix('dist/js/app-home.js')}}"></script>
</body>
</html>