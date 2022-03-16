<?php echo $this->sJsName ?> = function() {
    return jaxon.request({ jxnfun: '<?php echo $this->sName ?>' }, { parameters: arguments<?php
        foreach($this->aOptions as $sKey => $sValue): ?>, <?php echo $sKey ?>: <?php echo $sValue ?><?php endforeach ?> });
};
