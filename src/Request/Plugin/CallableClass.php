<?php

/**
 * CallableClass.php - Jaxon callable class plugin
 *
 * This class registers user defined callable classes, generates client side javascript code,
 * and calls their methods on user request
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

namespace Jaxon\Request\Plugin;

use Jaxon\Jaxon;
use Jaxon\Plugin\Request as RequestPlugin;
use Jaxon\Request\Support\CallableRepository;

class CallableClass extends RequestPlugin
{
    use \Jaxon\Utils\Traits\Manager;
    use \Jaxon\Utils\Traits\Validator;
    use \Jaxon\Utils\Traits\Translator;

    /**
     * The callable repository
     *
     * @var CallableRepository
     */
    protected $repository = null;

    /**
     * The value of the class parameter of the incoming Jaxon request
     *
     * @var string
     */
    protected $sRequestedClass = null;

    /**
     * The value of the method parameter of the incoming Jaxon request
     *
     * @var string
     */
    protected $sRequestedMethod = null;

    /**
     * The class constructor
     *
     * @param CallableRepository        $repository
     */
    public function __construct(CallableRepository $repository)
    {
        $this->repository = $repository;

        if(!empty($_GET['jxncls']))
        {
            $this->sRequestedClass = $_GET['jxncls'];
        }
        if(!empty($_GET['jxnmthd']))
        {
            $this->sRequestedMethod = $_GET['jxnmthd'];
        }
        if(!empty($_POST['jxncls']))
        {
            $this->sRequestedClass = $_POST['jxncls'];
        }
        if(!empty($_POST['jxnmthd']))
        {
            $this->sRequestedMethod = $_POST['jxnmthd'];
        }
    }

    /**
     * Return the name of this plugin
     *
     * @return string
     */
    public function getName()
    {
        return Jaxon::CALLABLE_CLASS;
    }

    /**
     * Register a callable class
     *
     * @param string        $sType          The type of request handler being registered
     * @param string        $sClassName     The name of the class being registered
     * @param array|string  $aOptions       The associated options
     *
     * @return boolean
     */
    public function register($sType, $sClassName, $aOptions)
    {
        if($sType != $this->getName())
        {
            return false;
        }

        if(!is_string($sClassName))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }
        if(is_string($aOptions))
        {
            $aOptions = ['include' => $aOptions];
        }
        if(!is_array($aOptions))
        {
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid-declaration'));
        }

        $this->repository->addClass($sClassName, $aOptions);

        return true;
    }

    /**
     * Generate a hash for the registered callable objects
     *
     * @return string
     */
    public function generateHash()
    {
        return $this->repository->generateHash();
    }

    /**
     * Generate client side javascript code for the registered callable objects
     *
     * @return string
     */
    public function getScript()
    {
        return $this->repository->getScript();
    }

    /**
     * Check if this plugin can process the incoming Jaxon request
     *
     * @return boolean
     */
    public function canProcessRequest()
    {
        // Check the validity of the class name
        if($this->sRequestedClass !== null && !$this->validateClass($this->sRequestedClass))
        {
            $this->sRequestedClass = null;
            $this->sRequestedMethod = null;
        }
        // Check the validity of the method name
        if($this->sRequestedMethod !== null && !$this->validateMethod($this->sRequestedMethod))
        {
            $this->sRequestedClass = null;
            $this->sRequestedMethod = null;
        }
        return ($this->sRequestedClass !== null && $this->sRequestedMethod !== null);
    }

    /**
     * Process the incoming Jaxon request
     *
     * @return boolean
     */
    public function processRequest()
    {
        if(!$this->canProcessRequest())
        {
            return false;
        }

        $aArgs = $this->getRequestManager()->process();

        // Find the requested method
        $xCallableObject = $this->repository->getCallableObject($this->sRequestedClass);
        if(!$xCallableObject || !$xCallableObject->hasMethod($this->sRequestedMethod))
        {
            // Unable to find the requested object or method
            throw new \Jaxon\Exception\Error($this->trans('errors.objects.invalid',
                ['class' => $this->sRequestedClass, 'method' => $this->sRequestedMethod]));
        }

        // Call the requested method
        $xCallableObject->call($this->sRequestedMethod, $aArgs);
        return true;
    }
}
