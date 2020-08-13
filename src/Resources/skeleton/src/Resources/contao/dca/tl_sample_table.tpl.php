<?= "<?php\n" ?>

<?= $this->phpdoc ?>
/**
 * Table <?= $this->dcatable . "\n" ?>
 */
$GLOBALS['TL_DCA']['<?= $this->dcatable ?>'] = [

    // Config
    'config'      => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id' => 'primary'
            ]
        ],
    ],
    'edit'        => [
        'buttons_callback' => [
            ['<?= $this->dcatable ?>', 'buttonsCallback']
        ]
    ],
    'list'        => [
        'sorting'           => [
            'mode'        => 2,
            'fields'      => ['title'],
            'flag'        => 1,
            'panelLayout' => 'filter;sort,search,limit'
        ],
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['<?= $this->dcatable ?>']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['<?= $this->dcatable ?>']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['<?= $this->dcatable ?>']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show'   => [
                'label'      => &$GLOBALS['TL_LANG']['<?= $this->dcatable ?>']['show'],
                'href'       => 'act=show',
                'icon'       => 'show.gif',
                'attributes' => 'style="margin-right:3px"'
            ],
        ]
    ],
    // Palettes
    'palettes'    => [
        '__selector__' => ['addSubpalette'],
        'default'      => '{first_legend},title,selectField,checkboxField,multitextField;{second_legend},addSubpalette'
    ],
    // Subpalettes
    'subpalettes' => [
        'addSubpalette' => 'textareaField',
    ],
    // Fields
    'fields'      => [
        'id'             => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'         => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'          => [
            'inputType' => 'text',
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'selectField'    => [
            'inputType' => 'select',
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'sorting'   => true,
            'reference' => $GLOBALS['TL_LANG']['<?= $this->dcatable ?>'],
            'options'   => ['firstoption', 'secondoption'],
            //'foreignKey'            => 'tl_user.name',
            //'options_callback'      => ['CLASS', 'METHOD'],
            'eval'      => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
            //'relation'  => ['type' => 'hasOne', 'load' => 'lazy']
        ],
        'checkboxField'  => [
            'inputType' => 'select',
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'sorting'   => true,
            'reference' => $GLOBALS['TL_LANG']['<?= $this->dcatable ?>'],
            'options'   => ['firstoption', 'secondoption'],
            //'foreignKey'            => 'tl_user.name',
            //'options_callback'      =>['CLASS', 'METHOD'],
            'eval'      => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
            //'relation'  => ['type' => 'hasOne', 'load' => 'lazy']
        ],
        'multitextField' => [
            'inputType' => 'text',
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'sorting'   => true,
            'eval'      => ['multiple' => true, 'size' => 4, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'addSubpalette'  => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'textareaField'  => [
            'inputType' => 'textarea',
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'sorting'   => true,
            'eval'      => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql'       => 'text NOT NULL'
        ]
    ]
];

/**
 * Class <?= $this->dcatable . "\n" ?>
 */
class <?= $this->dcatable ?> extends Contao\Backend
{

    /**
     * @param $arrButtons
     * @param \Contao\DC_Table $dc
     * @return mixed
     */
    public function buttonsCallback($arrButtons, Contao\DC_Table $dc)
    {
        if (Contao\Input::get('act') === 'edit')
        {
            $arrButtons['customButton'] = '<button type="submit" name="customButton" id="customButton" class="tl_submit customButton" accesskey="x">' . $GLOBALS['TL_LANG']['<?= $this->dcatable ?>']['customButton'] . '</button>';
        }

        return $arrButtons;
    }
}
