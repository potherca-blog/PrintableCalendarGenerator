<?php
/**
 *
 */
class Calendar extends Image
{

////////////////////////////////// Properties \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @var CalendarDimensions
     */
    protected $m_oDimensions;

    /**
     * @var \DateInterval
     */
    protected $m_oOneDay;

    /**
     * @var
     */
    protected $m_aColors;

    /**
     * @var bool
     */
    protected $m_bDebug=false;

    /**
     * @var array DayBlockDimensions
     */
    protected $m_aDateCoordinates = array();

    /**
     * @var array
     */
    protected $m_aApliedDecorations = array();

////////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param $p_aColors
     */
    public function setColors($p_aColors)
    {
        $this->m_aColors = $p_aColors;
    }

    /**
     * @return array
     */
    public function getColors()
    {
        return $this->m_aColors;
    }

    /**
     * @return array
     */
    public function getDateCoordinates()
    {
        return $this->m_aDateCoordinates;
    }

// 10 px = $this->getWidth()/175.4
//  8 px = $this->getWidth()/219.25
//  6 px = $this->getWidth()/292.33333333333
//  4 px = $this->getWidth()/438.5;
//  2 px = $this->getWidth()/877;
////////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param Dimensions $p_oDimensions
     * @param string $p_sType
     * @param bool $p_bAlpha
     */
    public function __construct(Dimensions $p_oDimensions, $p_sType='png', $p_bAlpha=true)
    {
        parent::__construct($p_oDimensions, $p_sType, $p_bAlpha);

        $this->m_oOneDay = new DateInterval('P1D');
    }

    public function create()
    {
        parent::create();

        $this->m_sFontDirectory = './fonts';
        $this->m_sFontPath      = '/erasblkb.pfb';

        DayBlockDimensions::setDimensionsFromParent($this->m_oDimensions);

        $this->buildColors();
    }

    /**
     * @param array $p_aColorSets
     * @param array $p_sBackgroundColor
     *
     * @return mixed
     */
    public function buildColors($p_aColorSets = array(), $p_sBackgroundColor=array('0xFF', '0xFF','0xFF'))
    {

        if(!isset($this->m_rImage))
        {
            throw new Exception('Cannot allocate colors, Image has not yet been created. Please invoke the "create" or "loadFromFile" method before trying to build colors.');
        }
        else
        {
            // set background color first
            $this->m_aColors = array(
                'background' => imagecolorallocate($this->m_rImage, $p_sBackgroundColor[0], $p_sBackgroundColor[1], $p_sBackgroundColor[2]),
            );

            // set common colors
            $this->m_aColors['white'] = imagecolorallocate($this->m_rImage, 0xFF, 0xFF,0xFF);
            $this->m_aColors['black'] = imagecolorallocate($this->m_rImage, 0x00, 0x00,0x00);

            $this->m_aColors['red']   = imagecolorallocate($this->m_rImage, 0xFF, 0x00,0x00);
            $this->m_aColors['blue']  = imagecolorallocate($this->m_rImage, 0x00, 0x00,0xFF);
            $this->m_aColors['green'] = imagecolorallocate($this->m_rImage, 0x00, 0xFF,0x00);

            $this->m_aColors['magenta'] = imagecolorallocate($this->m_rImage, 0xFF, 0x00,0xFF);
            $this->m_aColors['cyan']    = imagecolorallocate($this->m_rImage, 0x00, 0xFF,0xFF);
            $this->m_aColors['yellow']  = imagecolorallocate($this->m_rImage, 0xFF, 0xFF,0x00);

            $this->m_aColors['Weekend'] = imagecolorallocate($this->m_rImage, 0xBF, 0xBF, 0xBF);
            $this->m_aColors['Holiday'] = imagecolorallocate($this->m_rImage, 0xAA, 0xAB, 0xAA);

            $this->m_aColors['Week_Nr']         = imagecolorallocate($this->m_rImage, 0xCD, 0xCD, 0xCC);
            $this->m_aColors['Week_Nr_Border']  = imagecolorallocate($this->m_rImage, 0x66, 0x66, 0x66);
            $this->m_aColors['Week_Nr_Divider'] = imagecolorallocate($this->m_rImage, 0xBF, 0xBC, 0xBC);

            $this->m_aColors[DecorationType::BIRTHDAY]         = imagecolorallocatealpha($this->m_rImage, 0xFF, 0xFF, 0x00, 64);
            $this->m_aColors[DecorationType::NATIONAL_HOLIDAY] = imagecolorallocatealpha($this->m_rImage, 0x00, 0xFF, 0xFF, 64);
            $this->m_aColors[DecorationType::SCHOOL_HOLIDAY]   = imagecolorallocate($this->m_rImage, 0x99, 0x9A, 0x99);//, 0xFF, 0x00, 0xFF, 64);
            $this->m_aColors[DecorationType::SECULAR_HOLIDAY]  = imagecolorallocatealpha($this->m_rImage, 0x00, 0x00, 0xFF, 64);

            return $this->m_aColors;
        }
    }

