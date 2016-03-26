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

{if $this->nScriptLoadTimeout > 0}
window.setTimeout(
	function() {
		var printCoreError = true, printDebugError = true, printVerboseError = true, printLanguageError = true;
		try {
			if (xajax.isLoaded)
				printCoreError = false;
{if $bDebug}
			else if(xajax.debug.isLoaded)
				printDebugError = false;
{if $bVerboseDebug}
			else if(xajax.debug.verbose.isLoaded)
				printVerboseError = false;
{/if}
{if $bLanguage}
			else if(xajax.debug.lang.isLoaded)
				printLanguageError = false;
{/if}
{/if}
		}
		catch (e) {}
		if (printCoreError)
			alert("{$sJsCoreError|noescape}");
{if $bDebug}
		else if(printDebugError)
			alert("{$sJsDebugError|noescape}");
{if $bVerboseDebug}
		else if(printVerboseError)
			alert("{$sJsVerboseError|noescape}");
{/if}
{if $bLanguage}
		else if(printLanguageError)
			alert("{$sJsLanguageError|noescape}");
{/if}
{/if}
	},
	{$nScriptLoadTimeout|noescape}
);
{/if}
