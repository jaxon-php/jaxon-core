const jx = {
  rc: (name, method, parameters, options = {}) => jaxon.request({ type: 'class', name, method }, { parameters, ...options}),
  rf: (name, parameters, options = {}) => jaxon.request({ type: 'func', name }, { parameters, ...options}),
  c0: 'Jaxon_Tests_Ns_Ajax_ClassA',
  c1: 'Jaxon_Tests_Ns_Ajax_ClassB',
  c2: 'Jaxon_Tests_Ns_Ajax_ClassC',
  c3: 'Jaxon_Tests_Ns_Ajax_ClassA',
  c4: 'Jaxon_Tests_Ns_Ajax_ClassB',
  c5: 'Jaxon_Tests_Ns_Ajax_ClassC',
  c6: 'Jaxon.NsTests.DirA.ClassA',
  c7: 'Jaxon.NsTests.DirB.ClassB',
  c8: 'Jaxon.NsTests.DirC.ClassC',
};

Jaxon_Tests_Ns_Ajax_ClassA = {
  methodAa: (...args) => jx.rc(jx.c3, 'methodAa', args),
  methodAb: (...args) => jx.rc(jx.c3, 'methodAb', args),
};
Jaxon_Tests_Ns_Ajax_ClassB = {
  methodBa: (...args) => jx.rc(jx.c4, 'methodBa', args),
  methodBb: (...args) => jx.rc(jx.c4, 'methodBb', args),
};
Jaxon_Tests_Ns_Ajax_ClassC = {
  methodCa: (...args) => jx.rc(jx.c5, 'methodCa', args),
  methodCb: (...args) => jx.rc(jx.c5, 'methodCb', args),
};
Jaxon = {
  NsTests: {
    DirA: {
      ClassA: {
        methodAa: (...args) => jx.rc(jx.c6, 'methodAa', args),
        methodAb: (...args) => jx.rc(jx.c6, 'methodAb', args),
      },
    },
    DirB: {
      ClassB: {
        methodBa: (...args) => jx.rc(jx.c7, 'methodBa', args),
        methodBb: (...args) => jx.rc(jx.c7, 'methodBb', args),
      },
    },
    DirC: {
      ClassC: {
        methodCa: (...args) => jx.rc(jx.c8, 'methodCa', args),
        methodCb: (...args) => jx.rc(jx.c8, 'methodCb', args),
      },
    },
  },
};
