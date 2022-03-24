JxnSample = {};
JxnSample.myMethod = function() {
    return jaxon.request({ jxncls: 'Sample', jxnmthd: 'myMethod' }, { parameters: arguments, asynchronous: true });
};
JxnClassA = {};
JxnClassA.methodAb = function() {
    return jaxon.request({ jxncls: 'ClassA', jxnmthd: 'methodAb' }, { parameters: arguments });
};
JxnClassC = {};
JxnClassC.methodCa = function() {
    return jaxon.request({ jxncls: 'ClassC', jxnmthd: 'methodCa' }, { parameters: arguments, upload: 'methodBb' });
};
JxnClassC.methodCb = function() {
    return jaxon.request({ jxncls: 'ClassC', jxnmthd: 'methodCb' }, { parameters: arguments });
};
JxnClassB = {};
JxnClassB.methodBa = function() {
    return jaxon.request({ jxncls: 'ClassB', jxnmthd: 'methodBa' }, { parameters: arguments });
};
