<?php $sJsClass = $this->sPrefix . $this->sClass ?>
<?php
// An exported PHP class can have the same name as a subdir in the same parent dir.
// So, we must make sure that embedded js objects do not already exist, before we create them.
if(strpos($sJsClass, '.') !== false): ?>
if(<?php echo $sJsClass ?> === undefined) {
    <?php echo $sJsClass ?> = {};
}
<?php else: ?>
<?php echo $sJsClass ?> = {};
<?php endif ?>
<?php foreach($this->aMethods as $aMethod): ?>
<?php echo $sJsClass ?>.<?php echo $aMethod['name'] ?> = function() {
    return jaxon.request({ jxncls: '<?php echo $this->sClass ?>', jxnmthd: '<?php
        echo $aMethod['name'] ?>' }, { parameters: arguments<?php
        foreach($aMethod['config'] as $sKey => $sValue): ?>, <?php
        echo $sKey ?>: <?php echo $sValue ?><?php endforeach ?> });
};
<?php endforeach;
