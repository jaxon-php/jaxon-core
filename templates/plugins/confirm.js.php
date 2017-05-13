jaxon.confirm = {
    skip: function(command) {
        numberOfCommands = command.id;
        while (0 < numberOfCommands) {
            jaxon.tools.queue.pop(command.response);
            --numberOfCommands;
        }
    }
};
/*
    Function: jaxon.confirm.commands

    A rewrite of the jaxon.confirm.commands function which uses the user configured confirm library.

    Parameters:
        command (object) - jaxon response object

    Returns:
        true - The operation completed successfully.
*/
jaxon.confirm.commands = function(command) {
    command.fullName = 'confirmCommands';
    var msg = command.data;
    /*
     * Unlike javascript confirm(), third party confirm() functions are not blocking.
     * Therefore, to prevent the next commands to run while the library is waiting for the user confirmation,
     * the remaining commands are moved to a new queue in the command object.
     * They will be processed in the confirm callbacks.
     * Note that only one confirm command will be allowed in a Jaxon response.
     */
    command.response = jaxon.tools.queue.create(jaxon.config.responseQueueSize);
    while((obj = jaxon.tools.queue.pop(jaxon.response)) != null)
    {
        jaxon.tools.queue.push(command.response, obj);
        delete obj;
    }
    <?php echo $this->sConfirmScript ?>;
    return true;
};
