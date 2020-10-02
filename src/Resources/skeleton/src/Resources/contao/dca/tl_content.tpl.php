<?= "<?php\n" ?>

<?= $this->phpdoc ?>
<?php if($this->addContentElement): ?>

/**
 * Content elements
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['<?= $this->contentelementtype ?>'] = '{type_legend},type,headline;{text_legend},text;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';
<?php endif; ?>
