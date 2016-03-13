<script type="text/javascript" {$sDefer} charset="UTF-8">
/* <![CDATA[ */
window.setTimeout(
 function() {
  var scriptExists = false;
  try { if ({$name}.isLoaded) scriptExists = true; }
  catch (e) {}
  if (!scriptExists) {
   alert("Error: the {$name} Javascript component could not be included. Perhaps the URL is incorrect?\nURL: {$file}");
  }
 }, {$nScriptLoadTimeout});
/* ]]> */
</script>
