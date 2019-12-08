<?php echo $this->sJsScript ?>

jaxon.dom.ready(function() {
    jaxon.command.handler.register("cc", jaxon.confirm.commands);

<?php echo $this->sJsReadyScript ?>
});
