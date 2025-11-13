JxnSample = {
  myMethod: (...args) => jxn.rc('Sample', 'myMethod', args, { asynchronous: true }),
};
JxnClassA = {
  methodAb: (...args) => jxn.rc('ClassA', 'methodAb', args, { bags: ["bag.name"] }),
};
JxnClassB = {
  methodBa: (...args) => jxn.rc('ClassB', 'methodBa', args),
};
JxnClassC = {
  methodCa: (...args) => jxn.rc('ClassC', 'methodCa', args, { upload: 'methodBb' }),
  methodCb: (...args) => jxn.rc('ClassC', 'methodCb', args),
};
