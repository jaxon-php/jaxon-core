jaxon.dom.ready(function() {
    jaxon.command.handler.register('cc', jaxon.confirm.commands);

{if ($sPluginScript)}
{$sPluginScript|noescape}
{/if}
});
