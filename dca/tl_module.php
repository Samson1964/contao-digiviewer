<?php
/**
 * Avatar for Contao Open Source CMS
 *
 * Copyright (C) 2013 Kirsten Roschanski
 * Copyright (C) 2013 Tristan Lins <http://bit3.de>
 *
 * @package    Avatar
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Add palette to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['digiviewer_collection'] = '{title_legend},name,headline,type;{options_legend},digiviewer_collection;{expert_legend:hide},cssID,align,space';

$GLOBALS['TL_DCA']['tl_module']['fields']['digiviewer_collection'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['digiviewer_collection'],
	'exclude'          => true,
	'inputType'        => 'select',
	'foreignKey'       => 'tl_digiviewer.title',
	'eval'             => array
	(
		'tl_class'     => 'long'
	),
	'sql'              => "int(10) unsigned NOT NULL default '0'" 
);
