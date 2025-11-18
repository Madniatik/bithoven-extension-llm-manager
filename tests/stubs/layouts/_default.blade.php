<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Test')</title>
</head>
<body>
    @yield('breadcrumbs')
    {{ $slot }}
    @stack('scripts')
</body>
</html>
