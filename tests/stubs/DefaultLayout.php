<?php

namespace Tests\Stubs;

use Illuminate\View\Component;

class DefaultLayout extends Component
{
    public function render()
    {
        return view('test-components::default-layout');
    }
}
