<?php

namespace Jaxon\Tests\TestUi;

use Jaxon\Jaxon;
use Jaxon\Exception\SetupException;
use Jaxon\App\Pagination\FuncPaginator;
use Jaxon\App\Pagination\PaginationRenderer;
use Jaxon\Response\Response;
use PHPUnit\Framework\TestCase;

use function trim;

class PaginatorTest extends TestCase
{
    /**
     * @var Response
     */
    protected $xResponse = null;

    /**
     * @var PaginationRenderer
     */
    protected $xPaginationRenderer = null;

    /**
     * @throws SetupException
     */
    public function setUp(): void
    {
        jaxon()->setOption('core.prefix.class', '');
        jaxon()->register(Jaxon::CALLABLE_CLASS, 'Sample', dirname(__DIR__) . '/src/sample.php');

        $this->xResponse = jaxon()->getResponse();
        $this->xResponse->clearCommands();
        $this->xPaginationRenderer = jaxon()->di()->getPaginationRenderer();
    }

    /**
     * Create a paginator.
     *
     * @param int $nPageNumber      The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     *
     * @return FuncPaginator
     */
    protected function paginator(int $nPageNumber, int $nItemsPerPage, int $nTotalItems): FuncPaginator
    {
        return new FuncPaginator($nPageNumber, $nItemsPerPage, $nTotalItems,
            $this->xPaginationRenderer, $this->xResponse);
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
        // No pagination for only one page
        [, $aPages, ] = $this->paginator(1, 10, 0)->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(0, $aPages);

        [, $aPages, ] = $this->paginator(1, 10, 7)->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(0, $aPages);

        [, $aPages, ] = $this->paginator(1, 10, 10)->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(0, $aPages);

        $this->paginator(1, 10, 0)
            ->render(rq('Sample')->method(jq('#div')->val), 'wrapper');
        $this->assertCount(1, $this->xResponse->getCommands());

        $this->xResponse->clearCommands();
        $this->paginator(1, 10, 7)
            ->render(rq('Sample')->method(jq('#div')->val), 'wrapper');
        $this->assertCount(1, $this->xResponse->getCommands());

        $this->xResponse->clearCommands();
        $this->paginator(1, 10, 10)
            ->render(rq('Sample')->method(jq('#div')->val), 'wrapper');
        $this->assertCount(1, $this->xResponse->getCommands());
    }

    /**
     * @throws SetupException
     */
    public function testFirstPageWithNoPageNumber()
    {
        $xPaginator = $this->paginator(1, 10, 12);
        [, $aPages, ] = $xPaginator->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(2, $aPages);

        $sHtml = '<ul class="pagination">' .
            '<li class="disabled"><span>&laquo;</span></li>' .
            '<li class="active"><a role="link">1</a></li>' .
            '<li class="enabled" data-page="2"><a role="link">2</a></li>' .
            '<li class="enabled" data-page="2"><a role="link">&raquo;</a></li>' .
            '</ul>';
        $xPaginator->render(rq('Sample')->method('string', 26, true), 'wrapper');
        $aCommands = $this->xResponse->getCommands();

        $this->assertCount(2, $aCommands);
        $this->assertEquals('node.assign', $aCommands[0]['name']);
        $this->assertEquals('wrapper', $aCommands[0]['args']['id']);
        $this->assertEquals($sHtml, trim($aCommands[0]['args']['value']));

        $this->assertEquals('pg.paginate', $aCommands[1]['name']);
        $this->assertEquals('wrapper', $aCommands[1]['args']['id']);
        $this->assertEquals('func', $aCommands[1]['args']['func']['_type']);
        $this->assertEquals('Sample.method', $aCommands[1]['args']['func']['_name']);
        $this->assertCount(4, $aCommands[1]['args']['func']['args']);
        // Page number parameter
        $this->assertIsArray($aCommands[1]['args']['func']['args'][3]);
        $this->assertEquals('page', $aCommands[1]['args']['func']['args'][3]['_type']);
        $this->assertEquals('', $aCommands[1]['args']['func']['args'][3]['_name']);
    }

