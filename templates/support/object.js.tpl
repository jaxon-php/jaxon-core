{$sPrefix|noescape}{$sClass|noescape} = {};
{foreach $aMethods as $aMethod}
{$sPrefix|noescape}{$sClass|noescape}.{$aMethod['name']|noescape} = function() {
    return xajax.request(
        { xjxcls: '{$sClass|noescape}', xjxmthd: '{$aMethod['name']|noescape}' },
        { parameters: arguments{foreach $aMethod['config'] as $sKey => $sValue}, {$sKey|noescape}: {$sValue|noescape}{/foreach} }
    );
};
{/foreach}
