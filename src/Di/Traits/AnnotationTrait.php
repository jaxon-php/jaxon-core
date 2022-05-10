<?php

namespace Jaxon\Di\Traits;

use Jaxon\Annotations\AnnotationReader;
use Jaxon\App\Config\ConfigEventManager;
use Jaxon\App\Config\ConfigListenerInterface;
use Jaxon\Plugin\AnnotationReaderInterface;
use Jaxon\Utils\Config\Config;

use function class_exists;

trait AnnotationTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerAnnotations()
    {
        // By default, register a fake annotation reader.
        $this->set(AnnotationReaderInterface::class, function() {
            return new class implements AnnotationReaderInterface
            {
                public function getAttributes(string $sClass, array $aMethods, array $aProperties): array
                {
                    return [false, [], []];
                }
            };
        });

        if(class_exists(AnnotationReader::class))
        {
            $sEventListenerKey = AnnotationReader::class . '\\ConfigListener';
            // The annotation package is installed, register the real annotation reader,
            // but only if the feature is activated in the config.
            $this->set($sEventListenerKey, function() {
                return new class implements ConfigListenerInterface
                {
                    public function onChanges(Config $xConfig)
                    {
                        if($xConfig->getOption('core.annotations.on'))
                        {
                            AnnotationReader::register(jaxon()->di());
                        }
                    }

                    public function onChange(Config $xConfig, string $sName)
                    {
                        if($sName === 'core.annotations.on' && $xConfig->getOption('core.annotations.on'))
                        {
                            AnnotationReader::register(jaxon()->di());
                        }
                    }
                };
            });

            // Register the event listener
            $xEventManager = $this->g(ConfigEventManager::class);
            $xEventManager->addListener($sEventListenerKey);
        }
    }
}
