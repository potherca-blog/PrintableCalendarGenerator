<?php

namespace Potherca\PrintableCalendarGenerator;

/**
 * @TODO: Move all logic to a separate Drawing class
 */
abstract class AbstractImage extends \Potherca\Base\Decision
{
    //////////////////////////////// Properties \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @var bool Whether to write log file or not */
    private $m_bLogging = false;
    /**
     * @var AbstractDimensions
     */
    private $m_oDimensions;
    /**
     * @var resource
     */
    private $m_rImage;
    /**
     * @var string
     */
    private $m_sType;
    /**
     * @var string
     */
    private $m_sImageSourcePath;
    /**
     * @var bool
     */
    private $m_bAlpha;
    /**
     * @var bool
     */
    private $m_bDebug = false;
    // Font Default Settings
    /**
     * @var int
     */
    private $m_iFont = 5;
    /**
     * @var string
     */
    private $m_sFontDirectory;
    /**
     * @var string
     */
    private $m_sFontPath;
    /**
     * @var int
     */
    private $m_iFontAngle = 0;
    /**
     * @var integer
     */
    private $m_iFontSize;
    // GIF min=0 - max=100, PNG 0=no compression -> max quality, 9=max compression -> min quality? What to Do?
    /**
     * @var int
     */
    private $m_iQuality = 100;

    //////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param AbstractDimensions $p_oDimensions
     */
    private function setDimensions($p_oDimensions)
    {
        $this->m_oDimensions = $p_oDimensions;
    }

    /**
     * @return AbstractDimensions
     */
    final public function getDimensions()
    {
        return $this->m_oDimensions;
    }

    /**
     * @return integer
     */
    final public function getWidth()
    {
        $iWidth = null;

        if ($this->m_oDimensions instanceof AbstractDimensions) {
            $iWidth = $this->m_oDimensions->getWidth();
        }

        return $iWidth;
    }

    /**
     * @return integer
     */
    final public function getHeight()
    {
        $iHeight = null;

        if ($this->m_oDimensions instanceof AbstractDimensions) {
            $iHeight = $this->m_oDimensions->getHeight();
        }

        return $iHeight;
    }

    /**
     * @param $m_sType
     */
    final public function setType($m_sType)
    {
        $this->m_sType = $m_sType;
    }

    /**
     * @return string
     */
    final public function getType()
    {
        return $this->m_sType;
    }

    /**
     * @param $m_bAlpha
     */
    final public function setAlpha($m_bAlpha)
    {
        $this->m_bAlpha = $m_bAlpha;
    }

    /**
     * @return bool
     */
    final public function getAlpha()
    {
        return $this->m_bAlpha;
    }

    /**
     * @return string
     */
    final public function getFontPath()
    {
        return $this->m_sFontPath;
    }

    /**
     * @param string $p_sFontPath
     */
    final public function setFontPath($p_sFontPath)
    {
        $this->m_sFontPath = $p_sFontPath;
    }

    /**
     * @param bool $p_bDebug
     */
    final public function setDebug($p_bDebug)
    {
        $this->m_bDebug = (bool) $p_bDebug;
    }

    /**
     * @return bool
     */
    final public function getDebug()
    {
        return $this->m_bDebug;
    }

    /**
     * @return boolean
     */
    final public function getLogging()
    {
        return $this->m_bLogging;
    }

    /**
     * @param boolean $p_bLogging
     */
    final public function setLogging($p_bLogging)
    {
        $this->m_bLogging = $p_bLogging;
    }

    /**
     * @param $m_iQuality
     */
    final public function setQuality($m_iQuality)
    {
        $this->m_iQuality = $m_iQuality;
    }

    /**
     * @return int
     */
    final public function getQuality()
    {
        $iQuality = $this->m_iQuality;

        switch ($this->getType()) {
            case 'png':
                $iQuality = 9;
                break;
        }#switch

        return $iQuality;
    }

    /**
     * @param $p_rImage
     */
    private function setImageResource($p_rImage)
    {
        //@TODO: Check $p_rImage is actually an image resource
        $this->m_rImage = $p_rImage;
    }

