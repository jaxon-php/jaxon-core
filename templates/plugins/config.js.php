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
jaxon.debug.active = true;
<?php if(($this->sDebugOutputID)): ?>
jaxon.debug.outputID = "<?php echo $this->sDebugOutputID ?>";
<?php endif ?>
<?php if(($this->bVerboseDebug)): ?>
jaxon.debug.verbose.active = true;
<?php endif ?>
<?php endif ?>

<?php if(($this->sCsrfMetaName)): ?>
jaxon.setCsrf('<?php echo $this->sCsrfMetaName ?>');
<?php endif ?>

const jxn = jaxon;
jxn.rc = (name, method, parameters, options = {}) => jaxon.request({ type: 'class', name, method }, { parameters, ...options});
jxn.rf = (name, parameters, options = {}) => jaxon.request({ type: 'func', name }, { parameters, ...options});
