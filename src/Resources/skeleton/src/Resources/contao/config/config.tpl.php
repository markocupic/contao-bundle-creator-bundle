<?= "<?php\n" ?>

<?= $this->phpdoc ?>
<?php if($this->addBackendModule): ?>
/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['<?= $this->backendmodulecategory ?>']['<?= $this->backendmoduletype ?>'] = [
    'tables' => ['<?= $this->dcatable ?>']
];

/**
 * Models
 */
$GLOBALS['TL_MODELS']['<?= $this->dcatable ?>'] = \<?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>\Model\<?= $this->modelclassname ?>::class;
<?php endif; ?>
