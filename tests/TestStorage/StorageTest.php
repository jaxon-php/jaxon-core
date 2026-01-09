<?php

namespace Jaxon\Tests\TestStorage;

use Jaxon\Storage\StorageException;
use Jaxon\Storage\StorageManager;
use League\Flysystem\CorruptedPathDetected;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function Jaxon\storage;
use function dirname;
use function file_get_contents;

class StorageTest extends TestCase
{
    /**
     * @var StorageManager
     */
    protected $xManager;

    /**
     * @var string
     */
    protected $sInputDir;

    public function setUp(): void
    {
        $this->sInputDir = dirname(__DIR__) . '/files';
        $this->xManager = storage();
    }

    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    /**
     * @throws StorageException
     */
    public function testStorageReader()
    {
        $xInputStorage = $this->xManager->adapter('local')->make($this->sInputDir);
        $sInputContent = $xInputStorage->read('hello.txt');

        $this->assertEquals(file_get_contents("{$this->sInputDir}/hello.txt"), $sInputContent);
    }

    public function testAdapterAndDirOptions()
    {
        jaxon()->config()->setAppOptions([
            'adapters' => [
                'files' => [
                    'alias' => 'local',
                    'options' => [
                        'lazyRootCreation' => false, // Create dirs if they don't exist.
                    ],
                ],
            ],
            'stores' => [
                'files' => [
                    'adapter' => 'files',
                    'dir' => $this->sInputDir,
                    'options' => [
                        'config' => [
                            'public_url' => '/static/files',
                        ],
                    ],
                ],
            ],
        ], 'storage');

        $xInputStorage = $this->xManager->get('files');
        $sInputContent = $xInputStorage->read('hello.txt');

        $this->assertEquals(file_get_contents("{$this->sInputDir}/hello.txt"), $sInputContent);
        $this->assertEquals('/static/files/hello.txt', $xInputStorage->publicUrl('hello.txt'));
    }

    public function testWriteError()
    {
        jaxon()->config()->setAppOptions([
            'adapters' => [
                'files' => [
                    'alias' => 'local',
                    'options' => [
                        'lazyRootCreation' => true, // Don't create dirs if they don't exist.
                    ],
                ],
            ],
            'stores' => [
                'files' => [
                    'adapter' => 'files',
                    'dir' => dirname(__DIR__ . '/files'),
                    'options' => [
                        'config' => [
                            'public_url' => '/static/files',
                        ],
                    ],
                ],
            ],
        ], 'storage');

        $this->expectException(CorruptedPathDetected::class);
        $xInputStorage = $this->xManager->get('files');
        $sInputContent = $xInputStorage->read("\0hello.txt");
    }

    public function testStorageWriter()
    {
        $this->xManager->register('memory', fn() => new InMemoryFilesystemAdapter());
        jaxon()->config()->setAppOptions([
            'adapter' => 'memory',
            'dir' => 'files',
            'options' => [],
        ], 'storage.stores.memory');

        $xInputStorage = $this->xManager->adapter('local')->make($this->sInputDir);
        $sInputContent = $xInputStorage->read('hello.txt');

        $xOutputStorage = $this->xManager->get('memory');
        $xOutputStorage->write('hello.txt', $sInputContent);
        $sOutputContent = $xOutputStorage->read('hello.txt');

        $this->assertEquals($sOutputContent, $sInputContent);
    }

    public function testErrorUnknownAdapter()
    {
        $this->expectException(StorageException::class);
        $xUnknownStorage = $this->xManager->adapter('unknown')->make($this->sInputDir);
    }

    public function testErrorUnknownConfig()
    {
        $this->expectException(StorageException::class);
        $xUnknownStorage = $this->xManager->get('unknown');
    }

    public function testErrorIncorrectConfigAdapter()
    {
        jaxon()->config()->setAppOptions([
            'adapter' => null,
            'dir' => 'files',
            'options' => [],
        ], 'storage.stores.custom');

        $this->expectException(StorageException::class);
        $xErrorStorage = $this->xManager->get('custom');
    }

    public function testErrorIncorrectConfigDir()
    {
        jaxon()->config()->setAppOptions([
            'adapter' => 'memory',
            'dir' => null,
            'options' => [],
        ], 'storage.stores.custom');

        $this->expectException(StorageException::class);
        $xErrorStorage = $this->xManager->get('custom');
    }

    public function testErrorIncorrectConfigOptions()
    {
        jaxon()->config()->setAppOptions([
            'adapter' => 'memory',
            'dir' => 'files',
            'options' => null,
        ], 'storage.stores.custom');

        $this->expectException(StorageException::class);
        $xErrorStorage = $this->xManager->get('custom');
    }
}
