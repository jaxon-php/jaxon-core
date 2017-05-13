<?php echo $this->sPrefix ?><?php echo $this->sClass ?> = {};
<?php foreach($this->aMethods as $aMethod): ?>
<?php echo $this->sPrefix ?><?php echo $this->sClass ?>.<?php echo $aMethod['name'] ?> = function() {
    return jaxon.request(
        { jxncls: '<?php echo $this->sClass ?>', jxnmthd: '<?php echo $aMethod['name'] ?>' },
        { parameters: arguments<?php foreach($aMethod['config'] as $sKey => $sValue): ?>, <?php echo $sKey ?>: <?php echo $sValue ?><?php endforeach ?> }
    );
};
<?php endforeach ?>
