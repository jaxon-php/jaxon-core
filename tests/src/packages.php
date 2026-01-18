<?php

use Jaxon\App\FuncComponent;
use Jaxon\Plugin\AbstractPackage;

class SamplePackageClass extends FuncComponent
{
    public function home()
    {
        $this->response()->debug('This class is registered by a package!!');
    }
}

class SamplePackage extends AbstractPackage
{
    /**
     * @inheritDoc
     */
    public static function config(): array
    {
        return [
            'classes' => [
                SamplePackageClass::class,
            ],
            'views' => [
                'test' => [
                    'directory' => realpath(dirname(__DIR__) . '/views'),
                    'extension' => '.php',
                    // 'renderer' => 'jaxon', // This is the default value.
                    'template' => [
                        'option' => 'template',
                        'default' => 'test',
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getHtml(): string
    {
        return '';
    }
}

class BadConfigPackage extends AbstractPackage
{
    /**
     * @inheritDoc
     */
    public static function config(): string|array
    {
        return true; // This is wrong. The return value must be a string or an array.
    }

    /**
     * @inheritDoc
     */
    public function getHtml(): string
    {
        return '';
    }
}