    /**
     * @TODO: Replace $p_aDecorations array with DecorationCollection object
     * @param DateTime $p_oDate
     * @param array[Decoration] $p_aDecorations
     *
     * @return null|string
     */
    public function render(DateTime $p_oDate, Array $p_aDecorations)
    {
        // Create an Image
        $this->create();

        $this->calculateDateCoordinates($p_oDate);

        //@TODO: implements methods needed by $this->drawBase();
        //$this->drawBase();

        $this->drawDecorationBackgrounds($p_aDecorations);

        $this->writeMonth($p_oDate);

        $this->writeWeekNumbers($p_oDate);

        $this->writeDayNumbers($p_oDate);

        $this->drawDecorations($p_aDecorations);

        return $this->output();
    }


//////////////////////////////// Helper Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param DateTime $p_oDate
     *
     * @return DateTime
     */
    protected static function buildDateForDayNumbers(DateTime $p_oDate)
    {
        $oDate = clone $p_oDate;

        $oDate->sub(new DateInterval('P' . $oDate->format('w') . 'D'));

        if ($oDate->format('M') === $p_oDate->format('M')) {
            $oDate->sub(new DateInterval('P1W'));
            return $oDate;
        }#if

        return $oDate;
    }

    /**
     * @param DateTime $oDate
     * @param $p_iColumnCounter
     * @param $p_iRowCounter
     * @param $p_iX
     * @param $p_iY
     */
    protected function storeDateCoordinates(DateTime $oDate
        , $p_iColumnCounter, $p_iRowCounter, $p_iX, $p_iY)
    {
        $oDimensions = DayBlockDimensions::createFromParentDimensions();

        $oDimensions->setColumn($p_iColumnCounter);
        $oDimensions->setRow($p_iRowCounter);

        $oDimensions->setX($p_iX);
        $oDimensions->setY($p_iY);

        $this->m_aDateCoordinates[$oDate->format('Ymd')] = $oDimensions;
    }

    /**
     * @param Decoration $p_oDecoration
     *
     * @return DayBlockDimensions
     */
    protected function getDimensionsForDecoration(Decoration $p_oDecoration)
    {
        return $this->getDimensionsForDate($p_oDecoration->getStartDate());
    }

    /**
     * @param \DateTime $p_oDate
     *
     * @return DayBlockDimensions
     */
    protected function getDimensionsForDate(DateTime $p_oDate)
    {
        $aDateCoordinates = $this->getDateCoordinates();
        return $aDateCoordinates[$p_oDate->format('Ymd')];
    }

/////////////////////////////// Writing Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param DateTime $p_oDate
     */
    protected  function writeDayNumbers(DateTime $p_oDate)
    {
        $oDate = self::buildDateForDayNumbers($p_oDate);

        $this->m_iFontSize = $this->getWidth()/29.2333333333334;

        $iLetterBorder = $this->getWidth()/877;
        $iPadding    = $this->getWidth()/219.25;

        foreach($this->m_aDateCoordinates as $oDateCoordinate)
        {
            /** @var $oDateCoordinate DayBlockDimensions */
            $oDate->add($this->m_oOneDay);

            $iX = $oDateCoordinate->getX();
            $iY = $oDateCoordinate->getY();

            $sDay = $oDate->format('j');

            $oBoundingBox = $this->getBoundingBoxForText($sDay);

            $this->debug(null, $iX, $iY, $oDate);

            if($oDate->format('M') === $p_oDate->format('M'))
            {
                $this->writeText(
                      $sDay
                    , $iX+$iPadding, $iY+$iPadding+$oBoundingBox->getHeight()
                    , $this->m_aColors['black']
                );
            }
            else
            {
                $this->writeTextWithBorder(
                      $sDay
                    , $iX+$iPadding, $iY+$iPadding+$oBoundingBox->getHeight()
                    , $this->m_aColors['white']
                    , $iLetterBorder
                    , $this->m_aColors['black']
                );
            }#if
        }#foreach
    }

