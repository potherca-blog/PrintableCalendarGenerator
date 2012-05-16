<?php
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

    protected $m_aColors;

    protected $m_bDebug=false;

    /**
     * @var array DayBlockDimensions
     */
    protected $m_aDateCoordinates = array();

    protected $m_aApliedDecorations = array();

////////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    public function setColors($p_aColors)
    {
        $this->m_aColors = $p_aColors;
    }

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
     * @return DayBlockDimensions
     */
    protected function getDimensionsForDecoration(Decoration $p_oDecoration)
    {
        return $this->getDimensionsForDate($p_oDecoration->getStartDate());
    }

    /**
     * @param \DateTime $p_oDate
     * @internal param \Decoration $p_oDecoration
     * @return DayBlockDimensions
     */
    protected function getDimensionsForDate(DateTime $p_oDate)
    {
        $aDateCoordinates = $this->getDateCoordinates();
        return $aDateCoordinates[$p_oDate->format('Ymd')];
    }

/////////////////////////////// Writing Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    protected  function writeDayNumbers(DateTime $p_oDate)
    {
        $oDate = self::buildDateForDayNumbers($p_oDate);

        $this->m_iFontSize = $this->getWidth()/29.2333333333334;

        $iLetterBorder = $this->getWidth()/877;
        $iPadding    = $this->getWidth()/219.25;

        foreach($this->m_aDateCoordinates as $oDateCoordinate)
        {
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
    protected function drawDecorations($p_aDecorations)
    {
        return $this->drawDecorationFunction($p_aDecorations, 'drawDecoration');
    }

    protected function drawDecorationBackgrounds($p_aDecorations)
    {
        return $this->drawDecorationFunction($p_aDecorations, 'drawDecorationBackground');
    }

    /**
     * @param array Decoration $p_aDecorations
     * @param $p_sFunction
     */
    protected function drawDecorationFunction(Array $p_aDecorations, $p_sFunction)
    {
        foreach ($p_aDecorations as $t_oDecoration)
        {
            // Only use Decorations that are actually available this month
            if (isset($this->m_aDateCoordinates[$t_oDecoration->getStartDate()->format('Ymd')])) {
                $this->$p_sFunction($t_oDecoration);
            }
            #if
        }#foreach
    }

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

    protected function drawDecoration(Decoration $p_oDecoration)
    {
        if($p_oDecoration->getTitle() === '')
        {
            throw new Exception('Title not set for decoration.');
        }#if

        switch($p_oDecoration->getType())
        {
            case DecorationType::BIRTHDAY:
                $this->drawBirthdayDecoration($p_oDecoration);
            break;


            case DecorationType::NATIONAL_HOLIDAY:
            case DecorationType::SCHOOL_HOLIDAY:
            case DecorationType::SECULAR_HOLIDAY:
                $this->drawHolidayDecoration($p_oDecoration);
            break;
        }
    }

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

        imagecopyresampled(
            $this->m_rImage, $oScratchImage->getImageResource()
            , $iX, $iY + $oDimensions->getHeight() - $oBoundingBox->getHeight() + $oBoundingBox->getLowerRightY()
            , 0, 0
            , DayBlockDimensions::getBlockWidth(), $oScratchImage->getHeight()
            , $iTextWidth, $oBoundingBox->getHeight()
        );
    }

    protected function drawHolidayDecoration(Decoration $p_oDecoration)
    {
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
                    imagecopyresampled(
                        $this->m_rImage, $oScratchImage->getImageResource()
                        , $iX, $iY + $oDimensions->getHeight() - $oBoundingBox->getHeight() + $oBoundingBox->getLowerRightY()
                        , 0, 0
                        , DayBlockDimensions::getBlockWidth() * $iDuration, $oScratchImage->getHeight()
                        , $iTextWidth, $oBoundingBox->getHeight()
                    );
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
    }

    protected function drawBase()
    {
        // Colour the weekends light gray
        $this->colorWeekends();

        // Draw the outline box
        $this->drawOutline();
        // The border outline consists 8 parts, 4 sides + 4 rounded corners

        // Draw the grid lines
        $this->drawGrid();
        // The grid is 7x6 with an extra half-height row at the top and bottom for the
        // day names.

        // Draw dividers for the week numbers
        $this->drawDividers();

        $this->writeDayNames();
    }


/////////////////////////////// Calculate Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
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

    protected static function calculateXFromDimension(Dimensions $oDimensions)
    {
        return ($oDimensions->getWidth() + $oDimensions->getLineWidth())
            * $oDimensions->getRow()
            + $oDimensions->getLeftOffset()
        ;
    }

    protected static function calculateYFromDimension(Dimensions $oDimensions)
    {
        return ($oDimensions->getHeight() + $oDimensions->getLineHeight())
            * $oDimensions->getColumn()
            + $oDimensions->getTopOffset()
        ;
    }

///////////////////////////////// Debug Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    protected function debug($p_sMethodName=null)
    {
        if($this->m_bDebug === true)
        {
            if(!isset($p_sMethodName))
            {

                $aTrace = debug_backtrace();
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
                    $oDate = $aTrace[0]['args'][3];

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
