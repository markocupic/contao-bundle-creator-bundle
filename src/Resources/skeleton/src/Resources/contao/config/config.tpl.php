<?= "<?php\n" ?>

<?= $this->phpdoc ?>
<?php if($this->addBackendModule): ?>

use <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>\Model\<?= $this->modelclassname ?>;

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['<?= $this->backendmodulecategory ?>']['<?= $this->backendmoduletype ?>'] = array(
    'tables' => array('<?= $this->dcatable ?>')
);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['<?= $this->dcatable ?>'] = <?= $this->modelclassname ?>::class;
<?php endif; ?>
