<?php

abstract class Image
{
////////////////////////////////// Properties \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @var Dimensions
     */
    protected $m_oDimensions;

    protected $m_rImage;

    protected $m_sType;
    protected $m_sImageSourcePath;

    protected $m_bAlpha;
    protected $m_bDebug = false;

    // Font Default Settings
    protected $m_iFont = 5;
    protected $m_sFontDirectory;
    protected $m_sFontPath;
    protected $m_iFontAngle = 0;
    protected $m_iFontSize;

    // GIF min=0 - max=100, PNG 0=no compression -> max quality, 9=max compression -> min quality? What to Do?
    protected $m_iQuality = 100;

////////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param \Dimensions $p_oDimensions
     */
    protected function setDimensions($p_oDimensions)
    {
        $this->m_oDimensions = $p_oDimensions;
    }

    /**
     * @return \Dimensions
     */
    public function getDimensions()
    {
        return $this->m_oDimensions;
    }

    public function setAlpha($m_bAlpha)
    {
        $this->m_bAlpha = $m_bAlpha;
    }

    public function getAlpha()
    {
        return $this->m_bAlpha;
    }

    /**
     * @return integer
     */
    public function getHeight()
    {
        $iHeight = null;

        if($this->m_oDimensions instanceof Dimensions)
        {
            $iHeight = $this->m_oDimensions->getHeight();
        }

        return $iHeight;
    }

    /**
     * @return integer
     */
    public function getWidth()
    {
        $iWidth = null;

        if($this->m_oDimensions instanceof Dimensions)
        {
            $iWidth = $this->m_oDimensions->getWidth();
        }

        return $iWidth;
    }

    public function setImage($m_rImage)
    {
        $this->m_rImage = $m_rImage;
    }

    public function getImage()
    {
        return $this->m_rImage;
    }

    public function setType($m_sType)
    {
        $this->m_sType = $m_sType;
    }

    public function getType()
    {
        return $this->m_sType;
    }

    public function setQuality($m_iQuality)
    {
        $this->m_iQuality = $m_iQuality;
    }

    public function getQuality()
    {
        $iQuality = $this->m_iQuality;

        switch($this->getType())
        {
            case 'png':
                $iQuality = 9;
            break;
        }#switch

        return $iQuality;
    }

    public function setImageResource($p_rImage)
    {
        //@TODO: Check $p_rImage is actually an image resource
        $this->m_rImage = $p_rImage;
    }

    public function getImageResource()
    {
        return $this->m_rImage;
    }

    public function setSourcePath($p_sImageSourcePath)
    {
        $this->m_sImageSourcePath = $p_sImageSourcePath;
    }

    public function getSourcePath()
    {
        return $this->m_sImageSourcePath;
    }


    public function setFontSize($p_iFontSize)
    {
        $this->m_iFontSize = (int) $p_iFontSize;
    }

    public function getFontSize()
    {
        return $this->m_iFontSize;
    }

////////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    public function __construct(Dimensions $p_oDimensions, $p_sType, $p_bAlpha=false)
    {
        $this->setDimensions($p_oDimensions);
        $this->setType($p_sType);
        $this->setAlpha($p_bAlpha);
    }

    public function create()
    {
        $this->m_rImage = imagecreatetruecolor($this->getWidth(), $this->getHeight());
        if($this->getAlpha() === true)
        {
            imagealphablending($this->m_rImage, false);
            imagefilledrectangle($this->m_rImage
                , 0 , 0
                , $this->getWidth(), $this->getHeight()
                , imagecolorallocatealpha($this->m_rImage, 0, 0, 0, 127)
            );
            imagealphablending($this->m_rImage, true);
        }#if

        if(isset($this->m_sImageSourcePath))
        {
            $this->importFromFile($this->m_sImageSourcePath);
        }#if

        $this->debug();

        return $this;
     }

