jxn_my_first_function = function() {
    return jaxon.request({ type: 'func', name: 'my_first_function' }, { parameters: arguments });
};
jxn_my_alias_function = function() {
    return jaxon.request({ type: 'func', name: 'my_alias_function' }, { parameters: arguments, upload: 'html_field_id' });
};
jxn_my_third_function = function() {
    return jaxon.request({ type: 'func', name: 'my_third_function' }, { parameters: arguments });
};
