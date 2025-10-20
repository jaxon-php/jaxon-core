Jaxon = {
  Tests: {
    Ns: {
      Ajax: {
        ClassA: {
          methodAa: function() { return jaxon.request({ type: 'class', name: 'Jaxon.Tests.Ns.Ajax.ClassA', method: 'methodAa' }, { parameters: arguments }); },
          methodAb: function() { return jaxon.request({ type: 'class', name: 'Jaxon.Tests.Ns.Ajax.ClassA', method: 'methodAb' }, { parameters: arguments }); },
        },
        ClassB: {
          methodBa: function() { return jaxon.request({ type: 'class', name: 'Jaxon.Tests.Ns.Ajax.ClassB', method: 'methodBa' }, { parameters: arguments }); },
          methodBb: function() { return jaxon.request({ type: 'class', name: 'Jaxon.Tests.Ns.Ajax.ClassB', method: 'methodBb' }, { parameters: arguments }); },
        },
        ClassC: {
          methodCa: function() { return jaxon.request({ type: 'class', name: 'Jaxon.Tests.Ns.Ajax.ClassC', method: 'methodCa' }, { parameters: arguments }); },
          methodCb: function() { return jaxon.request({ type: 'class', name: 'Jaxon.Tests.Ns.Ajax.ClassC', method: 'methodCb' }, { parameters: arguments }); },
        },
      },
    },
  },
  NsTests: {
    DirA: {
      ClassA: {
        methodAa: function() { return jaxon.request({ type: 'class', name: 'Jaxon.NsTests.DirA.ClassA', method: 'methodAa' }, { parameters: arguments }); },
        methodAb: function() { return jaxon.request({ type: 'class', name: 'Jaxon.NsTests.DirA.ClassA', method: 'methodAb' }, { parameters: arguments }); },
      },
    },
    DirB: {
      ClassB: {
        methodBa: function() { return jaxon.request({ type: 'class', name: 'Jaxon.NsTests.DirB.ClassB', method: 'methodBa' }, { parameters: arguments }); },
        methodBb: function() { return jaxon.request({ type: 'class', name: 'Jaxon.NsTests.DirB.ClassB', method: 'methodBb' }, { parameters: arguments }); },
      },
    },
    DirC: {
      ClassC: {
        methodCa: function() { return jaxon.request({ type: 'class', name: 'Jaxon.NsTests.DirC.ClassC', method: 'methodCa' }, { parameters: arguments }); },
        methodCb: function() { return jaxon.request({ type: 'class', name: 'Jaxon.NsTests.DirC.ClassC', method: 'methodCb' }, { parameters: arguments }); },
      },
    },
  },
};
