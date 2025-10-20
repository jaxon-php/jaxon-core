JxnSample = {
  myMethod: function() { return jaxon.request({ type: 'class', name: 'Sample', method: 'myMethod' }, { parameters: arguments, asynchronous: true }); },
};
JxnClassA = {
  methodAb: function() { return jaxon.request({ type: 'class', name: 'ClassA', method: 'methodAb' }, { parameters: arguments, bags: ["bag.name"] }); },
};
JxnClassB = {
  methodBa: function() { return jaxon.request({ type: 'class', name: 'ClassB', method: 'methodBa' }, { parameters: arguments }); },
};
JxnClassC = {
  methodCa: function() { return jaxon.request({ type: 'class', name: 'ClassC', method: 'methodCa' }, { parameters: arguments, upload: 'methodBb' }); },
  methodCb: function() { return jaxon.request({ type: 'class', name: 'ClassC', method: 'methodCb' }, { parameters: arguments }); },
};
