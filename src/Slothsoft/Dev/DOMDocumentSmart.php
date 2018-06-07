<?php
declare(strict_types = 1);
namespace Slothsoft\Dev;

use DOMAttr;
use DOMCdataSection;
use DOMCharacterData;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMText;
use DOMXPath;
use Exception;
use XSLTProcessor;

/**
 * *********************************************************************
 * DOMDocument Erweiterung v1.33 19.01.2016 © Daniel Schulz
 *
 * Changelog:
 * v1.33 19.01.2016
 * construct //, ENT_COMPAT | ENT_HTML401, self::DEFAULT_CHARSET);
 * v1.32 10.12.2015
 * public static function loadSearchArray(DOMDocument $doc, array $search)
 * v1.31 15.04.2015
 * PHP 5.4
 * construct()
 * get_html_translation_table(HTML_ENTITIES, ENT_COMPAT | ENT_HTML401, self::DEFAULT_CHARSET);
 * parseHTML()
 * <html><head><meta charset="%s"></head><body>%s</body></html>
 * v1.30 21.11.2013
 * public static function translateArray(array $inputList, array $wordList)
 * getTextFromNode(DOMNode $Node, $showComments = false) $showComments
 * v1.29 10.09.2012
 * parseTemplate TEMPL_DATADOC_ERR, TEMPL_TEMPLDOC_ERR
 * v1.28 03.08.2012
 * parseHTML
 * saveXHTML
 * self::loadFile
 * v1.27 16.05.2012
 * output($xmlDoc, $xslDoc = null, $debug = false, $outputURI = 'php://output')
 * v1.26 11.05.2012
 * DOMDocumentFragment loadXHTML(DOMDocument $doc, $xhtml)
 * DOMNode setNamespaceURI(DOMNode $sourceNode, $namespaceURI = null)
 * const NS_HTML, NS_XSL
 * v1.25 30.04.2012
 * getTextFromNode(DOMNode $Node, $showComments = false)
 * v1.24 10.04.2012
 * arr2dom(DOMDocument $doc, $tagName, $structure, $assumeWellformed = false)
 * v1.23 04.04.2012
 * importLanguage(DOMDocument $doc, $file, $lang_id)
 * saveXHTML(DOMDocument $doc, DOMNode $node = null)
 * parseTemplate(DOMDocument $dataDoc, DOMDocument $templateDoc, $langFile = null, $langId = null)
 * transform($xmlDoc, $xslDoc, $returnAsString = false)
 * loadFile($file, $asHTML = false)
 * v1.22 10.08.2011
 * output($xmlDoc, $xslDoc, $debug = false)
 * v1.21 02.02.2011
 * parseHTML
 * html_entity_decode($entity_keys)
 * v1.20 01.02.2011
 * parseHTML
 * try { loadXML } catch { loadHTML }
 * v1.19 01.02.2011
 * public static string getTextFromNode(DOMNode)
 * v1.18 28.01.2011
 * replaceContent
 * if($Node->ownerElement)
 * v1.17 07.01.2011
 * loadTemplateTextNodes
 * ($data=utf8_decode($Root->data))
 *
 * Template Usage:
 *
 * $Document->loadTemplate($templateFile); an $templateFile wird noch der String ".templ" angehangen, um die Template-Datei zu finden
 * $Document->loadElements($placeholderNodes); $placeholderNodes ist ein array mit der Struktur PlatzhalterSchlüssel => Platzhalterknoten
 * $Document->loadLanguage($templateFile, $lang_id) an $templateFile wird noch der String "." und der Sprach-Schlüssel ("de") angehangen, um die Sprach-Datei zu finden
 *
 * echo $Document->saveHTML($keepPlaceholder); ist $keepPlaceholder wahr, werden die noch im HTML stehenden ##Platzhalter## nicht entfernt
 *
 *
 * *********************************************************************
 */
DOMDocumentSmart::construct();

class DOMDocumentSmart extends DOMDocument
{

    private $replaceNodes;

    const NS_HTML = 'http://www.w3.org/1999/xhtml';

    const NS_XSL = 'http://www.w3.org/1999/XSL/Transform';

    const DEFAULT_CHARSET = 'UTF-8';

    const TEMPL_SIGN = '##';

    // Zeichen für Template Platzhalter Begin/Ende
    const TEMPL_SIGN_LENGTH = 2;

    // Länge des Template-Zeichens
    const TEMPL_LIST = 'tm_list';

    // Attribute für Listen-Templates
    const TEMPL_COND = 'tm_cond';

    // Attribute für if-statements
    const TEMPL_TEMPFILE_END = '.templ';

    const TEMPL_TEMPFILE_ERR = 'Template File not found: ';

    const TEMPL_LANGFILE_END = '.';

    const TEMPL_LANGFILE_ERR = 'Language File not found: ';

    const TEMPL_XMLPARSE_ERR = 'Invalid XML, trying HTML: ';

