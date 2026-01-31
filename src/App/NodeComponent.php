<?php

namespace Jaxon\App;

abstract class NodeComponent extends Component\NodeComponent
{
    /**
     * Clear the attached DOM node content.
     *
     * @return void
     */
    final public function clear(): void
    {
        $this->node()->clear();
    }

    /**
     * Show/hide the attached DOM node.
     *
     * @param bool $bVisible
     *
     * @return void
     */
    final public function visible(bool $bVisible): void
    {
        $bVisible ? $this->node()->jq()->show() : $this->node()->jq()->hide();
    }
}
