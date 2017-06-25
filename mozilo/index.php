<?php

/***************************************************************
* 
* Demo-Plugin für moziloCMS.
* 
* 
* Jedes moziloCMS-Plugin muß...
* - als Verzeichnis [PLUGINNAME] unterhalb von "plugins" liegen.
* - im Pluginverzeichnis eine plugin.conf mit den Plugin-
*   Einstellungen enthalten (diese kann auch leer sein).
* - eine index.php enthalten, in der eine Klasse "[PLUGINNAME]" 
*   definiert ist.
* 
* Die Plugin-Klasse muß...
* - von der Klasse "Plugin" erben ("class [PLUGINNAME] extends Plugin")
* - folgende Funktionen enthalten:
*   getContent($value)
*       -> gibt die HTML-Ersetzung der Plugin-Variable zurück
*   getConfig()
*       -> gibt die Konfigurationsoptionen als Array zurück
*   getInfo()
*       -> gibt die Plugin-Infos als Array zurück
* 
***************************************************************/
class DigiViewer extends Plugin {

  /***************************************************************
  * 
  * Gibt den HTML-Code zurück, mit dem die Plugin-Variable ersetzt 
  * wird. Der String-Parameter $value ist Pflicht, kann aber leer 
  * sein.
  * 
  ***************************************************************/
  function getContent($value) {
  
    global $URL_BASE;
    global $PLUGIN_DIR_NAME; 

    // Bei aktiver Suche nichts machen
    if(getRequestParam("action",true)=="search") return "";

    /***************************************************************
    * Beispiel: Zugriff auf Werte aus der plugin.conf über das 
    * lokale Properties-Objekt $this->settings
    ***************************************************************/

    // Lesend: Der Wert des Schlüssels "demosetting" wird aus der plugin.conf ausgelesen
    // return $this->settings->get("demosetting"); // zum Testen entkommentieren!
    // Schreibend: Die aktuelle Unixtime wird als "timestring" in die plugin.conf geschrieben ("timestring = 1234567890")
    // $this->settings->set("timestring", time()); // zum Testen entkommentieren!
  
    # Konfiguration einlesen bzw. Defaultwerte setzen
    $zoomwerte = "50,60,70,80,90,100,110,120";
    if($this->settings->get("dv_zoomwerte"))
      $zoomwerte = $this->settings->get("dv_zoomwerte"); 
    else $this->settings->set("dv_zoomwerte",$zoomwerte);

    $zoomstandard = 50;
    if($this->settings->get("dv_zoomstandard"))
      $zoomstandard = $this->settings->get("dv_zoomstandard"); 
    else $this->settings->set("dv_zoomstandard",$zoomstandard);
            
    $thumbbreite = 100;
    if($this->settings->get("dv_thumbbreite"))
      $thumbbreite = $this->settings->get("dv_thumbbreite"); 
    else $this->settings->set("dv_thumbbreite",$thumbbreite);

    $anzeigebreite = 750;
    if($this->settings->get("dv_anzeigebreite"))
      $anzeigebreite = $this->settings->get("dv_anzeigebreite"); 
    else $this->settings->set("dv_anzeigebreite",$anzeigebreite);

    $anzeigehoehe = 900;
    if($this->settings->get("dv_anzeigehoehe"))
      $anzeigehoehe = $this->settings->get("dv_anzeigehoehe"); 
    else $this->settings->set("dv_anzeigehoehe",$anzeigehoehe);

    $values = explode(",", $value);
  
    /***************************************************************
    * Beispiel: Sicheres Auslesen des POST- bzw. GET-Parameters 
    * "parameter" durch Aufruf der entsprechenden Hilfsfunktion der 
    * index.php 
    ***************************************************************/

    // return getRequestParam("parameter", true); // zum Testen entkommentieren!
  
  
    $ordner = $values[0];
    $prefix = $values[1];
    $laenge_prefix = strlen($prefix);
    $dateien = array();
    
    // Dateien einlesen
    $dir = $_SERVER["DOCUMENT_ROOT"].$ordner;

    // Thumbverzeichnis prüfen und ggfs. erstellen
    if(!is_dir($dir."thumbs/")) @mkdir($dir."thumbs/",0777);

    $d = dir($dir);
    while($file = $d->read()) {
      if(is_dir($dir.$file) && $file != "." && $file != "..") {
        // Nichts machen, Verzeichnis
      }
      elseif(is_file($dir.$file) && substr($file,0,$laenge_prefix)==$prefix) {
        // Datei gefunden, merken
        array_push($dateien,$file);
        // Thumbnaildatei prüfen und ggfs. erstellen/ändern
        $erstellen = false;
        if(!is_file($dir."thumbs/$file")) $erstellen = true;
        else {
          // Datei laden und Breite prüfen
          $imginfo = getimagesize($dir."thumbs/$file");
          if($imginfo[0]!=$thumbbreite) $erstellen = true;
        }
        if($erstellen) $this->thumbnailmaker($dir,$dir."thumbs/",$file,$thumbbreite);
      }
    }
    sort($dateien);
    
    $ausgabe = "";

    $ausgabe .= '<link rel="stylesheet" type="text/css" href="'.$URL_BASE.$PLUGIN_DIR_NAME.'/DigiViewer/dv/main.css">';
    $ausgabe .= '<!--[if lt IE 7]>';
    $ausgabe .= '<link rel="stylesheet" type="text/css" href="'.$URL_BASE.$PLUGIN_DIR_NAME.'/DigiViewer/dv/ie6.css" />';
    $ausgabe .= '<![endif]-->';
    $ausgabe .= '<link rel="stylesheet" type="text/css" href="'.$URL_BASE.$PLUGIN_DIR_NAME.'/DigiViewer/dv/digiviewer.css">';
    $ausgabe .= '<script src="'.$URL_BASE.$PLUGIN_DIR_NAME.'/DigiViewer/dv/prototype.js" type="text/javascript"></script>';
    $ausgabe .= '<script src="'.$URL_BASE.$PLUGIN_DIR_NAME.'/DigiViewer/dv/digiviewer.js" type="text/javascript"></script>';
    $ausgabe .= '<!--[if lt IE 7]>';
    $ausgabe .= '<style type="text/css">';
    $ausgabe .= '#dv_sidebar {height:900px}';
    $ausgabe .= '.slideWindow {height:900px}';
    $ausgabe .= '</style>';
    $ausgabe .= '<![endif]-->';
      
    $ausgabe .= '<div id="viewer_div" style="border: 1px solid gray; height: '.$anzeigehoehe.'px; width: '.$anzeigebreite.'px; position: relative; overflow: hidden;"></div>';

    $ausgabe .= "<script type=\"text/javascript\">\n";
    $ausgabe .= "viewer = new DigiViewer('viewer_div', {'useAnchors': true});\n";
    $ausgabe .= "viewer.addToNavbar( DV_NavSeparatorSpace() )\n;";
    $ausgabe .= "pager = new DV_Pager();\n";
    $ausgabe .= "viewer.addToNavbar( pager );\n";
    $ausgabe .= "viewer.addToNavbar( DV_NavSeparatorSpace() );\n";
    $ausgabe .= "viewer.addToNavbar( DV_NavSeparatorLine() );\n";
    $ausgabe .= "viewer.addToNavbar( DV_NavSeparatorSpace() );\n";
    $ausgabe .= "zoomer = new DV_Zoomer({'standard':".$zoomstandard.", 'steps':[".$zoomwerte."], 'wheelZoom': true});\n";
    $ausgabe .= "viewer.addToNavbar( zoomer );\n";
    $ausgabe .= "thumbs = new DV_Thumbs(".$thumbbreite.");\n";
    for($seite=0;$seite<count($dateien);$seite++) {
      $seitennummer = $seite+1;
      // Bildabmessungen ermitteln
      $imginfo = getimagesize($_SERVER["DOCUMENT_ROOT"].$ordner.$dateien[$seite]);
      $ausgabe .= "page$seite = new Page('$seite', {'href': '$ordner$dateien[$seite]', 'height': $imginfo[1], 'width': $imginfo[0], 'pageNum': $seitennummer});\n";
    }  
    //$ausgabe .= "page0 = new Page('0', {'href': '/mbarchiv/1990/1990-06-p01.jpg', 'height': 1745, 'width': 1200, 'pageNum': 1});\n";
    for($seite=0;$seite<count($dateien);$seite++) {
      $ausgabe .= "viewer.addPage(page$seite);\n";
    }  
    //$ausgabe .= "viewer.addPage(page0);\n";
    for($seite=0;$seite<count($dateien);$seite++) {
      $ausgabe .= "thumbs.addThumb(page$seite, '".$ordner."thumbs/$dateien[$seite]');\n";
    }  
    //$ausgabe .= "thumbs.addThumb(page0, '/mbarchiv/1990/thumbs/1990-06-p01.jpg');\n";
    $ausgabe .= "viewer.addToSidebar(thumbs);\n";
    $ausgabe .= "viewer.show();\n";
    $ausgabe .= "pager.connect(viewer);\n";
    $ausgabe .= "zoomer.connect(viewer);\n";
    $ausgabe .= "thumbs.connect(viewer);\n";
    $ausgabe .= "viewer.loadPage(page0);\n";
    $ausgabe .= "</script>\n";

    return $ausgabe;

  } // function getContent
  
