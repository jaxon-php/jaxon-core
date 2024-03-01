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
 * $paginator = $this->response->pg->create($pageNumber, $perPage, $total);
 * // Or, using the response shortcut
 * $paginator = $this->response->paginator($pageNumber, $perPage, $total);
 * // Or, in a class that inherits from CallableClass
 * $paginator = $this->paginator($pageNumber, $perPage, $total);
 * $html = $this->render($pageTemplate, [
 *     // ...
 *     'pagination' => $paginator->wrapper($wrapperId),
 * ]);
 * $this->response->html($pageWrapper, $html);
 * $this->response->pg->render($paginator, $this->rq()->page());
 * // Or, using the response shortcut
 * $this->response->paginate($paginator, $this->rq()->page());
 * // Or, in a class that inherits from CallableClass
 * $this->paginate($paginator, $this->rq()->page());
 * 
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
     * Create a paginator
     *
     * @param int $nCurrentPage     The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     *
     * @return Paginator
     */
    public function create(int $nCurrentPage, int $nItemsPerPage, int $nTotalItems): Paginator
    {
        return new Paginator($nCurrentPage, $nItemsPerPage, $nTotalItems);
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
     * @param Paginator $xPaginator
     * @param Call $xCall
     * @param string $sWrapperId
     *
     * @return void
     */
    public function render(Paginator $xPaginator, Call $xCall, string $sWrapperId = '')
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
        $sWrapperId = trim($sWrapperId) ?? $xPaginator->wrapperId();
        $this->response()->html($sWrapperId, $xStore->__toString());
        // Set click handlers on the pagination links
        $this->addCommand('paginate', [
            'id' => $sWrapperId,
            'call' => $xCall->toArray(),
            'pages' => array_map(function(Page $xPage) {
                return ['type' => $xPage->sType, 'number' => $xPage->nNumber];
            }, $aPages),
        ]);
    }
}
