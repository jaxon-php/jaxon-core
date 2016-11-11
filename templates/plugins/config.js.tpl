try {
    if(typeof jaxon.config == undefined)
        jaxon.config = {};
}
catch(e) {
    jaxon = {};
    jaxon.config = {};
};

jaxon.config.requestURI = "{$sRequestURI|noescape}";
jaxon.config.statusMessages = {$sStatusMessages|noescape};
jaxon.config.waitCursor = {$sWaitCursor|noescape};
jaxon.config.version = "{$sVersion|noescape}";
jaxon.config.defaultMode = "{$sDefaultMode|noescape}";
jaxon.config.defaultMethod = "{$sDefaultMethod|noescape}";
jaxon.config.responseType = "{$sResponseType|noescape}";
{if $nResponseQueueSize > 0}
jaxon.config.responseQueueSize = {$nResponseQueueSize|noescape};
{/if}
{if ($bDebug) && ($sDebugOutputID)}
jaxon.debug = {};
jaxon.debug.outputID = "{$sDebugOutputID|noescape}";
{/if}
{if ($sCsrfMetaName) }
metaTags = document.getElementsByTagName('meta');
for(i = 0; i < metaTags.length; i++)
{
    if(metaTags[i].getAttribute('name') == '{$sCsrfMetaName|noescape}')
    {
        if((csrfToken = metaTags[i].getAttribute('content')))
        {
            jaxon.config.postHeaders = {'X-CSRF-TOKEN': csrfToken};
        }
        break;
    }
}
{/if}
