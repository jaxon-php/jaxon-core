JxnClassA = {};
JxnClassA.methodAa = function() {
    return jaxon.request({ type: 'class', name: 'ClassA', method: 'methodAa' }, { parameters: arguments });
};
JxnClassA.methodAb = function() {
    return jaxon.request({ type: 'class', name: 'ClassA', method: 'methodAb' }, { parameters: arguments });
};
JxnClassB = {};
JxnClassB.methodBa = function() {
    return jaxon.request({ type: 'class', name: 'ClassB', method: 'methodBa' }, { parameters: arguments });
};
JxnClassB.methodBb = function() {
    return jaxon.request({ type: 'class', name: 'ClassB', method: 'methodBb' }, { parameters: arguments });
};
JxnClassC = {};
JxnClassC.methodCa = function() {
    return jaxon.request({ type: 'class', name: 'ClassC', method: 'methodCa' }, { parameters: arguments });
};
JxnClassC.methodCb = function() {
    return jaxon.request({ type: 'class', name: 'ClassC', method: 'methodCb' }, { parameters: arguments });
};