    public function output()
    {
        $sImage = null;

        if(!headers_sent())
        {
            header('Content-type: image/' . $this->getType());
            ob_start();

            imagesavealpha($this->m_rImage, true);
            call_user_func('image' . $this->getType()
                , $this->m_rImage
                , null                    // if filename is include an actual file is created
                , $this->getQuality()
            );

            $sImage = ob_get_clean();
        }
        else
        {
            throw new Exception('Header Already sent... no use in making image.');
        }

        imagedestroy($this->m_rImage);

        return $sImage;
    }

//////////////////////////////// Helper Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    protected function importFromFile($p_sImageFilePath)
    {
            if(!is_file($p_sImageFilePath))
            {
                throw new Exception('Could not find a file at given image path "' . $p_sImageFilePath . '"');
            }
            else
            {
                $aInfo = getimagesize($p_sImageFilePath);

                $iWidth  = $aInfo[0];
                $iHeight = $aInfo[1];

                $this->setType(substr($aInfo['mime'], strpos($aInfo['mime'], '/')+1));

                //@TODO: Use type of image and call imagecreatefrom* accordingly
                $rImage = @imagecreatefrompng($p_sImageFilePath);

                if(!$rImage)
                {
                    throw new Exception('Could not load image from path "' . $p_sImageFilePath . '"');
                }
                else
                {
                    $this->m_rImage = imagecreatetruecolor($this->getWidth(), $this->getHeight());

                    imagecopyresampled($this->m_rImage, $rImage
                        , 0, 0, 0, 0
                        , $this->getWidth(), $this->getHeight()
                        , $iWidth, $iHeight
                    );
                }#if
            }#if
        }

    protected function drawRectangleFilled($p_iTopLeftX, $p_iTopLeftY, $p_iBottomRightX, $p_iBottomRightY, $p_iColor)
    {
        imagefilledrectangle($this->getImage(), $p_iTopLeftX, $p_iTopLeftY, $p_iBottomRightX, $p_iBottomRightY, $p_iColor);

        return $this;
    }

    protected function drawRectangle($p_iTopLeftX, $p_iTopLeftY, $p_iBottomRightX, $iBottomRightY, $p_iColor, $p_iWidth=null)
    {
        if(isset($p_iWidth))
        {
            imagesetthickness($this->getImage(), (int) $p_iWidth);
        }#if

        imagerectangle ($this->getImage(), $p_iTopLeftX, $p_iTopLeftY, $p_iBottomRightX, $iBottomRightY, $p_iColor);

        if(isset($p_iWidth))
        {
            imagesetthickness($this->getImage(), 1);
        }#if

        return $this;
    }