    /**
     * @return resource
     */
    private function getImageResource()
    {
        return $this->m_rImage;
    }

    /**
     * @param $p_sImageSourcePath
     */
    final public function setSourcePath($p_sImageSourcePath)
    {
        $this->m_sImageSourcePath = $p_sImageSourcePath;
    }

    /**
     * @return string
     */
    final public function getSourcePath()
    {
        return $this->m_sImageSourcePath;
    }

    /**
     * @param $p_iFontSize
     */
    final public function setFontSize($p_iFontSize)
    {
        $this->m_iFontSize = (int) $p_iFontSize;
    }

    /**
     * @return int
     */
    final public function getFontSize()
    {
        return $this->m_iFontSize;
    }

    /**
     * @param string $p_sFontDirectory
     */
    final public function setFontDirectory($p_sFontDirectory)
    {
        $this->m_sFontDirectory = $p_sFontDirectory;
    }

    /**
     * @return string
     */
    final public function getFontDirectory()
    {
        return $this->m_sFontDirectory;
    }

    //////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * @param AbstractDimensions $p_oDimensions
     * @param $p_sType
     * @param bool $p_bAlpha
     */
    public function __construct(
        AbstractDimensions $p_oDimensions,
        $p_sType,
        $p_bAlpha = false
    ) {
        $this->setDimensions($p_oDimensions);
        $this->setType($p_sType);
        $this->setAlpha($p_bAlpha);
    }

    /**
     * @return AbstractImage
     */
    public function create()
    {
        $this->log('Creating Image - %sx%s (ImageSourcePath = "%s")',
            $this->m_sImageSourcePath, $this->getWidth(), $this->getHeight()
        );
        $this->createImageResource();

        if ($this->getAlpha() === true) {
            imagealphablending($this->m_rImage, false);
            imagefilledrectangle($this->m_rImage
                , 0, 0
                , $this->getWidth(), $this->getHeight()
                , $this->allocateColor(0, 0, 0, 127)
            );
            imagealphablending($this->m_rImage, true);
        }

        if (isset($this->m_sImageSourcePath)) {
            $this->importFromFile($this->m_sImageSourcePath);
        }

        $this->debug();

        return $this;
    }

    /**
     * @param int $p_iRed
     * @param int $p_iGreen
     * @param int $p_iBlue
     * @param int $p_iAlpha Value between 0 (completely opaque) and 127 (completely transparent)
     *
     * @throws Exception
     *
     * @return int
     */
    final public function allocateColor($p_iRed, $p_iGreen, $p_iBlue, $p_iAlpha = 0)
    {
        if (is_resource($this->getImageResource()) === false) {
            throw new \UnexpectedValueException(
                'Cannot allocate color, Image has not yet been created.'
                . ' Please invoke the "create" or "loadFromFile" method before allocating colors.'
            );
        } else {
            $this->log('Allocating Color - rgba(%s, %s, %s, %s)',$p_iRed, $p_iGreen, $p_iBlue, $p_iAlpha);
            return imagecolorallocatealpha($this->getImageResource(), $p_iRed, $p_iGreen, $p_iBlue, $p_iAlpha);
        }
    }

    /**
     * @param int $p_iColor
     *
     * @return int
     */
    final public function defineTransparentColor($p_iColor)
    {
        return imagecolortransparent($this->getImageResource(), $p_iColor);
    }

    /**
     * @param $p_iTopLeftX
     * @param $p_iTopLeftY
     * @param $p_iBottomRightX
     * @param $iBottomRightY
     * @param $p_iColor
     * @param null $p_iWidth
     *
     * @return AbstractImage
     */
    final public function drawRectangle(
        $p_iTopLeftX,
        $p_iTopLeftY,
        $p_iBottomRightX,
        $iBottomRightY,
        $p_iColor,
        $p_iWidth = null
    ) {
        if (isset($p_iWidth)) {
            imagesetthickness($this->getImageResource(), (int) $p_iWidth);
        }

        imagerectangle($this->getImageResource(), $p_iTopLeftX, $p_iTopLeftY,
            $p_iBottomRightX, $iBottomRightY, $p_iColor);

        if (isset($p_iWidth)) {
            imagesetthickness($this->getImageResource(), 1);
        }

        return $this;
    }

