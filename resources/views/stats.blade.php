<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{config("app.debug") ? '[DEV] ' : '' }}Money Tracker | STATS</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <link href="{{asset('vue/css/app.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('vue/css/font-awesome.css')}}" rel="stylesheet" type="text/css">
</head>
<body>
<div id="app">
    <navbar></navbar>
    <div class="columns is-gapless">
        <div class="column is-one-fifth">
            <stats-nav></stats-nav>
        </div>
        <div class="column is-three-fifths">
            <stats></stats>
        </div>

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
<script type="text/javascript" src="{{asset('vue/js/app-stats.js')}}"></script>
</body>
</html>