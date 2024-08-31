<script type="text/javascript" src="https://cdn.jsdelivr.net/gh/jaxon-php/jaxon-js@4.0.6/dist/jaxon.core.js"  charset="UTF-8"></script>

<script type="text/javascript"  charset="UTF-8">
/* <![CDATA[ */
try {
    if(typeof jaxon.config == undefined)
        jaxon.config = {};
}
catch(e) {
    jaxon = {};
    jaxon.config = {};
};

jaxon.config.requestURI = "http://example.test/path";
jaxon.config.statusMessages = false;
jaxon.config.waitCursor = true;
jaxon.config.version = "Jaxon 4.x";
jaxon.config.defaultMode = "asynchronous";
jaxon.config.defaultMethod = "POST";
jaxon.config.responseType = "JSON";

jxn_my_first_function = function() {
    return jaxon.request({ jxnfun: 'my_first_function' }, { parameters: arguments });
};
jxn_my_alias_function = function() {
    return jaxon.request({ jxnfun: 'my_alias_function' }, { parameters: arguments, upload: 'html_field_id' });
};
jxn_my_third_function = function() {
    return jaxon.request({ jxnfun: 'my_third_function' }, { parameters: arguments });
};

jaxon.dialogs = {};

jaxon.dom.ready(function() {
jaxon.command.handler.register("jquery", (args) => jaxon.cmd.script.execute(args));

jaxon.command.handler.register("bags.set", (args) => {
        for (const bag in args.data) {
            jaxon.ajax.parameters.bags[bag] = args.data[bag];
        }
    });
});

    jaxon.command.handler.register("rd", (command) => {
        const { data: sUrl, delay: nDelay } = command;
        if (nDelay <= 0) {
            window.location = sUrl;
            return true;
        }
        window.setTimeout(() => window.location = sUrl, nDelay * 1000);
        return true;
    });

/* ]]> */
</script>