    /**
     * @throws SetupException
     */
    public function testLastPageWithNoPageNumber()
    {
        $xPaginator = $this->paginator(2, 10, 12);
        [, $aPages, ] = $xPaginator->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(2, $aPages);

        $sHtml = '<ul class="pagination">' .
            '<li class="enabled" data-page="1"><a role="link">&laquo;</a></li>' .
            '<li class="enabled" data-page="1"><a role="link">1</a></li>' .
            '<li class="active"><a role="link">2</a></li>' .
            '<li class="disabled"><span>&raquo;</span></li>' .
            '</ul>';
        $xPaginator->render(rq('Sample')->method('string', 26, true), 'wrapper');
        $aCommands = $this->xResponse->getCommands();

        $this->assertCount(2, $aCommands);
        $this->assertEquals('node.assign', $aCommands[0]['name']);
        $this->assertEquals('wrapper', $aCommands[0]['args']['id']);
        $this->assertEquals($sHtml, trim($aCommands[0]['args']['value']));

        $this->assertEquals('pg.paginate', $aCommands[1]['name']);
        $this->assertEquals('wrapper', $aCommands[1]['args']['id']);
        $this->assertEquals('func', $aCommands[1]['args']['func']['_type']);
        $this->assertEquals('Sample.method', $aCommands[1]['args']['func']['_name']);
        $this->assertCount(4, $aCommands[1]['args']['func']['args']);
    }

    /**
     * @throws SetupException
     */
    public function testMiddlePageWithNoPageNumber()
    {
        $xPaginator = $this->paginator(2, 10, 24);
        [, $aPages, ] = $xPaginator->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(3, $aPages);

        $sHtml = '<ul class="pagination">' .
            '<li class="enabled" data-page="1"><a role="link">&laquo;</a></li>' .
            '<li class="enabled" data-page="1"><a role="link">1</a></li>' .
            '<li class="active"><a role="link">2</a></li>' .
            '<li class="enabled" data-page="3"><a role="link">3</a></li>' .
            '<li class="enabled" data-page="3"><a role="link">&raquo;</a></li>' .
            '</ul>';
        $xPaginator->render(rq('Sample')->method('string', 26, true), 'wrapper');
        $aCommands = $this->xResponse->getCommands();

        $this->assertCount(2, $aCommands);
        $this->assertEquals('node.assign', $aCommands[0]['name']);
        $this->assertEquals('wrapper', $aCommands[0]['args']['id']);
        $this->assertEquals($sHtml, trim($aCommands[0]['args']['value']));

        $this->assertEquals('pg.paginate', $aCommands[1]['name']);
        $this->assertEquals('wrapper', $aCommands[1]['args']['id']);
        $this->assertEquals('func', $aCommands[1]['args']['func']['_type']);
        $this->assertEquals('Sample.method', $aCommands[1]['args']['func']['_name']);
        $this->assertCount(4, $aCommands[1]['args']['func']['args']);
    }

    /**
     * @throws SetupException
     */
    public function testPaginationWithPageNumber()
    {
        $xPaginator = $this->paginator(1, 10, 24);
        [, $aPages, ] = $xPaginator->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(3, $aPages);

        $sHtml = '<ul class="pagination">' .
            '<li class="disabled"><span>&laquo;</span></li>' .
            '<li class="active"><a role="link">1</a></li>' .
            '<li class="enabled" data-page="2"><a role="link">2</a></li>' .
            '<li class="enabled" data-page="3"><a role="link">3</a></li>' .
            '<li class="enabled" data-page="2"><a role="link">&raquo;</a></li>' .
            '</ul>';
        $xPaginator->render(rq('Sample')->method('string', je()->rd()->page(), 26, true), 'wrapper');
        $aCommands = $this->xResponse->getCommands();

        $this->assertCount(2, $aCommands);
        $this->assertEquals('node.assign', $aCommands[0]['name']);
        $this->assertEquals('wrapper', $aCommands[0]['args']['id']);
        $this->assertEquals($sHtml, trim($aCommands[0]['args']['value']));

        $this->assertEquals('pg.paginate', $aCommands[1]['name']);
        $this->assertEquals('wrapper', $aCommands[1]['args']['id']);
        $this->assertEquals('func', $aCommands[1]['args']['func']['_type']);
        $this->assertEquals('Sample.method', $aCommands[1]['args']['func']['_name']);
        $this->assertCount(4, $aCommands[1]['args']['func']['args']);
        // Page number parameter
        $this->assertIsArray($aCommands[1]['args']['func']['args'][1]);
        $this->assertEquals('page', $aCommands[1]['args']['func']['args'][1]['_type']);
        $this->assertEquals('', $aCommands[1]['args']['func']['args'][1]['_name']);
    }

