<?= "<?php\n" ?>

declare(strict_types=1);

<?= $this->phpdoc ?>
<?php if($this->addFrontendModule): ?>

use <?= $this->fullyquallifiedfrontendmoduleclassname ?>;

/**
 * Frontend modules
 */
$GLOBALS['TL_DCA']['tl_module']['palettes'][<?= $this->frontendmoduleclassname ?>::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
<?php endif; ?>
