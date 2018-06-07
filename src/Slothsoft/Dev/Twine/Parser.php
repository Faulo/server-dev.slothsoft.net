<?php
declare(strict_types = 1);
namespace Slothsoft\Parser\Twine;

class Parser
{

    const EOL = "\n";

    const TAG_TEXT = 'text';

    const TAG_NARRATION = 'narration';

    const TAG_DIALOG = 'dialogue';

    const TAG_CONTINUE = 'continue';

    const TAG_DECISION = 'decision';

    const TAG_POINTS = 'points';

    public static function createFromFile($file)
    {
        $ret = new Parser();
        $ret->initFile($file);
        
        return $ret;
    }

    protected $file;

    protected $noteList;

    protected $defaultNote;

    protected $utf8BOM;

    protected $weekdayTagList = [
        'mo',
        'tu',
        'we',
        'th',
        'fr'
    ];

    protected $textTagList = [
        self::TAG_NARRATION,
        self::TAG_DIALOG,
        self::TAG_CONTINUE
    ];

    protected $classTagList = [
        self::TAG_DIALOG,
        self::TAG_POINTS
    ];

    protected function __construct()
    {
        $this->utf8BOM = pack('CCC', 0xef, 0xbb, 0xbf);
        
        $this->defaultNote = [];
        $this->defaultNote['name'] = '';
        $this->defaultNote['tags'] = '';
        $this->defaultNote['head'] = '';
        $this->defaultNote['body'] = '';
    }

    public function initFile($file)
    {
        $this->file = $file;
        $this->noteList = [];
        
        $rowList = file($this->file);
        
        if (substr($rowList[0], 0, 3) === $this->utf8BOM) {
            $rowList[0] = substr($rowList[0], 3);
        }
        
        $note = $this->defaultNote;
        $rowList[] = ':: EOF';
        foreach ($rowList as $row) {
            if (preg_match('/^\:\: (.+)/', $row, $match)) {
                if (strlen($note['name'])) {
                    $this->parseNote($note);
                    $this->noteList[] = $note;
                    $note = $this->defaultNote;
                }
                $note['head'] = $row;
                $note['name'] = $match[1];
                if (preg_match('/ \[(.+)\]/', $note['name'], $match)) {
                    $note['name'] = str_replace($match[0], '', $note['name']);
                    $note['tags'] = $match[1];
                }
            } else {
                $note['body'] .= $row;
            }
        }
    }

    public function parseNote(array &$note)
    {
        if (preg_match('/^([a-z]{2})[$\-]/', trim($note['name']), $match)) {
            $day = $match[1];
            $note['tags'] .= ' ' . $day;
        }
        $note['tags'] = trim($note['tags']);
        if ($note['tags']) {
            $note['head'] = sprintf(':: %s [%s]%s', $note['name'], $note['tags'], self::EOL);
        }
        $pList = [];
        if (in_array($note['tags'], $this->weekdayTagList)) {
            $rowList = explode(self::EOL, $note['body']);
            $p = [];
            foreach ($rowList as $row) {
                $row = trim($row);
                switch (true) {
                    case $row === '':
                        if (count($p)) {
                            $pList[] = $p;
                            $p = [];
                        }
                        break;
                    case preg_match('/\<\<if/', $row):
                    case preg_match('/\<\<endif/', $row):
                    case preg_match('/\<\<else/', $row):
                    case preg_match('/^\<\<set /', $row):
                        if (count($p)) {
                            $pList[] = $p;
                            $p = [];
                        }
                        $pList[] = $row;
                        break;
                    default:
                        $p[] = $row;
                        break;
                }
            }
            // my_dump($pList);
            foreach ($pList as &$p) {
                $arr = [];
                $arr['type'] = '';
                $arr['body'] = '';
                if (is_array($p)) {
                    $p = implode(self::EOL, $p);
                    switch (true) {
                        case preg_match('/\[\[.+\[\[/', $p):
                            $type = self::TAG_DECISION;
                            break;
                        case preg_match('/\[\[/', $p):
                            $type = self::TAG_CONTINUE;
                            break;
                        case preg_match('/".+"/', $p):
                            $type = self::TAG_DIALOG;
                            break;
                        case preg_match('/Your Points\:/', $p):
                            $type = self::TAG_POINTS;
                            break;
                        default:
                            $type = self::TAG_NARRATION;
                            break;
                    }
                    $arr['type'] = $type;
                    $arr['body'] = $p;
                } else {
                    $arr['body'] = $p;
                }
                $p = $arr;
            }
            unset($p);
            
            $isText = false;
            $textList = [];
            foreach ($pList as $p) {
                if ($p['type']) {
                    if (in_array($p['type'], $this->textTagList)) {
                        if (! $isText) {
                            $isText = true;
                            $textList[] = $this->createStartTag(self::TAG_TEXT);
                        }
                    } else {
                        if ($isText) {
                            $isText = false;
                            $textList[] = $this->createEndTag(self::TAG_TEXT);
                        }
                    }
                    
                    $textList[] = $this->createStartTag($p['type']);
                    $textList[] = $p['body'];
                    $textList[] = $this->createEndTag($p['type']);
                } else {
                    $textList[] = $p['body'];
                }
            }
            if ($isText) {
                $isText = false;
                $textList[] = $this->createEndTag(self::TAG_TEXT);
            }
            $note['body'] = implode(self::EOL, $textList);
            if (substr($note['body'], - 1) === '\\') {
                $note['body'] = substr($note['body'], 0, - 1);
            }
            // echo implode(self::EOL, $textList) . PHP_EOL . PHP_EOL;
        }
    }

    protected function createStartTag($tag)
    {
        $class = in_array($tag, $this->classTagList) ? ' class="npc-?"' : '';
        return sprintf('<%s%s>\\', $tag, $class);
    }

    protected function createEndTag($tag)
    {
        return sprintf('</%s>\\', $tag);
    }

    public function asDocument()
    {
        $retDoc = new \DOMDocument();
        $rootNode = $retDoc->createElement('twine');
        $retDoc->appendChild($rootNode);
        
        $noteNode = $retDoc->createElement('noteList');
        $rootNode->appendChild($noteNode);
        foreach ($this->noteList as $note) {
            $node = $retDoc->createElement('note');
            $node->setAttribute('name', $note['name']);
            if ($note['tags']) {
                $node->setAttribute('tags', $note['tags']);
            }
            $node->appendChild($retDoc->createTextNode($note['head']));
            $node->appendChild($retDoc->createTextNode($note['body']));
            $noteNode->appendChild($node);
        }
        
        return $retDoc;
    }

    public function save($file)
    {
        $body = '';
        foreach ($this->noteList as $note) {
            $body .= $note['head'];
            $body .= $note['body'];
            $body .= self::EOL;
            $body .= self::EOL;
            $body .= self::EOL;
        }
        return file_put_contents($file, $body);
    }
}