<?php
/**
 * Plugin g5newsletter: to use the Newsletter function with G5-Scripts.de PHP Newsletter Script
 *  
 * i wrote this plug in for DE, my sister in law ;-)  
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *             and see G5-Scripts.de 
 * @author     Mark Wolfgruber <mark@600infos.de>
 * 
 * Version 18.06.2011 Mark Wolfgruber
 *   
 * open todo:
 *   - multilingual, using lang vars
 *   - special functions only viewed for @user or @admin 
 *     like countusers for a category or view the mailadresses of the users  
 *   - if nessesary, i will try to add function for reading 
 *     - categories
 *     - users mail addresses
 *     - add title, first name, family name, mailaddress to an extra file
 *     - write a logfile for subscribe and unsubscripe     
 *     
 *  Help are welcome, plese write a mail to me.
 *      
 */
 
// must be run within DokuWiki
if(!defined('DOKU_INC')) die();
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_g5newsletter extends DokuWiki_Syntax_Plugin {
 
    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Mark Wolfgruber',
            'email'  => 'mark@600infos.de',
            'date'   => '2011-06-22',
            'name'   => 'G5 Newsletter Plugin',
            'desc'   => 'add a Newsletter function with G5-Scripts.de PHP Newsletter Script',
            'url'    => 'http://www.dokuwiki.org/plugin:tutorial',
        );
    }
    function getType() { return 'substition'; }
    function getSort() { return 305; }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) { $this->Lexer->addSpecialPattern('{{g5newsletter.*?}}',$mode,'plugin_g5newsletter'); }