    /**
     * @throws SetupException
     */
    public function testNextAndPrevTexts()
    {
        $xPaginator = $this->paginator(1, 10, 12)->setNextText('Next')->setPreviousText('Prev');
        [, $aPages, ] = $xPaginator->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(2, $aPages);

        $sHtml = '<ul class="pagination">' .
            '<li class="disabled"><span>Prev</span></li>' .
            '<li class="active"><a role="link">1</a></li>' .
            '<li class="enabled" data-page="2"><a role="link">2</a></li>' .
            '<li class="enabled" data-page="2"><a role="link">Next</a></li>' .
            '</ul>';
        $xPaginator->render(rq('Sample')->method('string', 26, true), 'wrapper');
        $aCommands = $this->xResponse->getCommands();

        $this->assertCount(2, $aCommands);
        $this->assertEquals('node.assign', $aCommands[0]['name']);
        $this->assertEquals('wrapper', $aCommands[0]['args']['id']);
        $this->assertEquals($sHtml, trim($aCommands[0]['args']['value']));

        $this->assertEquals('pg.paginate', $aCommands[1]['name']);
        $this->assertEquals('wrapper', $aCommands[1]['args']['id']);
        $this->assertEquals('func', $aCommands[1]['args']['func']['_type']);
        $this->assertEquals('Sample.method', $aCommands[1]['args']['func']['_name']);
        $this->assertCount(4, $aCommands[1]['args']['func']['args']);
    }

    /**
     * @throws SetupException
     */
    public function testMaxPagesStart()
    {
        $xPaginator = $this->paginator(2, 5, 48)
            ->setNextText('Next')->setPreviousText('Prev')->setMaxPages(5);
        [, $aPages, ] = $xPaginator->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(5, $aPages);

        $sHtml = '<ul class="pagination">' .
            '<li class="enabled" data-page="1"><a role="link">Prev</a></li>' .
            '<li class="enabled" data-page="1"><a role="link">1</a></li>' .
            '<li class="active"><a role="link">2</a></li>' .
            '<li class="enabled" data-page="3"><a role="link">3</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li class="enabled" data-page="10"><a role="link">10</a></li>' .
            '<li class="enabled" data-page="3"><a role="link">Next</a></li>' .
            '</ul>';
        $xPaginator->render(rq('Sample')->method('string', 26, true), 'wrapper');
        $aCommands = $this->xResponse->getCommands();

        $this->assertCount(2, $aCommands);
        $this->assertEquals('node.assign', $aCommands[0]['name']);
        $this->assertEquals('wrapper', $aCommands[0]['args']['id']);
        $this->assertEquals($sHtml, trim($aCommands[0]['args']['value']));

        $this->assertEquals('pg.paginate', $aCommands[1]['name']);
        $this->assertEquals('wrapper', $aCommands[1]['args']['id']);
        $this->assertEquals('func', $aCommands[1]['args']['func']['_type']);
        $this->assertEquals('Sample.method', $aCommands[1]['args']['func']['_name']);
        $this->assertCount(4, $aCommands[1]['args']['func']['args']);
    }

