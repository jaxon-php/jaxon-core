<?php echo $this->aMethod['name'] ?>: function() { return jaxon.request({ type: 'class', name: '<?php
    echo $this->sJsClass ?>', method: '<?php echo $this->aMethod['name']
    ?>' }, { parameters: arguments<?php foreach($this->aMethod['options'] as $sKey => $sValue):
        ?>, <?php echo $sKey ?>: <?php echo $sValue ?><?php endforeach ?> }); },
