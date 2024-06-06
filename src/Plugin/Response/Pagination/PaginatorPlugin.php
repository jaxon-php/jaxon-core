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
use Jaxon\JsCall\JsExpr;
use Jaxon\JsCall\Js\Func;
use Jaxon\Plugin\ResponsePlugin;
use Jaxon\Response\Response;
use Jaxon\Response\ComponentResponse;

use function array_map;
use function array_pop;
use function array_shift;
use function count;
use function is_a;
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
     * @inheritDoc
     */
    public function getCss(): string
    {
        return '
<style>
  .pagination li a {
    cursor: pointer;
  }
</style>
';
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
     * Show the pagination links
     *
     * @param Func $xFunc
     * @param string $sWrapperId
     * @param string $sHtml
     *
     * @return void
     */
    private function showLinks(Func $xFunc, string $sWrapperId, string $sHtml)
    {
        if(is_a($this->response(), ComponentResponse::class))
        {
            /** @var ComponentResponse */
            $xResponse = $this->response();
            $xResponse->html($sHtml);
            if($sHtml !== '')
            {
                // Set click handlers on the pagination links
                $this->addCommand('pg.paginate', [
                    'func' => $xFunc->withPage()->jsonSerialize(),
                ]);
            }
            return;
        }
        if(is_a($this->response(), Response::class))
        {
            /** @var Response */
            $xResponse = $this->response();
            $xResponse->html($sWrapperId, $sHtml);
            if($sHtml !== '')
            {
                // Set click handlers on the pagination links
                $this->addCommand('pg.paginate', [
                    'id' => $sWrapperId,
                    'func' => $xFunc->withPage()->jsonSerialize(),
                ]);
            }
        }
    }

    /**
     * @param array<Page> $aPages
     * @param JsExpr $xCall
     * @param string $sWrapperId
     *
     * @return void
     */
    public function render(array $aPages, JsExpr $xCall, string $sWrapperId)
    {
        if(($xFunc = $xCall->func()) === null)
        {
            return;
        }

        $xStore = null;
        if(count($aPages) > 0)
        {
            $aPages = array_map(function($xPage) {
                return $this->xRenderer->render('pagination::links/' . $xPage->sType, [
                    'page' => $xPage->nNumber,
                    'text' => $xPage->sText,
                ]);
            }, $aPages);
            $aPrevPage = array_shift($aPages); // The first entry in the array
            $aNextPage = array_pop($aPages); // The last entry in the array
            $xStore = $this->xRenderer->render('pagination::wrapper', [
                'links' => $aPages,
                'prev' => $aPrevPage,
                'next' => $aNextPage,
            ]);
        }
        $this->showLinks($xFunc, trim($sWrapperId), !$xStore ? '' : trim($xStore->__toString()));
    }
}
