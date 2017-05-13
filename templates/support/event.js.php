<?php echo $this->sPrefix ?><?php echo $this->sEvent ?> = function() {
    return jaxon.request(
        { jxnevt: '<?php echo $this->sEvent ?>' },
        { parameters: arguments<?php if(($this->sMode)): ?>, mode: '<?php echo $this->sMode ?>'<?php endif ?><?php if(($this->sMethod)): ?>, method: '<?php echo $this->sMethod ?>'<?php endif ?> }
    );
};
