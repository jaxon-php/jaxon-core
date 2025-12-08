const jx = {
  rc: (name, method, parameters, options = {}) => jaxon.request({ type: 'class', name, method }, { parameters, ...options}),
  rf: (name, parameters, options = {}) => jaxon.request({ type: 'func', name }, { parameters, ...options}),
  c0: 'Jaxon.Tests.Ns.Ajax.ClassA',
  c1: 'Jaxon.Tests.Ns.Ajax.ClassB',
  c2: 'Jaxon.Tests.Ns.Ajax.ClassC',
  c3: 'Jaxon.NsTests.DirA.ClassA',
  c4: 'Jaxon.NsTests.DirB.ClassB',
  c5: 'Jaxon.NsTests.DirC.ClassC',
};

Jaxon_Tests_Ns_Ajax_ClassA = {
  methodAa: (...args) => jx.rc(jx.c0, 'methodAa', args),
  methodAb: (...args) => jx.rc(jx.c0, 'methodAb', args),
};
Jaxon_Tests_Ns_Ajax_ClassB = {
  methodBa: (...args) => jx.rc(jx.c1, 'methodBa', args),
  methodBb: (...args) => jx.rc(jx.c1, 'methodBb', args),
};
Jaxon_Tests_Ns_Ajax_ClassC = {
  methodCa: (...args) => jx.rc(jx.c2, 'methodCa', args),
  methodCb: (...args) => jx.rc(jx.c2, 'methodCb', args),
};
Jaxon = {
  NsTests: {
    DirA: {
      ClassA: {
        methodAa: (...args) => jx.rc(jx.c3, 'methodAa', args),
        methodAb: (...args) => jx.rc(jx.c3, 'methodAb', args),
      },
    },
    DirB: {
      ClassB: {
        methodBa: (...args) => jx.rc(jx.c4, 'methodBa', args),
        methodBb: (...args) => jx.rc(jx.c4, 'methodBb', args),
      },
    },
    DirC: {
      ClassC: {
        methodCa: (...args) => jx.rc(jx.c5, 'methodCa', args),
        methodCb: (...args) => jx.rc(jx.c5, 'methodCb', args),
      },
    },
  },
};
