const jx = {
  rc: (name, method, parameters, options = {}) => jaxon.request({ type: 'class', name, method }, { parameters, ...options}),
  rf: (name, parameters, options = {}) => jaxon.request({ type: 'func', name }, { parameters, ...options}),
  c0: 'Sample',
  c1: 'ClassA',
  c2: 'ClassB',
  c3: 'ClassC',
};

JxnSample = {
  myMethod: (...args) => jx.rc(jx.c0, 'myMethod', args, { asynchronous: true }),
};
JxnClassA = {
  methodAb: (...args) => jx.rc(jx.c1, 'methodAb', args, { bags: ["bag.name"] }),
};
JxnClassB = {
  methodBa: (...args) => jx.rc(jx.c2, 'methodBa', args),
};
JxnClassC = {
  methodCa: (...args) => jx.rc(jx.c3, 'methodCa', args, { upload: 'methodBb' }),
  methodCb: (...args) => jx.rc(jx.c3, 'methodCb', args),
};
