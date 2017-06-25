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
 * BACK END MODULES
 *
 * Back end modules are stored in a global array called "BE_MOD". You can add
 * your own modules by adding them to the array.
 *
 * Not all of the keys mentioned above (like "tables", "key", "callback" etc.)
 * have to be set. Take a look at the system/modules/core/config/config.php
 * file to see how back end modules are configured.
 */

$GLOBALS['BE_MOD']['content']['digiviewer'] = array
(
   'tables'       	=> array('tl_digiviewer', 'tl_digiviewer_items'),
   'icon'         	=> 'system/modules/digiviewer/assets/images/icon.png',
);

/**
 * FRONT END MODULES
 */

$GLOBALS['FE_MOD']['digiviewer'] = array
(
	'digiviewer_collection' => 'Samson\DigiViewer\DigiViewer',
);  
 