<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--  CSRF TOKEN  -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','laravel-shop') - laravel</title>
    <!-- 样式  -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

</head>
<body>
    <div id="app" class="{{ route_class() }}-page">
        @include('layouts._header')
        <div class="container">
            @yield('content')
        </div>
        @include('layouts._footer')
    </div>

<!-- js 脚本 -->
<script src="{{ mix('js/app.js') }}"></script>
</body>
</html>