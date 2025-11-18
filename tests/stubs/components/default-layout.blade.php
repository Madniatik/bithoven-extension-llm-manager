<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Test Layout' }}</title>
</head>
<body>
    @if(isset($breadcrumbs))
        <nav>{{ $breadcrumbs }}</nav>
    @endif
    
    <main>
        {{ $slot }}
    </main>
    
    @if(isset($scripts))
        {{ $scripts }}
    @endif
</body>
</html>
