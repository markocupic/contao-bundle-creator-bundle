<?= "<?php\n"; ?>

declare(strict_types=1);

<?= $this->phpdoc; ?>

<?php if ($this->addFrontendModule) { ?>
use <?= $this->fullyquallifiedfrontendmoduleclassname; ?>;

<?php } ?>
<?php if ($this->addBackendModule) { ?>
/**
 * Backend modules
 */
<?php if ('' !== $this->backendmodulecategorytrans) { ?>
$GLOBALS['TL_LANG']['MOD']['<?= $this->backendmodulecategory; ?>'] = '<?= $this->backendmodulecategorytrans; ?>';
<?php } ?>
$GLOBALS['TL_LANG']['MOD']['<?= $this->backendmoduletype; ?>'] = ['<?= $this->backendmoduletrans_0; ?>', '<?= $this->backendmoduletrans_1; ?>'];

<?php } ?>
<?php if ($this->addFrontendModule) { ?>
/**
 * Frontend modules
 */
<?php if ('' !== $this->frontendmodulecategorytrans) { ?>
$GLOBALS['TL_LANG']['FMD']['<?= $this->frontendmodulecategory; ?>'] = '<?= $this->frontendmodulecategorytrans; ?>';
<?php } ?>
$GLOBALS['TL_LANG']['FMD'][<?= $this->frontendmoduleclassname; ?>::TYPE] = ['<?= $this->frontendmoduletrans_0; ?>', '<?= $this->frontendmoduletrans_1; ?>'];

<?php } ?>
