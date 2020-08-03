<?= "<?php\n" ?>

<?= $this->phpdoc ?>
<?php if($this->addFrontendModule): ?>
/**
 * Frontend modules
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['<?= $this->frontendmoduletype ?>'] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
<?php endif; ?>
