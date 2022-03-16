<?php

/**
 * FunctionPlugin.php - Jaxon user function plugin
 *
 * This class registers user defined functions, generates client side javascript code,
 * and calls them on user request
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Plugin\CallableFunction;

use Jaxon\Jaxon;
use Jaxon\Container\Container;
use Jaxon\Plugin\RequestPlugin;
use Jaxon\Request\Handler\RequestHandler;
use Jaxon\Request\Target;
use Jaxon\Request\Validator;
use Jaxon\Response\ResponseManager;
use Jaxon\Utils\Template\Engine as TemplateEngine;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Exception\RequestException;
use Jaxon\Exception\SetupException;

use function implode;
use function is_array;
use function is_string;
use function md5;
use function trim;

class FunctionPlugin extends RequestPlugin
{
    /**
     * @var Container
     */
    private $di;

    /**
     * The request handler
     *
     * @var RequestHandler
     */
    protected $xRequestHandler;

    /**
     * The response manager
     *
     * @var ResponseManager
     */
    protected $xResponseManager;

    /**
     * The request data validator
     *
     * @var Validator
     */
    protected $xValidator;

    /**
     * @var TemplateEngine
     */
    protected $xTemplateEngine;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * The registered user functions names
     *
     * @var array
     */
    protected $aFunctions = [];

    /**
     * The name of the function that is being requested (during the request processing phase)
     *
     * @var string
     */
    protected $sRequestedFunction = null;

    /**
     * The constructor
     *
     * @param Container  $di
     * @param RequestHandler  $xRequestHandler
     * @param ResponseManager  $xResponseManager
     * @param TemplateEngine  $xTemplateEngine
     * @param Translator  $xTranslator
     * @param Validator  $xValidator
     */
    public function __construct(Container $di, RequestHandler $xRequestHandler, ResponseManager $xResponseManager,
        TemplateEngine $xTemplateEngine, Translator $xTranslator, Validator $xValidator)
    {
        $this->di = $di;
        $this->xRequestHandler = $xRequestHandler;
        $this->xResponseManager = $xResponseManager;
        $this->xTemplateEngine = $xTemplateEngine;
        $this->xTranslator = $xTranslator;
        $this->xValidator = $xValidator;

        if(isset($_GET['jxnfun']))
        {
            $this->sRequestedFunction = $_GET['jxnfun'];
        }
        if(isset($_POST['jxnfun']))
        {
            $this->sRequestedFunction = $_POST['jxnfun'];
        }
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return Jaxon::CALLABLE_FUNCTION;
    }

    /**
     * @inheritDoc
     */
    public function getTarget(): ?Target
    {
        if(!$this->sRequestedFunction)
        {
            return null;
        }
        return Target::makeFunction($this->sRequestedFunction);
    }

    /**
     * @inheritDoc
     * @throws SetupException
     */
    public function checkOptions(string $sCallable, $xOptions): array
    {
        if(!$this->xValidator->validateFunction(trim($sCallable)))
        {
            throw new SetupException($this->xTranslator->trans('errors.objects.invalid-declaration'));
        }
        if(is_string($xOptions))
        {
            $xOptions = ['include' => $xOptions];
        }
        elseif(!is_array($xOptions))
        {
            throw new SetupException($this->xTranslator->trans('errors.objects.invalid-declaration'));
        }
        return $xOptions;
    }

    /**
     * Register a user defined function
     *
     * @param string $sType    The type of request handler being registered
     * @param string $sCallable    The name of the function being registered
     * @param array $aOptions    The associated options
     *
     * @return bool
     */
    public function register(string $sType, string $sCallable, array $aOptions): bool
    {
        $sFunctionName = trim($sCallable);
        // Check if an alias is defined
        $sJsFunction = $sFunctionName;
        if(isset($aOptions['alias']))
        {
            $sJsFunction = (string)$aOptions['alias'];
            unset($aOptions['alias']);
        }
        $this->aFunctions[$sFunctionName] = $sFunctionName;
        $this->di->registerCallableFunction($sFunctionName, $sJsFunction, $aOptions);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        return md5(implode('', $this->aFunctions));
    }

    /**
     * Generate the javascript function stub that is sent to the browser on initial page load
     *
     * @param CallableFunction $xFunction
     *
     * @return string
     */
    private function getCallableScript(CallableFunction $xFunction): string
    {
        return $this->xTemplateEngine->render('jaxon::support/function.js', [
            'sName' => $xFunction->getName(),
            'sJsName' => $xFunction->getJsName(),
            'aOptions' => $xFunction->getOptions(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getScript(): string
    {
        $code = '';
        foreach($this->aFunctions as $sName)
        {
            $xFunction = $this->di->getCallableFunction($sName);
            $code .= $this->getCallableScript($xFunction);
        }
        return $code;
    }

    /**
     * @inheritDoc
     */
    public function canProcessRequest(): bool
    {
        // Check the validity of the function name
        if(($this->sRequestedFunction) && !$this->xValidator->validateFunction($this->sRequestedFunction))
        {
            $this->sRequestedFunction = null;
        }
        return ($this->sRequestedFunction != null);
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function processRequest(): bool
    {
        // Security check: make sure the requested function was registered.
        if(!isset($this->aFunctions[$this->sRequestedFunction]))
        {
            // Unable to find the requested function
            throw new RequestException($this->xTranslator->trans('errors.functions.invalid',
                ['name' => $this->sRequestedFunction]));
        }

        $xFunction = $this->di->getCallableFunction($this->sRequestedFunction);
        $aArgs = $this->xRequestHandler->processArguments();
        $xResponse = $xFunction->call($aArgs);
        if(($xResponse))
        {
            $this->xResponseManager->append($xResponse);
        }
        return true;
    }
}
