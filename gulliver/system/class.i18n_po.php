<?php

/**
 * i18n_PO
 * This class build biggers PO files without size limit and this not use much 
 * memory that the allowed.
 */
class i18n_PO
{
    private $_file = null;
    private $_string = '';
    private $_meta;
    private $_fp;
    private $_fileComments;
    protected $_editingHeader;
    protected $_fileLine;
    protected $flagEndHeaders;
    protected $flagError;
    protected $flagInit;
    protected $lineNumber;
    public $translatorComments;
    public $extractedComments;
    public $references;
    public $flags;
    public $previousUntranslatedStrings;

    /**
     * Constructor.
     * 
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Prepares the construction of the .po file.
     * 
     * @return boolean|undefined
     * @throws Exception
     */
    public function buildInit()
    {
        $this->_fp = fopen($this->file, 'w');

        if (!is_resource($this->_fp)) {
            throw new Exception('Could\'t open ' . $this->file . ' file');
        }

        // lock PO file exclusively
        if (!flock($this->_fp, LOCK_EX)) {
            fclose($this->_fp);
            return false;
        }

        $this->_meta = 'msgid ""';
        $this->_writeLine($this->_meta);
        $this->_meta = 'msgstr ""';
        $this->_writeLine($this->_meta);

        $this->_editingHeader = true;
    }

    /**
     * Start reading the .po file.
     * 
     * @throws Exception
     */
    public function readInit()
    {
        $this->_fp = fopen($this->file, 'r');

        if (!is_resource($this->_fp)) {
            throw new Exception('Could\'t open ' . $this->file . ' file');
        }
        //skipping comments
        $this->skipComments();
        //deaing headers
        $this->readHeaders();

        $this->translatorComments = [];
        $this->extractedComments = [];
        $this->references = [];
        $this->flags = [];
        $this->previousUntranslatedStrings = [];
    }

    /**
     * Add header information.
     * 
     * @param string $id
     * @param string $value
     */
    public function addHeader($id, $value)
    {
        if ($this->_editingHeader) {
            $meta = '"' . trim($id) . ': ' . trim($value) . '\n"';
            $this->_writeLine($meta);
        }
    }

    /**
     * Add a translator comment.
     * 
     * @param string $str
     */
    public function addTranslatorComment($str)
    {
        $this->headerStroke();
        $comment = '# ' . trim($str);
        $this->_writeLine($comment);
    }

    /**
     * Add a extracted comment.
     * 
     * @param string $str
     */
    public function addExtractedComment($str)
    {
        $this->headerStroke();
        $comment = '#. ' . trim($str);
        $this->_writeLine($comment);
    }

    /**
     * Add a reference comment.
     * 
     * @param string $str
     */
    public function addReference($str)
    {
        $this->headerStroke();
        $reference = '#: ' . trim($str);
        $this->_writeLine($reference);
    }

    /**
     * Add a flag comment.
     * 
     * @param string $str
     */
    public function addFlag($str)
    {
        $this->headerStroke();
        $flag = '#, ' . trim($str);
        $this->_writeLine($flag);
    }

    /**
     * Add previous untranslated string.
     * 
     * @param string $str
     */
    public function addPreviousUntranslatedString($str)
    {
        $this->headerStroke();
        $str = '#| ' . trim($str);
        $this->_writeLine($str);
    }

    /**
     * Add a translation.
     * 
     * @param string $msgid
     * @param string $msgstr
     */
    public function addTranslation($msgid, $msgstr)
    {
        $this->headerStroke();
        $this->_writeLine('msgid "' . $this->prepare($msgid, true) . '"');
        $this->_writeLine('msgstr "' . $this->prepare($msgstr, true) . '"');
        $this->_writeLine('');
    }

    /**
     * Write line into file.
     * 
     * @param string $str
     */
    public function _writeLine($str)
    {
        $this->_write($str . "\n");
    }

    /**
     * Write into file.
     * 
     * @param string $str
     */
    public function _write($str)
    {
        fwrite($this->_fp, $str);
    }

    /**
     * Prepare string for add to file.
     * 
     * @param string $string
     * @param boolean $reverse
     * @return string
     */
    public function prepare($string, $reverse = false)
    {
        if ($reverse) {
            $smap = array('"', "\n", "\t", "\r");
            $rmap = array('\"', '\\n"' . "\n" . '"', '\\t', '\\r');
            return (string) str_replace($smap, $rmap, $string);
        } else {
            $string = preg_replace('/"\s+"/', '', $string);
            $smap = array('\\n', '\\r', '\\t', '\"');
            $rmap = array("\n", "\r", "\t", '"');
            return (string) str_replace($smap, $rmap, $string);
        }
    }

    /**
     * Add a header stroke.
     */
    public function headerStroke()
    {
        if ($this->_editingHeader) {
            $this->_editingHeader = false;
            $this->_writeLine('');
        }
    }
    /**
     * read funtions *
     */

    /**
     * Skip comments
     */
    private function skipComments()
    {
        $this->_fileComments = '';
        do {
            $lastPos = ftell($this->_fp);
            $line = fgets($this->_fp);
            $this->_fileComments .= $line;
        } while ((substr($line, 0, 1) == '#' || trim($line) == '') && !feof($this->_fp));
        fseek($this->_fp, $lastPos);
    }

