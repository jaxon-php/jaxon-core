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
use Jaxon\Plugin\AbstractResponsePlugin;

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
 *     ->render($this->rq()->page(), $wrapperId);
 *
 * // Or, using the response shortcut
 * $this->response->paginator($pageNumber, $perPage, $total)
 *     ->render($this->rq()->page(), $wrapperId);
 *
 * // In a class that inherits from CallableClass
 * $this->paginator($pageNumber, $perPage, $total)
 *     ->render($this->rq()->page(), $wrapperId);
 *
 * // In a class that inherits from Component (no need for a wrapper id)
 * $this->paginator($pageNumber, $perPage, $total)
 *     ->render($this->rq()->page());
 */
class PaginatorPlugin extends AbstractResponsePlugin
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
     * Get the view renderer
     *
     * @return ViewRenderer
     */
    public function renderer(): ViewRenderer
    {
        return $this->xRenderer;
    }

    /**
     * Create a paginator
     *
     * @param int $nPageNumber     The current page number
     * @param int $nItemsPerPage    The number of items per page
     * @param int $nTotalItems      The total number of items
     *
     * @return Paginator
     */
    public function paginator(int $nPageNumber, int $nItemsPerPage, int $nTotalItems): Paginator
    {
        return new Paginator($this, $nPageNumber, $nItemsPerPage, $nTotalItems);
    }
}
