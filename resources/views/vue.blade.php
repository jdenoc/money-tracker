<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Money Tracker | vue testing</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <link href="{{asset('vue/css/app.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('vue/css/font-awesome.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('vue/css/tags-input.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('vue/css/vue-dropzone.css')}}" rel="stylesheet" type="text/css">
</head>
<body>
    <div id="app">
        <navbar></navbar>
        <div class="columns is-gapless">
            <div id="institutions-panel-column" class="column is-one-quarter">
                <institutions-panel></institutions-panel>
            </div>
            <div class="column">
                <entries-table></entries-table>
            </div>
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
    <script type="text/javascript" src="{{asset('vue/js/app.js')}}"></script>
    <script type="text/javascript" src="{{asset('vue/js/bulma-accordion.js')}}"></script>
</body>
</html>