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
 * Table tl_digiviewer_items
 */
$GLOBALS['TL_DCA']['tl_digiviewer_items'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'             => 'Table',
		'ptable'					=> 'tl_digiviewer',
		'enableVersioning'          => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' 				=> 'primary',
				'pid'				=> 'index',
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('sorting'),
			'headerFields'            => array('title'), 
			'panelLayout'             => 'sort,filter;search,limit',
			'child_record_callback'   => array('tl_digiviewer_items', 'listItems'),
			'child_record_class'      => 'no_padding',
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
				'label'               => &$GLOBALS['TL_LANG']['tl_digiviewer_items']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_digiviewer_items']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_digiviewer_items']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif'
			), 
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_digiviewer_items']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_digiviewer_items']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => array('tl_digiviewer_items', 'toggleIcon')
			),  
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_digiviewer_items']['show'],
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
		'default'                     => '{title_legend},title;{settings_legend},singleSRC,text;{publish_legend},published'
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
		'pid' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'sorting' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		), 
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_digiviewer_items']['title'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => true, 
				'maxlength'           => 255
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'singleSRC' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_digiviewer_items']['singleSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array
			(
				'filesOnly'           => true, 
				'fieldType'           => 'radio', 
				'mandatory'           => true, 
				'tl_class'            => 'clr'
			),
			'sql'                     => "binary(16) NULL"
		), 
		'text' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_digiviewer_items']['text'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => array
			(
				'mandatory'           => false, 
				'rte'                 => 'tinyMCE', 
			),
			'sql'                     => "mediumtext NULL"
		), 
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_digiviewer_items']['published'],
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
class tl_digiviewer_items extends Backend
{
	 
	/**
	 * Generiere eine Zeile als HTML
	 * @param array
	 * @return string
	 */
	public function listItems($arrRow)
	{
		$line = '';
		$line .= '<div>';
		if($arrRow['singleSRC'])
		{
			$objFile = \FilesModel::findByPk($arrRow['singleSRC']);
			$thumbnail = Image::get($objFile->path, 70, 'proportional');
			$line .= '<img src="'.$thumbnail.'" style="float:left; margin-right:5px;">';
		}
		$line .= '<b>'.$arrRow['title'].'</b>';
		$line .= "</div>";
		$line .= "\n";
		return($line);
	
	}
	
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
	    if (!$this->User->isAdmin && !$this->User->hasAccess('tl_digiviewer_items::published', 'alexf'))
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
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_digiviewer_items::published', 'alexf'))
		{
			$this->log('Not enough permissions to show/hide record ID "'.$intId.'"', 'tl_digiviewer_items toggleVisibility', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}
	
		$this->createInitialVersion('tl_digiviewer_items', $intId);
	
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_digiviewer_items']['fields']['published']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_digiviewer_items']['fields']['published']['save_callback'] as $callback)
			{
				$this->import($callback[0]);
				$blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
			}
		}
	
		// Update the database
		$this->Database->prepare("UPDATE tl_digiviewer_items SET tstamp=". time() .", published='" . ($blnPublished ? '' : '1') . "' WHERE id=?")
			->execute($intId);
		$this->createNewVersion('tl_digiviewer_items', $intId);
	}

}
