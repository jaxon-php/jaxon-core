<?php foreach($this->aUrls as $sUrl):
$this->include('jaxon::plugins/include.js', ['sUrl' => $sUrl, 'sOptions' => $this->sOptions]);
endforeach;