    /**
     * @throws SetupException
     */
    public function testMaxPagesMiddle()
    {
        $xPaginator = $this->paginator(6, 5, 48)
            ->setNextText('Next')->setPreviousText('Prev')->setMaxPages(5);
        [, $aPages, ] = $xPaginator->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(5, $aPages);

        $sHtml = '<ul class="pagination">' .
            '<li class="enabled" data-page="5"><a role="link">Prev</a></li>' .
            '<li class="enabled" data-page="1"><a role="link">1</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li class="active"><a role="link">6</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li class="enabled" data-page="10"><a role="link">10</a></li>' .
            '<li class="enabled" data-page="7"><a role="link">Next</a></li>' .
            '</ul>';
        $xPaginator->render(rq('Sample')->method('string', 26, true), 'wrapper');
        $aCommands = $this->xResponse->getCommands();

        $this->assertCount(2, $aCommands);
        $this->assertEquals('node.assign', $aCommands[0]['name']);
        $this->assertEquals('wrapper', $aCommands[0]['args']['id']);
        $this->assertEquals($sHtml, trim($aCommands[0]['args']['value']));

        $this->assertEquals('pg.paginate', $aCommands[1]['name']);
        $this->assertEquals('wrapper', $aCommands[1]['args']['id']);
        $this->assertEquals('func', $aCommands[1]['args']['func']['_type']);
        $this->assertEquals('Sample.method', $aCommands[1]['args']['func']['_name']);
        $this->assertCount(4, $aCommands[1]['args']['func']['args']);
    }

    /**
     * @throws SetupException
     */
    public function testMaxPagesSevenMiddle()
    {
        $xPaginator = $this->paginator(6, 5, 48)
            ->setNextText('Next')->setPreviousText('Prev')->setMaxPages(7);
        [, $aPages, ] = $xPaginator->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(7, $aPages);

        $sHtml = '<ul class="pagination">' .
            '<li class="enabled" data-page="5"><a role="link">Prev</a></li>' .
            '<li class="enabled" data-page="1"><a role="link">1</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li class="enabled" data-page="5"><a role="link">5</a></li>' .
            '<li class="active"><a role="link">6</a></li>' .
            '<li class="enabled" data-page="7"><a role="link">7</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li class="enabled" data-page="10"><a role="link">10</a></li>' .
            '<li class="enabled" data-page="7"><a role="link">Next</a></li>' .
            '</ul>';
        $xPaginator->render(rq('Sample')->method('string', 26, true), 'wrapper');
        $aCommands = $this->xResponse->getCommands();

        $this->assertCount(2, $aCommands);
        $this->assertEquals('node.assign', $aCommands[0]['name']);
        $this->assertEquals('wrapper', $aCommands[0]['args']['id']);
        $this->assertEquals($sHtml, trim($aCommands[0]['args']['value']));

        $this->assertEquals('pg.paginate', $aCommands[1]['name']);
        $this->assertEquals('wrapper', $aCommands[1]['args']['id']);
        $this->assertEquals('func', $aCommands[1]['args']['func']['_type']);
        $this->assertEquals('Sample.method', $aCommands[1]['args']['func']['_name']);
        $this->assertCount(4, $aCommands[1]['args']['func']['args']);
    }

    /**
     * @throws SetupException
     */
    public function testMaxPagesEnd()
    {
        $xPaginator = $this->paginator(10, 5, 48)
            ->setNextText('Next')->setPreviousText('Prev')->setMaxPages(5);
        [, $aPages, ] = $xPaginator->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(5, $aPages);

        $sHtml = '<ul class="pagination">' .
            '<li class="enabled" data-page="9"><a role="link">Prev</a></li>' .
            '<li class="enabled" data-page="1"><a role="link">1</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li class="enabled" data-page="8"><a role="link">8</a></li>' .
            '<li class="enabled" data-page="9"><a role="link">9</a></li>' .
            '<li class="active"><a role="link">10</a></li>' .
            '<li class="disabled"><span>Next</span></li>' .
            '</ul>';
        $xPaginator->render(rq('Sample')->method('string', 26, true), 'wrapper');
        $aCommands = $this->xResponse->getCommands();

        $this->assertCount(2, $aCommands);
        $this->assertEquals('node.assign', $aCommands[0]['name']);
        $this->assertEquals('wrapper', $aCommands[0]['args']['id']);
        $this->assertEquals($sHtml, trim($aCommands[0]['args']['value']));

        $this->assertEquals('pg.paginate', $aCommands[1]['name']);
        $this->assertEquals('wrapper', $aCommands[1]['args']['id']);
        $this->assertEquals('func', $aCommands[1]['args']['func']['_type']);
        $this->assertEquals('Sample.method', $aCommands[1]['args']['func']['_name']);
        $this->assertCount(4, $aCommands[1]['args']['func']['args']);
    }

