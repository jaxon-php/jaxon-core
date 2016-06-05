{$sPrefix|noescape}{$sAlias|noescape} = function() {
    return jaxon.request(
        { jxnfun: '{$sFunction|noescape}' },
        { parameters: arguments{foreach $aConfig as $sKey => $sValue}, {$sKey|noescape}: {$sValue|noescape}{/foreach} }
    );
};
