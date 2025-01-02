<?php echo $this->sJsName ?> = function() {
    return jaxon.request({ type: 'func', name: '<?php
        echo $this->sName ?>' }, { parameters: arguments<?php
        foreach($this->aOptions as $sKey => $sValue): ?>, <?php
        echo $sKey ?>: <?php echo $sValue ?><?php endforeach ?> });
};