    /**
     * @param DateTime $p_oDate
     */
    protected function writeMonth(DateTime $p_oDate)
    {
        $this->m_iFontSize = $this->getWidth()/13.492307692308;

        $sMonth = $p_oDate->format('F - Y');

        $oBoundingBox = $this->getBoundingBoxForText($sMonth);

        $iX = ($this->getWidth() - $oBoundingBox->getWidth())/2;
        $iY = ($this->getHeight()/10.80) - $this->m_iFontSize/2.5;

        $this->debug();

        $this->writeText($sMonth, $iX, $iY, $this->m_aColors['black']);
    }

    /**
     * @param DateTime $p_oDate
     */
    protected  function writeWeekNumbers(DateTime $p_oDate)
    {
        $oDate = clone $p_oDate;

        $oOneWeek = new DateInterval('P7D');

        $iOffsetTop  = $this->getHeight()/7.1556195965418;
        $iOffsetLeft = $this->getWidth()/46.157894736842;

        $iWidth  = DayBlockDimensions::getBlockWidth()/2;
        $iHeight = DayBlockDimensions::getBlockHeight() + DayBlockDimensions::getLineHeight();

        $iBorderSize = $this->getWidth()/877;

        $this->m_iFontSize = $this->getWidth()/29.2333333333334;

        for($iCounter = 0; $iCounter < 6; $iCounter++)
        {
            $sWeek = $oDate->format('W');

            $iY = $iOffsetTop + ($iHeight * $iCounter);
            $this->debug(null, $iOffsetLeft, $iY);

            $oBoundingBox = $this->getBoundingBoxForText($sWeek);

            $iX = $iOffsetLeft + ($iWidth-$oBoundingBox->getWidth())/2;
            $iY = $iY + ($iHeight+$oBoundingBox->getHeight())/2;

            $this->writeTextWithBorder($sWeek, $iX, $iY, $this->m_aColors['Week_Nr']
                , $iBorderSize, $this->m_aColors['Week_Nr_Border']
            );

            $oDate->add($oOneWeek);
        }#for
    }

/////////////////////////////// Drawing Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param $p_aDecorations
     *
     * @return bool
     */
    protected function drawDecorations($p_aDecorations)
    {
        return $this->drawDecorationFunction($p_aDecorations, 'drawDecoration');
    }

    /**
     * @param $p_aDecorations
     *
     * @return bool
     */
    protected function drawDecorationBackgrounds($p_aDecorations)
    {
        return $this->drawDecorationFunction($p_aDecorations, 'drawDecorationBackground');
    }

    /**
     * @param array $p_aDecorations
     * @param $p_sFunction
     *
     * @return bool
     */
    protected function drawDecorationFunction(Array $p_aDecorations, $p_sFunction)
    {
        $success = true;
        foreach ($p_aDecorations as $t_oDecoration)
        {
            /** @var $t_oDecoration Decoration */
            /** @var $startDate DateTime */
            // Only use Decorations that are actually available this month
            $startDate = $t_oDecoration->getStartDate();
            if (isset($this->m_aDateCoordinates[$startDate->format('Ymd')])) {
                $b = $this->$p_sFunction($t_oDecoration);
                $success = ($success && $b);
            }#if
        }#foreach

        $result = $success;
        return $result;
    }

