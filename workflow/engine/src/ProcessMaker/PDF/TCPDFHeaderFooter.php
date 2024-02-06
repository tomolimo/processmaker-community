<?php

namespace ProcessMaker\PDF;

use TCPDF;

class TCPDFHeaderFooter extends TCPDF
{
    /**
     * Property for configure header element.
     * @var HeaderStruct
     */
    private $headerStruct;

    /**
     * Property for configure footer element.
     * @var FooterStruct
     */
    private $footerStruct;

    /**
     * Save the original margins configured in the page.
     * @var array
     */
    private $originalMargins;

    /**
     * Constructor of the class.
     * @param string $orientation
     * @param string $unit
     * @param string $format
     * @param bool $unicode
     * @param string $encoding
     * @param bool $diskcache
     * @param bool $pdfa
     */
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->headerStruct = new HeaderStruct();
        $this->footerStruct = new FooterStruct();
    }

    /**
     * Destructor of the class.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Gets an object that contains the properties of the header.
     * @return HeaderStruct
     */
    public function getHeaderStruct(): HeaderStruct
    {
        return $this->headerStruct;
    }

    /**
     * Gets an object that contains the properties of the footer.
     * @return FooterStruct
     */
    public function getFooterStruct(): FooterStruct
    {
        return $this->footerStruct;
    }

    /**
     * This method is used to render the page header.
     * This method has been overwritten.
     */
    public function Header()
    {
        $heights = [];
        $struct = $this->getHeaderStruct();

        if (empty($this->originalMargins)) {
            $this->originalMargins = $this->getMargins();
        }
        $margins = $this->originalMargins;

        $this->buildHeaderLogo($struct, $margins, $heights);
        $this->buildHeaderTitle($struct, $margins, $heights);
        $this->buildHeaderPageNumber($struct, $margins, $heights);

        //page adjust
        $newHeight = max($heights);
        $this->SetTopMargin($newHeight);
    }

    /**
     * Build header logo.
     * @param HeaderStruct $struct
     * @param array $margins
     * @param array $heights
     * @return void
     */
    private function buildHeaderLogo(HeaderStruct $struct, array $margins, array &$heights): void
    {
        $path = $struct->getLogo();
        if (!file_exists($path)) {
            return;
        }
        $pathinfo = pathinfo($path);
        $imageSize = getimagesize($path);
        $extension = $pathinfo['extension'];
        $x = $struct->getLogoPositionX() + $margins['left'];
        $y = $struct->getLogoPositionY() + $margins['top'];
        $width = $struct->getLogoWidth();
        $this->Image($path, $x, $y, $width, 0, $extension, '', '', false, 300, '', false, false, 0, false, false, false);
        $newImageHeight = ($width * $imageSize[1] / $imageSize[0]);
        $heights[] = $margins['top'] + $newImageHeight;
    }

    /**
     * Build header title.
     * @param HeaderStruct $struct
     * @param array $margins
     * @param array $heights
     * @return void
     */
    private function buildHeaderTitle(HeaderStruct $struct, array $margins, array &$heights): void
    {
        $string = $struct->getTitle();
        $x = $struct->getTitleFontPositionX() + $margins['left'];
        $y = $struct->getTitleFontPositionY() + $margins['top'];
        $fontSize = $struct->getTitleFontSize();
        $this->SetXY($x, $y);
        $this->SetFont('helvetica', 'B', $fontSize);
        $this->MultiCell(0, 0, $string, 0, 'L', false, 1, '', '', true, 0, false, true, 0, 'T', false);
        $heights[] = $margins['top'] + ($this->getCellHeight($fontSize, false)) / 2;
    }

    /**
     * Build header page number.
     * @param HeaderStruct $struct
     * @param array $margins
     * @param array $heights
     * @return void
     */
    private function buildHeaderPageNumber(HeaderStruct $struct, array $margins, array &$heights): void
    {
        if ($struct->getPageNumber() === true) {
            $pageString = empty($struct->getPageNumberTitle()) ? 'Page ' : $struct->getPageNumberTitle() . ' ';
            $pageNumberTotal = $struct->getPageNumberTotal() === true ? ' / ' . $this->getAliasNbPages() : '';
            $string = $pageString . $this->getAliasNumPage() . $pageNumberTotal;
            $x = $struct->getPageNumberPositionX() + $margins['left'];
            $y = $struct->getPageNumberPositionY() + $margins['top'];
            $fontSize = 8;
            $this->SetXY($x, $y);
            $this->SetFont('helvetica', 'I', $fontSize);
            $this->Cell(0, 0, $string, 0, 0, '', false, '', 0, false, 'T', 'M');
            $heights[] = $margins['top'] + ($this->getCellHeight($fontSize, false)) / 2;
        }
    }

    /**
     * This method is used to render the page footer.
     * This method has been overwritten.
     */
    public function Footer()
    {
        $struct = $this->getFooterStruct();

        if (empty($this->originalMargins)) {
            $this->originalMargins = $this->getMargins();
        }
        $margins = $this->originalMargins;

        //page adjust
        $bottom = $margins['bottom'] <= 0 ? 1 : $margins['bottom'];
        $this->SetY(-1 * $bottom);
        $currentY = $this->GetY();

        $this->buildFooterLogo($margins, $currentY, $struct);
        $this->buildFooterTitle($margins, $currentY, $struct);
        $this->buildFooterPageNumber($margins, $currentY, $struct);
    }

    /**
     * Build footer logo.
     * @param array $margins
     * @param float $currentY
     * @param HeaderStruct $struct
     * @return void
     */
    private function buildFooterLogo(array $margins, float $currentY, FooterStruct $struct): void
    {
        $path = $struct->getLogo();
        if (!file_exists($path)) {
            return;
        }
        $pathinfo = pathinfo($path);
        $extension = $pathinfo['extension'];
        $x = $struct->getLogoPositionX() + $margins['left'];
        $y = $struct->getLogoPositionY() + $currentY;
        $width = $struct->getLogoWidth();
        $this->Image($path, $x, $y, $width, 0, $extension, '', '', false, 300, '', false, false, 0, false, false, false);
    }

    /**
     * Build footer title.
     * @param array $margins
     * @param float $currentY
     * @param HeaderStruct $struct
     * @return void
     */
    private function buildFooterTitle(array $margins, float $currentY, FooterStruct $struct): void
    {
        $string = $struct->getTitle();
        $x = $struct->getTitleFontPositionX() + $margins['left'];
        $y = $struct->getTitleFontPositionY() + $currentY;
        $fontSize = $struct->getTitleFontSize();
        $this->SetXY($x, $y);
        $this->SetFont('helvetica', 'B', $fontSize);
        $this->MultiCell(0, 0, $string, 0, 'L', false, 1, '', '', true, 0, false, true, 0, 'T', false);
    }

    /**
     * Build footer page number.
     * @param array $margins
     * @param float $currentY
     * @param HeaderStruct $struct
     * @return void
     */
    private function buildFooterPageNumber(array $margins, float $currentY, FooterStruct $struct): void
    {
        if ($struct->getPageNumber() === true) {
            $pageString = empty($struct->getPageNumberTitle()) ? 'Page ' : $struct->getPageNumberTitle() . ' ';
            $pageNumberTotal = $struct->getPageNumberTotal() === true ? ' / ' . $this->getAliasNbPages() : '';
            $string = $pageString . $this->getAliasNumPage() . $pageNumberTotal;
            $x = $struct->getPageNumberPositionX() + $margins['left'];
            $y = $struct->getPageNumberPositionY() + $currentY;
            $fontSize = 8;
            $this->SetXY($x, $y);
            $this->SetFont('helvetica', 'I', $fontSize);
            $this->Cell(0, 0, $string, 0, 0, '', false, '', 0, false, 'T', 'M');
        }
    }

}