    /**
     * Read headers information from .po file.
     * 
     * @throws Exception
     */
    private function readHeaders()
    {
        $this->flagEndHeaders = false;
        $this->flagError = false;
        $this->flagInit = true;
        $this->lineNumber = 0;
        $errMsg = '';

        while (!$this->flagError && !$this->flagEndHeaders) {

            if ($this->flagInit) {
                //in first instance
                $this->flagInit = false; //unset init flag
                //read the first and second line of the file
                $firstLine = fgets($this->_fp);
                $secondLine = fgets($this->_fp);

                //verifying the file head
                if (strpos($firstLine, 'msgid ""') === false || strpos($secondLine, 'msgstr ""') === false) {
                    $this->flagError = true;
                    $errMsg = 'Misplace for firts msgid "" and msgstr "" in the header';
                }
                continue;
            }

            //getting the new line
            $this->_fileLine = trim(fgets($this->_fp));
            //set line number
            $this->lineNumber++;

            //verifying that is not end of file and applying a restriction for to read just the twenty firsts lines
            if (trim($this->_fileLine) == '' || !$this->_fileLine || $this->lineNumber >= 20) {
                $this->flagEndHeaders = true; // set ending to read the headers
                continue;
            }
            //verify if has a valid mask header line
            preg_match('/^"([a-z0-9\._-]+)\s*:\s*([\W\w]+)\\\n"$/i', $this->_fileLine, $match);

            //for a valid header line the $match size should three
            if (sizeof($match) == 3) {
                $key = trim($match[1]); //getting the key of the header
                $value = trim($match[2]); //getting the value of the header
                $this->_meta[$key] = $value; //setting a new header
            } else {
                $this->flagEndHeaders = true; //otherwise set the ending to read the headers
                break;
            }
        } //end looking for headeers
        //verifying the headers data
        if (!isset($this->_meta['X-Poedit-Language'])) {
            if (!isset($this->_meta['Language'])) {
                $this->flagError = true;
                $errMsg = "X-Poedit-Language and Language meta doesn't exist";
            } elseif ($this->_meta['Language'] == '') {
                $this->flagError = true;
                $errMsg = "Language meta is empty";
            } else {
                $this->_meta['X-Poedit-Language'] = $this->_meta['Language'];
                unset($this->_meta['Language']);
                $this->flagError = false;
            }
        } elseif ($this->_meta['X-Poedit-Language'] == '') {
            $this->flagError = true;
            $errMsg = "X-Poedit-Language meta is empty";
        }

        //if the country is not present in metadata
        if (!isset($this->_meta['X-Poedit-Country'])) {
            $this->_meta['X-Poedit-Country'] = '.';
        } elseif ($this->_meta['X-Poedit-Country'] == '') {
            $this->_meta['X-Poedit-Country'] = '.';
        }

        //thowing the exception if is necesary
        if ($this->flagError) {
            throw new Exception("This file is not a valid PO file. ($errMsg)");
        }
    }

    /**
     * Get headers information.
     * 
     * @return array
     */
    public function getHeaders()
    {
        return $this->_meta;
    }

    /**
     * Get translations.
     * 
     * @return array|boolean
     * @throws Exception
     */
    public function getTranslation()
    {

        $flagReadingComments = true;
        $this->translatorComments = [];
        $this->extractedComments = [];
        $this->references = [];
        $this->flags = [];

        //getting the new line
        while ($flagReadingComments && !$this->flagError) {

            $this->_fileLine = trim(fgets($this->_fp));
            //set line number
            $this->lineNumber++;

            if (!$this->_fileLine) {
                return false;
            }

            $prefix = substr($this->_fileLine, 0, 2);

            switch ($prefix) {
                case '# ':
                    $lineItem = str_replace('# ', '', $this->_fileLine);
                    $this->translatorComments[] = $lineItem;
                    break;
                case '#.':
                    if (substr_count($this->_fileLine, '#. ') == 0) {
                        $this->flagError = true;
                    } else {
                        $lineItem = str_replace('#. ', '', $this->_fileLine);
                        $this->extractedComments[] = $lineItem;
                    }
                    break;
                case '#:':
                    if (substr_count($this->_fileLine, '#: ') == 0) {
                        $this->flagError = true;
                    } else {
                        $lineItem = str_replace('#: ', '', $this->_fileLine);
                        $this->references[] = $lineItem;
                    }
                    break;
                case '#,':
                    if (substr_count($this->_fileLine, '#, ') == 0) {
                        $this->flagError = true;
                    } else {
                        $lineItem = str_replace('#, ', '', $this->_fileLine);
                        $this->flags[] = $lineItem;
                    }
                    break;
                case '#|':
                    if (substr_count($this->_fileLine, '#| ') == 0) {
                        $this->flagError = true;
                    } else {
                        $lineItem = str_replace('#| ', '', $this->_fileLine);
                        $this->previousUntranslatedStrings[] = $lineItem;
                    }
                    break;
                default:
                    $flagReadingComments = false;
            }
        }

        if (!$this->_fileLine) {
            return false;
        }

        //Getting the msgid
        preg_match('/\s*msgid\s*"(.*)"\s*/s', $this->_fileLine, $match);

        if (sizeof($match) != 2) {
            throw new Exception('Invalid PO file format1');
        }

        $msgid = '';

        do {
            //g::pr($match);
            $msgid .= $match[1];
            $this->_fileLine = trim(fgets($this->_fp));
            preg_match('/^"(.*)"\s*/s', $this->_fileLine, $match);
        } while (sizeof($match) == 2);

        //Getting the msgstr
        preg_match('/\s*msgstr\s*"(.*)"\s*/s', $this->_fileLine, $match);

        if (sizeof($match) != 2) {
            throw new Exception('Invalid PO file format2');
        }

        $msgstr = '';

        do {
            //g::pr($match);
            $msgstr .= $match[1] . "\n";
            $this->_fileLine = trim(fgets($this->_fp));
            preg_match('/^"(.*)"\s*/s', $this->_fileLine, $match);
        } while (sizeof($match) == 2);

        return [
            'msgid' => trim($msgid),
            'msgstr' => trim($msgstr)
        ];
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if ($this->_fp) {
            fclose($this->_fp);
        }
    }
}
