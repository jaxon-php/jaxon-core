{$sPrefix|noescape}{$sEvent|noescape} = function() {
    return jaxon.request(
        { jxnevt: '{$sEvent|noescape}' },
        { parameters: arguments{($sMode) ? , mode: '{$sMode|noescape}'}{($sMethod) ? , method: '{$sMethod|noescape}'} }
    );
};
