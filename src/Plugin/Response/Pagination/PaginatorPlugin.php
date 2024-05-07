<?php

/**
 * PaginatorPlugin.php - The Jaxon Paginator plugin
 *
 * @package jaxon-core
 * @copyright 2024 Thierry Feuzeu
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Response\Pagination;

use Jaxon\App\View\ViewRenderer;
use Jaxon\App\View\Store;
use Jaxon\Plugin\ResponsePlugin;
use Jaxon\Request\Call\Call;
use Jaxon\Request\Call\Parameter;

use function array_map;
use function array_pop;
use function array_shift;
use function count;
use function trim;

/**
 * Usage
 *
 * Step 1: Create a wrapper for the pagination.
 *
 * $html = $this->render($pageTemplate, [
 *     // ...
 *     'pagination' => $this->response->pg->wrapper($wrapperId),
 * ]);
 *
 * Step 2: Render the pagination into the wrapper.
 *
 * $this->response->pg->render($this->rq()->page(), $pageNumber, $perPage, $total);
 * // Or, using the response shortcut
 * $this->response->paginate($this->rq()->page(), $pageNumber, $perPage, $total);
 * // Or, in a class that inherits from CallableClass
 * $this->paginate($this->rq()->page(), $pageNumber, $perPage, $total);
 */
class PaginatorPlugin extends ResponsePlugin
{
    /**
     * @const The plugin name
     */
    const NAME = 'pg';

    /**
     * @var ViewRenderer
     */
    protected $xRenderer;

    /**
     * @var string
     */
    protected $sWrapperId = '';

    /**
     * The constructor.
     *
     * @param ViewRenderer $xRenderer
     */
    public function __construct(ViewRenderer $xRenderer)
    {
        $this->xRenderer = $xRenderer;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        // Use the version number as hash
        return '5.0.0';
    }

    /**
     * Get the pagination wrapper HTML
     *
     * @param string $sWrapperId        The pagination wrapper id
     *
     * @return string
     */
    public function wrapper(string $sWrapperId): string
    {
        $this->sWrapperId = trim($sWrapperId);
        return $this->sWrapperId;
    }

    /**
     * @param array<Page> $aPages
     *
     * @return null|Store
     */
    private function _render(array $aPages): ?Store
    {
        $aPages = array_map(function($xPage) {
            return $this->xRenderer->render('pagination::links/' . $xPage->sType, [
                'page' => $xPage->nNumber,
                'text' => $xPage->sText,
            ]);
        }, $aPages);
        $aPrevPage = array_shift($aPages); // The first entry in the array
        $aNextPage = array_pop($aPages); // The last entry in the array

        return $this->xRenderer->render('pagination::wrapper', [
            'links' => $aPages,
            'prev' => $aPrevPage,
            'next' => $aNextPage,
        ]);
    }

    /**
     * Render an HTML pagination control.
     *
     * @param Call $xCall
     * @param int $nCurrentPage     The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     *
     * @return void
     */
    public function render(Call $xCall, int $nCurrentPage, int $nItemsPerPage, int $nTotalItems)
    {
        $xPaginator = new Paginator($nCurrentPage, $nItemsPerPage, $nTotalItems);
        $aPages = $xPaginator->pages();
        if(count($aPages) === 0)
        {
            return;
        }
        $xStore = $this->_render($aPages);
        if(!$xStore)
        {
            return;
        }

        // Append the page number to the parameter list, if not yet given.
        if(!$xCall->hasPageNumber())
        {
            $xCall->addParameter(Parameter::PAGE_NUMBER, 0);
        }
        // Show the pagination links
        $this->response()->html($this->sWrapperId, $xStore->__toString());
        // Set click handlers on the pagination links
        $this->addCommand('pg.paginate', [
            'id' => $this->sWrapperId,
            'call' => $xCall->toArray(),
            // 'pages' => array_map(function(Page $xPage) {
            //     return ['type' => $xPage->sType, 'number' => $xPage->nNumber];
            // }, $aPages),
        ]);
    }
}
