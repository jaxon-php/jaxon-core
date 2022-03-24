JxnSample = {};
JxnSample.myMethod = function() {
    return jaxon.request({ jxncls: 'Sample', jxnmthd: 'myMethod' }, { parameters: arguments });
};
JxnTheClass = {};
JxnTheClass.theMethod = function() {
    return jaxon.request({ jxncls: 'TheClass', jxnmthd: 'theMethod' }, { parameters: arguments });
};
