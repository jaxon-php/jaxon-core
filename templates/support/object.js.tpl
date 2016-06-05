{$sPrefix|noescape}{$sClass|noescape} = {};
{foreach $aMethods as $aMethod}
{$sPrefix|noescape}{$sClass|noescape}.{$aMethod['name']|noescape} = function() {
    return jaxon.request(
        { jxncls: '{$sClass|noescape}', jxnmthd: '{$aMethod['name']|noescape}' },
        { parameters: arguments{foreach $aMethod['config'] as $sKey => $sValue}, {$sKey|noescape}: {$sValue|noescape}{/foreach} }
    );
};
{/foreach}
