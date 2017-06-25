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
 * Namespace
 */
namespace Samson\DigiViewer;

/**
 * Class DigiViewer
 *
 * @copyright  Frank Hoppe 2016
 * @author     Frank Hoppe
 * @package    Devtools
 */
class DigiViewer extends \Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_digiviewer';

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### DIGIVIEWER ###';
			$objTemplate->title = $this->name;
			$objTemplate->id = $this->id;

			return $objTemplate->parse();
		}
		
		return parent::generate(); // Weitermachen mit dem Modul
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		$this->import('Database');

		// Stammdaten der Bildsammlung laden
		$objMain = $this->Database->prepare('SELECT * FROM tl_digiviewer WHERE published = ? AND id = ?')
		                ->execute(1, $this->digiviewer_collection);

		// Bilder der Bildsammlung laden
		$objResult = $this->Database->prepare('SELECT * FROM tl_digiviewer_items WHERE published = ? AND pid = ? ORDER BY sorting ASC')
		                  ->execute(1, $this->digiviewer_collection);

		$arrData = array();
		$i = 0;
		// Datensätze einlesen
		if($objResult->numRows > 1)
		{
			// Datensätze zuweisen
			while($objResult->next()) 
			{
				$objFile = \FilesModel::findByPk($objResult->singleSRC);
				$thumbnail = \Image::get($objFile->path, $objMain->thumb_width, 'proportional');
				$arrData[] = array
				(
					'index'     => $i,
					'page'      => $i + 1,
					'title'     => $objResult->title,
					'image'     => $objFile->path,
					'thumbnail' => $thumbnail,
					'text'      => $objResult->text,
				);
				$i++;
			}
		}
		
		$this->Template->headline = $objMain->title;
		$this->Template->view_width = $objMain->view_width;
		$this->Template->view_height = $objMain->view_height;
		$this->Template->thumb_width = $objMain->thumb_width;
		$this->Template->zoom_default = $objMain->zoom_default;
		$this->Template->zoom_grades = $objMain->zoom_grades;
		$this->Template->daten = $arrData;

	}
}
