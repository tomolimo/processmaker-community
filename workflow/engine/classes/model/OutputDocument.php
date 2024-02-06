<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use ProcessMaker\Core\System;
use ProcessMaker\PDF\TCPDFHeaderFooter;

class OutputDocument extends BaseOutputDocument
{
    public function __construct()
    {
        $javaInput = PATH_C . 'javaBridgePM' . PATH_SEP . 'input' . PATH_SEP;
        $javaOutput = PATH_C . 'javaBridgePM' . PATH_SEP . 'output' . PATH_SEP;

        G::mk_dir($javaInput);
        G::mk_dir($javaOutput);
    }

    public function getByUid($sOutDocUid)
    {
        try {
            $oOutputDocument = OutputDocumentPeer::retrieveByPK($sOutDocUid);

            if (is_null($oOutputDocument)) {
                return false;
            }

            $aFields = $oOutputDocument->toArray(BasePeer::TYPE_FIELDNAME);
            $this->fromArray($aFields, BasePeer::TYPE_FIELDNAME);

            return $aFields;
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /*
     * Load the application document registry
     * @param string $sAppDocUid
     * @return variant
     */

    public function load($sOutDocUid)
    {
        try {
            $oOutputDocument = OutputDocumentPeer::retrieveByPK($sOutDocUid);

            if (!is_null($oOutputDocument)) {
                $aFields = $oOutputDocument->toArray(BasePeer::TYPE_FIELDNAME);
                $this->fromArray($aFields, BasePeer::TYPE_FIELDNAME);

                return $aFields;
            } else {
                throw(new Exception('This row doesn\'t exist!'));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Create the application document registry
     * @param array $aData
     * @return string
     * */
    public function create($aData)
    {
        $oConnection = Propel::getConnection(OutputDocumentPeer::DATABASE_NAME);

        try {
            if (isset($aData['OUT_DOC_UID']) && $aData['OUT_DOC_UID'] == '') {
                unset($aData['OUT_DOC_UID']);
            }

            if (!isset($aData['OUT_DOC_UID'])) {
                $aData['OUT_DOC_UID'] = G::generateUniqueID();
            }

            if (!isset($aData['OUT_DOC_GENERATE'])) {
                $aData['OUT_DOC_GENERATE'] = 'BOTH';
            } else {
                if ($aData['OUT_DOC_GENERATE'] == '') {
                    $aData['OUT_DOC_GENERATE'] = 'BOTH';
                }
            }

            $oOutputDocument = new OutputDocument();
            $oOutputDocument->fromArray($aData, BasePeer::TYPE_FIELDNAME);

            if ($oOutputDocument->validate()) {
                $oConnection->begin();

                if (isset($aData['OUT_DOC_TITLE'])) {
                    $oOutputDocument->setOutDocTitleContent($aData['OUT_DOC_TITLE']);
                }

                if (isset($aData['OUT_DOC_DESCRIPTION'])) {
                    $oOutputDocument->setOutDocDescriptionContent($aData['OUT_DOC_DESCRIPTION']);
                }

                $oOutputDocument->setOutDocFilenameContent($aData['OUT_DOC_FILENAME']);

                if (isset($aData['OUT_DOC_TEMPLATE'])) {
                    $oOutputDocument->setOutDocTemplateContent($aData['OUT_DOC_TEMPLATE']);
                }

                $iResult = $oOutputDocument->save();
                $oConnection->commit();
                //Add Audit Log
                $description = "Output Document Name: " . $aData['OUT_DOC_TITLE'] . ", Output Document Uid: " . $aData['OUT_DOC_UID'] . ", Filename generated: " . $aData['OUT_DOC_FILENAME'];
                if (!empty($aData['OUT_DOC_DESCRIPTION'])) {
                    $description .= ", Description: " . $aData['OUT_DOC_DESCRIPTION'];
                }
                if (!empty($aData['OUT_DOC_REPORT_GENERATOR'])) {
                    $description .= ", Report Generator: " . $aData['OUT_DOC_REPORT_GENERATOR'];
                }
                if (!empty($aData['OUT_DOC_GENERATE'])) {
                    $description .= ", Output Document to Generate: " . $aData['OUT_DOC_GENERATE'];
                }
                if ($aData['OUT_DOC_PDF_SECURITY_ENABLED'] == 0) {
                    $pdfSecurity = 'Disabled';
                } else {
                    $pdfSecurity = 'Enabled';
                }
                $description .= ", PDF Security: " . $pdfSecurity;
                if (!empty($aData['OUT_DOC_VERSIONING'])) {
                    $description .= ", Enable Versioning: Yes";
                }
                if (!empty($aData['OUT_DOC_DESTINATION_PATH'])) {
                    $description .= ", Destination Path: " . $aData['OUT_DOC_DESTINATION_PATH'];
                }
                if (!empty($aData['OUT_DOC_TAGS'])) {
                    $description .= ", Tags: " . $aData['OUT_DOC_TAGS'];
                }
                if (!empty($aData['OUT_DOC_OPEN_TYPE'])) {
                    if ($aData['OUT_DOC_OPEN_TYPE'] == 0) {
                        $genLink = 'Open the file';
                    } else {
                        $genLink = 'Download the file';
                    }
                    $description .= ", By clicking on the generated file link: " . $genLink;
                }
                G::auditLog("CreateOutputDocument", $description);

                return $aData['OUT_DOC_UID'];
            } else {
                $sMessage = '';
                $aValidationFailures = $oOutputDocument->getValidationFailures();

                foreach ($aValidationFailures as $oValidationFailure) {
                    $sMessage .= $oValidationFailure->getMessage() . '<br />';
                }

                throw (new Exception('The registry cannot be created!<br />' . $sMessage));
            }
        } catch (Exception $oError) {
            $oConnection->rollback();

            throw ($oError);
        }
    }

    /**
     * Update the application document registry
     * @param array $aData
     * @return string
     * */
    public function update($aData)
    {
        $oConnection = Propel::getConnection(OutputDocumentPeer::DATABASE_NAME);

        try {
            $oOutputDocument = OutputDocumentPeer::retrieveByPK($aData['OUT_DOC_UID']);

            if (!is_null($oOutputDocument)) {
                $oOutputDocument->fromArray($aData, BasePeer::TYPE_FIELDNAME);

                if ($oOutputDocument->validate()) {
                    $oConnection->begin();

                    if (isset($aData['OUT_DOC_TITLE'])) {
                        $oOutputDocument->setOutDocTitleContent($aData['OUT_DOC_TITLE']);
                    }

                    if (isset($aData['OUT_DOC_DESCRIPTION'])) {
                        $oOutputDocument->setOutDocDescriptionContent($aData['OUT_DOC_DESCRIPTION']);
                    }

                    if (isset($aData['OUT_DOC_FILENAME'])) {
                        $oOutputDocument->setOutDocFilenameContent($aData['OUT_DOC_FILENAME']);
                    }

                    if (isset($aData['OUT_DOC_TEMPLATE'])) {
                        $oOutputDocument->setOutDocTemplateContent($aData['OUT_DOC_TEMPLATE']);
                    }

                    $iResult = $oOutputDocument->save();
                    $oConnection->commit();
                    //Add Audit Log
                    $description = 'Output Document Uid: ' . $aData['OUT_DOC_UID'];

                    if (array_key_exists('OUT_DOC_TITLE', $aData) && (string)($aData['OUT_DOC_TITLE']) != '') {
                        $description .= ', Output Document Name: ' . $aData['OUT_DOC_TITLE'];
                    }

                    if (array_key_exists('OUT_DOC_FILENAME', $aData) && (string)($aData['OUT_DOC_FILENAME']) != '') {
                        $description .= ', Filename generated: ' . $aData['OUT_DOC_FILENAME'];
                    }

                    if (!empty($aData['OUT_DOC_DESCRIPTION'])) {
                        $description .= ", Description: " . $aData['OUT_DOC_DESCRIPTION'];
                    }
                    if (!empty($aData['OUT_DOC_REPORT_GENERATOR'])) {
                        $description .= ", Report Generator: " . $aData['OUT_DOC_REPORT_GENERATOR'];
                    }
                    if (!empty($aData['OUT_DOC_REPORT_GENERATOR'])) {
                        $description .= ", Output Document to Generate: " . $aData['OUT_DOC_GENERATE'];
                    }

                    if (array_key_exists('OUT_DOC_PDF_SECURITY_ENABLED', $aData) && (string)($aData['OUT_DOC_PDF_SECURITY_ENABLED']) != '') {
                        $description .= ', PDF Security: ' . (((int)($aData['OUT_DOC_PDF_SECURITY_ENABLED']) != 0) ? 'Enabled' : 'Disabled');
                    }

                    if (!empty($aData['OUT_DOC_VERSIONING'])) {
                        $description .= ", Enable Versioning: Yes";
                    }
                    if (!empty($aData['OUT_DOC_DESTINATION_PATH'])) {
                        $description .= ", Destination Path: " . $aData['OUT_DOC_DESTINATION_PATH'];
                    }
                    if (!empty($aData['OUT_DOC_TAGS'])) {
                        $description .= ", Tags: " . $aData['OUT_DOC_TAGS'];
                    }
                    if (!empty($aData['OUT_DOC_OPEN_TYPE'])) {
                        if ($aData['OUT_DOC_OPEN_TYPE'] == 0) {
                            $genLink = 'Open the file';
                        } else {
                            $genLink = 'Download the file';
                        }
                        $description .= ", By clicking on the generated file link: " . $genLink;
                    }
                    if (isset($aData['OUT_DOC_TEMPLATE'])) {
                        $description .= ", [EDIT TEMPLATE]";
                    }
                    G::auditLog("UpdateOutputDocument", $description);

                    return $iResult;
                } else {
                    $sMessage = '';
                    $aValidationFailures = $oOutputDocument->getValidationFailures();

                    foreach ($aValidationFailures as $oValidationFailure) {
                        $sMessage .= $oValidationFailure->getMessage() . '<br />';
                    }

                    throw (new Exception('The registry cannot be updated!<br />' . $sMessage));
                }
            } else {
                throw (new Exception('This row doesn\'t exist!'));
            }
        } catch (Exception $oError) {
            $oConnection->rollback();

            throw ($oError);
        }
    }

    /**
     * Remove the application document registry
     * @param array $aData
     * @return string
     * */
    public function remove($sOutDocUid)
    {
        $oConnection = Propel::getConnection(OutputDocumentPeer::DATABASE_NAME);

        try {
            $oOutputDocument = OutputDocumentPeer::retrieveByPK($sOutDocUid);

            if (!is_null($oOutputDocument)) {
                $oConnection->begin();
                Content::removeContent('OUT_DOC_TITLE', '', $oOutputDocument->getOutDocUid());
                Content::removeContent('OUT_DOC_DESCRIPTION', '', $oOutputDocument->getOutDocUid());
                Content::removeContent('OUT_DOC_FILENAME', '', $oOutputDocument->getOutDocUid());
                Content::removeContent('OUT_DOC_TEMPLATE', '', $oOutputDocument->getOutDocUid());
                $iResult = $oOutputDocument->delete();
                $oConnection->commit();

                //Add Audit Log
                G::auditLog("DeleteOutputDocument", "Output Document Name: " . $oOutputDocument->getOutDocTitle() . ", Output Document Uid: " . $sOutDocUid . ", Description: " . $oOutputDocument->getOutDocDescription() . ", Filename generated: " . $oOutputDocument->getOutDocFilename());

                //Return
                return $iResult;
            } else {
                throw (new Exception('This row doesn\'t exist!'));
            }
        } catch (Exception $oError) {
            $oConnection->rollback();

            throw ($oError);
        }
    }

    /**
     * Get the [out_doc_title] column value.
     * @return string
     */
    public function getOutDocTitleContent()
    {
        if ($this->out_doc_title == '') {
            try {
                $this->out_doc_title = Content::load(
                    'OUT_DOC_TITLE',
                    '',
                    $this->getOutDocUid(),
                    (defined('SYS_LANG') ? SYS_LANG : 'en')
                );
            } catch (Exception $oError) {
                throw ($oError);
            }
        }

        return $this->out_doc_title;
    }

    /**
     * Set the [out_doc_title] column value.
     *
     * @param string $sValue new value
     * @return void
     */
    public function setOutDocTitleContent($sValue)
    {
        if ($sValue !== null && !is_string($sValue)) {
            $sValue = (string)$sValue;
        }
        if (in_array(OutputDocumentPeer::OUT_DOC_TITLE, $this->modifiedColumns) || $sValue === '') {
            try {
                $this->out_doc_title = $sValue;

                $iResult = Content::addContent(
                    'OUT_DOC_TITLE',
                    '',
                    $this->getOutDocUid(),
                    (defined('SYS_LANG') ? SYS_LANG : 'en'),
                    $this->out_doc_title
                );
            } catch (Exception $oError) {
                $this->out_doc_title = '';

                throw ($oError);
            }
        }
    }

    /**
     * Get the [out_doc_comment] column value.
     * @return string
     */
    public function getOutDocDescriptionContent()
    {
        if ($this->out_doc_description == '') {
            try {
                $this->out_doc_description = Content::load(
                    'OUT_DOC_DESCRIPTION',
                    '',
                    $this->getOutDocUid(),
                    (defined('SYS_LANG') ? SYS_LANG : 'en')
                );
            } catch (Exception $oError) {
                throw ($oError);
            }
        }

        return $this->out_doc_description;
    }

    /**
     * Set the [out_doc_comment] column value.
     *
     * @param string $sValue new value
     * @return void
     */
    public function setOutDocDescriptionContent($sValue)
    {
        if ($sValue !== null && !is_string($sValue)) {
            $sValue = (string)$sValue;
        }

        if (in_array(OutputDocumentPeer::OUT_DOC_DESCRIPTION, $this->modifiedColumns) || $sValue === '') {
            try {
                $this->out_doc_description = $sValue;

                $iResult = Content::addContent(
                    'OUT_DOC_DESCRIPTION',
                    '',
                    $this->getOutDocUid(),
                    (defined('SYS_LANG') ? SYS_LANG : 'en'),
                    $this->out_doc_description
                );
            } catch (Exception $oError) {
                $this->out_doc_description = '';

                throw ($oError);
            }
        }
    }

    /**
     * Get the [out_doc_filename] column value.
     * @return string
     */
    public function getOutDocFilenameContent()
    {
        if ($this->out_doc_filename == '') {
            try {
                $this->out_doc_filename = Content::load(
                    'OUT_DOC_FILENAME',
                    '',
                    $this->getOutDocUid(),
                    (defined('SYS_LANG') ? SYS_LANG : 'en')
                );
            } catch (Exception $oError) {
                throw ($oError);
            }
        }

        return $this->out_doc_filename;
    }

    /**
     * Set the [out_doc_filename] column value.
     *
     * @param string $sValue new value
     * @return void
     */
    public function setOutDocFilenameContent($sValue)
    {
        if ($sValue !== null && !is_string($sValue)) {
            $sValue = (string)$sValue;
        }

        if (in_array(OutputDocumentPeer::OUT_DOC_FILENAME, $this->modifiedColumns) || $sValue === '') {
            try {
                $this->out_doc_filename = $sValue;

                $iResult = Content::addContent(
                    'OUT_DOC_FILENAME',
                    '',
                    $this->getOutDocUid(),
                    (defined('SYS_LANG') ? SYS_LANG : 'en'),
                    $this->out_doc_filename
                );
            } catch (Exception $oError) {
                $this->out_doc_filename = '';

                throw ($oError);
            }
        }
    }

    /**
     * Get the [out_doc_template] column value.
     * @return string
     */
    public function getOutDocTemplateContent()
    {
        if ($this->out_doc_template == '') {
            try {
                $this->out_doc_template = Content::load(
                    'OUT_DOC_TEMPLATE',
                    '',
                    $this->getOutDocUid(),
                    (defined('SYS_LANG') ? SYS_LANG : 'en')
                );
            } catch (Exception $oError) {
                throw ($oError);
            }
        }

        return $this->out_doc_template;
    }

    /**
     * Set the [out_doc_template] column value.
     *
     * @param string $sValue new value
     * @return void
     */
    public function setOutDocTemplateContent($sValue)
    {
        if ($sValue !== null && !is_string($sValue)) {
            $sValue = (string)$sValue;
        }

        if (in_array(OutputDocumentPeer::OUT_DOC_TEMPLATE, $this->modifiedColumns) || $sValue === '') {
            try {
                $this->out_doc_template = $sValue;

                $iResult = Content::addContent(
                    'OUT_DOC_TEMPLATE',
                    '',
                    $this->getOutDocUid(),
                    (defined('SYS_LANG') ? SYS_LANG : 'en'),
                    $this->out_doc_template
                );
            } catch (Exception $oError) {
                $this->out_doc_template = '';

                throw ($oError);
            }
        }
    }

    /*
     * Generate the output document
     * @param string $sUID
     * @param array $aFields
     * @param string $sPath
     * @return variant
     */

    public function generate($sUID, $aFields, $sPath, $sFilename, $sContent, $sLandscape = false, $sTypeDocToGener = 'BOTH', $aProperties = array())
    {
        if (($sUID != '') && is_array($aFields) && ($sPath != '')) {
            $sContent = G::replaceDataGridField($sContent, $aFields);

            if (strpos($sContent, '<!---{') !== false) {
                $template = new Smarty();
                $template->compile_dir = PATH_SMARTY_C;
                $template->cache_dir = PATH_SMARTY_CACHE;
                $template->config_dir = PATH_THIRDPARTY . 'smarty/configs';
                $template->caching = false;
                $template->left_delimiter = '<!---{';
                $template->right_delimiter = '}--->';
                $oFile = fopen($sPath . $sFilename . '_smarty.html', 'wb');
                fwrite($oFile, $sContent);
                fclose($oFile);
                $template->templateFile = $sPath . $sFilename . '_smarty.html';
                //assign the variables and use the template $template
                $template->assign($aFields);
                $sContent = $template->fetch($template->templateFile);
                unlink($template->templateFile);
            }

            G::verifyPath($sPath, true);

            //Start - Create .doc
            $oFile = fopen($sPath . $sFilename . '.doc', 'wb');

            $size = [];
            $size["Letter"] = "216mm  279mm";
            $size["Legal"] = "216mm  357mm";
            $size["Executive"] = "184mm  267mm";
            $size["B5"] = "182mm  257mm";
            $size["Folio"] = "216mm  330mm";
            $size["A0Oversize"] = "882mm  1247mm";
            $size["A0"] = "841mm  1189mm";
            $size["A1"] = "594mm  841mm";
            $size["A2"] = "420mm  594mm";
            $size["A3"] = "297mm  420mm";
            $size["A4"] = "210mm  297mm";
            $size["A5"] = "148mm  210mm";
            $size["A6"] = "105mm  148mm";
            $size["A7"] = "74mm   105mm";
            $size["A8"] = "52mm   74mm";
            $size["A9"] = "37mm   52mm";
            $size["A10"] = "26mm   37mm";
            $size["Screenshot640"] = "640mm  480mm";
            $size["Screenshot800"] = "800mm  600mm";
            $size["Screenshot1024"] = "1024mm 768mm";

            $sizeLandscape["Letter"] = "279mm  216mm";
            $sizeLandscape["Legal"] = "357mm  216mm";
            $sizeLandscape["Executive"] = "267mm  184mm";
            $sizeLandscape["B5"] = "257mm  182mm";
            $sizeLandscape["Folio"] = "330mm  216mm";
            $sizeLandscape["A0Oversize"] = "1247mm 882mm";
            $sizeLandscape["A0"] = "1189mm 841mm";
            $sizeLandscape["A1"] = "841mm  594mm";
            $sizeLandscape["A2"] = "594mm  420mm";
            $sizeLandscape["A3"] = "420mm  297mm";
            $sizeLandscape["A4"] = "297mm  210mm";
            $sizeLandscape["A5"] = "210mm  148mm";
            $sizeLandscape["A6"] = "148mm  105mm";
            $sizeLandscape["A7"] = "105mm  74mm";
            $sizeLandscape["A8"] = "74mm   52mm";
            $sizeLandscape["A9"] = "52mm   37mm";
            $sizeLandscape["A10"] = "37mm   26mm";
            $sizeLandscape["Screenshot640"] = "480mm  640mm";
            $sizeLandscape["Screenshot800"] = "600mm  800mm";
            $sizeLandscape["Screenshot1024"] = "768mm  1024mm";

            if (!isset($aProperties['media'])) {
                $aProperties['media'] = 'Letter';
            }

            if ($sLandscape) {
                $media = $sizeLandscape[$aProperties['media']];
            } else {
                $media = $size[$aProperties['media']];
            }

            $marginLeft = '15';

            if (isset($aProperties['margins']['left'])) {
                $marginLeft = $aProperties['margins']['left'];
            }

            $marginRight = '15';

            if (isset($aProperties['margins']['right'])) {
                $marginRight = $aProperties['margins']['right'];
            }

            $marginTop = '15';

            if (isset($aProperties['margins']['top'])) {
                $marginTop = $aProperties['margins']['top'];
            }

            $marginBottom = '15';

            if (isset($aProperties['margins']['bottom'])) {
                $marginBottom = $aProperties['margins']['bottom'];
            }

            fwrite($oFile, '<html xmlns:v="urn:schemas-microsoft-com:vml"
            xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:w="urn:schemas-microsoft-com:office:word"
            xmlns="http://www.w3.org/TR/REC-html40">
            <head>
            <meta http-equiv=Content-Type content="text/html; charset=utf-8">
            <meta name=ProgId content=Word.Document>
            <meta name=Generator content="Microsoft Word 9">
            <meta name=Originator content="Microsoft Word 9">
            <!--[if !mso]>
            <style>
            v\:* {behavior:url(#default#VML);}
            o\:* {behavior:url(#default#VML);}
            w\:* {behavior:url(#default#VML);}
            .shape {behavior:url(#default#VML);}
            </style>
            <![endif]-->
            <!--[if gte mso 9]><xml>
             <w:WordDocument>
              <w:View>Print</w:View>
              <w:DoNotHyphenateCaps/>
              <w:PunctuationKerning/>
              <w:DrawingGridHorizontalSpacing>9.35 pt</w:DrawingGridHorizontalSpacing>
              <w:DrawingGridVerticalSpacing>9.35 pt</w:DrawingGridVerticalSpacing>
             </w:WordDocument>
            </xml><![endif]-->

            <style>
            <!--
            @page WordSection1
             {size:' . $media . ';
             margin-left:' . $marginLeft . 'mm;
             margin-right:' . $marginRight . 'mm;
             margin-bottom:' . $marginBottom . 'mm;
             margin-top:' . $marginTop . 'mm;
             mso-header-margin:35.4pt;
             mso-footer-margin:35.4pt;
             mso-paper-source:0;}
            div.WordSection1
             {page:WordSection1;}
            -->
            </style>
            </head>
            <body>
            <div class=WordSection1>');

            fwrite($oFile, $sContent);
            fwrite($oFile, "\n</div></body></html>\n\n");
            fclose($oFile);
            /* End - Create .doc */

            if ($sTypeDocToGener == 'BOTH' || $sTypeDocToGener == 'PDF') {
                $oFile = fopen($sPath . $sFilename . '.html', 'wb');
                fwrite($oFile, $sContent);
                fclose($oFile);
                /* Start - Create .pdf */
                if (isset($aProperties['report_generator'])) {
                    switch ($aProperties['report_generator']) {
                        case 'TCPDF':
                        default:
                            $this->generateTcpdf($sUID, $aFields, $sPath, $sFilename, $sContent, $sLandscape, $aProperties);
                            break;
                    }
                } else {
                    $this->generateTcpdf($sUID, $aFields, $sPath, $sFilename, $sContent, $sLandscape, $aProperties);
                }
            }
            //end if $sTypeDocToGener
            /* End - Create .pdf */
        } else {
            return PEAR::raiseError(
                null,
                G_ERROR_USER_UID,
                null,
                null,
                'You tried to call to a generate method without send the Output Document UID, fields to use and the file path!',
                'G_Error',
                true
            );
        }
    }

    public function generateHtml2pdf($sUID, $aFields, $sPath, $sFilename, $sContent, $sLandscape = false, $aProperties = array())
    {

        // define("MAX_FREE_FRACTION", 1);
        define('PATH_OUTPUT_FILE_DIRECTORY', PATH_HTML . 'files/' . $_SESSION['APPLICATION'] . '/outdocs/');
        G::verifyPath(PATH_OUTPUT_FILE_DIRECTORY, true);

        require_once(PATH_THIRDPARTY . 'html2pdf/html2pdf.class.php');

        // define Save file
        $sOutput = 2;
        $sOrientation = ($sLandscape == false) ? 'P' : 'L';
        $sLang = (defined('SYS_LANG')) ? SYS_LANG : 'en';
        $sMedia = $aProperties['media'];
        // margin define
        define("MINIMAL_MARGIN", 15);
        $marges = array(MINIMAL_MARGIN, MINIMAL_MARGIN, MINIMAL_MARGIN, MINIMAL_MARGIN);
        if (isset($aProperties['margins'])) {
            // Default marges (left, top, right, bottom)
            $margins = $aProperties['margins'];
            $margins['left'] = ($margins['left'] > 0) ? $margins['left'] : MINIMAL_MARGIN;
            $margins['top'] = ($margins['top'] > 0) ? $margins['top'] : MINIMAL_MARGIN;
            $margins['right'] = ($margins['right'] > 0) ? $margins['right'] : MINIMAL_MARGIN;
            $margins['bottom'] = ($margins['bottom'] > 0) ? $margins['bottom'] : MINIMAL_MARGIN;
            $marges = array($margins['left'], $margins['top'], $margins['right'], $margins['bottom']);
        }

        $html2pdf = new HTML2PDF($sOrientation, $sMedia, $sLang, true, 'UTF-8', $marges);

        $html2pdf->pdf->SetAuthor($aFields['USR_USERNAME']);
        $html2pdf->pdf->SetTitle('Processmaker');
        $html2pdf->pdf->SetSubject($sFilename);
        $html2pdf->pdf->SetCompression(true);

        //$html2pdf->pdf->SetKeywords('HTML2PDF, TCPDF, processmaker');

        if (isset($aProperties['pdfSecurity'])) {
            $pdfSecurity = $aProperties['pdfSecurity'];
            $userPass = G::decrypt($pdfSecurity['openPassword'], $sUID);
            $ownerPass = ($pdfSecurity['ownerPassword'] != '') ? G::decrypt($pdfSecurity['ownerPassword'], $sUID) : null;
            $permissions = explode("|", $pdfSecurity['permissions']);
            $html2pdf->pdf->SetProtection($permissions, $userPass, $ownerPass);
        }

        $html2pdf->setTestTdInOnePage(false);
        $html2pdf->setTestIsImage(false);
        $html2pdf->setTestIsDeprecated(false);

        $html2pdf->writeHTML($html2pdf->getHtmlFromPage($sContent));

        switch ($sOutput) {
            case 0:
                // Vrew browser
                $html2pdf->Output($sPath . $sFilename . '.pdf', 'I');
                break;
            case 1:
                // Donwnload
                $html2pdf->Output($sPath . $sFilename . '.pdf', 'D');
                break;
            case 2:
                // Save file
                $html2pdf->Output($sPath . $sFilename . '.pdf', 'F');
                break;
        }

        copy($sPath . $sFilename . '.html', PATH_OUTPUT_FILE_DIRECTORY . $sFilename . '.html');

        copy(PATH_OUTPUT_FILE_DIRECTORY . $sFilename . '.pdf', $sPath . $sFilename . '.pdf');
        unlink(PATH_OUTPUT_FILE_DIRECTORY . $sFilename . '.pdf');
        unlink(PATH_OUTPUT_FILE_DIRECTORY . $sFilename . '.html');
    }

    /**
     * Generate a PDF file using the TCPDF library
     *
     * @param string $outDocUid
     * @param array $fields
     * @param string $path
     * @param string $filename
     * @param string $content
     * @param bool $landscape
     * @param array $properties
     *
     * @see generate()
     * @see \ProcessMaker\BusinessModel\Cases\OutputDocument::generate()
     *
     * @link https://wiki.processmaker.com/3.3/Output_Documents#Creating_Output_Documents_Usign_TCPDF_Generator
     */
    public function generateTcpdf($outDocUid, $fields, $path, $filename, $content, $landscape = false, $properties = [])
    {
        // Check and prepare the fonts path used by TCPDF library
        self::checkTcPdfFontsPath();

        // Initialize variables
        $nrt = ["\n", "\r", "\t"];
        $nrtHtml = ["(n /)", "(r /)", "(t /)"];
        $outputType = 2;
        // Page orientation (P=portrait, L=landscape).
        $orientation = ($landscape == false) ? 'P' : 'L';
        $media = (isset($properties['media'])) ? $properties['media'] : 'A4';
        $lang = (defined('SYS_LANG')) ? SYS_LANG : 'en';
        $strContentAux = str_replace($nrt, $nrtHtml, $content);
        $content = null;

        // Convert the deprecated "font" tags into "style" tags
        while (preg_match("/^(.*)<font([^>]*)>(.*)$/i", $strContentAux, $arrayMatch)) {
            $str = trim($arrayMatch[2]);
            $strAttribute = null;

            if (!empty($str)) {
                $strAux = $str;
                $str = null;

                while (preg_match("/^(.*)([\"'].*[\"'])(.*)$/", $strAux, $arrayMatch2)) {
                    $strAux = $arrayMatch2[1];
                    $str = str_replace(" ", "__SPACE__", $arrayMatch2[2]) . $arrayMatch2[3] . $str;
                }

                $str = $strAux . $str;

                // Get attributes
                $strStyle = null;
                $array = explode(" ", $str);

                foreach ($array as $value) {
                    $arrayAux = explode("=", $value);

                    if (isset($arrayAux[1])) {
                        $a = trim($arrayAux[0]);
                        $v = trim(str_replace(["__SPACE__", "\"", "'"], [" ", null, null], $arrayAux[1]));

                        switch (strtolower($a)) {
                            case "color":
                                $strStyle = $strStyle . "color: $v;";
                                break;
                            case "face":
                                $strStyle = $strStyle . "font-family: $v;";
                                break;
                            case "size":
                                $arrayPt = [0, 8, 10, 12, 14, 18, 24, 36];
                                $strStyle = $strStyle . "font-size: " . $arrayPt[intval($v)] . "pt;";
                                break;
                            case "style":
                                $strStyle = $strStyle . "$v;";
                                break;
                            default:
                                $strAttribute = $strAttribute . " $a=\"$v\"";
                                break;
                        }
                    }
                }

                if ($strStyle != null) {
                    $strAttribute = $strAttribute . " style=\"$strStyle\"";
                }
            }

            $strContentAux = $arrayMatch[1];
            $content = "<span" . $strAttribute . ">" . $arrayMatch[3] . $content;
        }

        // Replenish the content
        $content = $strContentAux . $content;

        // Replace some remaining incorrect/deprecated HTML tags/properties
        $content = str_ireplace("</font>", "</span>", $content);
        $content = str_replace($nrtHtml, $nrt, $content);
        $content = str_replace("margin-left", "text-indent", $content);

        // Instance the TCPDF library
        $pdf = new TCPDFHeaderFooter($orientation, PDF_UNIT, strtoupper($media), true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($fields['USR_USERNAME']);
        $pdf->SetTitle('ProcessMaker');
        $pdf->SetSubject($filename);
        $pdf->SetCompression(true);

        // Define margins
        $margins = $properties['margins'];
        $margins["left"] = ($margins["left"] >= 0) ? $margins["left"] : PDF_MARGIN_LEFT;
        $margins["top"] = ($margins["top"] >= 0) ? $margins["top"] : PDF_MARGIN_TOP;
        $margins["right"] = ($margins["right"] >= 0) ? $margins["right"] : PDF_MARGIN_RIGHT;
        $margins["bottom"] = ($margins["bottom"] >= 0) ? $margins["bottom"] : PDF_MARGIN_BOTTOM;

        // Set margins configuration
        $headerOptions = $this->setHeaderOptions($pdf, $fields);
        $footerOptions = $this->setFooterOptions($pdf, $fields);
        $pdf->setPrintHeader($headerOptions);
        $pdf->setPrintFooter($footerOptions);
        // Important: footer position depends on header enable
        if ($footerOptions === true) {
            $pdf->setPrintHeader(true);
        }

        $pdf->SetLeftMargin($margins['left']);
        $pdf->SetTopMargin($margins['top']);
        $pdf->SetRightMargin($margins['right']);
        $pdf->SetAutoPageBreak(true, $margins['bottom']);

        // Get ServerConf singleton
        $serverConf = ServerConf::getSingleton();

        // Set language configuration
        $lg = [];
        $lg['a_meta_charset'] = 'UTF-8';
        $lg['a_meta_dir'] = ($serverConf->isRtl($lang)) ? 'rtl' : 'ltr';
        $lg['a_meta_language'] = $lang;
        $lg['w_page'] = 'page';
        $pdf->setLanguageArray($lg);

        // Set security configuration
        if (isset($properties['pdfSecurity'])) {
            $tcPdfPermissions = ['print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'];
            $pdfSecurity = $properties['pdfSecurity'];
            $userPass = G::decrypt($pdfSecurity['openPassword'], $outDocUid);
            $ownerPass = ($pdfSecurity['ownerPassword'] != '') ? G::decrypt($pdfSecurity['ownerPassword'], $outDocUid) : null;
            $permissions = explode("|", $pdfSecurity['permissions']);
            $permissions = array_diff($tcPdfPermissions, $permissions);
            $pdf->SetProtection($permissions, $userPass, $ownerPass);
        }

        // Enable the font sub-setting option
        $pdf->setFontSubsetting(true);

        // Set default unicode font if is required, we need to detect if is chinese, japanese, thai, etc.
        if (preg_match('/[\x{30FF}\x{3040}-\x{309F}\x{4E00}-\x{9FFF}\x{0E00}-\x{0E7F}]/u', $content, $matches)) {
            // The additional fonts should be in "shared/fonts" folder
            $fileArialUniTTF = PATH_DATA . 'fonts' . PATH_SEP . 'arialuni.ttf';
            if (file_exists($fileArialUniTTF)) {
                // Convert TTF file to the format required by TCPDF library
                $tcPdfFileName = TCPDF_FONTS::addTTFfont($fileArialUniTTF, 'TrueTypeUnicode');

                // Set the default unicode font for the document
                $pdf->SetFont($tcPdfFileName);

                // Register the font file if is not present in the JSON file
                if (!self::existTcpdfFont('arialuni.ttf')) {
                    // Add font "arialuni.ttf"
                    $font = [
                        'fileName' => 'arialuni.ttf',
                        'tcPdfFileName' => $tcPdfFileName,
                        'familyName' => $tcPdfFileName,
                        'inTinyMce' => true,
                        'friendlyName' => $tcPdfFileName,
                        'properties' => ''
                    ];
                    self::addTcPdfFont($font);
                }
            }
        }

        // Convert the encoding of the content if is UTF-8
        if (mb_detect_encoding($content) == 'UTF-8') {
            $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
        }

        // Fix the HTML using DOMDocument class
        libxml_use_internal_errors(true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        if ($content != '') {
            $doc->loadHtml($content);
            foreach (libxml_get_errors() as $error) {
                $detail = (array) $error;
                Log::channel(':OutputDocument::generateTcpdf')->warning('DOMDocument::loadHtml', Bootstrap::context($detail));
            }
        }
        libxml_clear_errors();

        // Add a page and put the HTML fixed
        $pdf->AddPage();
        $pdf->writeHTML($doc->saveXML(), false, false, false, false, '');

        // Generate the PDF file
        switch ($outputType) {
            case 0:
                // Browser
                $pdf->Output($path . $filename . '.pdf', 'I');
                break;
            case 1:
                // Download
                $pdf->Output($path . $filename . '.pdf', 'D');
                break;
            case 2:
                // Save to file
                $pdf->Output($path . $filename . '.pdf', 'F');
                break;
        }
    }

    /**
     * verify if Output row specified in [sUid] exists.
     *
     * @param      string $sUid the uid of the Prolication
     */
    public function OutputExists($sUid)
    {
        $con = Propel::getConnection(OutputDocumentPeer::DATABASE_NAME);

        try {
            $oObj = OutputDocumentPeer::retrieveByPk($sUid);

            if (is_object($oObj) && get_class($oObj) == 'OutputDocument') {
                return true;
            } else {
                return false;
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Check and prepare the fonts path used by TCPDF library
     *
     * @param string $folderName
     */
    public static function checkTcPdfFontsPath($folderName = 'tcpdf')
    {
        if (!defined('K_PATH_FONTS')) {
            // Define the path of the fonts, "K_PATH_FONTS" is a constant used by "TCPDF" library
            define('K_PATH_FONTS', PATH_DATA . 'fonts' . PATH_SEP . $folderName . PATH_SEP);
        }

        // Check if already exists the path, if not exist we need to prepare the same
        if (!file_exists(K_PATH_FONTS)) {
            // Instance Filesystem class
            $filesystem = new Filesystem();

            // Create the missing folder(s)
            $filesystem->makeDirectory(K_PATH_FONTS, 0755, true, true);

            // Copy files related to the fonts from vendors
            $filesystem->copyDirectory(PATH_TRUNK . 'vendor' . PATH_SEP . 'tecnickcom' . PATH_SEP . 'tcpdf' . PATH_SEP . 'fonts' . PATH_SEP, K_PATH_FONTS);

            // Copy files related to the fonts from core
            $filesystem->copyDirectory(PATH_CORE . 'content' . PATH_SEP . 'tcPdfFonts' . PATH_SEP, K_PATH_FONTS);
        }
    }

    /**
     * Load the custom fonts list
     *
     * @return array
     */
    public static function loadTcPdfFontsList()
    {
        // Initialize variables
        $jsonFilePath = K_PATH_FONTS . 'fonts.json';

        // Load the custom fonts list
        if (file_exists($jsonFilePath)) {
            $fonts = json_decode(file_get_contents($jsonFilePath), true);
        } else {
            $fonts = [];
        }

        return $fonts;
    }

    /**
     * Save the custom fonts list
     *
     * @param $fonts
     */
    public static function saveTcPdfFontsList($fonts)
    {
        // Initialize variables
        $jsonFilePath = K_PATH_FONTS . 'fonts.json';

        // Save the JSON file
        file_put_contents($jsonFilePath, json_encode($fonts));
    }

    /**
     * Check if a font file name exist in the fonts list
     *
     * @param string $fontFileName
     * @return bool
     */
    public static function existTcpdfFont($fontFileName)
    {
        // Load the custom fonts list
        $fonts = self::loadTcPdfFontsList();

        // Exist?
        return isset($fonts[$fontFileName]);
    }

    /**
     * Add a custom font to be used by TCPDF library
     *
     * @param array $font
     */
    public static function addTcPdfFont($font)
    {
        // Load the custom fonts list
        $fonts = self::loadTcPdfFontsList();

        // Add the font
        $fonts[$font['fileName']] = $font;

        // Save the fonts list
        self::saveTcPdfFontsList($fonts);

        // Re-generate CSS file
        self::generateCssFile();
    }

    /**
     * Remove a custom font used in TCPDF library
     *
     * @param string $fileName
     */
    public static function removeTcPdfFont($fileName)
    {
        // Load the custom fonts list
        $fonts = self::loadTcPdfFontsList();

        // Add the font
        unset($fonts[$fileName]);

        // Save the fonts list
        self::saveTcPdfFontsList($fonts);

        // Re-generate CSS file
        self::generateCssFile();
    }

    /**
     * Generate CSS with the fonts definition to be used by TinyMCE editor
     */
    public static function generateCssFile()
    {
        // Initialize variables
        $template = "@font-face {font-family: @familyName;src: url('/fonts/font.php?file=@fileName') format('truetype');@properties}\n";
        $css = '';

        // Load the custom fonts list
        $fonts = self::loadTcPdfFontsList();

        // Build the CSS content
        foreach ($fonts as $font) {
            if ($font['inTinyMce']) {
                $css .= str_replace(['@familyName', '@fileName', '@properties'],
                    [$font['familyName'], $font['fileName'], $font['properties']], $template);
            }
        }

        // Save the CSS file
        file_put_contents(K_PATH_FONTS . 'fonts.css', $css);
    }
    
    /**
     * Set and build if header options exist.
     * @param TCPDFHeaderFooter $pdf
     * @param array $fields
     * @return bool
     */
    private function setHeaderOptions(TCPDFHeaderFooter $pdf, array $fields): bool
    {
        if (empty($this->out_doc_header)) {
            return false;
        }
        $header = json_decode($this->out_doc_header);
        if ($header->enableHeader === false) {
            return false;
        }

        $struct = $pdf->getHeaderStruct();

        $struct->setLogo(G::replaceDataField($header->logo ?? '', $fields));
        $struct->setLogoWidth($header->logoWidth ?? 0);
        $struct->setLogoPositionX($header->logoPositionX ?? 0);
        $struct->setLogoPositionY($header->logoPositionY ?? 0);

        $struct->setTitle(G::replaceDataField($header->title ?? '', $fields));
        $struct->setTitleFontSize($header->titleFontSize ?? 0);
        $struct->setTitleFontPositionX($header->titleFontPositionX ?? 0);
        $struct->setTitleFontPositionY($header->titleFontPositionY ?? 0);

        $struct->setPageNumber($header->pageNumber ?? false);
        $struct->setPageNumberTitle(G::replaceDataField($header->pageNumberTitle ?? '', $fields));
        $struct->setPageNumberTotal($header->pageNumberTotal ?? false);
        $struct->setPageNumberPositionX($header->pageNumberPositionX ?? 0);
        $struct->setPageNumberPositionY($header->pageNumberPositionY ?? 0);
        return true;
    }

    /**
     * Set and build if footer options exist.
     * @param TCPDFHeaderFooter $pdf
     * @param array $fields
     * @return bool
     */
    private function setFooterOptions(TCPDFHeaderFooter $pdf, array $fields): bool
    {
        if (empty($this->out_doc_footer)) {
            return false;
        }
        $footer = json_decode($this->out_doc_footer);
        if ($footer->enableFooter === false) {
            return false;
        }

        $struct = $pdf->getFooterStruct();

        $struct->setLogo(G::replaceDataField($footer->logo ?? '', $fields));
        $struct->setLogoWidth($footer->logoWidth ?? 0);
        $struct->setLogoPositionX($footer->logoPositionX ?? 0);
        $struct->setLogoPositionY($footer->logoPositionY ?? 0);

        $struct->setTitle(G::replaceDataField($footer->title ?? '', $fields));
        $struct->setTitleFontSize($footer->titleFontSize ?? 0);
        $struct->setTitleFontPositionX($footer->titleFontPositionX ?? 0);
        $struct->setTitleFontPositionY($footer->titleFontPositionY ?? 0);

        $struct->setPageNumber($footer->pageNumber ?? false);
        $struct->setPageNumberTitle(G::replaceDataField($footer->pageNumberTitle ?? '', $fields));
        $struct->setPageNumberTotal($footer->pageNumberTotal ?? false);
        $struct->setPageNumberPositionX($footer->pageNumberPositionX ?? 0);
        $struct->setPageNumberPositionY($footer->pageNumberPositionY ?? 0);
        return true;
    }
}
