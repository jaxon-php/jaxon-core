<?php

use Jaxon\App\FuncComponent;

class TestDialog extends FuncComponent
{
    public function show(string $title, string $content)
    {
        $this->response()->dialog()->show($title, $content);
    }

    public function with(string $title, string $content)
    {
        $this->response()->dialog()->with('jslib')->show($title, $content);
    }

    public function hide()
    {
        $this->response()->dialog()->hide();
    }

    public function alerts()
    {
        $this->response()->dialog()->title('Error')->error('Error message');
        $this->response()->dialog()->title('Warning')->warning('Warning message');
        $this->response()->dialog()->title('Info')->info('Info message');
        $this->response()->dialog()->title('Success')->success('Success message');
    }
}