    /**
     * @param Decoration $p_oDecoration
     */
    protected function drawDecorationBackground (Decoration $p_oDecoration)
    {
        // Secular holidays and birthdays should not have a background
        if($this->m_bDebug === true
            || $p_oDecoration->getType() == DecorationType::NATIONAL_HOLIDAY
            || $p_oDecoration->getType() == DecorationType::SCHOOL_HOLIDAY
        )
        {

            $oDate = clone $p_oDecoration->getStartDate();

            $sColor = $this->m_aColors[$p_oDecoration->getType()->__toString()];

            while((int) $oDate->format('Ymd') < $p_oDecoration->getEndDate()->format('Ymd'))
            {
                $oDimensions = $this->getDimensionsForDate($oDate);

                $iX = $oDimensions->getLeftOffset() + ($oDimensions->getWidth()+$oDimensions->getLineWidth())  * $oDimensions->getRow();
                $iY = $oDimensions->getTopOffset()  + ($oDimensions->getHeight() + $oDimensions->getLineHeight())* $oDimensions->getColumn();

                $this->drawRectangleFilled($iX, $iY, $iX+$oDimensions->getWidth(), $iY+$oDimensions->getHeight(), $sColor);

                $oDate->add($this->m_oOneDay);
            }#while
        }#if
    }

    /**
     * @param Decoration $p_oDecoration
     *
     */
    protected function drawDecoration(Decoration $p_oDecoration)
    {
        $uResult = null;

        if($p_oDecoration->getTitle() === '')
        {
            throw new Exception('Title not set for decoration.');
        }
        else
        {
            switch($p_oDecoration->getType())
            {
                case DecorationType::BIRTHDAY:
                    $uResult = $this->drawBirthdayDecoration($p_oDecoration);
                break;


                case DecorationType::NATIONAL_HOLIDAY:
                case DecorationType::SCHOOL_HOLIDAY:
                case DecorationType::SECULAR_HOLIDAY:
                    $uResult = $this->drawHolidayDecoration($p_oDecoration);
                break;
            }#switch
        }#if

        return $uResult;
    }

    /**
     * @param Decoration $p_oDecoration
     *
     * @return bool
     */
    protected function drawBirthdayDecoration(Decoration $p_oDecoration)
    {
        $this->m_iFont = ceil($this->getWidth() / 350.8);
        $iBorderThickness = 3;
        $iBorderColor = $this->m_aColors['black'];
        $oBoundingBox = $this->getBoundingBoxForText($p_oDecoration->getTitle());
        $iTextWidth = $oBoundingBox->getWidth();

        $oDimensions = $this->getDimensionsForDate($p_oDecoration->getStartDate());
        $iDateWidth = ceil($this->getWidth() / 21.925);//@TODO: Calculate DateWidth

        $iBoxWidth = DayBlockDimensions::getBlockWidth() - $iDateWidth;

        $oScratchImage = new ScratchImage($oBoundingBox);
        $oScratchImage->setFontSize($this->m_iFontSize);

        $oScratchImage->writeTextWithBorder(
            $p_oDecoration->getTitle()
            , 0, $oBoundingBox->getHeight() - $oBoundingBox->getLowerRightY() - 1
            , $this->m_aColors['white']
            , $iBorderThickness
            , $iBorderColor
        );

        $iX = self::calculateXFromDimension($oDimensions) + $iDateWidth;

        $iY = self::calculateYFromDimension($oDimensions) - DayBlockDimensions::getBlockHeight()  + $oBoundingBox->getHeight();

        return imagecopyresampled(
            $this->m_rImage, $oScratchImage->getImageResource()
            , $iX, $iY + $oDimensions->getHeight() - $oBoundingBox->getHeight() + $oBoundingBox->getLowerRightY()
            , 0, 0
            , DayBlockDimensions::getBlockWidth(), $oScratchImage->getHeight()
            , $iTextWidth, $oBoundingBox->getHeight()
        );
    }

