JxnSample = {};
JxnSample.myMethod = function() {
    return jaxon.request({ type: 'class', name: 'Sample', method: 'myMethod' }, { parameters: arguments });
};
JxnTheClass = {};
JxnTheClass.theMethod = function() {
    return jaxon.request({ type: 'class', name: 'TheClass', method: 'theMethod' }, { parameters: arguments });
};
