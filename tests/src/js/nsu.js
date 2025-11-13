Jaxon_Tests_Ns_Ajax_ClassA = {
  methodAa: (...args) => jxn.rc('Jaxon_Tests_Ns_Ajax_ClassA', 'methodAa', args),
  methodAb: (...args) => jxn.rc('Jaxon_Tests_Ns_Ajax_ClassA', 'methodAb', args),
};
Jaxon_Tests_Ns_Ajax_ClassB = {
  methodBa: (...args) => jxn.rc('Jaxon_Tests_Ns_Ajax_ClassB', 'methodBa', args),
  methodBb: (...args) => jxn.rc('Jaxon_Tests_Ns_Ajax_ClassB', 'methodBb', args),
};
Jaxon_Tests_Ns_Ajax_ClassC = {
  methodCa: (...args) => jxn.rc('Jaxon_Tests_Ns_Ajax_ClassC', 'methodCa', args),
  methodCb: (...args) => jxn.rc('Jaxon_Tests_Ns_Ajax_ClassC', 'methodCb', args),
};
Jaxon = {
  NsTests: {
    DirA: {
      ClassA: {
        methodAa: (...args) => jxn.rc('Jaxon.NsTests.DirA.ClassA', 'methodAa', args),
        methodAb: (...args) => jxn.rc('Jaxon.NsTests.DirA.ClassA', 'methodAb', args),
      },
    },
    DirB: {
      ClassB: {
        methodBa: (...args) => jxn.rc('Jaxon.NsTests.DirB.ClassB', 'methodBa', args),
        methodBb: (...args) => jxn.rc('Jaxon.NsTests.DirB.ClassB', 'methodBb', args),
      },
    },
    DirC: {
      ClassC: {
        methodCa: (...args) => jxn.rc('Jaxon.NsTests.DirC.ClassC', 'methodCa', args),
        methodCb: (...args) => jxn.rc('Jaxon.NsTests.DirC.ClassC', 'methodCb', args),
      },
    },
  },
};
