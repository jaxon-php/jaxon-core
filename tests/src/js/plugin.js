<style>
  .pagination li a {
    cursor: pointer;
  }
</style>

<script type="text/javascript" src="https://cdn.jsdelivr.net/gh/jaxon-php/jaxon-js@5.0.1/dist/libs/chibi/chibi.js"  charset="UTF-8"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/gh/jaxon-php/jaxon-js@5.0.1/dist/jaxon.core.js"  charset="UTF-8"></script>

<script type="text/javascript"  charset="UTF-8">
/* <![CDATA[ */
jaxon.config.requestURI = "http://example.test/path";
jaxon.config.statusMessages = false;
jaxon.config.waitCursor = true;
jaxon.config.version = "Jaxon 5.x";
jaxon.config.defaultMode = "asynchronous";
jaxon.config.defaultMethod = "POST";
jaxon.config.responseType = "JSON";

JaxonSamplePackageClass = {};
JaxonSamplePackageClass.home = function() {
    return jaxon.request({ type: 'class', name: 'SamplePackageClass', method: 'home' }, { parameters: arguments });
};

jxn_my_first_function = function() {
    return jaxon.request({ type: 'func', name: 'my_first_function' }, { parameters: arguments });
};
jxn_my_alias_function = function() {
    return jaxon.request({ type: 'func', name: 'my_alias_function' }, { parameters: arguments, upload: 'html_field_id' });
};
jxn_my_third_function = function() {
    return jaxon.request({ type: 'func', name: 'my_third_function' }, { parameters: arguments });
};

jaxon.dom.ready(() => jaxon.processCustomAttrs());
/* ]]> */
</script>