    /**
     * @throws Exception
     *
     * @return string
     */
    final public function output()
    {
        $sImage = null;

        if (!headers_sent()) {
            header('Content-type: image/' . $this->getType());
            ob_start();

            imagesavealpha($this->m_rImage, true);
            call_user_func('image' . $this->getType()
                , $this->m_rImage
                , null// if filename is include an actual file is created
                , $this->getQuality()
            );

            $sImage = ob_get_clean();
        } else {
            throw new Exception('Header Already sent... no use in making image.');
        }

        imagedestroy($this->m_rImage);

        return $sImage;
    }

    /**
     * @param $p_iTopLeftX
     * @param $p_iTopLeftY
     * @param $p_iBottomRightX
     * @param $p_iBottomRightY
     * @param $p_iColor
     *
     * @return AbstractImage
     */
    final public function drawRectangleFilled(
        $p_iTopLeftX,
        $p_iTopLeftY,
        $p_iBottomRightX,
        $p_iBottomRightY,
        $p_iColor
    ) {
        $this->log('Drawing Filled Rectangle - %s/%s, %s/%s, %s', $p_iTopLeftX, $p_iTopLeftY, $p_iBottomRightX, $p_iBottomRightY, $p_iColor);
        return imagefilledrectangle($this->getImageResource(), $p_iTopLeftX, $p_iTopLeftY, $p_iBottomRightX, $p_iBottomRightY, $p_iColor);
    }

    /**
     * @throws Exception
     *
     * @param $p_sText
     *
     * @return BoundingBox
     */
    final public function getBoundingBoxForText($p_sText)
    {
        $aBoundingBox = imagettfbbox(
            $this->m_iFontSize, $this->m_iFontAngle
            , $this->m_sFontDirectory . $this->m_sFontPath
            , $p_sText
        );

        if ($aBoundingBox === false) {
            throw new Exception('Could not create bounding box');
        } else {
            return BoundingBox::fromArray($aBoundingBox);
        }
    }

    /**
     * Write a text in the image with a border.
     *
     * The border appears on the *outside* of the letters,
     *
     * @param $p_sText
     * @param $p_iX
     * @param $p_iY
     * @param $p_iColor
     * @param $p_iBorderWidth
     * @param $p_iBorderColor
     * @param $p_iKerning
     *
     * @return BoundingBox
     */
    final public function writeTextWithBorder(
        $p_sText,
        $p_iX,
        $p_iY,
        $p_iColor,
        $p_iBorderWidth,
        $p_iBorderColor,
        $p_iKerning = 0
    ) {
        for ($t_iCounter = 0; $t_iCounter <= $p_iBorderWidth; $t_iCounter++) {

            $this->writeText($p_sText, $p_iX + $t_iCounter,
                $p_iY - $p_iBorderWidth, $p_iBorderColor, $p_iKerning);
            $this->writeText($p_sText, $p_iX + $t_iCounter,
                $p_iY + $p_iBorderWidth, $p_iBorderColor, $p_iKerning);
            $this->writeText($p_sText, $p_iX - $p_iBorderWidth,
                $p_iY - $t_iCounter, $p_iBorderColor, $p_iKerning);
            $this->writeText($p_sText, $p_iX - $p_iBorderWidth,
                $p_iY + $t_iCounter, $p_iBorderColor, $p_iKerning);
        }

        return $this->writeText($p_sText, $p_iX, $p_iY, $p_iColor, $p_iKerning);
    }

