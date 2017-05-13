<?php echo $this->sPrefix ?><?php echo $this->sAlias ?> = function() {
    return jaxon.request(
        { jxnfun: '<?php echo $this->sFunction ?>' },
        { parameters: arguments<?php foreach($this->aConfig as $sKey => $sValue): ?>, <?php echo $sKey ?>: <?php echo $sValue ?><?php endforeach ?> }
    );
};
