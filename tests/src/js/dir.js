const jx = {
  rc: (name, method, parameters, options = {}) => jaxon.request({ type: 'class', name, method }, { parameters, ...options}),
  rf: (name, parameters, options = {}) => jaxon.request({ type: 'func', name }, { parameters, ...options}),
  c0: 'ClassA',
  c1: 'ClassB',
  c2: 'ClassC',
};

JxnClassA = {
  methodAa: (...args) => jx.rc(jx.c0, 'methodAa', args),
  methodAb: (...args) => jx.rc(jx.c0, 'methodAb', args),
};
JxnClassB = {
  methodBa: (...args) => jx.rc(jx.c1, 'methodBa', args),
  methodBb: (...args) => jx.rc(jx.c1, 'methodBb', args),
};
JxnClassC = {
  methodCa: (...args) => jx.rc(jx.c2, 'methodCa', args),
  methodCb: (...args) => jx.rc(jx.c2, 'methodCb', args),
};
