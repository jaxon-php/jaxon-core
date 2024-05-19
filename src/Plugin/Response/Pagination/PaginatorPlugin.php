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
use Jaxon\Js\Call;
use Jaxon\Js\Parameter;
use Jaxon\Plugin\ResponsePlugin;

use function array_map;
use function array_pop;
use function array_shift;
use function count;
use function trim;

/**
 * Usage
 *
 * Step 1: Render a template containing a wrapper for the pagination.
 *
 * $html = $this->render($pageTemplate, [
 *     // ...
 * ]);
 *
 * Step 2: Create a paginator and render the pagination into the wrapper.
 *
 * $this->response->pg->paginator($pageNumber, $perPage, $total)
 *     ->paginate($this->rq()->page(), $wrapperId);
 * // Or, using the response shortcut
 * $this->response->paginator($pageNumber, $perPage, $total)
 *     ->paginate($this->rq()->page(), $wrapperId);
 * // Or, in a class that inherits from CallableClass
 * $this->paginator($pageNumber, $perPage, $total)
 *     ->paginate($this->rq()->page(), $wrapperId);
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
     * Create a paginator
     *
     * @param int $nCurrentPage     The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     *
     * @return Paginator
     */
    public function paginator(int $nCurrentPage, int $nItemsPerPage, int $nTotalItems): Paginator
    {
        return new Paginator($this, $nCurrentPage, $nItemsPerPage, $nTotalItems);
    }

    /**
     * Render an HTML pagination control.
     *
     * @param Paginator $xPaginator
     * @param Call $xCall
     * @param string $sWrapperId
     *
     * @return void
     */
    public function render(Paginator $xPaginator, Call $xCall, string $sWrapperId)
    {
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
        $sWrapperId = trim($sWrapperId);
        $this->response()->html($sWrapperId, $xStore->__toString());
        // Set click handlers on the pagination links
        $this->addCommand('pg.paginate', [
            'id' => $sWrapperId,
            'func' => $xCall->toArray(),
        ]);
    }
}
