<!DOCTYPE html>
<html lang="en">
<head>
    @component('head-component')
        Money Tracker | STATS
    @endcomponent
    @vite('resources/js/app-stats.js')
</head>
<body>
<div id="app-stats" class="py-16">
    <nav-bar page-name="stats"></nav-bar>
    <stats-nav></stats-nav>
    <div class="ml-80">
        <stats-display></stats-display>

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
</body>
</html>