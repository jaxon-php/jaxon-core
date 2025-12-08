const jx = {
  rc: (name, method, parameters, options = {}) => jaxon.request({ type: 'class', name, method }, { parameters, ...options}),
  rf: (name, parameters, options = {}) => jaxon.request({ type: 'func', name }, { parameters, ...options}),
<?php
foreach($this->aCallableParams as $sName => $nIndex):
  echo "  c$nIndex: '$sName',\n";
endforeach
?>
};
