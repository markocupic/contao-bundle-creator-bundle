<?= "<?php\n" ?>

declare(strict_types=1);

<?= $this->phpdoc ?>
<?php if($this->addBackendModule): ?>

use <?= $this->fullyquallifiedfrontendmoduleclassname ?>;

/**
 * Backend modules
 */
<?php if($this->backendmodulecategorytrans != ""): ?>
$GLOBALS['TL_LANG']['MOD']['<?= $this->backendmodulecategory ?>'] = '<?= $this->backendmodulecategorytrans ?>';
<?php endif; ?>
$GLOBALS['TL_LANG']['MOD']['<?= $this->backendmoduletype ?>'] = ['<?= $this->backendmoduletrans_0 ?>', '<?= $this->backendmoduletrans_1 ?>'];
<?php endif; ?>

<?php if($this->addFrontendModule): ?>
/**
* Frontend modules
*/
<?php if($this->frontendmodulecategorytrans != ""): ?>
$GLOBALS['TL_LANG']['FMD']['<?= $this->frontendmodulecategory ?>'] = '<?= $this->frontendmodulecategorytrans ?>';
<?php endif; ?>
$GLOBALS['TL_LANG']['FMD'][<?= $this->frontendmoduleclassname ?>::TYPE] = ['<?= $this->frontendmoduletrans_0 ?>', '<?= $this->frontendmoduletrans_1 ?>'];
<?php endif; ?>
