<script type="text/javascript" {$sDefer} charset="UTF-8">
/* <![CDATA[ */
try
{
    if(typeof xajax.config == undefined)
        xajax.config = {};
}
catch(e)
{
    xajax = {};
    xajax.config = {};
};
xajax.config.requestURI = "{$sRequestURI}";
xajax.config.statusMessages = {$sStatusMessages};
xajax.config.waitCursor = {$sWaitCursor};
xajax.config.version = "{$sVersion}";
xajax.config.defaultMode = "{$sDefaultMode}";
xajax.config.defaultMethod = "{$sDefaultMethod}";
xajax.config.JavaScriptURI = "{$sJsURI}";
xajax.config.responseType = "{$sResponseType}";
{if $nResponseQueueSize gt 0}
xajax.config.responseQueueSize = {$nResponseQueueSize};
{endif}
{if $bDebug and $sDebugOutputID}
xajax.debug = {};
xajax.debug.outputID = "{$sDebugOutputID}";
{endif}
/* ]]> */
</script>