//     function postConnect() { $this->Lexer->addExitPattern('<hr>','plugin_g5newsletter'); }
    
 
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        // $match = substr($match, 2, -2);
        // list($g5newsletter, $param) = explode(' ',$match,2);
        // set defaults
        $_PLUGIN_URL=DOKU_URL; $_PLUGIN_URL.='/lib/plugins/'.$this->getPluginName();
        $data = array(
                    'nlfile'    => DOKU_PLUGIN.$this->getPluginName().'/newsletter.php',
                    'protected'    => DOKU_PLUGIN.$this->getPluginName().'/protected',
//                    'script_url' => 'http://domain.org/htdocs/lib/plugins/g5newsletter',
                    'script_url' => $_PLUGIN_URL,
                    'pluginname' => $this->getPluginName(),
                    'g5newsletter'    => false,
                    'letterarchiv' => false,
                    'admin' => 'off',
                );

            if(preg_match('/(archiv|letterarchiv)/',$match)){
                $data['form'] = true;
                $data['letterarchiv'] = true;
            }
            if(preg_match('/(small|short)/',$match)){
                $data['form'] = true;
                $data['small'] = true;
            }
            if(preg_match('/subscribe/',$match)){
                $data['form'] = true;
                if(preg_match('/(\ssubscribe)/',$match)){
                    $data['subscribe'] = 'on';
                }
                if(preg_match('/unsubscribe/',$match)){
                    $data['subscribe'].= 'off';
                }
            }
            if(preg_match('/size/',$match)) {
                $data['form'] = true;
                $data['size']=(preg_match("/size=\"?(\d*)\"?/",$match,$valarray))?$valarray[1]:'';
            }
            if(preg_match('/space/',$match)) {
                $data['form'] = true;
                $data['space']=(preg_match("/space=\"?(\d*)\"?/",$match,$valarray))?$valarray[1]:'';
            }
            // category have to be the last entry and the value encapsulated with "" 
            if(preg_match('/category/',$match)) {
                $data['form'] = true;
                $data['category']=(preg_match("/category=\"(.*)\"}}/",$match,$valarray))?$valarray[1]:'';
            }
            
            /** if newsletter.php exist set $g5newsletter=1 **/
            if (file_exists($data['nlfile']) ) {
              $data['g5newsletter'] = true;
            } else {
              $data['g5newsletter'] = false;
            }
              $data['match'] = true;            
            if(preg_match('/(errormsg|errormessage)/',$match)){
                $data['errormsg'] = true;
                $data['form'] = false;
            }
            return $data;
    }
 
    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
      $_PLUGIN_URL=DOKU_URL; $_PLUGIN_URL.='/lib/plugins/'.$this->getPluginName();
        if($mode != 'xhtml') return false;

 /*
 echo "<pre>"; print_r($data); 
 // print_r($_SERVER); 
 echo "</pre>"; /** only for testing **/
            
        if($data['g5newsletter']==true) {

          if($data['form']==true) {
              $output= ''; // reset output
              $script_pfad=DOKU_PLUGIN.$this->getPluginName();
              $output.='<span style="font-family:"Courier New, Courier">';    
              $output.='<form name="letter" method="post" action="'.$_PLUGIN_URL.'/newsletter.php">';
              if($data['small']) {
                  $size=($data['size'])?$data['size']:'15';
                  $output.='E-Mail:<br/><input type="text" name="email" size="'.$size.'">';
              } else {
                  $size=($data['size'])?$data['size']:'40';
                  $output.='E-Mail:&nbsp;';
                  $output.='<input type="text" name="email" size="'.$size.'">&nbsp;';
              }
              
              /**
               *  want do create a selectbox by reading the cat-files 
               *  if more than one cat is available
               *  or i can use the admin config menue later               
               **/
               
                /*        echo 'welche News m&ouml;chten Sie erhalten? ';
                              <select name="cat">
                                    <option value="default_newsletter">Standard Newsletter</option>
                              </select>';  /**/
                              
              if($data['category']) {
                  $output.='<input type="hidden" name="cat" value="'.$data['category'].'" >';
              } else {
                  $output.='<input type="hidden" name="cat" value="default_newsletter" >';
              }
                         

              if($data['space']) $output.='<span style="margin-left:'.$data["space"].'em; ">';
              if($data['subscribe']=='onoff' || !$data['subscribe'] ) {
                  if($data['small']) $output.='<br />';
                  $output.='<input type="radio" name="ac" value="eintragen" checked> Eintragen&nbsp;';                  
                  if($data['small']) $output.='<br />';
                  $output.='<input type="radio" name="ac" value="austragen"> Austragen&nbsp;';
                  if($data['small']) $output.='<br />';
                  $output.='<input type="submit" name="btn" value="senden">'; 
              } else if($data['subscribe']=='on') {
                  $output.='<input type="hidden" name="ac" value="eintragen">';                  
                  // if($data['small']) $output.='<br />';
                  $output.='<input type="submit" name="btn" value="Eintragen">'; 
              } else if($data['subscribe']=='off') {
                  $output.='<input type="hidden" name="ac" value="austragen"> ';
                  // if($data['small']) $output.='<br />';
                  $output.='<input type="submit" name="btn" value="Austragen">'; 
              } 

              if($data['space']) $output.='</span>';
              $output.='</form>';
              $output.='</span>';
              $renderer->doc .=$output;
            
          } elseif($data['letterarchiv']) {
            // global $script_url, $script_pfad, $in;
            global $wtrmrk, $script_url, $cat;

                if(file_exists($data['protected'].'/variablen.php'))    {require_once($data['protected'].'/variablen.php');    }
                if(file_exists($data['protected'].'/kategorien.php'))   {require_once($data['protected'].'/kategorien.php');   }
                if(file_exists($data['protected'].'/data/betreffs.php')){require_once($data['protected'].'/data/betreffs.php');}
                if(file_exists($data['protected'].'/subs.php'))         {require_once($data['protected'].'/subs.php');         } 
              // Letterachiv	
              $output= ''; // reset output
              $script_pfad=DOKU_PLUGIN.$this->getPluginName();
              $output.='<span style="font-family: Arial, Helvetica, sans-serif">';
              // show a newsletter specified by the newsletterid
              if($_GET['newsletterid']) {
                if (file_exists($script_pfad.'/protected/letters/'.$_GET['newsletterid'])) {
                    $letterfile=file_exists($script_pfad.'/protected/letters/'.$_GET['newsletterid']) ? file($script_pfad.'/protected/letters/'.$_GET['newsletterid']) : array();
                    $fileformat =preg_replace("/(\015\012)+|(\015)+|(\012)+/i","",$letterfile[2]);
                    if ($fileformat == "text") {
                        /** need to display correctly if using UTF-8 / ISO 8859-1 **/
                        for($row=0;$row<sizeof($letterfile);$row++) {
                         $letterfile[$row]=convert2html_entities($letterfile[$row]);
                        }
                    }
                    $lettercategorie=preg_replace("/(\015\012)+|(\015)+|(\012)+/i","",$letterfile[1]);
                    $subject=preg_replace("/(\015\012)+|(\015)+|(\012)+/i","",$letterfile[3]);
    
                    $output.='<h2>
                      Newsletter-ID: '.$_GET['newsletterid'].' Format: '.$fileformat.'
                      </h2>
                      <p align="right"><a href="'.$_SERVER['SCRIPT_NAME'].'?id='.$_GET['id'].'"> [ Zur&uuml;ck ] </a><p>
                      <h3 style="font-family: Arial, Helvetica, sans-serif !important;">
                        Kategorie: '.$cat[$lettercategorie].'<br/>
                        Verschickt am: '.date("d.m.Y H:i",$_GET['newsletterid']).'<br/>
                        Betreff: '.$subject.'
                      </h3>
                      <b>Inhalt:</b><br /> ';
                    // display the content of the letter
                    for($row=4;$row<sizeof($letterfile);$row++) {
                        $output.= ($fileformat == "text") ? (preg_replace("/\r\n|\n\r|\r|\n/s", "<br />", $letterfile[$row])) : $letterfile[$row];
                    }
                } else {
                    // used if the index file is not correct, because the newsletter was deleted manuelly
                    $output.='<h2>
                      Newsletter-ID: '.$_GET['newsletterid'].'
                      </h2>
                      <p align="right"><a href="'.$_SERVER['SCRIPT_NAME'].'?id='.$_GET['id'].'"> [ Zur&uuml;ck ] </a><p>
                      <h3 style="font-family: Arial, Helvetica, sans-serif !important;">
                        Kategorie: unbekannt<br/>
                        Verschickt am: '.date("d.m.Y H:i",$_GET['newsletterid']).'<br/>
                        Betreff: unbekannt
                      </h3>
                      <b>Inhalt:</b><br /><br /><br /><hr><br /><br /><center>
                          NEWSLETTER WURDE BEREITS ENTFERNT
                      </center><br /><br /><hr><br /><br /> ';                
                }
              } else {
              // show a overview list of the newsletters
                $output=''; // reset output
                if(isset($cat)){asort($cat);}
                for($countcat=0;$countcat<sizeof($cat);$countcat++) {
                    $usersfile=file_exists($script_pfad.'/protected/abonnenten/'.key($cat).'.txt') ? file($script_pfad.'/protected/abonnenten/'.key($cat).'.txt') : array();
                    $countusers=count($usersfile); 
                    $letters = saGetFileContent($script_pfad."/protected/letters/".key($cat).".idx");
                    $output.='<h2>Kategorie: '.$cat[key($cat)];
                    $output.='<br/>Abonennten: '.$countusers; /** may deactivated or only shown if user is registerd **/
                    $output.='</h2>' ;
                    if (!empty($letters)) {
                      $output.= '<ul>';
                      foreach($letters as $line){
                          list($time,$betreff)=explode("|", $line);
                          $output.= '<a href="'.$_SERVER['REQUEST_URI'].'&newsletter=show&newsletterid='.$time.'"> <li>'.date("d.m.Y H:i",$time).' '.$betreff.' </li></a>';
                          
                      }
                      $output.= '</ul>';
                    } else { 
                       $output.= '<p>Keine Newsletter in dieser Kategorie vorhanden</p>' ;
                    }
                    next($cat);
                }
              }
              $output.='</span>';
              $renderer->doc .=$output;
            } elseif($data['errormsg']) {
                if (!empty($_GET["errormsg"])){
                    $errormsg = $_GET["errormsg"];
                }elseif (!empty($_POST["errormsg"])){
                    $errormsg = $_POST["errormsg"];
                }else{
                    $errormsg = 'No ERROR to display here at the moment';
                }
                $output=convert2html_entities($errormsg);
                $renderer->doc .=$output;
            } else {
                        if (file_exists($data['nlfile']) ) {
            // global $script_url, $script_pfad, $in;
            global $wtrmrk, $script_url, $cat;

                if(file_exists($data['protected'].'/variablen.php'))    {require_once($data['protected'].'/variablen.php');    }
                if(file_exists($data['protected'].'/kategorien.php'))   {require_once($data['protected'].'/kategorien.php');   }
                if(file_exists($data['protected'].'/data/betreffs.php')){require_once($data['protected'].'/data/betreffs.php');}
                if(file_exists($data['protected'].'/subs.php'))         {require_once($data['protected'].'/subs.php');         } 
                $wtrmrk ='will be loaded'; 
                echo '<div class="g5newsletter"><h1>Newsletter</h1>'; 
                 require_once($data['nlfile']); // not nice but works 
                echo '</div>'; 
              } else { 
                $renderer->doc .= '<hr><b>ERROR, can not load <br />';     
                $renderer->doc .= $data['nlfile'].'</b><br />'; 
                $renderer->doc .= 'Please install g5-scripts.de newsletter.php</b> first<hr>';        
              } /**/
              //  $renderer->doc .= '<b>ERROR: Newsletter-Plugin need a command!</b>';   
            } 

          } else {
              $renderer->doc .= '<hr>Download G5 Newsletter from <a href="http://www.g5-scripts.de" target=_self>g5-scripts.de</a><br/>';
              $renderer->doc .= 'and install it to <b>'.$data['nlfile'].'</b> first</hr>';
    
            }
            return true;
    }
}

