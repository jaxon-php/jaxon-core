<?php

namespace Jaxon\Script\Action;

use function array_map;

class Func extends TypedValue
{
    /**
     * The constructor.
     *
     * @param string $sName     The method name
     * @param array $aArguments The method arguments
     */
    public function __construct(private string $sName, private array $aArguments)
    {}

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'func';
    }

    /**
     * Add the page number to the function arguments.
     *
     * @return self
     */
    public function withPage(): self
    {
        // Check if there's already a page number in the parameters list.
        foreach($this->aArguments as $xArgument)
        {
            if(TypedValue::isPage($xArgument))
            {
                return $this;
            }
        }
        $this->aArguments[] = TypedValue::page();
        return $this;
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            '_type' => $this->getType(),
            '_name' => $this->sName,
            'args' => array_map(fn(mixed $xArgument) =>
                TypedValue::make($xArgument)->jsonSerialize(), $this->aArguments),
        ];
    }
}