    /**
     * @param $p_sText
     * @param $p_iX
     * @param $p_iY
     * @param $p_iColor
     * @param int $p_iKerning
     *
     * @return BoundingBox
     */
    final public function writeText(
        $p_sText,
        $p_iX,
        $p_iY,
        $p_iColor,
        $p_iKerning = 0
    ) {

        if ($p_iKerning > 0) {
            $oBoundingBox = $this->writeTextWithKerning($p_sText, $p_iX, $p_iY,
                $p_iColor, $p_iKerning);
        } else {
            $aBoundingBox = imagettftext(
                $this->m_rImage
                , $this->m_iFontSize, $this->m_iFontAngle
                , $p_iX, $p_iY
                , $p_iColor
                , $this->m_sFontDirectory . $this->m_sFontPath
                , $p_sText
            );
            $oBoundingBox = BoundingBox::fromArray($aBoundingBox);
        }

        return $oBoundingBox;
    }

    /**
     * @param $p_oSourceImage
     * @param $p_iDestinationX
     * @param $p_iDestinationY
     * @param $p_iSourceX
     * @param $p_iSourceY
     * @param $p_iDestinationWidth
     * @param $p_iDestinationHeight
     * @param $p_iSourceWidth
     * @param $p_iSourceHeight
     *
     * @return bool
     */
    final public function copyFromImage(
        $p_oSourceImage,
        $p_iDestinationX,
        $p_iDestinationY,
        $p_iSourceX,
        $p_iSourceY,
        $p_iDestinationWidth,
        $p_iDestinationHeight,
        $p_iSourceWidth,
        $p_iSourceHeight
    ) {
        if ($p_oSourceImage instanceof AbstractImage) {
            $p_oSourceImage = $p_oSourceImage->getImageResource();
        }
        return imagecopyresampled(
            $this->getImageResource()
            , $p_oSourceImage
            , $p_iDestinationX, $p_iDestinationY
            , $p_iSourceX, $p_iSourceY
            , $p_iDestinationWidth, $p_iDestinationHeight
            , $p_iSourceWidth, $p_iSourceHeight
        );
    }

    ////////////////////////////// Helper Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    private function createImageResource()
    {
        $rImage = imagecreatetruecolor($this->getWidth(), $this->getHeight());
        $this->setImageResource($rImage);
    }

    /**
     * @param $p_sImageFilePath
     *
     * @throws Exception
     *
     * @return bool
     */
    private function importFromFile($p_sImageFilePath)
    {
        if (!is_file($p_sImageFilePath)) {
            throw new Exception('Could not find a file at given image path "' . $p_sImageFilePath . '"');
        } else {
            $aInfo = getimagesize($p_sImageFilePath);

            $iWidth = $aInfo[0];
            $iHeight = $aInfo[1];

            $this->setType(substr($aInfo['mime'], strpos($aInfo['mime'], '/') + 1));

            //@TODO: Use type of image and call `imageCreateFrom[TYPE]()` accordingly
            $rImage = @imagecreatefrompng($p_sImageFilePath);

            if (!$rImage) {
                throw new Exception('Could not load image from path "' . $p_sImageFilePath . '"');
            } else {
                $this->createImageResource();

                $bImported = $this->copyFromImage($rImage
                    , 0, 0, 0, 0
                    , $this->getWidth(), $this->getHeight()
                    , $iWidth, $iHeight
                );
            }
        }

        return $bImported;
    }

    /**
     * @param $p_sText
     * @param $p_iX
     * @param $p_iY
     * @param $p_iColor
     * @param $p_iKerning
     *
     * @return BoundingBox
     *
     * @throws Exception
     */
    private function writeTextWithKerning(
        $p_sText,
        $p_iX,
        $p_iY,
        $p_iColor,
        $p_iKerning
    ) {
        $iX = $p_iX;
        $iLength = strlen($p_sText);
        for ($iCounter = 0; $iCounter < $iLength; $iCounter++) {
            $t_oBoundingBox = $this->writeText($p_sText[$iCounter], $iX, $p_iY,
                $p_iColor);
            $iX += $p_iKerning + ($t_oBoundingBox->offsetGet(2) - $t_oBoundingBox->offsetGet(0));

            if (!isset($oBoundingBox)) {
                $oBoundingBox = $t_oBoundingBox;
            }

            if ($iCounter === $iLength) {
                $oBoundingBox->offsetSet('LowerRightX',
                    $t_oBoundingBox->offsetGet(BoundingBox::LOWER_RIGHT_X));
                $oBoundingBox->offsetSet('UpperRightX',
                    $t_oBoundingBox->offsetGet(BoundingBox::UPPER_RIGHT_X));
            }

            if ($t_oBoundingBox->getHeight() > $oBoundingBox->getHeight()) {
                $oBoundingBox->offsetSet('UpperLeftY',
                    $t_oBoundingBox->offsetGet(BoundingBox::UPPER_LEFT_Y));
                $oBoundingBox->offsetSet('UpperRightY',
                    $t_oBoundingBox->offsetGet(BoundingBox::UPPER_RIGHT_Y));
            }
        }

        if (!isset($oBoundingBox)) {
            throw new Exception('!');
        }

        return $oBoundingBox;
    }

