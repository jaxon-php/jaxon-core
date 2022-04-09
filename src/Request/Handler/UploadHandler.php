<?php

/**
 * UploadHandler.php - This class implements file upload with Ajax.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2017 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Handler;

use Jaxon\Di\Container;
use Jaxon\Exception\RequestException;
use Jaxon\Response\Manager\ResponseManager;
use Jaxon\Response\UploadResponse;
use Jaxon\Utils\Translation\Translator;
use Psr\Http\Message\ServerRequestInterface;

use Closure;
use Exception;

use function count;
use function trim;

class UploadHandler
{
    /**
     * DI container
     *
     * @var Container
     */
    protected $di;

    /**
     * The response manager
     *
     * @var ResponseManager
     */
    protected $xResponseManager;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * The uploaded files copied in the user dir
     *
     * @var array
     */
    protected $aUserFiles = [];

    /**
     * The name of file containing upload data
     *
     * @var string
     */
    protected $sTempFile = '';

    /**
     * Is the current request an HTTP upload
     *
     * @var bool
     */
    protected $bIsAjaxRequest = true;

    /**
     * The constructor
     *
     * @param Container $di
     * @param ResponseManager $xResponseManager
     * @param Translator $xTranslator
     */
    public function __construct(Container $di, ResponseManager $xResponseManager, Translator $xTranslator)
    {
        $this->di = $di;
        $this->xResponseManager = $xResponseManager;
        $this->xTranslator = $xTranslator;
    }

    /**
     * Set the uploaded file name sanitizer
     *
     * @param Closure $cSanitizer    The closure
     *
     * @return void
     */
    public function sanitizer(Closure $cSanitizer)
    {
        $this->di->getUploadManager()->setNameSanitizer($cSanitizer);
    }

    /**
     * Get the uploaded files
     *
     * @return array
     */
    public function files(): array
    {
        return $this->aUserFiles;
    }

    /**
     * Inform this plugin that other plugin can process the current request
     *
     * @return void
     */
    public function isHttpUpload()
    {
        $this->bIsAjaxRequest = false;
    }

    /**
     * Check if the current request contains uploaded files
     *
     * @param ServerRequestInterface $xRequest
     *
     * @return bool
     */
    public function canProcessRequest(ServerRequestInterface $xRequest): bool
    {
        if(count($xRequest->getUploadedFiles()) > 0)
        {
            return true;
        }
        $aBody = $xRequest->getParsedBody();
        if(is_array($aBody))
        {
            return isset($aBody['jxnupl']);
        }
        $aParams = $xRequest->getQueryParams();
        return isset($aParams['jxnupl']);
    }

    /**
     * Read the upload temp file name from the HTTP request
     *
     * @param ServerRequestInterface $xRequest
     *
     * @return bool
     */
    private function setTempFile(ServerRequestInterface $xRequest): bool
    {
        $aBody = $xRequest->getParsedBody();
        if(is_array($aBody))
        {
            $this->sTempFile = trim($aBody['jxnupl'] ?? '');
            return $this->sTempFile !== '';
        }
        $aParams = $xRequest->getQueryParams();
        $this->sTempFile = trim($aParams['jxnupl'] ?? '');
        return $this->sTempFile !== '';
    }

    /**
     * Process the uploaded files in the HTTP request
     *
     * @param ServerRequestInterface $xRequest
     *
     * @return bool
     * @throws RequestException
     */
    public function processRequest(ServerRequestInterface $xRequest): bool
    {
        $xUploadManager = $this->di->getUploadManager();
        if($this->setTempFile($xRequest))
        {
            // Ajax request following a normal HTTP upload.
            // Copy the previously uploaded files' location from the temp file.
            $this->aUserFiles = $xUploadManager->readFromTempFile($this->sTempFile);
            return true;
        }

        if($this->bIsAjaxRequest)
        {
            // Ajax request with upload.
            // Copy the uploaded files from the HTTP request.
            $this->aUserFiles = $xUploadManager->readFromHttpData($xRequest);
            return true;
        }

        // For HTTP requests, save the files' location to a temp file,
        // and return a response with a reference to this temp file.
        try
        {
            // Copy the uploaded files from the HTTP request, and create the temp file.
            $this->aUserFiles = $xUploadManager->readFromHttpData($xRequest);
            $sTempFile = $xUploadManager->saveToTempFile($this->aUserFiles);
            $this->xResponseManager->append(new UploadResponse($this->di->getPsr17Factory(), $sTempFile));
        }
        catch(Exception $e)
        {
            $this->xResponseManager->append(new UploadResponse($this->di->getPsr17Factory(), '', $e->getMessage()));
        }
        return true;
    }
}
