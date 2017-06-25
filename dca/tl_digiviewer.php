<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   Elo
 * @author    Frank Hoppe
 * @license   GNU/LPGL
 * @copyright Frank Hoppe 2016
 */


/**
 * Table tl_digiviewer
 */
$GLOBALS['TL_DCA']['tl_digiviewer'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               	=> 'Table',
		'ctable'						=> array('tl_digiviewer_items'),
		'enableVersioning'            	=> true,
		'sql' => array
		(
			'keys' => array
			(
				'id' 				=> 'primary',
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('title'),
			'flag'                    => 1
		),
		'label' => array
		(
			'fields'                  => array('title'),
			'format'                  => '%s'
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_digiviewer']['edit'],
				'href'                => 'table=tl_digiviewer_items',
				'icon'                => 'edit.gif',
			),
			'editheader' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_digiviewer']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'header.gif',
			),  
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_digiviewer']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_digiviewer']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_digiviewer']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => array('tl_digiviewer', 'toggleIcon')
			),  
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_digiviewer']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Select
	'select' => array
	(
		'buttons_callback' => array()
	),

	// Edit
	'edit' => array
	(
		'buttons_callback' => array()
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'                => array(''),
		'default'                     => '{title_legend},title;{settings_legend},thumb_width,view_width,view_height,zoom_grades,zoom_default;{publish_legend},published'
	),

	// Subpalettes
	'subpalettes' => array
	(
		''                            => ''
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_digiviewer']['title'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => true, 
				'maxlength'           => 255
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		// Thumbnailbreite
		'thumb_width' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_digiviewer']['thumb_width'],
			'exclude'                 => true,
			'default'                 => 100,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'rgxp'                => 'digit',
				'mandatory'           => false, 
				'maxlength'           => 4
			),
			'sql'                     => "int(4) unsigned NOT NULL default '0'"
		),
		// Anzeigebreite
		'view_width' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_digiviewer']['view_width'],
			'exclude'                 => true,
			'default'                 => 750,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'rgxp'                => 'digit',
				'mandatory'           => false, 
				'tl_class'            => 'w50 clr',
				'maxlength'           => 4
			),
			'sql'                     => "int(4) unsigned NOT NULL default '0'"
		),
		// Anzeigehöhe
		'view_height' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_digiviewer']['view_height'],
			'exclude'                 => true,
			'default'                 => 900,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'rgxp'                => 'digit',
				'mandatory'           => false, 
				'tl_class'            => 'w50',
				'maxlength'           => 4
			),
			'sql'                     => "int(4) unsigned NOT NULL default '0'"
		),
		// Zoomstufen in Prozent
		'zoom_grades' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_digiviewer']['zoom_grades'],
			'exclude'                 => true,
			'default'                 => '50,60,70,80,90,100,110,120',
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => false, 
				'tl_class'            => 'w50',
				'maxlength'           => 255
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		// Standardzoom in Prozent
		'zoom_default' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_digiviewer']['zoom_default'],
			'exclude'                 => true,
			'default'                 => 50,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'rgxp'                => 'digit',
				'mandatory'           => false, 
				'tl_class'            => 'w50',
				'maxlength'           => 4
			),
			'sql'                     => "int(4) unsigned NOT NULL default '0'"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_digiviewer']['published'],
			'exclude'                 => true,
			'search'                  => false,
			'sorting'                 => false,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'isBoolean'           => true
			),
			'sql'                     => "char(1) NOT NULL default ''"
		), 
	)
);


/**
 * Provide miscellaneous methods that are used by the data configuration array
 */
class tl_digiviewer extends Backend
{
	
	/**
	 * Return the "toggle visibility" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
	    $this->import('BackendUser', 'User');
	
	    if (strlen($this->Input->get('tid')))
	    {
	        $this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 0));
	        $this->redirect($this->getReferer());
	    }
	
	    // Check permissions AFTER checking the tid, so hacking attempts are logged
	    if (!$this->User->isAdmin && !$this->User->hasAccess('tl_digiviewer::published', 'alexf'))
	    {
	        return '';
	    }
	
	    $href .= '&amp;id='.$this->Input->get('id').'&amp;tid='.$row['id'].'&amp;state='.$row[''];
	
	    if (!$row['published'])
	    {
	        $icon = 'invisible.gif';
	    }
	
	    return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}
	
	/**
	 * Disable/enable a user group
	 * @param integer
	 * @param boolean
	 */
	public function toggleVisibility($intId, $blnPublished)
	{
		// Check permissions to publish
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_digiviewer::published', 'alexf'))
		{
			$this->log('Not enough permissions to show/hide record ID "'.$intId.'"', 'tl_digiviewer toggleVisibility', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}
	
		$this->createInitialVersion('tl_digiviewer', $intId);
	
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_digiviewer']['fields']['published']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_digiviewer']['fields']['published']['save_callback'] as $callback)
			{
				$this->import($callback[0]);
				$blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
			}
		}
	
		// Update the database
		$this->Database->prepare("UPDATE tl_digiviewer SET tstamp=". time() .", published='" . ($blnPublished ? '' : '1') . "' WHERE id=?")
			->execute($intId);
		$this->createNewVersion('tl_digiviewer', $intId);
	}

}
