{$sPrefix|noescape}{$sEvent|noescape} = function() {
    return xajax.request(
        { xjxevt: '{$sEvent|noescape}' },
        { parameters: arguments{($sMode) ? , mode: '{$sMode|noescape}'}{($sMethod) ? , method: '{$sMethod|noescape}'} }
    );
};
