try {
    if(typeof jaxon.config == undefined)
        jaxon.config = {};
}
catch(e) {
    jaxon = {};
    jaxon.config = {};
};

jaxon.config.requestURI = "<?php echo $this->sRequestURI ?>";
jaxon.config.statusMessages = <?php echo $this->sStatusMessages ?>;
jaxon.config.waitCursor = <?php echo $this->sWaitCursor ?>;
jaxon.config.version = "<?php echo $this->sVersion ?>";
jaxon.config.defaultMode = "<?php echo $this->sDefaultMode ?>";
jaxon.config.defaultMethod = "<?php echo $this->sDefaultMethod ?>";
jaxon.config.responseType = "<?php echo $this->sResponseType ?>";
<?php if($this->nResponseQueueSize > 0): ?>
jaxon.config.responseQueueSize = <?php echo $this->nResponseQueueSize ?>;
<?php endif ?>
<?php if(($this->bDebug)): ?>
<?php if(($this->sDebugOutputID)): ?>
jaxon.debug.outputID = "<?php echo $this->sDebugOutputID ?>";
<?php endif ?>
<?php if(($this->bVerboseDebug)): ?>
jaxon.debug.verbose.active = true;
<?php endif ?>
<?php endif ?>
<?php if(($this->sCsrfMetaName)): ?>
metaTags = document.getElementsByTagName('meta');
for(i = 0; i < metaTags.length; i++)
{
    if(metaTags[i].getAttribute('name') == '<?php echo $this->sCsrfMetaName ?>')
    {
        if((csrfToken = metaTags[i].getAttribute('content')))
        {
            jaxon.config.postHeaders = {'X-CSRF-TOKEN': csrfToken};
        }
        break;
    }
}
<?php endif ?>

<?php echo $this->sConfirmScript ?>
