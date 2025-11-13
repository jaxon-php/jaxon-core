const jxn = jaxon;
jxn.rc = (name, method, parameters, options = {}) => jxn.request({ type: 'class', name, method }, { parameters, ...options});
jxn.rf = (name, parameters, options = {}) => jxn.request({ type: 'func', name }, { parameters, ...options});

jxn.config.requestURI = "<?php echo $this->sRequestURI ?>";
jxn.config.statusMessages = <?php echo $this->sStatusMessages ?>;
jxn.config.waitCursor = <?php echo $this->sWaitCursor ?>;
jxn.config.version = "<?php echo $this->sVersion ?>";
jxn.config.defaultMode = "<?php echo $this->sDefaultMode ?>";
jxn.config.defaultMethod = "<?php echo $this->sDefaultMethod ?>";
jxn.config.responseType = "<?php echo $this->sResponseType ?>";

<?php if($this->nResponseQueueSize > 0): ?>
jxn.config.responseQueueSize = <?php echo $this->nResponseQueueSize ?>;
<?php endif ?>

<?php if(($this->bDebug)): ?>
jxn.debug.active = true;
<?php if(($this->sDebugOutputID)): ?>
jxn.debug.outputID = "<?php echo $this->sDebugOutputID ?>";
<?php endif ?>
<?php if(($this->bVerboseDebug)): ?>
jxn.debug.verbose.active = true;
<?php endif ?>
<?php endif ?>

<?php if(($this->sCsrfMetaName)): ?>
jxn.setCsrf('<?php echo $this->sCsrfMetaName ?>');
<?php endif ?>
