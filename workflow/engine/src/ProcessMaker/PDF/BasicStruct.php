<?php

namespace ProcessMaker\PDF;

/**
 * Struct for header and footer configuration.
 */
trait BasicStruct
{
    /**
     * Path to image file.
     * @var string
     */
    private $logo = '';

    /**
     * Width to image file, the height is calculated automatically proportional 
     * to the original dimensions.
     * @var float
     */
    private $logoWidth = 0;

    /**
     * Position of the image with respect to the ordinate X since left margin.
     * @var float
     */
    private $logoPositionX = 0;

    /**
     * Position of the image with respect to the ordinate Y since top margin.
     * @var float
     */
    private $logoPositionY = 0;

    /**
     * Title of the page.
     * @var string
     */
    private $title = '';

    /**
     * Font size of the title.
     * @var float
     */
    private $titleFontSize = 0;

    /**
     * Position of the title with respect to the ordinate X since left margin.
     * @var float
     */
    private $titleFontPositionX = 0;

    /**
     * Position of the title with respect to the ordinate Y since top margin.
     * @var float
     */
    private $titleFontPositionY = 0;

    /**
     * Indicates if the page number is displayed.
     * @var bool
     */
    private $pageNumber = false;

    /**
     * Alternative text to indicate the numbered page.
     * @var string
     */
    private $pageNumberTitle = '';

    /**
     * Indicates if the pages total is displayed.
     * @var bool
     */
    private $pageNumberTotal = false;

    /**
     * Position of the page number with respect to the ordinate X since left margin.
     * @var float
     */
    private $pageNumberPositionX = 0;

    /**
     * Position of the page number with respect to the ordinate Y since bottom margin.
     * @var float
     */
    private $pageNumberPositionY = 0;

    /**
     * Get logo property.
     * @return string
     */
    public function getLogo(): string
    {
        return $this->logo;
    }

    /**
     * Get logoWidth property.
     * @return float
     */
    public function getLogoWidth(): float
    {
        return $this->logoWidth;
    }

    /**
     * Get logoPositionX property.
     * @return float
     */
    public function getLogoPositionX(): float
    {
        return $this->logoPositionX;
    }

    /**
     * Get logoPositionY property.
     * @return float
     */
    public function getLogoPositionY(): float
    {
        return $this->logoPositionY;
    }

    /**
     * Get title property.
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get titleFontSize property.
     * @return float
     */
    public function getTitleFontSize(): float
    {
        return $this->titleFontSize;
    }

    /**
     * Get titleFontPositionX property.
     * @return float
     */
    public function getTitleFontPositionX(): float
    {
        return $this->titleFontPositionX;
    }

    /**
     * Get titleFontPositionY property.
     * @return float
     */
    public function getTitleFontPositionY(): float
    {
        return $this->titleFontPositionY;
    }

    /**
     * Get pageNumber property.
     * @return bool
     */
    public function getPageNumber(): bool
    {
        return $this->pageNumber;
    }

    /**
     * Get pageNumberTitle property.
     * @return string
     */
    public function getPageNumberTitle(): string
    {
        return $this->pageNumberTitle;
    }

    /**
     * Get pageNumberTotal property.
     * @return bool
     */
    public function getPageNumberTotal(): bool
    {
        return $this->pageNumberTotal;
    }

    /**
     * Get pageNumberPositionX property.
     * @return float
     */
    public function getPageNumberPositionX(): float
    {
        return $this->pageNumberPositionX;
    }

    /**
     * Get pageNumberPositionY property.
     * @return float
     */
    public function getPageNumberPositionY(): float
    {
        return $this->pageNumberPositionY;
    }

    /**
     * Set property title.
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Set property titleFontSize.
     * @param float $titleFontSize
     * @return void
     */
    public function setTitleFontSize(float $titleFontSize): void
    {
        $this->titleFontSize = $titleFontSize;
    }

    /**
     * Set property titleFontPositionX.
     * @param float $titleFontPositionX
     * @return void
     */
    public function setTitleFontPositionX(float $titleFontPositionX): void
    {
        $this->titleFontPositionX = $titleFontPositionX;
    }

    /**
     * Set property titleFontPositionY.
     * @param float $titleFontPositionY
     * @return void
     */
    public function setTitleFontPositionY(float $titleFontPositionY): void
    {
        $this->titleFontPositionY = $titleFontPositionY;
    }

    /**
     * Set property logo.
     * @param string $logo
     * @return void
     */
    public function setLogo(string $logo): void
    {
        $this->logo = $logo;
    }

    /**
     * Set property logoWidth.
     * @param float $logoWidth
     * @return void
     */
    public function setLogoWidth(float $logoWidth): void
    {
        $this->logoWidth = $logoWidth;
    }

    /**
     * Set property logoPositionX.
     * @param float $logoPositionX
     * @return void
     */
    public function setLogoPositionX(float $logoPositionX): void
    {
        $this->logoPositionX = $logoPositionX;
    }

    /**
     * Set property logoPositionY.
     * @param float $logoPositionY
     * @return void
     */
    public function setLogoPositionY(float $logoPositionY): void
    {
        $this->logoPositionY = $logoPositionY;
    }

    /**
     * Set property pageNumber.
     * @param bool $pageNumber
     * @return void
     */
    public function setPageNumber(bool $pageNumber): void
    {
        $this->pageNumber = $pageNumber;
    }

    /**
     * Set property pageNumberTitle.
     * @param string $pageNumberTitle
     * @return void
     */
    public function setPageNumberTitle(string $pageNumberTitle): void
    {
        $this->pageNumberTitle = $pageNumberTitle;
    }

    /**
     * Set property pageNumberTotal.
     * @param bool $pageNumberTotal
     * @return void
     */
    public function setPageNumberTotal(bool $pageNumberTotal): void
    {
        $this->pageNumberTotal = $pageNumberTotal;
    }

    /**
     * Set property pageNumberPositionX.
     * @param float $pageNumberPositionX
     * @return void
     */
    public function setPageNumberPositionX(float $pageNumberPositionX): void
    {
        $this->pageNumberPositionX = $pageNumberPositionX;
    }

    /**
     * Set property pageNumberPositionY.
     * @param float $pageNumberPositionY
     * @return void
     */
    public function setPageNumberPositionY(float $pageNumberPositionY): void
    {
        $this->pageNumberPositionY = $pageNumberPositionY;
    }

}