    /**
     * @throws Exception
     *
     * @param $p_sText
     *
     * @return BoundingBox
     */
    protected function getBoundingBoxForText($p_sText)
    {
        $aBoundingBox = imagettfbbox(
            $this->m_iFontSize, $this->m_iFontAngle
            , $this->m_sFontDirectory . $this->m_sFontPath
            , $p_sText
        );

        if ($aBoundingBox === false)
        {
            throw new Exception('Could not create bounding box');
        }
        else
        {
            return BoundingBox::fromArray($aBoundingBox);
        }#if
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
    protected function writeText($p_sText, $p_iX, $p_iY, $p_iColor, $p_iKerning=0)
    {

        if ($p_iKerning > 0)
        {
            $oBoundingBox = $this->writeTextWithKerning($p_sText, $p_iX, $p_iY, $p_iColor, $p_iKerning);
        }
        else
        {
            $aBoundingBox = imagettftext(
                $this->m_rImage
                , $this->m_iFontSize, $this->m_iFontAngle
                , $p_iX, $p_iY
                , $p_iColor
                , $this->m_sFontDirectory . $this->m_sFontPath
                , $p_sText
            );
            $oBoundingBox = BoundingBox::fromArray($aBoundingBox);
        }#if

        return $oBoundingBox;
    }

    protected function _writeTextWithKerning($p_sText, $p_iX, $p_iY, $p_iColor, $p_iKerning)
    {
        $iX = $p_iX;
        foreach (explode('', $p_sText) as $t_sLetter)
        {
            $aBoundingBox = $this->writeText($t_sLetter, $p_iX, $p_iY, $p_iColor);
            $iX += $aBoundingBox[2] + $p_iKerning;
        }#foreach

        //@TODO: Calculate $aBoundingBox so we can return it;
    }

    protected function writeTextWithKerning($p_sText, $p_iX, $p_iY, $p_iColor, $p_iKerning)
    {
        $iX = $p_iX;
        $iLength = strlen($p_sText);
        for ($iCounter = 0; $iCounter < $iLength; $iCounter++)
        {
            $t_oBoundingBox = $this->writeText($p_sText[$iCounter], $iX, $p_iY, $p_iColor);
            $iX += $p_iKerning + ($t_oBoundingBox->offsetGet(2) - $t_oBoundingBox->offsetGet(0));

            if(!isset($oBoundingBox))
            {
                $oBoundingBox = $t_oBoundingBox;
            }#if

            if($iCounter === $iLength)
            {
                $oBoundingBox->offsetSet('LowerRightX', $t_oBoundingBox->offsetGet(BoundingBox::LOWER_RIGHT_X));
                $oBoundingBox->offsetSet('UpperRightX', $t_oBoundingBox->offsetGet(BoundingBox::UPPER_RIGHT_X));
            }#if

            if($t_oBoundingBox->getHeight() > $oBoundingBox->getHeight())
            {
                $oBoundingBox->offsetSet('UpperLeftY', $t_oBoundingBox->offsetGet(BoundingBox::UPPER_LEFT_Y));
                $oBoundingBox->offsetSet('UpperRightY', $t_oBoundingBox->offsetGet(BoundingBox::UPPER_RIGHT_Y));
            }#if
        }#for

        if(!isset($oBoundingBox))
        {
            throw new Exception('!');
        }#if

        return $oBoundingBox;
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
     * @return void
     */
    protected function writeTextWithBorder($p_sText, $p_iX, $p_iY, $p_iColor, $p_iBorderWidth, $p_iBorderColor, $p_iKerning=0)
    {
        for($t_iCounter=0;$t_iCounter<=$p_iBorderWidth;$t_iCounter++)
        {

            $this->writeText($p_sText, $p_iX+$t_iCounter, $p_iY-$p_iBorderWidth, $p_iBorderColor, $p_iKerning);
            $this->writeText($p_sText, $p_iX+$t_iCounter, $p_iY+$p_iBorderWidth, $p_iBorderColor, $p_iKerning);
            $this->writeText($p_sText, $p_iX-$p_iBorderWidth, $p_iY-$t_iCounter, $p_iBorderColor, $p_iKerning);
            $this->writeText($p_sText, $p_iX-$p_iBorderWidth, $p_iY+$t_iCounter, $p_iBorderColor, $p_iKerning);
        }#for

        return $this->writeText($p_sText, $p_iX, $p_iY, $p_iColor, $p_iKerning);
    }

    protected function writeString($p_sText, $p_iX, $p_iY, $p_iColor)
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

    protected function debug($p_sMethodName=null)
    {
        if($this->m_bDebug === true)
        {
            if(!isset($p_sMethodName))
            {

                $aTrace = debug_backtrace();
                $sMethodName = $aTrace[1]['function'];
            }
            else{
                $sMethodName = $p_sMethodName;
            }#if

            $iThickness = $this->getWidth()/350;

            switch($sMethodName)
            {
                case 'create':

                    $iThickness = $this->getWidth()/350;
                    $this->drawRectangle(
                          0, 0
                        , $this->getWidth()-$iThickness
                        , $this->getHeight()-$iThickness
                        , imagecolorallocate($this->m_rImage, 0xFF, 0x00,0x00)
                        , $iThickness
                    );
                break;
            }#switch
        }#if
    }

}


#EOF