    const TEMPL_DATADOC_ERR = 'Could not read Data File "%s" into DOMDocument!';

    const TEMPL_TEMPLDOC_ERR = 'Could not read Template File "%s" into DOMDocument!';

    private static $DTD = array( // definiert alle Attribute, ihre möglichen Parameter und den Datentyp ihrer Werte, wenn vorhanden
        'tm_list' => array(
            'repeat' => null, // standalone
            'atleast' => 0, // datentyp int
            'atmost' => 0,
            'odd' => null,
            'even' => null,
            'startwith' => 0
        ),
        'tm_cond' => array(
            'if' => true, // datentyp bool
            'ifnot' => true,
            'else' => null,
            'elseif' => true
        )
    );

    public static $entity_list = array( // Entities, die benutzt werden sollen aber nicht in der Standard-Übersetzungstabelle von htmlentities() stehen
        '€' => '&euro;'
    );

    public static $entity_keys = array();

    // Gegenstück zu $entity_list, um &entities; zu UTF-8 zu konvertieren
    public static function construct()
    {
        $entity_whitelist = get_html_translation_table(HTML_ENTITIES); // , ENT_COMPAT | ENT_HTML401, self::DEFAULT_CHARSET);
        $entity_blacklist = get_html_translation_table(HTML_SPECIALCHARS); // , ENT_COMPAT | ENT_HTML401, self::DEFAULT_CHARSET);
        foreach ($entity_whitelist as $key => $val) {
            if (! isset($entity_blacklist[$key]))
                self::$entity_list[$key] = $val;
        }
        self::$entity_keys = array_flip(self::$entity_list);
    }