  function thumbnailmaker($quelle,$ziel,$datei,$neueBreite) {

    // Bilddaten feststellen 
    $size=getimagesize($quelle.$datei); 
    $breite=$size[0]; 
    $hoehe=$size[1]; 
    
    // Neue Breite auf Höhe skalieren 
    $neueHoehe=intval($hoehe*$neueBreite/$breite); 
    
    if($size[2]==1) { 
      // GIF 
      $altesBild=ImageCreateFromGIF($quelle.$datei); 
      $neuesBild=ImageCreate($neueBreite,$neueHoehe); 
      ImageCopyResized($neuesBild,$altesBild,0,0,0,0,$neueBreite,$neueHoehe,$breite,$hoehe); 
      ImageGIF($neuesBild,$ziel.$datei); 
    } 
    
    elseif($size[2]==2) { 
      // JPG 
      $altesBild=ImageCreateFromJPEG($quelle.$datei); 
      $neuesBild=imagecreatetruecolor($neueBreite,$neueHoehe); 
      ImageCopyResized($neuesBild,$altesBild,0,0,0,0,$neueBreite,$neueHoehe,$breite,$hoehe); 
      ImageJPEG($neuesBild,$ziel.$datei,80); 
    } 
    
    elseif($size[2]==3) { 
      // PNG 
      $altesBild=ImageCreateFromPNG($quelle.$datei); 
      $neuesBild=imagecreatetruecolor($neueBreite,$neueHoehe); 
      ImageCopyResized($neuesBild,$altesBild,0,0,0,0,$neueBreite,$neueHoehe,$breite,$hoehe); 
      ImagePNG($neuesBild,$ziel.$datei); 
    } 
  
  }
  