    /**
     * @param Decoration $p_oDecoration
     *
     * @return bool
     */
    protected function drawHolidayDecoration(Decoration $p_oDecoration)
    {
        $bResult = false;

        $oDate = clone $p_oDecoration->getStartDate();

        $this->m_iFont = ceil($this->getWidth() / 350.8);
        $iBorderThickness = ceil($this->getWidth() / 584.66666666667); // 3 pixels

        $iBorderColor = $this->m_aColors['black'];
        $oBoundingBox = $this->getBoundingBoxForText($p_oDecoration->getTitle());
        $iTextWidth = $oBoundingBox->getWidth();

        /*
          @TODO: Take a decoration that spans more than one week into account

           Either the holiday is a single day or it is several days or weeks.
           In the latter case the holiday might be spread across several rows
           Extra logic will be needed for such cases to calculate how many days
           are on which row (week). The text should be written on the (first)
           row that has the most days.
        */
        $oDimensions = $this->getDimensionsForDate($p_oDecoration->getStartDate());
        $iDuration = $p_oDecoration->getDuration();
        if (7 - $oDimensions->getRow() < $p_oDecoration->getDuration()) {
            // Decoration spans more than one week
            $iDuration = 7 - $oDimensions->getRow();
        }
        #if

        if ($iDuration === 1
                && $iTextWidth <= DayBlockDimensions::getBlockWidth()
        ) {
            $iKerning =
                    (DayBlockDimensions::getBlockWidth() - $iTextWidth)
                            / strlen($p_oDecoration->getTitle())
            ;
        }
        else if ($iTextWidth > DayBlockDimensions::getBlockWidth() * $iDuration)
        {
            $oScratchImage = new ScratchImage($oBoundingBox);
            $oScratchImage->setFontSize($this->m_iFontSize);
            if ($p_oDecoration->getTitle() !== '')
            {
                $oScratchImage->writeTextWithBorder(
                    $p_oDecoration->getTitle()
                    , 0, $oBoundingBox->getHeight() - $oBoundingBox->getLowerRightY() - 1
                    , $this->m_aColors['white']
                    , $iBorderThickness
                    , $iBorderColor
                );
            }#if
        }
        else
        {
            $iBoxWidth = DayBlockDimensions::getBlockWidth() * $iDuration;
            $iKerning = ($iBoxWidth - $iTextWidth) / strlen($p_oDecoration->getTitle());
            if ($iKerning < 2) //@TODO: Replace hard-coded value for minimum-kerning with class field
            {
                $iKerning = 0;
            }#if
        }#if

        $bSuccess = true;
        while ((int) $oDate->format('Ymd') < $p_oDecoration->getEndDate()->format('Ymd'))
        {
            $oDimensions = $this->getDimensionsForDate($oDate);

            $iX = self::calculateXFromDimension($oDimensions);
            $iY = self::calculateYFromDimension($oDimensions);

            if(in_array($iX . 'x' . $iY, $this->m_aApliedDecorations))
            {
                $iY = $iY - $oBoundingBox->getHeight();
            }#if

            array_push($this->m_aApliedDecorations,$iX . 'x' . $iY);

            if ($oDate->format('Ymd') === $p_oDecoration->getStartDate()->format('Ymd'))
            {
                if (isset($oScratchImage))
                {
                    $bCopied = imagecopyresampled(
                        $this->m_rImage, $oScratchImage->getImageResource()
                        , $iX, $iY + $oDimensions->getHeight() - $oBoundingBox->getHeight() + $oBoundingBox->getLowerRightY()
                        , 0, 0
                        , DayBlockDimensions::getBlockWidth() * $iDuration, $oScratchImage->getHeight()
                        , $iTextWidth, $oBoundingBox->getHeight()
                    );

                    $bSuccess = ($bSuccess && $bCopied);
                }
                else
                {
                    $iX = $iX + ($iKerning / 2);

                    $this->writeTextWithBorder(
                        $p_oDecoration->getTitle()
                        , $iX, $iY + $oDimensions->getHeight()
                        , $this->m_aColors['white']
                        , $iBorderThickness
                        , $iBorderColor
                        , $iKerning
                    );
                }#if
            }#if

            $oDate->add($this->m_oOneDay);
        }#while

        $bResult = $bSuccess;

        return $bResult;
    }

