<?php

use Jaxon\App\Dialog\Library\DialogLibraryTrait;
use Jaxon\App\Dialog\LibraryInterface;
use Jaxon\App\Dialog\QuestionInterface;
use Jaxon\Tests\Ns\CallableClass;

class Dialog extends CallableClass
{
    public function success()
    {
        $this->response->dialog->success('This is a message!!', 'Success');
    }

    public function info()
    {
        $this->response->dialog->info('This is a message!!', 'Info');
    }

    public function warning()
    {
        $this->response->dialog->warning('This is a message!!', 'Warning');
    }

    public function error()
    {
        $this->response->dialog->error('This is a message!!', 'Error');
    }

    public function show()
    {
        $this->response->dialog->show('Dialog', 'This is the dialog content!!',
            [['title' => 'Save', 'class' => 'btn', 'click' => $this->rq()->save()->confirm('Save?')]]);
    }

    public function showWith()
    {
        $this->response->dialog->with('bootbox')->show('Dialog', 'This is the dialog content!!',
            [['title' => 'Save', 'class' => 'btn', 'click' => $this->rq()->save()->confirm('Save?')]]);
    }

    public function hide()
    {
        $this->response->dialog->hide();
    }
}

class TestDialogLibrary implements LibraryInterface, QuestionInterface
{
    use DialogLibraryTrait;

    /**
     * @const The library name
     */
    const NAME = 'test';

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    public function confirm(string $sQuestion, string $sYesScript, string $sNoScript): string
    {
        return '';
    }
}
