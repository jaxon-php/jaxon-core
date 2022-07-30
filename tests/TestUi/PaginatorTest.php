<?php

namespace Jaxon\Tests\TestUi;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Jaxon\Request\Call\Parameter;
use PHPUnit\Framework\TestCase;

use function Jaxon\jaxon;
use function Jaxon\rq;
use function Jaxon\pm;
use function Jaxon\jq;

class PaginatorTest extends TestCase
{
    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', __DIR__ . '/../src/sample.php');
    }

    /**
     * @throws SetupException
     */
    public function tearDown(): void
    {
        jaxon()->reset();
        parent::tearDown();
    }

    /**
     * @throws SetupException
     */
    public function testNoPagination()
    {
        // No pagination HTML code for only one page
        $aPagination = rq('Sample')->method(jq('#div')->val)->pg(1, 10, 0);
        $this->assertEquals('', (string)$aPagination);
        $aPagination = rq('Sample')->method(jq('#div')->val)->paginate(1, 10, 7);
        $this->assertEquals('', (string)$aPagination);
        $aPagination = rq('Sample')->method(jq('#div')->val)->pg(1, 10, 10);
        $this->assertEquals('', (string)$aPagination);

        $aPagination = rq('Sample')->method(jq('#div')->val)->pages(1, 10, 0);
        $this->assertIsArray($aPagination);
        $this->assertCount(0, $aPagination);
        $aPagination = rq('Sample')->method(jq('#div')->val)->pages(1, 10, 7);
        $this->assertIsArray($aPagination);
        $this->assertCount(0, $aPagination);
        $aPagination = rq('Sample')->method(jq('#div')->val)->pages(1, 10, 10);
        $this->assertIsArray($aPagination);
        $this->assertCount(0, $aPagination);
    }

    /**
     * @throws SetupException
     */
    public function testFirstPageWithNoPageNumber()
    {
        $sHtml = '<ul class="pagination">' .
            '<li class="disabled"><span>&laquo;</span></li>' .
            '<li class="active"><a href="javascript:;">1</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 2)">2</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 2)">&raquo;</a></li>' .
            '</ul>';
        $aPagination = rq('Sample')->method('string', 26, true)->pg(1, 10, 12);
        $this->assertEquals($sHtml, (string)$aPagination);

        $aPagination = rq('Sample')->method('string', 26, true)->pages(1, 10, 12);
        $this->assertIsArray($aPagination);
        $this->assertCount(4, $aPagination);
    }

    /**
     * @throws SetupException
     */
    public function testLastPageWithNoPageNumber()
    {
        $sHtml = '<ul class="pagination">' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 1)">&laquo;</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 1)">1</a></li>' .
            '<li class="active"><a href="javascript:;">2</a></li>' .
            '<li class="disabled"><span>&raquo;</span></li>' .
            '</ul>';
        $aPagination = rq('Sample')->method('string', 26, true)->pg(2, 10, 12);
        $this->assertEquals($sHtml, (string)$aPagination);

        $aPagination = rq('Sample')->method('string', 26, true)->pages(2, 10, 12);
        $this->assertIsArray($aPagination);
        $this->assertCount(4, $aPagination);
    }

    /**
     * @throws SetupException
     */
    public function testMiddlePageWithNoPageNumber()
    {
        $sHtml = '<ul class="pagination">' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 1)">&laquo;</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 1)">1</a></li>' .
            '<li class="active"><a href="javascript:;">2</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 3)">3</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 3)">&raquo;</a></li>' .
            '</ul>';
        $aPagination = rq('Sample')->method('string', 26, true)->pg(2, 10, 24);
        $this->assertEquals($sHtml, (string)$aPagination);

        $aPagination = rq('Sample')->method('string', 26, true)->pages(2, 10, 24);
        $this->assertIsArray($aPagination);
        $this->assertCount(5, $aPagination);
    }

    /**
     * @throws SetupException
     */
    public function testPaginationWithPageNumber()
    {
        $sHtml = '<ul class="pagination">' .
            '<li class="disabled"><span>&laquo;</span></li>' .
            '<li class="active"><a href="javascript:;">1</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 2, 26, true)">2</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 3, 26, true)">3</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 2, 26, true)">&raquo;</a></li>' .
            '</ul>';
        $aPagination = rq('Sample')->method('string', pm()->page(), 26, true)->pg(1, 10, 24);
        $this->assertEquals($sHtml, (string)$aPagination);

        $aPagination = rq('Sample')->method('string', pm()->page(), 26, true)->pages(1, 10, 24);
        $this->assertIsArray($aPagination);
        $this->assertCount(5, $aPagination);
    }

    /**
     * @throws SetupException
     */
    public function testNextAndPrevTexts()
    {
        jaxon()->paginator()->setNextText('Next');
        jaxon()->paginator()->setPreviousText('Prev');
        $sHtml = '<ul class="pagination">' .
            '<li class="disabled"><span>Prev</span></li>' .
            '<li class="active"><a href="javascript:;">1</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 2)">2</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 2)">Next</a></li>' .
            '</ul>';
        $aPagination = rq('Sample')->method('string', 26, true)->pg(1, 10, 12);
        $this->assertEquals($sHtml, (string)$aPagination);

        $aPagination = rq('Sample')->method('string', 26, true)->pages(1, 10, 12);
        $this->assertIsArray($aPagination);
        $this->assertCount(4, $aPagination);
    }

    /**
     * @throws SetupException
     */
    public function testMaxPagesStart()
    {
        jaxon()->paginator()->setNextText('Next');
        jaxon()->paginator()->setPreviousText('Prev');
        jaxon()->paginator()->setMaxPages(5);
        $sHtml = '<ul class="pagination">' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 1)">Prev</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 1)">1</a></li>' .
            '<li class="active"><a href="javascript:;">2</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 3)">3</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 10)">10</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 3)">Next</a></li>' .
            '</ul>';
        $aPagination = rq('Sample')->method('string', 26, true)->pg(2, 5, 48);
        $this->assertEquals($sHtml, (string)$aPagination);

        $aPagination = rq('Sample')->method('string', 26, true)->pages(2, 5, 48);
        $this->assertIsArray($aPagination);
        $this->assertCount(7, $aPagination);
    }

    /**
     * @throws SetupException
     */
    public function testMaxPagesMiddle()
    {
        jaxon()->paginator()->setNextText('Next');
        jaxon()->paginator()->setPreviousText('Prev');
        jaxon()->paginator()->setMaxPages(5);
        $sHtml = '<ul class="pagination">' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 5)">Prev</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 1)">1</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li class="active"><a href="javascript:;">6</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 10)">10</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 7)">Next</a></li>' .
            '</ul>';
        $aPagination = rq('Sample')->method('string', 26, true)->pg(6, 5, 48);
        $this->assertEquals($sHtml, (string)$aPagination);

        $aPagination = rq('Sample')->method('string', 26, true)->pages(6, 5, 48);
        $this->assertIsArray($aPagination);
        $this->assertCount(7, $aPagination);
    }

    /**
     * @throws SetupException
     */
    public function testMaxPagesSevenMiddle()
    {
        jaxon()->paginator()->setNextText('Next');
        jaxon()->paginator()->setPreviousText('Prev');
        jaxon()->paginator()->setMaxPages(7);
        $sHtml = '<ul class="pagination">' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 5)">Prev</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 1)">1</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 5)">5</a></li>' .
            '<li class="active"><a href="javascript:;">6</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 7)">7</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 10)">10</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 7)">Next</a></li>' .
            '</ul>';
        $aPagination = rq('Sample')->method('string', 26, true)->pg(6, 5, 48);
        $this->assertEquals($sHtml, (string)$aPagination);

        $aPagination = rq('Sample')->method('string', 26, true)->pages(6, 5, 48);
        $this->assertIsArray($aPagination);
        $this->assertCount(9, $aPagination);
    }

    /**
     * @throws SetupException
     */
    public function testMaxPagesEnd()
    {
        jaxon()->paginator()->setNextText('Next');
        jaxon()->paginator()->setPreviousText('Prev');
        jaxon()->paginator()->setMaxPages(5);
        $sHtml = '<ul class="pagination">' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 9)">Prev</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 1)">1</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 8)">8</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 9)">9</a></li>' .
            '<li class="active"><a href="javascript:;">10</a></li>' .
            '<li class="disabled"><span>Next</span></li>' .
            '</ul>';
        $aPagination = rq('Sample')->method('string', 26, true)->pg(10, 5, 48);
        $this->assertEquals($sHtml, (string)$aPagination);

        $aPagination = rq('Sample')->method('string', 26, true)->pages(10, 5, 48);
        $this->assertIsArray($aPagination);
        $this->assertCount(7, $aPagination);
    }

    /**
     * @throws SetupException
     */
    public function testMaxPagesBeforeEnd()
    {
        jaxon()->paginator()->setNextText('Next');
        jaxon()->paginator()->setPreviousText('Prev');
        jaxon()->paginator()->setMaxPages(5);
        $sHtml = '<ul class="pagination">' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 8)">Prev</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 1)">1</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 8)">8</a></li>' .
            '<li class="active"><a href="javascript:;">9</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 10)">10</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 10)">Next</a></li>' .
            '</ul>';
        $aPagination = rq('Sample')->method('string', 26, true)->pg(9, 5, 48);
        $this->assertEquals($sHtml, (string)$aPagination);

        $aPagination = rq('Sample')->method('string', 26, true)->pages(9, 5, 48);
        $this->assertIsArray($aPagination);
        $this->assertCount(7, $aPagination);
    }

    /**
     * @throws SetupException
     */
    public function testMaxPagesMin()
    {
        jaxon()->paginator()->setNextText('Next');
        jaxon()->paginator()->setPreviousText('Prev');
        jaxon()->paginator()->setMaxPages(3);
        $sHtml = '<ul class="pagination">' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 8)">Prev</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 1)">1</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 8)">8</a></li>' .
            '<li class="active"><a href="javascript:;">9</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 10)">10</a></li>' .
            '<li><a href="javascript:;" onclick="Sample.method(\'string\', 26, true, 10)">Next</a></li>' .
            '</ul>';
        $aPagination = rq('Sample')->method('string', 26, true)->pg(9, 5, 48);
        $this->assertEquals($sHtml, (string)$aPagination);

        $aPagination = rq('Sample')->method('string', 26, true)->pages(9, 5, 48);
        $this->assertIsArray($aPagination);
        $this->assertCount(7, $aPagination);
    }
}
