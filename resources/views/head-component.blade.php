<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
@component('favicon-component');
@endcomponent

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{config("app.debug") ? '[DEV] ' : ''}}{{$slot}}</title>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

<link href="{{mix('vue/css/app.css')}}" rel="stylesheet" type="text/css">
<link href="{{mix('vue/css/font-awesome.css')}}" rel="stylesheet" type="text/css">

<script type="text/javascript" src="{{mix('vue/js/manifest.js')}}"></script>
<script type="text/javascript" src="{{mix('vue/js/vendor.js')}}"></script>