<?php

/**
 * Renderer.php - Paginator renderer
 *
 * Render pagination links.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Pagination;

use Jaxon\Utils\Template\Template;

class Renderer
{
    /**
     * The template manager.
     *
     * Will be used to render HTML code for links.
     *
     * @var Template
     */
    protected $xTemplate = null;

    /**
     * The class contructor
     *
     * @param Template          $xTemplate
     */
    public function __construct(Template $xTemplate)
    {
        $this->xTemplate = $xTemplate;
    }

    /**
     * Render the previous link.
     *
     * @param Paginator         $xPaginator         The paginator to be rendered
     *
     * @return string
     */
    protected function getPrevLink($xPaginator)
    {
        if(!($sCall = $xPaginator->getPrevCall()))
        {
            return '';
        }
        return $this->xTemplate->render('pagination::links/prev',
            ['call' => $sCall, 'text' => $xPaginator->getPreviousText()]);
    }

    /**
     * Render the next link.
     *
     * @param Paginator         $xPaginator         The paginator to be rendered
     *
     * @return string
     */
    protected function getNextLink($xPaginator)
    {
        if(!($sCall = $xPaginator->getNextCall()))
        {
            return '';
        }
        return $this->xTemplate->render('pagination::links/next',
            ['call' => $sCall, 'text' => $xPaginator->getNextText()]);
    }

    /**
     * Render the pagination links.
     *
     * @param Paginator         $xPaginator         The paginator to be rendered
     *
     * @return string
     */
    protected function getLinks($xPaginator)
    {
        $sLinks = '';
        foreach($xPaginator->getPages() as $page)
        {
            if($page['call'])
            {
                $sTemplate = ($page['isCurrent'] ? 'pagination::links/current' : 'pagination::links/enabled');
                $sLinks .= $this->xTemplate->render($sTemplate, ['call' => $page['call'], 'text' => $page['num']]);
            }
            else
            {
                $sLinks .= $this->xTemplate->render('pagination::links/disabled', ['text' => $page['num']]);
            }
        }
        return $sLinks;
    }

    /**
     * Render an HTML pagination control.
     *
     * @param Paginator         $xPaginator         The paginator to be rendered
     *
     * @return string
     */
    public function render(Paginator $xPaginator)
    {
        $xPaginator = $xPaginator;
        return $this->xTemplate->render('pagination::wrapper', [
            'links' => $this->getLinks($xPaginator),
            'prev' => $this->getPrevLink($xPaginator),
            'next' => $this->getNextLink($xPaginator),
        ]);
    }
}