    protected function drawBase()
    {
        // Colour the weekends light gray
        /** @noinspection PhpUndefinedMethodInspection */
        $this->colorWeekends();

        // Draw the outline box
        /** @noinspection PhpUndefinedMethodInspection */
        $this->drawOutline();
        // The border outline consists 8 parts, 4 sides + 4 rounded corners

        // Draw the grid lines
        /** @noinspection PhpUndefinedMethodInspection */
        $this->drawGrid();
        // The grid is 7x6 with an extra half-height row at the top and bottom for the
        // day names.

        // Draw dividers for the week numbers
        /** @noinspection PhpUndefinedMethodInspection */
        $this->drawDividers();

        /** @noinspection PhpUndefinedMethodInspection */
        $this->writeDayNames();
    }


/////////////////////////////// Calculate Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param DateTime $p_oDate
     */
    protected function calculateDateCoordinates(DateTime $p_oDate)
    {
        $oDate = self::buildDateForDayNumbers($p_oDate);

        $iLeftOffset = DayBlockDimensions::getLeftOffset() + DayBlockDimensions::getLineWidth();
        $iTopOffset  = DayBlockDimensions::getTopOffset()  + DayBlockDimensions::getLineHeight();

        $iHeight = DayBlockDimensions::getBlockHeight()+DayBlockDimensions::getLineHeight();
        $iWidth  = DayBlockDimensions::getBlockWidth()+DayBlockDimensions::getLineWidth();

        for($t_iColumnCounter = 0; $t_iColumnCounter < 6; $t_iColumnCounter++)
        {
            for($t_iRowCounter = 0; $t_iRowCounter < 7; $t_iRowCounter++)
            {
                $oDate->add($this->m_oOneDay);

                $iX = $iLeftOffset + $iWidth * $t_iRowCounter;
                $iY = $iTopOffset + $iHeight * $t_iColumnCounter;

                $this->storeDateCoordinates(
                      $oDate
                    , $t_iColumnCounter, $t_iRowCounter
                    , $iX, $iY
                );
            }#for
        }#for
    }

    /**
     * @param DayBlockDimensions $oDimensions
     *
     * @return int
     */
    protected static function calculateXFromDimension(DayBlockDimensions $oDimensions)
    {
        return ($oDimensions->getWidth() + $oDimensions->getLineWidth())
            * $oDimensions->getRow()
            + $oDimensions->getLeftOffset()
        ;
    }

    /**
     * @param DayBlockDimensions $oDimensions
     *
     * @return int
     */
    protected static function calculateYFromDimension(DayBlockDimensions $oDimensions)
    {
        return ($oDimensions->getHeight() + $oDimensions->getLineHeight())
            * $oDimensions->getColumn()
            + $oDimensions->getTopOffset()
        ;
    }

///////////////////////////////// Debug Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param null $p_sMethodName
     */
    protected function debug($p_sMethodName=null)
    {
        if($this->m_bDebug === true)
        {
            $aTrace = debug_backtrace();

            if(!isset($p_sMethodName))
            {
                $aCaller = $aTrace[1];
                $sMethodName = $aCaller['function'];
            }
            else{
                $sMethodName = $p_sMethodName;
            }#if

            $iThickness = $this->getWidth()/350;

            switch($sMethodName)
            {
                case 'writeMonth':
                    $this->drawRectangle(
                        0, 0
                        , $this->getWidth()-$iThickness
                        , $this->getHeight()/10.80-$iThickness
                        , $this->m_aColors['cyan']
                        , $iThickness
                    );
                break;

                case 'writeDayNumbers':
                    $iX = $aTrace[0]['args'][1];
                    $iY = $aTrace[0]['args'][2];
                    //$oDate = $aTrace[0]['args'][3];

                    $this->drawRectangle(
                          $iX, $iY
                        , $iX+DayBlockDimensions::getBlockWidth()-$iThickness
                        , $iY+DayBlockDimensions::getBlockHeight()-$iThickness
                        , $this->m_aColors['magenta']
                        , $iThickness
                    );
//                    imagestring($this->m_rImage, 5
//                        , $iX
//                        , $iY
//                        , $oDate->format('D M')
//                        , $this->m_aColors['magenta']
//                    );
                break;

                case 'writeWeekNumbers':
                    $iX = $aTrace[0]['args'][1];
                    $iY = $aTrace[0]['args'][2];

                    $this->drawRectangle(
                          $iX, $iY
                        , $iX+DayBlockDimensions::getBlockWidth()/2-$iThickness
                        , $iY+DayBlockDimensions::getBlockHeight()-$iThickness
                        , $this->m_aColors['yellow']
                        , $iThickness
                    );
                break;

                default:
                    parent::debug($sMethodName);
                break;
            }#switch
        }#if
    }
}


#EOF
