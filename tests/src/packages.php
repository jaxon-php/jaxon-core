<?php

use Jaxon\App\CallableClass;
use Jaxon\Plugin\AbstractPackage;

class SamplePackageClass extends CallableClass
{
    public function home()
    {
        $this->response->debug('This class is registered by a package!!');
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
                    'directory' => realpath(__DIR__ . '/../views'),
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
    public static function config()
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