    /**
     * @throws SetupException
     */
    public function testMaxPagesBeforeEnd()
    {
        $xPaginator = $this->paginator(9, 5, 48)
            ->setNextText('Next')->setPreviousText('Prev')->setMaxPages(5);
        [, $aPages, ] = $xPaginator->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(5, $aPages);

        $sHtml = '<ul class="pagination">' .
            '<li class="enabled" data-page="8"><a role="link">Prev</a></li>' .
            '<li class="enabled" data-page="1"><a role="link">1</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li class="enabled" data-page="8"><a role="link">8</a></li>' .
            '<li class="active"><a role="link">9</a></li>' .
            '<li class="enabled" data-page="10"><a role="link">10</a></li>' .
            '<li class="enabled" data-page="10"><a role="link">Next</a></li>' .
            '</ul>';
        $xPaginator->render(rq('Sample')->method('string', 26, true), 'wrapper');
        $aCommands = $this->xResponse->getCommands();

        $this->assertCount(2, $aCommands);
        $this->assertEquals('node.assign', $aCommands[0]['name']);
        $this->assertEquals('wrapper', $aCommands[0]['args']['id']);
        $this->assertEquals($sHtml, trim($aCommands[0]['args']['value']));

        $this->assertEquals('pg.paginate', $aCommands[1]['name']);
        $this->assertEquals('wrapper', $aCommands[1]['args']['id']);
        $this->assertEquals('func', $aCommands[1]['args']['func']['_type']);
        $this->assertEquals('Sample.method', $aCommands[1]['args']['func']['_name']);
        $this->assertCount(4, $aCommands[1]['args']['func']['args']);
    }

    /**
     * @throws SetupException
     */
    public function testMaxPagesMin()
    {
        $xPaginator = $this->paginator(9, 5, 48)
            ->setNextText('Next')->setPreviousText('Prev')->setMaxPages(3);
        [, $aPages, ] = $xPaginator->pages();
        $this->assertIsArray($aPages);
        $this->assertCount(5, $aPages);

        $sHtml = '<ul class="pagination">' .
            '<li class="enabled" data-page="8"><a role="link">Prev</a></li>' .
            '<li class="enabled" data-page="1"><a role="link">1</a></li>' .
            '<li class="disabled"><span>...</span></li>' .
            '<li class="enabled" data-page="8"><a role="link">8</a></li>' .
            '<li class="active"><a role="link">9</a></li>' .
            '<li class="enabled" data-page="10"><a role="link">10</a></li>' .
            '<li class="enabled" data-page="10"><a role="link">Next</a></li>' .
            '</ul>';
        $xPaginator->render(rq('Sample')->method('string', 26, true), 'wrapper');
        $aCommands = $this->xResponse->getCommands();

        $this->assertCount(2, $aCommands);
        $this->assertEquals('node.assign', $aCommands[0]['name']);
        $this->assertEquals('wrapper', $aCommands[0]['args']['id']);
        $this->assertEquals($sHtml, trim($aCommands[0]['args']['value']));

        $this->assertEquals('pg.paginate', $aCommands[1]['name']);
        $this->assertEquals('wrapper', $aCommands[1]['args']['id']);
        $this->assertEquals('func', $aCommands[1]['args']['func']['_type']);
        $this->assertEquals('Sample.method', $aCommands[1]['args']['func']['_name']);
        $this->assertCount(4, $aCommands[1]['args']['func']['args']);
    }
}
