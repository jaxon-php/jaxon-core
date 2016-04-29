try {
	if(typeof xajax.config == undefined)
		xajax.config = {};
}
catch(e) {
	xajax = {};
	xajax.config = {};
};

xajax.config.requestURI = "{$sRequestURI|noescape}";
xajax.config.statusMessages = {$sStatusMessages|noescape};
xajax.config.waitCursor = {$sWaitCursor|noescape};
xajax.config.version = "{$sVersion|noescape}";
xajax.config.defaultMode = "{$sDefaultMode|noescape}";
xajax.config.defaultMethod = "{$sDefaultMethod|noescape}";
xajax.config.responseType = "{$sResponseType|noescape}";
{if $nResponseQueueSize > 0}
xajax.config.responseQueueSize = {$nResponseQueueSize|noescape};
{/if}
{if ($bDebug) && ($sDebugOutputID)}
xajax.debug = {};
xajax.debug.outputID = "{$sDebugOutputID|noescape}";
{/if}
