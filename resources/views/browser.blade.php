<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Locale -->
    <meta name="locale" content="{{ app()->getLocale() }}">
    <title>File Manager</title>
    <base href="/">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <meta name="robots" content="noodp,index,follow"/>
    <meta http-equiv="X-UA-Compatible" content="requiresActiveX=true"/>
    <meta http-equiv="content-language" content="{{ app()->getLocale() }}"/>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/element-ui.min.css') }}">
    <link rel="stylesheet" href="{{ mix('css/file-manager.css') }}">
</head>
<body>
<div id="app">
    <file-manager
        :has-selector="{{ json_encode($hasSelector) }}"
        :has-editor="{{ json_encode($hasEditor) }}"
        :domain="{{ json_encode(config('app.url')) }}"
        :root-directory="{{ json_encode($userDirectory) }}"
    ></file-manager>
</div>
<script src="{{ mix('js/file-manager.js') }}" defer></script>
</body>
</html>
