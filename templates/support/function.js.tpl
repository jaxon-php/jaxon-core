{$sPrefix|noescape}{$sAlias|noescape} = function() {
    return xajax.request(
        { xjxfun: '{$sFunction|noescape}' },
        { parameters: arguments{foreach $aConfig as $sKey => $sValue}, {$sKey|noescape}: {$sValue|noescape}{/foreach} }
    );
};