    /**
     * @param $p_sMessage
     */
    private function log($p_sMessage)
    {
        if ($this->getLogging() === true) {
            static $bLoaded;

            $aArguments = func_get_args();

            $sFile = 'debug.calendar.log';

            if ($bLoaded !== true) {
                file_put_contents(
                    $sFile
                    ,
                    '# ==============================================================================' . PHP_EOL
                );
                $bLoaded = true;
            }

            if (is_string($aArguments[0]) && strpos($aArguments[0],
                    '%s') !== false
            ) {
                $sMessage = call_user_func_array('sprintf', $aArguments);
            } else {
                $sMessage = '';
                foreach ($aArguments as $uArgument) {
                    $sMessage .= var_export($uArgument, true);
                }
            }

            file_put_contents($sFile, $sMessage . PHP_EOL, FILE_APPEND);
        }
    }

    /**
     * @param $p_sText
     * @param $p_iX
     * @param $p_iY
     * @param $p_iColor
     * @param $p_iKerning
     */
    private function _writeTextWithKerning(
        $p_sText,
        $p_iX,
        $p_iY,
        $p_iColor,
        $p_iKerning
    ) {
        $iX = $p_iX;
        foreach (explode('', $p_sText) as $t_sLetter) {
            $aBoundingBox = $this->writeText($t_sLetter, $p_iX, $p_iY,
                $p_iColor);
            $iX += $aBoundingBox[2] + $p_iKerning;
        }

        //@TODO: Calculate $aBoundingBox so we can return it;
    }

    /**
     * @param $p_sText
     * @param $p_iX
     * @param $p_iY
     * @param $p_iColor
     *
     * @return bool
     */
    private function writeString($p_sText, $p_iX, $p_iY, $p_iColor)
    {
        return imagestring(
            $this->m_rImage
            , $this->m_iFont
            , $p_iX
            , $p_iY
            , $p_sText
            , $p_iColor
        );
    }
    /*
            imagearc();
            imageellipse();
            imagepolygon();
            imagerectangle();

            imagefilledarc();
            imagefilledellipse();
            imagefilledpolygon();
            imagefilledrectangle()

            imagefill();
            imagefilltoborder();

            imagerotate();

            imagedashedline();
            imageline();
            imagesetbrush();
            imagesetpixel();
            imagesetstyle();
            imagesetthickness();
            imagesettile();

            imagestring()
            imagestringup()

    */

    /**
     * @param string $p_sMethodName
     */
    private function debug($p_sMethodName = null)
    {
        if ($this->m_bDebug === true) {
            if (!isset($p_sMethodName)) {

                $aTrace = debug_backtrace();
                $sMethodName = $aTrace[1]['function'];
            } else {
                $sMethodName = $p_sMethodName;
            }

            $iThickness = $this->getWidth() / 350;

            switch ($sMethodName) {
                case 'create':

                    #$iThickness = $this->getWidth()/350;
                    $this->drawRectangle(
                        0, 0
                        , $this->getWidth() - $iThickness
                        , $this->getHeight() - $iThickness
                        , $this->allocateColor(0xFF, 0x00, 0x00)
                        , $iThickness
                    );
                    break;
            }#switch
        }
    }

}

/*EOF*/
