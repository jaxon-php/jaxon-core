<?php if(($this->sCode)): ?>
<script type="text/javascript"<?php echo $this->sOptions === '' ? '' : ' ', $this->sOptions ?>>
<?php echo $this->sCode, "\n" ?>
</script>
<?php endif ?>
