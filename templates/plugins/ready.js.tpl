docReady(function() {
{if ($sPluginScript)}
{$sPluginScript|noescape}
{/if}

{if $this->nScriptLoadTimeout > 0}
window.setTimeout(
    function() {
        var printCoreError = true, printDebugError = true, printVerboseError = true, printLanguageError = true;
        try {
            if (jaxon.isLoaded)
                printCoreError = false;
{if $bDebug}
            else if(jaxon.debug.isLoaded)
                printDebugError = false;
{if $bVerboseDebug}
            else if(jaxon.debug.verbose.isLoaded)
                printVerboseError = false;
{/if}
{if $bLanguage}
            else if(jaxon.debug.lang.isLoaded)
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
});
