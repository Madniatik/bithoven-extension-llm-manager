<?php

namespace Tests\Stubs;

use Illuminate\View\Component;

class DefaultLayout extends Component
{
    public function render()
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Layout</title>
</head>
<body>
    {{ $slot }}
</body>
</html>
HTML;
    }
}