  /***************************************************************
  * 
  * Gibt die Konfigurationsoptionen als Array zurück.
  * Ist keine Konfiguration nötig, ist dieses Array leer.
  * 
  ***************************************************************/
  function getConfig() {
    global $ADMIN_CONF;
    $language = $ADMIN_CONF->get("language");

    /***************************************************************
    * 
    * Details (Beispiele folgen weiter unten):
    * 
    * Die Funktion liefert ein Array zurück. Dieses enthält die 
    * Eingabefelder, mit denen der User im moziloAdmin Einstellungen 
    * am Plugin vornehmen kann.
    * Der "type"-Parameter der Eingabefelder bestimmt, um welche Art 
    * Eingabefeld es sich handelt und ist Pflicht. Folgende Werte
    * stehen zur Verfügung:
    *   text            Textfeld (beliebiger Text)
    *   textarea        mehrzeilige Texteingabe
    *   password        Passwortfeld (Anzeige des Inhalts als ***)
    *   checkbox        Checkbox (ja/nein)
    *   radio           Radio-Buttons (entweder/oder)
    *   select          Auswahlliste
    * 
    * Die Werte der Eingabefelder werden in die plugin.conf des 
    * Plugins geschrieben - der Name des Eingabefelds ist dabei der 
    * Schlüssel in der plugin.conf (siehe Beispiele).
    * 
    ***************************************************************/
    
    
    // Rückgabe-Array initialisieren
    // Das muß auf jeden Fall geschehen!
    $config = array();
    
    # nur eine Sprache ---------------------------------
    
    /***************************************************************
    * Beispiel: Normales Textfeld, beliebige Eingaben
    * - das Textfeld heißt "texteingabe"; gibt der Benutzer "abc" 
    *   ein und speichert die Plugin-Einstellungen, wird in der 
    *   plugin.conf folgende Zeile angelegt bzw. überschrieben:
    *   texteingabe = abc 
    ***************************************************************/
    
    $config['dv_zoomwerte']  = array(
        "type" => "text",                           
        "description" => "Zoomwerte in Prozent, getrennt durch Komma",
        "size" => "30",
     );

    $config['dv_zoomstandard']  = array(
        "type" => "text",                          
        "description" => "Standard-Zoomwert in Prozent", 
    );

    $config['dv_thumbbreite']  = array(
        "type" => "text",                          
        "description" => "Breite der Thumbnails in Pixeln", 
    );
        
    $config['dv_anzeigebreite']  = array(
        "type" => "text",                          
        "description" => "Breite des DigiViewer-Bereichs in Pixeln", 
    );
        
    $config['dv_anzeigehoehe']  = array(
        "type" => "text",                          
        "description" => "Höhe des DigiViewer-Bereichs in Pixeln", 
    );

    // Nicht vergessen: Das gesamte Array zurückgeben
    return $config;
      
  } // function getConfig    
  
  
  /***************************************************************
  * 
  * Gibt die Plugin-Infos als Array zurück - in dieser 
  * Reihenfolge:
  *   - Name und Version des Plugins
  *   - für moziloCMS-Version
  *   - Kurzbeschreibung
  *   - Name des Autors
  *   - Download-URL
  *   - Platzhalter für die Selectbox
  * 
  ***************************************************************/
  function getInfo() {
    global $ADMIN_CONF;
    $language = $ADMIN_CONF->get("language");
    # nur eine Sprache ---------------------------------
    $info = array(
        // Plugin-Name + Version
        "<b>DigiViewer</b> 0.1",
        // moziloCMS-Version
        "1.12",
        // Kurzbeschreibung nur <span> und <br /> sind erlaubt
        'Einbindung des DigiViewer von Jörg Breitbart (www.rockborn.de). Ideal für die Anzeige von Digitalisaten in den Formaten JPEG, PNG und GIF, die am Bildschirm verschoben und gezoomt werden können.<br /><br /><span style="font-weight:bold">Wichtiger Hinweis: Wenn Du das Plugin in einem anderen Webroot-Pfad als /plugins/ installiert hast, mußt Du in den Dateien dv/digiviewer.css, dv/digiviewer.js und dv/main.css die Pfade zu den Grafiken anpassen.<br /><br />Im Internet Explorer rutscht das Digitalisat nach unten und die Navigation oben links wird falsch dargestellt. Vielleicht hat jemand einen Tip für mich...</span><br /><br />Einbindung in Inhaltsseiten mit:<br /><span style="font-weight:bold">{DigiViewer|Pfad,Prefix}</span><br /><br />Pfad = Absoluter Pfad (im Webroot) des Verzeichnisses mit den Digitalisaten/Grafiken. Der Pfad muß mit einem / beginnen und mit einem / enden.<br />Prefix = Vorsilbe der anzuzeigenden Digitalisate/Grafiken im Pfad.<br /><br />Beispiel:<br /><span style="font-weight:bold">{DigiViewer|/mbarchiv/1996/,1996-06}</span><br /><br />Erklärung: Es werden alle Digitalisate/Grafiken aus dem Verzeichnis /mbarchiv/1996/ angezeigt, die mit den Zeichen (der Prefix) "1996-06" beginnen. Die Dateien werden in aufsteigender Reihenfolge angezeigt.',
        // Name des Autors
        "Frank Hoppe (Samson)",
        // Download-URL
        "http://moziloplugins.hehoe.de/",
        // Platzhalter für die Selectbox in der Editieransicht 
        // - ist das Array leer, erscheint das Plugin nicht in der Selectbox
        array(
            '{DigiViewer|/Pfad/,Prefix}' => 'Pfad = Absoluter Pfad der Digitalisate zum Webroot, beginnend und endend mit einem /; Prefix = Vorzeichen der anzuzeigenden Dateien'
        )
    );
    // Rückgabe der Infos.
    // Auch hier könnte man die Inhalte natürlich von der aktuell im Admin eingestellten 
    // Sprache abhängig machen - siehe getConfig().
    return $info;
      
  } // function getInfo

} // class DigiViewer

?>