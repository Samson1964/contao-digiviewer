<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'Samson\DigiViewer',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'Samson\DigiViewer\DigiViewer' => 'system/modules/digiviewer/classes/DigiViewer.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_digiviewer' => 'system/modules/digiviewer/templates',
));
