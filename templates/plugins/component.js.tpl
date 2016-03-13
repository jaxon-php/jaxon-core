<script type="text/javascript" {$sDefer|noescape} charset="UTF-8">
/* <![CDATA[ */
window.setTimeout(
 function() {
  var scriptExists = false;
  try { if ({$sFile|noescape}.isLoaded) scriptExists = true; }
  catch (e) {}
  if (!scriptExists) {
   alert("Error: the {$sFile|noescape} Javascript component could not be included. Perhaps the URL is incorrect?\nURL: {$sUrl|noescape}");
  }
 },
 {$nScriptLoadTimeout|noescape}
);
/* ]]> */
</script>
