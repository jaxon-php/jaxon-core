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
     * Set the paginator to be rendered.
     *
     * @var Paginator
     */
    protected $xPaginator = null;

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
     * Set the paginator to be rendered.
     *
     * @param Paginator         $xPaginator         The paginator to be rendered
     *
     * @return void
     */
    public function setPaginator(\Jaxon\Utils\Pagination\Paginator $xPaginator)
    {
        $this->xPaginator = $xPaginator;
    }

    /**
     * Render the previous link.
     *
     * @return string
     */
    protected function getPrevLink()
    {
        if(!($sCall = $this->xPaginator->getPrevCall()))
        {
            return '';
        }
        return $this->xTemplate->render('pagination::links/prev',
            ['call' => $sCall, 'text' => $this->xPaginator->getPreviousText()]);
    }

    /**
     * Render the next link.
     *
     * @return string
     */
    protected function getNextLink()
    {
        if(!($sCall = $this->xPaginator->getNextCall()))
        {
            return '';
        }
        return $this->xTemplate->render('pagination::links/next',
            ['call' => $sCall, 'text' => $this->xPaginator->getNextText()]);
    }

    /**
     * Render the pagination links.
     *
     * @return string
     */
    protected function getLinks()
    {
        $sLinks = '';
        foreach($this->xPaginator->getPages() as $page)
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
     * @return string
     */
    public function render()
    {
        return $this->xTemplate->render('pagination::wrapper', [
            'links' => $this->getLinks(),
            'prev' => $this->getPrevLink(),
            'next' => $this->getNextLink(),
        ]);
    }
}
