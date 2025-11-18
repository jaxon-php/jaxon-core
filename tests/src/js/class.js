const jx = {
  rc: (name, method, parameters, options = {}) => jaxon.request({ type: 'class', name, method }, { parameters, ...options}),
  rf: (name, parameters, options = {}) => jaxon.request({ type: 'func', name }, { parameters, ...options}),
  c0: 'Sample',
  c1: 'TheClass',
};

JxnSample = {
  myMethod: (...args) => jx.rc(jx.c0, 'myMethod', args),
};
JxnTheClass = {
  theMethod: (...args) => jx.rc(jx.c1, 'theMethod', args),
};