// if using DE (german) UTF-8
function convert2html_entities($str) {
    $html_entities = array ( 
      chr('34') => '&quot;',     // Anfhrungszeichen oben
      chr('38') => '&amp;',      // Ampersand-Zeichen, kaufm„nnisches Und
      chr('60') => '&lt;',       // ”ffnende spitze Klammer
      chr('62') => '&gt;',       // schlieáende spitze Klammer
      chr('128') => '&euro;',    // euro sign
      chr('160') => '&nbsp;',    // erzwungenes Leerzeichen
      chr('161') => '&iexcl;',   // umgekehrtes Ausrufezeichen
      chr('162') => '&cent;',    // Cent-Zeichen
      chr('163') => '&pound;',   // Pfund-Zeichen
      chr('164') => '&curren;',  // W„hrungszeichen
      chr('165') => '&yen;',     // Yen-Zeichen
      chr('166') => '&brvbar;',  // durchbrochener Strich
      chr('167') => '&sect;',    // Paragraph-Zeichen
      chr('168') => '&uml;',     // Pnktchen oben
      chr('169') => '&copy;',    // Copyright-Zeichen
      chr('170') => '&ordf;',    // Ordinal-Zeichen weiblich
      chr('171') => '&laquo;',   // angewinkelte Anfhrungszeichen links
      chr('172') => '&not;',     // Verneinungs-Zeichen
      chr('173') => '&shy;',     // bedingter Trennstrich
      chr('174') => '&reg;',     // Registriermarke-Zeichen
      chr('175') => '&macr;',    // šberstrich
      chr('176') => '&deg;',     // Grad-Zeichen
      chr('177') => '&plusmn;',  // Plusminus-Zeichen
      chr('178') => '&sup2;',    // hoch-2-Zeichen
      chr('179') => '&sup3;',    // hoch-3-Zeichen
      chr('180') => '&acute;',   // Akut-Zeichen
      chr('181') => '&micro;',   // Mikro-Zeichen (griechisches m)
      chr('182') => '&para;',    // Absatz-Zeichen
      chr('183') => '&middot;',  // Mittelpunkt
      chr('184') => '&cedil;',   // H„kchen unten
      chr('185') => '&sup1;',    // hoch-1-Zeichen
      chr('186') => '&ordm;',    // Ordinal-Zeichen m„nnlich
      chr('187') => '&raquo;',   // angewinkelte Anfhrungszeichen rechts
      chr('188') => '&frac14;',  // ein Viertel
      chr('189') => '&frac12;',  // ein Halb
      chr('190') => '&frac34;',  // drei Viertel
      chr('191') => '&iquest;',  // umgekehrtes Fragezeichen
      chr('192') => '&Agrave;',  // A mit Accent Grave
      chr('193') => '&Aacute;',  // A mit Accent Aigu
      chr('194') => '&Acirc;',   // A mit Zirkumflex
      chr('195') => '&Atilde;',  // A mit Tilde
      chr('196') => '&Auml;',    // A Umlaut
      chr('197') => '&Aring;',   // A mit Ring
      chr('198') => '&AElig;',   // A mit legiertem E
      chr('199') => '&Ccedil;',  // C mit H„kchen
      chr('200') => '&Egrave;',  // E mit Accent Grave
      chr('201') => '&Eacute;',  // E mit Accent Aigu
      chr('202') => '&Ecirc;',   // E mit Zirkumflex
      chr('203') => '&Euml;',    // E Umlaut
      chr('204') => '&Igrave;',  // I mit Accent Grave
      chr('205') => '&Iacute;',  // I mit Accent Aigu
      chr('206') => '&Icirc;',   // I mit Zirkumflex
      chr('207') => '&Iuml;',    // I Umlaut
      chr('208') => '&ETH;',     // groáes Eth
      chr('209') => '&Ntilde;',  // N mit Tilde
      chr('210') => '&Ograve;',  // O mit Accent Grave
      chr('211') => '&Oacute;',  // O mit Accent Aigu
      chr('212') => '&Ocirc;',   // O mit Zirkumflex
      chr('213') => '&Otilde;',  // O mit Tilde
      chr('214') => '&Ouml;',    // O Umlaut
      chr('215') => '&times;',   // Mal-Zeichen
      chr('216') => '&Oslash;',  // O mit schr„gstrich
      chr('217') => '&Ugrave;',  // U mit Accent Grave
      chr('218') => '&Uacute;',  // U mit Accent Aigu
      chr('219') => '&Ucirc;',   // U mit Zirkumflex
      chr('220') => '&Uuml;',    // U Umlaut
      chr('221') => '&Yacute;',  // Y mit Accent Aigu
      chr('222') => '&THORN;',   // groáes Thorn
      chr('223') => '&szlig;',   // scharfes s
      chr('224') => '&agrave;',  // a mit Accent Grave
      chr('225') => '&aacute;',  // a mit Accent Aigu
      chr('226') => '&acirc;',   // a mit Zirkumflex
      chr('227') => '&atilde;',  // a mit Tilde
      chr('228') => '&auml;',    // a Umlaut
      chr('229') => '&aring;',   // a mit Ring
      chr('230') => '&aelig;',   // a mit legiertem e
      chr('231') => '&ccedil;',  // c mit H„kchen
      chr('232') => '&egrave;',  // e mit Accent Grave
      chr('233') => '&eacute;',  // e mit Accent Aigu
      chr('234') => '&ecirc;',   // e mit Zirkumflex
      chr('235') => '&euml;',    // e Umlaut
      chr('236') => '&igrave;',  // i mit Accent Grave
      chr('237') => '&iacute;',  // i mit Accent Aigu
      chr('238') => '&icirc;',   // i mit Zirkumflex
      chr('239') => '&iuml;',    // i Umlaut
      chr('240') => '&eth;',     // kleines Eth
      chr('241') => '&ntilde;',  // n mit Tilde
      chr('242') => '&ograve;',  // o mit Accent Grave
      chr('243') => '&oacute;',  // o mit Accent Aigu
      chr('244') => '&ocirc;',   // o mit Zirkumflex
      chr('245') => '&otilde;',  // o mit Tilde
      chr('246') => '&ouml;',    // o Umlaut
      chr('247') => '&divide;',  // Divisions-Zeichen
      chr('248') => '&oslash;',  // o mit schr„gstrich
      chr('249') => '&ugrave;',  // u mit Accent Grave
      chr('250') => '&uacute;',  // u mit Accent Aigu
      chr('251') => '&ucirc;',   // u mit Zirkumflex
      chr('252') => '&uuml;',    // u Umlaut
      chr('253') => '&yacute;',  // y mit Accent Aigu
      chr('254') => '&thorn;',   // kleines Thorn
      chr('255') => '&yuml;',    // y Umlaut
    );
    foreach ($html_entities as $key => $value) {
        $str = str_replace($key, $value, $str);
    }
    
    return $str;
}

?>
