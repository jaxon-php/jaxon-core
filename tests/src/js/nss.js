Jaxon = {
  Tests: {
    Ns: {
      Ajax: {
        ClassA: {
          methodAa: (...args) => jxn.rc('Jaxon.Tests.Ns.Ajax.ClassA', 'methodAa', args),
          methodAb: (...args) => jxn.rc('Jaxon.Tests.Ns.Ajax.ClassA', 'methodAb', args),
        },
        ClassB: {
          methodBa: (...args) => jxn.rc('Jaxon.Tests.Ns.Ajax.ClassB', 'methodBa', args),
          methodBb: (...args) => jxn.rc('Jaxon.Tests.Ns.Ajax.ClassB', 'methodBb', args),
        },
        ClassC: {
          methodCa: (...args) => jxn.rc('Jaxon.Tests.Ns.Ajax.ClassC', 'methodCa', args),
          methodCb: (...args) => jxn.rc('Jaxon.Tests.Ns.Ajax.ClassC', 'methodCb', args),
        },
      },
    },
  },
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
