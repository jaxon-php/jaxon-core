<?php $sJsClass = $this->sPrefix . $this->sClass ?>
if(<?php echo $sJsClass ?> === undefined) <?php echo $sJsClass ?> = {};
<?php foreach($this->aMethods as $aMethod): ?>
<?php echo $sJsClass ?>.<?php echo $aMethod['name'] ?> = function() {
    return jaxon.request({ jxncls: '<?php echo $this->sClass ?>', jxnmthd: '<?php
            echo $aMethod['name'] ?>' }, { parameters: arguments<?php
            foreach($aMethod['config'] as $sKey => $sValue): ?>, <?php
            echo $sKey ?>: <?php echo $sValue ?><?php endforeach ?> });
};
<?php endforeach;