    public static function output($xmlDoc, $xslDoc = null, $debug = false, $outputURI = 'php://output')
    {
        if (! ($xmlDoc instanceof DOMDocument)) {
            $xmlDoc = self::loadFile($xmlDoc);
        }
        $charset = self::DEFAULT_CHARSET;
        if (isset($_SERVER['HTTP_ACCEPT']) and ! (stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml'))) {
            $mime = 'text/html';
            $method = 'html';
            $version = '5.0';
        } else {
            $mime = 'application/xhtml+xml';
            $method = 'xml';
            $version = '1.0';
        }
        if (! headers_sent()) {
            header('Content-Type: ' . $mime . '; charset=' . $charset);
        }
        if ($xslDoc === null) {
            $xmlDoc->save($outputURI);
        } else {
            if (! ($xslDoc instanceof DOMDocument)) {
                $xslDoc = self::loadFile($xslDoc);
            }
            $outputElements = $xslDoc->getElementsByTagNameNS(self::NS_XSL, 'output');
            foreach ($outputElements as $outputElement) {
                $outputElement->setAttribute('media-type', $mime);
                $outputElement->setAttribute('method', $method);
                $outputElement->setAttribute('encoding', $charset);
                $outputElement->setAttribute('version', $version);
            }
            $xslt = new XSLTProcessor();
            $xslt->importStylesheet($xslDoc);
            if ($debug) {
                $finalDoc = $xslt->transformToDoc($xmlDoc);
                $finalDoc->documentElement->appendChild($finalDoc->importNode($xmlDoc->documentElement, true));
                $finalDoc->documentElement->appendChild($finalDoc->importNode($xslDoc->documentElement, true));
                $finalDoc->save($outputURI);
            } else {
                $xslt->transformToURI($xmlDoc, $outputURI);
            }
        }
    }

    public static function translateArray(array $inputList, array $wordList)
    {
        $searchList = array();
        $replaceList = array();
        foreach ($wordList as $key => $val) {
            $searchList[] = self::TEMPL_SIGN . $key . self::TEMPL_SIGN;
            $replaceList[] = $val;
        }
        $searchList[] = self::TEMPL_SIGN;
        $replaceList[] = '';
        return str_replace($searchList, $replaceList, $inputList);
    }

    // $content kann sein: string, DOMNode, array(DOMNode, DOMNode, ...)
    public function makeElement($tagName, array $attributeArray = array(), $content = null)
    {
        $Node = $this->createElement($tagName);
        foreach ($attributeArray as $key => $val) {
            $Node->setAttribute($key, $val);
        }
        $this->appendChilds($Node, $content);
        return $Node;
    }

    // gibt Array aller Attribut-Parameter entsprechend der DTD zurück
    public function parseDTD(DOMAttr $Attr, array $depthStack)
    {
        $ret = array();
        
        if (isset(self::$DTD[$Attr->name])) { // wenn Attribut nicht in DTD spezifiziert, leeres Array zurückgeben
            $dtd = self::$DTD[$Attr->name];
            $arr = explode(' ', $Attr->value);
            $arr = array_filter($arr);
            foreach ($arr as $val) {
                $val = explode(':', trim($val));
                $key = $val[0];
                if (! array_key_exists($key, $dtd)) // nicht spezifizierte Parameter ignorieren
                    continue;
                if ($dtd[$key] === null) { // standalone Parameter erhalten den Werte "true"
                    $ret[$key] = true;
                } else { // Werte werden auf den spezifizierten datentyp gecastet oder der default wert aus der DTD genommen
                    if (isset($val[1])) {
                        $val = $val[1];
                        if ((($start = strpos($val, self::TEMPL_SIGN)) !== false) && (($end = strpos($val, self::TEMPL_SIGN, $start + self::TEMPL_SIGN_LENGTH)) !== false)) {
                            $val = substr($val, $start + self::TEMPL_SIGN_LENGTH, $end - $start - self::TEMPL_SIGN_LENGTH);
                            $val = $this->getReplaceNode($this->replaceNodes, $val, $depthStack);
                            if ($val instanceof DOMCharacterData) {
                                $val = $val->data;
                            }
                        }
                        settype($val, gettype($dtd[$key]));
                    } else {
                        $val = $dtd[$key];
                    }
                    $ret[$key] = $val;
                }
            }
        }
        
        return $ret;
    }

    public function appendChilds($Node, $childs)
    {
        switch (true) {
            case is_string($childs):
                $Node->appendChild($this->createTextNode($childs));
                break;
            case is_array($childs):
                foreach ($childs as $children) {
                    $this->appendChilds($Node, $children);
                }
                break;
            case $childs instanceof DOMNodeList:
                while ($childs->length) {
                    $Node->appendChild($childs->item(0));
                }
                break;
            case $childs instanceof DOMNode:
                $Node->appendChild($childs);
                break;
            default:
                break;
        }
    }

    public function insertBefores($Node, $childs, $insertBefore)
    {
        switch (true) {
            case is_string($childs):
                $Node->insertBefore($this->createTextNode($childs), $insertBefore);
                break;
            case is_array($childs):
                foreach ($childs as $children) {
                    $this->insertBefores($Node, $children, $insertBefore);
                }
                break;
            case $childs instanceof DOMNodeList:
                for ($i = 0, $j = $childs->length; $i < $j; $i ++) {
                    $Node->insertBefore($this->copyNodeIfNecessary($childs->item($i)), $insertBefore);
                }
                break;
            case $childs instanceof DOMNode:
                $Node->insertBefore($this->copyNodeIfNecessary($childs), $insertBefore);
                break;
            default:
                break;
        }
    }

    public function saveHTML($keepTemplate = false)
    {
        if (! $keepTemplate) {
            $removeAttributeExceptions = array(
                'value',
                'title',
                'alt'
            ); // bei diesen Attributen wird nur ## entfernt
            $TextNodes = array();
            $AttrNodes = array();
            self::loadTemplateTextNodes($this->documentElement, $TextNodes, $AttrNodes);
            foreach ($AttrNodes as $arr) { // Attribut-Platzhalter werden entfernt
                $Attr = $arr[0];
                if (in_array($Attr->name, $removeAttributeExceptions))
                    $search = self::TEMPL_SIGN;
                else
                    $search = $arr[1];
                $Attr->value = trim(str_replace($search, '', $Attr->value));
                if (! $Attr->value) { // Wenn das Attribut jetzt leer ist, wird es komplett entfernt
                    $Attr->ownerElement->removeAttributeNode($Attr);
                }
            }
            foreach ($TextNodes as $Text) { // Text-Platzhalter werden nicht entfernt, aber die ## gelöscht
                $Text->data = str_replace(self::TEMPL_SIGN, '', $Text->data);
            }
        }
        return parent::saveHTML();
    }

    // wandelt Eingabe in DOM Knoten um, nimmt beliebig tief geschachteltes Array, Struktur und Schlüssel bleiben erhalten
    public function parseHTML($Input)
    {
        switch (true) {
            case is_array($Input): // Arrays werden rekursiv geparst
                $Output = array();
                foreach ($Input as $key => $val) {
                    $Output[$key] = $this->parseHTML($val);
                }
                break;
            case $Input === null: // NULL bleibt NULL
            case $Input instanceof DOMNode:
            case $Input instanceof DOMNodeList:
                $Output = $Input;
                break;
            default: // HTML Parser anwerfen
                $Input = trim((string) $Input);
                if (strlen($Input)) {
                    try { // XML probieren
                        $xml = sprintf('<?xml version="1.0" encoding="%s"?><xml>%s</xml>', self::DEFAULT_CHARSET, strtr($Input, self::$entity_keys));
                        $NewDoc = new DOMDocument();
                        $NewDoc->loadXML($xml);
                        if ($NewDoc->documentElement and $NewDoc->documentElement->hasChildNodes()) {
                            $Output = $this->importNode($NewDoc->documentElement->firstChild, true);
                        } else {
                            // debug_print_backtrace();
                            throw new Exception(self::TEMPL_XMLPARSE_ERR . '"' . substr(html_entity_decode($Input, ENT_QUOTES, self::DEFAULT_CHARSET), 0, 100) . '..."');
                        }
                    } catch (Exception $e) {
                        trigger_error($e->getMessage(), E_USER_WARNING);
                        $xml = sprintf('<html><head><meta charset="%s"></head><body>%s</body></html>', self::DEFAULT_CHARSET, strtr($Input, self::$entity_keys));
                        $NewDoc = new DOMDocument();
                        $NewDoc->loadHTML($xml);
                        if ($NewDoc->documentElement->lastChild->hasChildNodes()) {
                            $Output = $this->importNode($NewDoc->documentElement->lastChild->firstChild, true);
                        } else {
                            $Output = null;
                        }
                    }
                } else {
                    $Output = $this->createTextNode('');
                }
                break;
        }
        return $Output;
    }

    // Läd HTML Template-Datei
    public function loadTemplate($file)
    {
        $file .= self::TEMPL_TEMPFILE_END;
        
        if (! is_file($file)) {
            throw new Exception(self::TEMPL_TEMPFILE_ERR . $file);
        }
        // $html = utf8_decode(file_get_contents($file));
        $html = file_get_contents($file);
        $this->appendChild($this->parseHTML($html));
    }

    // Ersetzt einen einzelnen Platzhalter mit einem DOMNode
    public function loadElement($NodeKey, DOMNode $Node)
    {
        $TextNodes = array();
        $AttrNodes = array();
        self::loadTemplateTextNodes($this->documentElement, $TextNodes, $AttrNodes);
        $success = false;
        foreach ($TextNodes as $Text) {
            $key = substr($Text->data, self::TEMPL_SIGN_LENGTH, - self::TEMPL_SIGN_LENGTH);
            if ($key === $NodeKey) {
                $Text->parentNode->replaceChild($this->copyNodeIfNecessary($Node), $Text);
                $success = true;
            }
        }
        return $success;
    }

    // Ersetzt Platzhalter mit DOMNodes
    public function loadElements(array $replaceNodes)
    {
        $this->replaceNodes = $this->parseHTML($replaceNodes);
        $this->proccessTemplateNode($this->lastChild, $this->replaceNodes);
    }

    // Läd Sprach-Datei und ersetzt alle passenden Platzhalter
    public function loadLanguage($file, $lang_id)
    {
        return self::importLanguage($this, $file, $lang_id);
    }

    // Läd Sprach-Datei und ersetzt alle passenden Platzhalter
    public static function importLanguage(DOMDocument $doc, $file, $lang_id)
    {
        global $_LANG, $_LANG_DFLT;
        $lang_ids = array_unique(array_merge(array(
            $lang_id,
            $_LANG_DFLT
        ), array_keys($_LANG['str'])));
        $lang_file = null;
        foreach ($lang_ids as $lang_id) {
            if (isset($_LANG['str'][$lang_id]) and is_file($file . self::TEMPL_LANGFILE_END . $_LANG['str'][$lang_id])) {
                $lang_file = $file . self::TEMPL_LANGFILE_END . $_LANG['str'][$lang_id];
                break;
            }
        }
        if ($lang_file === null) {
            throw new Exception(self::TEMPL_LANGFILE_ERR . $file);
        }
        
        $tm = array(
            'repl_lang' => array()
        );
        include ($lang_file);
        
        self::loadLanguageArray($doc, $tm['repl_lang']);
    }

    public static function loadLanguageArray(DOMDocument $doc, array &$LangArr)
    {
        $TextNodes = array();
        $AttrNodes = array();
        self::loadTemplateTextNodes($doc->documentElement, $TextNodes, $AttrNodes);
        
        foreach ($AttrNodes as $arr) {
            $Attr = $arr[0];
            $search = $arr[1];
            $key = substr($search, self::TEMPL_SIGN_LENGTH, - self::TEMPL_SIGN_LENGTH);
            if (isset($LangArr[$key])) {
                $Attr->value = str_replace($search, $LangArr[$key], $Attr->value);
            }
        }
        
        foreach ($TextNodes as $Text) {
            $search = $Text->data;
            $key = substr($search, self::TEMPL_SIGN_LENGTH, - self::TEMPL_SIGN_LENGTH);
            if (isset($LangArr[$key])) {
                $Text->data = str_replace($search, $LangArr[$key], $Text->data);
            }
        }
    }

    public static function loadSearchArray(DOMDocument $doc, array $search)
    {
        $xpath = new DOMXPath($doc);
        $encoding = $doc->encoding ? $doc->encoding : self::DEFAULT_CHARSET;
        foreach ($search as $searchVal) {
            $length = mb_strlen($searchVal, $encoding);
            $textNodeList = array();
            $nodeList = $xpath->evaluate('//text()');
            foreach ($nodeList as $node) {
                $textNodeList[] = $node;
            }
            foreach ($textNodeList as $textNode) {
                while ($textNode and mb_strlen($textNode->data, $encoding)) {
                    $i = mb_stripos($textNode->data, $searchVal, 0, $encoding);
                    if ($i === false) {
                        break;
                    }
                    if ($i === 0) {
                        $searchNode = $textNode;
                    } else {
                        $searchNode = $textNode->splitText($i);
                    }
                    if ($length >= $searchNode->length) {
                        $followingNode = null;
                    } else {
                        $followingNode = $searchNode->splitText($length);
                    }
                    $markNode = $doc->createElement('mark');
                    $searchNode->parentNode->replaceChild($markNode, $searchNode);
                    $markNode->appendChild($searchNode);
                    $textNode = $followingNode;
                }
            }
        }
    }

    private function proccessTemplateNode($Root, array &$replaceNodes, $depthStack = array())
    {
        $ListNodes = array();
        $appendContent = '0';
        $this->loadTemplateNodesByAttr($Root, $ListNodes, self::TEMPL_LIST);
        $curr_depth = count($depthStack);
        foreach ($ListNodes as $ListNodeRef) {
            $options = $this->parseDTD($ListNodeRef->getAttributeNode(self::TEMPL_LIST), $depthStack);
            $ListNodeRef->removeAttribute(self::TEMPL_LIST);
            
            $ListNodeStart = 1;
            $ListNodeMinCount = 0;
            $ListNodeMaxCount = false;
            $ListNodeStep = 1;
            $ListNodeMod = false;
            
            foreach ($options as $key => $val) {
                switch ($key) {
                    case 'repeat':
                        break;
                    case 'atleast':
                        $ListNodeMinCount = intval($val);
                        break;
                    case 'atmost':
                        $ListNodeMaxCount = intval($val);
                        break;
                    case 'odd':
                        $ListNodeStep = 2;
                        $ListNodeMod = 0;
                        break;
                    case 'even':
                        $ListNodeStep = 2;
                        $ListNodeMod = 1;
                        break;
                    case 'startwith':
                        $ListNodeStart = intval($val);
                        break;
                }
            }
            
            if ($ListNodeMod !== false) {
                if ($ListNodeStart % 2 === $ListNodeMod)
                    $ListNodeStart ++;
            }
            
            for ($i = $ListNodeStart, $ListNodeCount = 0, $ListNodeContinue = true; $ListNodeContinue; $i += $ListNodeStep, $ListNodeCount ++) {
                
                if ($ListNodeMaxCount !== false and $ListNodeCount >= $ListNodeMaxCount) {
                    break;
                }
                
                $ListNode = $ListNodeRef->cloneNode(true);
                
                $depthStack[$curr_depth] = $i;
                
                $ListNodeContinue = $this->proccessTemplateNode($ListNode, $replaceNodes, $depthStack);
                
                if ($ListNodeContinue === '1' or $ListNodeCount < $ListNodeMinCount) {
                    $ListNodeRef->parentNode->insertBefore($ListNode, $ListNodeRef);
                    if (! $appendContent) {
                        $appendContent = $ListNodeContinue;
                    }
                    $ListNodeContinue = '1';
                }
            }
            $ListNodeRef->parentNode->removeChild($ListNodeRef);
            unset($depthStack[$curr_depth]);
        }
        
        $CondNodes = array();
        $this->loadTemplateNodesByAttr($Root, $CondNodes, self::TEMPL_COND);
        $CondPrev = '0';
        foreach ($CondNodes as $CondNode) {
            $options = $this->parseDTD($CondNode->getAttributeNode(self::TEMPL_COND), $depthStack);
            $CondNode->removeAttribute(self::TEMPL_COND);
            
            $CondTruth = '0';
            
            foreach ($options as $key => $val) {
                switch ($key) {
                    case 'elseif':
                        if ($CondPrev === '1')
                            break;
                    // Fallthrough wuuuu :D
                    case 'if':
                        if ($val)
                            $CondTruth = '1';
                        break;
                    case 'ifnot':
                        if (! $val)
                            $CondTruth = '1';
                        break;
                    case 'else':
                        if ($CondPrev === '0') {
                            $CondTruth = '1';
                        }
                        break;
                    default:
                        break;
                }
            }
            if ($CondTruth) {
                if ($this->proccessTemplateNode($CondNode, $replaceNodes, $depthStack))
                    $appendContent = $CondTruth;
            } else {
                if (! $CondNode->parentNode)
                    var_dump($CondNode->tagName);
                $CondNode->parentNode->removeChild($CondNode);
            }
            $CondPrev = $CondTruth;
        }
        $TextNodes = array();
        $AttrNodes = array();
        self::loadTemplateTextNodes($Root, $TextNodes, $AttrNodes);
        foreach ($AttrNodes as $arr) {
            $key = substr($arr[1], self::TEMPL_SIGN_LENGTH, - self::TEMPL_SIGN_LENGTH);
            if (! $appendContent)
                $appendContent = $this->chkReplaceNode($replaceNodes, $key, $depthStack);
            $this->replaceContent($arr[0], array(
                $arr[1],
                $this->getReplaceNode($replaceNodes, $key, $depthStack)
            ));
        }
        // if($TextNodes[10]) my_dump($this->saveXML($TextNodes[10]));
        foreach ($TextNodes as $Text) {
            $key = substr($Text->data, self::TEMPL_SIGN_LENGTH, - self::TEMPL_SIGN_LENGTH);
            if (! $appendContent)
                $appendContent = $this->chkReplaceNode($replaceNodes, $key, $depthStack);
            $this->replaceContent($Text, $this->getReplaceNode($replaceNodes, $key, $depthStack));
        }
        return $appendContent;
    }

    private function replaceContent($Node, $Content)
    {
        switch (true) {
            case $Node instanceof DOMAttr:
                $search = $Content[0];
                $replace = $Content[1];
                switch (true) {
                    case $replace instanceof DOMCharacterData:
                        $replace = $replace->data;
                        break;
                    case is_string($replace):
                        break;
                    default:
                        if ($Node->ownerElement) {
                            $Node->ownerElement->removeAttributeNode($Node);
                        }
                        break 2;
                }
                if ($Node->ownerElement) {
                    $Node->ownerElement->setAttribute($Node->name, str_replace($search, $replace, $Node->value));
                }
                break;
            case $Node instanceof DOMCharacterData:
                switch (true) {
                    case $Content instanceof DOMNodeList:
                    case is_array($Content):
                        $this->insertBefores($Node->parentNode, $Content, $Node);
                        $Node->parentNode->removeChild($Node);
                        break;
                    case $Content instanceof DOMNode:
                        $Node->parentNode->replaceChild($this->copyNodeIfNecessary($Content), $Node);
                        break;
                    default:
                        // $Node->parentNode->removeChild($Node);
                        break;
                }
                break;
            case $Node instanceof DOMElement:
                my_dump($Content);
                break;
            default:
                break;
        }
    }

    private function chkReplaceNode(&$replaceNodes, $key, $depthStack)
    {
        for ($ret = isset($replaceNodes[$key]) ? $replaceNodes[$key] : false; count($depthStack) and (is_array($ret) or $ret = false);) {
            $i = array_shift($depthStack) - 1;
            $keys = array_keys($ret);
            $ret = isset($keys[$i]) ? $ret[$keys[$i]] : false;
        }
        return $ret ? '1' : '0';
    }

    private function getReplaceNode(&$replaceNodes, $key, $depthStack)
    {
        for ($ret = isset($replaceNodes[$key]) ? $replaceNodes[$key] : null; is_array($ret) and count($depthStack);) {
            $i = array_shift($depthStack) - 1;
            $keys = array_keys($ret);
            $ret = isset($keys[$i]) ? $ret[$keys[$i]] : $this->createTextNode('');
        }
        
        return $ret;
    }

    private function copyNodeIfNecessary($Node)
    {
        return $Node->ownerDocument !== $this ? $this->importNode($Node, true) : ($Node->parentNode ? $Node->cloneNode(true) : $Node);
    }

    // Läd alle DOMTexte innerhalb $Root deren DOMText->data ein Template-String enthält
    public static function loadTemplateTextNodes($Root, &$TextNodes, &$AttrNodes)
    {
        if ($Root instanceof DOMText) {
            if ($Root instanceof DOMCdataSection) { // HACK: PHP BUG IN DOMCdataSection::splitText! (http://bugs.php.net/bug.php?id=52656)
                $Tmp = $Root->ownerDocument->createTextNode('');
                $Tmp->data = $Root->data;
                $Root->parentNode->replaceChild($Tmp, $Root);
                $Root = $Tmp;
            }
            while ($Root && ($data = utf8_decode($Root->data)) && (($start = strpos($data, self::TEMPL_SIGN)) !== false) && (($end = strpos($data, self::TEMPL_SIGN, $start + self::TEMPL_SIGN_LENGTH)) !== false)) {
                $end += self::TEMPL_SIGN_LENGTH;
                if ($end === strlen($data)) {
                    $NewRoot = null;
                } else {
                    $NewRoot = $Root->splitText($end);
                }
                
                if ($start === 0) {
                    $TextNodes[] = $Root;
                } else {
                    $TextNodes[] = $Root->splitText($start);
                }
                $Root = $NewRoot;
            }
            // if ($debug)
            // my_dump($TextNodes);
        } elseif ($Root instanceof DOMElement) {
            if ($Root->hasAttributes()) {
                foreach ($Root->attributes as $Attr) {
                    $start = 0;
                    while ((($start = strpos($Attr->value, self::TEMPL_SIGN, $start)) !== false) && (($end = strpos($Attr->value, self::TEMPL_SIGN, $start + self::TEMPL_SIGN_LENGTH)) !== false)) {
                        $end += self::TEMPL_SIGN_LENGTH;
                        $AttrNodes[] = array(
                            $Attr,
                            substr($Attr->value, $start, $end - $start)
                        );
                        $start = $end;
                    }
                }
            }
            if ($Root->hasChildNodes()) {
                $Nodes = array();
                foreach ($Root->childNodes as $Node) {
                    $Nodes[] = $Node;
                }
                foreach ($Nodes as $Node) {
                    self::loadTemplateTextNodes($Node, $TextNodes, $AttrNodes);
                }
            }
        }
    }

    // Läd alle DOMElemente innerhalb $Root die das Attribut TEMPL_LIST besitzen
    public function loadTemplateNodesByAttr($Root, &$ListNodes, $attribute)
    {
        if ($Root->hasAttributes() and $Root->hasAttribute($attribute)) {
            $ListNodes[] = $Root;
            return;
        }
        if ($Root->hasChildNodes()) {
            foreach ($Root->childNodes as $Node) {
                $this->loadTemplateNodesByAttr($Node, $ListNodes, $attribute);
            }
        }
    }

    // Creates DOMNodes from Array
    public function insertArray(array $html_arr, $ParentNode = null)
    {
        if (! $ParentNode instanceof DOMNode)
            $ParentNode = $this->documentElement;
        for ( // Stack initialisieren
        $html_stack = array(
            $html_arr
        ), $html_stack_count = 1; 
    // Abbruch wenn der Stack alle ist
    $html_stack_count; 
    // Stack kürzen & im Baum nach oben gehen
    array_pop($html_stack), $html_stack_count --, $ParentNode = $Node->parentNode) {
            while (list ($tmp, $arr) = each($html_stack[$html_stack_count - 1])) {
                $arr = array_merge(array(
                    't' => 'div',
                    'a' => array(),
                    'c' => ''
                ), $arr);
                
                $Node = $this->makeElement($arr['t'], $arr['a']);
                
                $ParentNode->appendChild($Node);
                
                if (is_string($arr['c'])) {
                    $Node->appendChild($this->createTextNode($arr['c']));
                } else if (is_array($arr['c'])) {
                    $html_stack[] = $arr['c'];
                    $html_stack_count ++;
                    $ParentNode = $Node;
                }
            }
        }
    }

    public static function setNamespaceURI(DOMNode $sourceNode, $namespaceURI = null)
    {
        $doc = $sourceNode->ownerDocument;
        $retNode = null;
        switch ($sourceNode->nodeType) {
            case XML_ELEMENT_NODE:
                $qualifiedName = $sourceNode->nodeName;
                $retNode = $doc->createElementNS($namespaceURI, $qualifiedName);
                while ($sourceNode->attributes->length) {
                    $childNode = $sourceNode->attributes->item(0);
                    $retNode->appendChild($childNode);
                    self::setNamespaceURI($childNode, $namespaceURI);
                }
                while ($sourceNode->hasChildNodes()) {
                    $childNode = $sourceNode->firstChild;
                    $retNode->appendChild($childNode);
                    self::setNamespaceURI($childNode, $namespaceURI);
                }
                break;
            case XML_ATTRIBUTE_NODE:
                $qualifiedName = $sourceNode->nodeName;
                $retNode = $doc->createAttributeNS($namespaceURI, $qualifiedName);
                $retNode->value = $sourceNode->value;
                break;
        }
        if ($retNode and $sourceNode->parentNode) {
            $sourceNode->parentNode->replaceChild($retNode, $sourceNode);
        }
        return $retNode ? $retNode : $sourceNode;
    }

    public static function loadXHTML(DOMDocument $doc, $xhtml)
    {
        $retFragment = $doc->createDocumentFragment();
        $tmpDoc = new DOMDocument('1.0', 'UTF-8');
        $xhtml = '<html xmlns="' . self::NS_HTML . '"><head><meta http-equiv="content-type" content="text/html; charset=UTF-8" /></head><body>' . $xhtml . '</body></html>';
        $tmpDoc->loadHTML($xhtml);
        $root = $tmpDoc->documentElement->lastChild;
        foreach ($root->childNodes as $node) {
            $node = $doc->importNode($node, true);
            $node = self::setNamespaceURI($node, self::NS_HTML);
            $retFragment->appendChild($node);
        }
        return $retFragment;
    }

    public static function saveXHTML(DOMDocument $doc, DOMNode $node = null)
    {
        $selfTerminate = array(
            'area',
            'base',
            'basefont',
            'br',
            'col',
            'frame',
            'hr',
            'img',
            'input',
            'link',
            'meta',
            'param'
        );
        if ($node === null) {
            $node = $doc->documentElement;
        }
        $tagName = $node->tagName;
        $ret = $doc->saveHTML();
        $firstPos = strpos($ret, '<' . $tagName);
        $lastPos = strrpos($ret, '</' . $tagName . '>') + strlen('</' . $tagName . '>');
        $ret = substr($ret, $firstPos, $lastPos - $firstPos);
        foreach ($selfTerminate as &$tagName) {
            $tagName = '></' . $tagName . '>';
        }
        unset($tagName);
        $ret = str_replace($selfTerminate, ' />', $ret);
        return $ret;
    }

    public static function parseTemplate($dataFile, $templateFile, $langFile = null, $langId = null)
    {
        if ($dataFile instanceof DOMDocument) {
            $dataDoc = $dataFile;
        } else {
            $dataDoc = self::loadFile($dataFile);
        }
        if (! $dataDoc) {
            throw new Exception(sprintf(self::TEMPL_DATADOC_ERR, $dataFile));
        }
        if ($templateFile instanceof DOMDocument) {
            $templateDoc = $templateFile;
        } else {
            $templateDoc = self::loadFile($templateFile);
        }
        if (! $templateDoc) {
            throw new Exception(sprintf(self::TEMPL_TEMPLDOC_ERR, $templateFile));
        }
        $xslt = new XSLTProcessor();
        $xslt->importStylesheet($templateDoc);
        $doc = $xslt->transformToDoc($dataDoc);
        if ($langFile !== null) {
            self::importLanguage($doc, $langFile, $langId);
        }
        return $doc;
    }

    public static function transform($xmlDoc, $xslDoc, $returnAsString = false)
    {
        if (! ($xmlDoc instanceof DOMDocument)) {
            $xmlDoc = self::loadFile($xmlDoc);
        }
        if (! ($xslDoc instanceof DOMDocument)) {
            $xslDoc = self::loadFile($xslDoc);
        }
        
        $charset = self::DEFAULT_CHARSET;
        
        if (isset($_SERVER['HTTP_ACCEPT']) and ! (stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml'))) {
            $mime = 'text/html';
            $method = 'html';
            $version = '5.0';
        } else {
            $mime = 'application/xhtml+xml';
            $method = 'xml';
            $version = '1.0';
        }
        $outputElements = $xslDoc->getElementsByTagNameNS(self::NS_XSL, 'output');
        foreach ($outputElements as $outputElement) {
            $outputElement->setAttribute('media-type', $mime);
            $outputElement->setAttribute('method', $method);
            $outputElement->setAttribute('encoding', $charset);
            $outputElement->setAttribute('version', $version);
        }
        if (! headers_sent()) {
            // header('Content-Type: '.$mime.'; charset='.$charset);
        }
        
        $xslt = new XSLTProcessor();
        
        $xslt->importStylesheet($xslDoc);
        
        return $returnAsString ? $xslt->transformToXML($xmlDoc) : $xslt->transformToDoc($xmlDoc);
    }

    public static function loadFile($file, $asHTML = false)
    {
        $doc = new DOMDocument();
        if ($asHTML) {
            $ret = $doc->loadHTMLFile((string) $file);
        } else {
            $ret = $doc->load((string) $file);
        }
        return $ret ? $doc : null;
    }

    // returns recursive wholeText
    public static function getTextFromNode(DOMNode $Node, $showComments = false)
    {
        if (in_array($Node->nodeType, $showComments ? array(
            XML_TEXT_NODE,
            XML_CDATA_SECTION_NODE,
            XML_COMMENT_NODE
        ) : array(
            XML_TEXT_NODE,
            XML_CDATA_SECTION_NODE
        ))) {
            return $Node->textContent;
        }
        $text = '';
        for ($Node = $Node->firstChild; $Node; $Node = $Node->nextSibling) {
            $text .= self::getTextFromNode($Node, $showComments);
        }
        return $text;
    }

    // builds DOM Tree out of an array recursively
    // $tagName: DOMElement tagName or DOMAttribute nodeName
    // $structure: array for DOMElement or scalar value for DOMAttribute
    // $assumeWellformed: wenn gesetzt sollten Array-Schlüssel innerhalb eines Arrays entweder alle Integer oder alle tagName-valide Strings sein
    public static function arr2dom(DOMDocument $doc, $tagName, $structure, $assumeWellformed = false)
    {
        if (is_array($structure)) {
            try {
                $retNode = $doc->createElement($tagName);
            } catch (Exception $e) {
                $retNode = $doc->createElement('Object-' . $tagName);
            }
            foreach ($structure as $key => $val) {
                $childNode = null;
                if ($assumeWellformed) {
                    if (is_int($key)) {
                        $childNode = self::arr2dom($doc, $tagName, $val, $assumeWellformed);
                    } else {
                        $childNode = self::arr2dom($doc, $key, $val, $assumeWellformed);
                    }
                } else {
                    $childNode = self::arr2dom($doc, $key, $val, $assumeWellformed);
                }
                if ($childNode) {
                    $retNode->appendChild($childNode);
                }
            }
        } else {
            try {
                $retNode = $doc->createAttribute($tagName);
            } catch (Exception $e) {
                $retNode = $doc->createAttribute('Attribute-' . $tagName);
            }
            $retNode->value = $structure;
        }
        return $retNode;
    }
}