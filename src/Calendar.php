<?php

namespace Potherca\PrintableCalendarGenerator;

/**
 * @FIXME: This class should not _extend _ Image but have an Image member
 */
class Calendar extends AbstractImage
{
    //////////////////////////////// Properties \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /*
     10 px = $this->getWidth()/175.4
      8 px = $this->getWidth()/219.25
      6 px = $this->getWidth()/292.33333333333
      4 px = $this->getWidth()/438.5;
      2 px = $this->getWidth()/877;
    */

    /** @var \\DateInterval */
    private $m_oOneDay;
    /** @var array */
    private $m_aColors;
    /** @var array DayBlockDimensions */
    private $m_aDateCoordinates = array();
    /** @var array */
    private $m_aDecorations = array(); //@TODO: Replace $m_aDecorations array with DecorationCollection object
    /** @var array */
    private $m_aAppliedDecorations = array();

    //////////////////////////// Getters and Setters \\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param array $p_aColors
     */
    public function setColors(array $p_aColors)
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
     * @param array $p_aDecorations
     *
     * @TODO: Replace $p_aDecorations array with DecorationCollection object
     */
    public function setDecorations(/*DecorationCollection */
        $p_aDecorations
    ) {
        $this->m_aDecorations = $p_aDecorations;
    }

    /**
     * @return array
     */
    public function getDateCoordinates()
    {
        return $this->m_aDateCoordinates;
    }

    /**
     * @return array
     */
    public function getDecorations()
    {
        return $this->m_aDecorations;
    }

    //////////////////////////////// Public API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param AbstractDimensions $p_oDimensions
     * @param string $p_sType
     * @param bool $p_bAlpha
     */
    public function __construct(
        AbstractDimensions $p_oDimensions,
        $p_sType = 'png',
        $p_bAlpha = true
    ) {
        parent::__construct($p_oDimensions, $p_sType, $p_bAlpha);

        $this->m_oOneDay = new \DateInterval('P1D');
    }

    /**
     * @param \DateTime $p_oDate
     *
     * @return null|string
     */
    public function render(\DateTime $p_oDate)
    {
        // Create an Image
        $this->create();

        $this->calculateDateCoordinates($p_oDate);

        //@TODO: implements methods needed by $this->drawBase();
        //$this->drawBase();

        $this->drawDecorationBackgrounds();

        $this->writeMonth($p_oDate);

        $this->writeWeekNumbers($p_oDate);

        $this->writeDayNumbers($p_oDate);

        $this->drawDecorations();

        return $this->output();
    }

    /**
     * @return AbstractImage|void
     * @throws Exception
     */
    public function create()
    {
        parent::create();

        DayBlockDimensions::setDimensionsFromParent($this->getDimensions());

        $this->buildColors();

        return $this;
    }

    /**
     * @param array $p_sBackgroundColor
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function buildColors(
        $p_sBackgroundColor = array('0xFF', '0xFF', '0xFF', 0)
    ) {

        // set background color first
        $this->m_aColors = array(
            'background' => $this->allocateColor(
                $p_sBackgroundColor[0],
                $p_sBackgroundColor[1],
                $p_sBackgroundColor[2],
                $p_sBackgroundColor[3]
            ),
        );

        // set common colors
        $this->m_aColors['white'] = $this->allocateColor(0xFF, 0xFF, 0xFF);

        $this->m_aColors['black'] = $this->allocateColor(0x00, 0x00, 0x00);

        $this->m_aColors['red'] = $this->allocateColor(0xFF, 0x00, 0x00);
        $this->m_aColors['blue'] = $this->allocateColor(0x00, 0x00, 0xFF);
        $this->m_aColors['green'] = $this->allocateColor(0x00, 0xFF, 0x00);

        $this->m_aColors['magenta'] = $this->allocateColor(0xFF, 0x00, 0xFF);
        $this->m_aColors['cyan'] = $this->allocateColor(0x00, 0xFF, 0xFF);
        $this->m_aColors['yellow'] = $this->allocateColor(0xFF, 0xFF, 0x00);

        $this->m_aColors['Weekend'] = $this->allocateColor(0xBF, 0xBF, 0xBF);
        $this->m_aColors['Holiday'] = $this->allocateColor(0xAA, 0xAB, 0xAA);

        $this->m_aColors['Week_Nr'] = $this->allocateColor(0xCD, 0xCD, 0xCC);
        $this->m_aColors['Week_Nr_Border'] = $this->allocateColor(0x66, 0x66, 0x66);
        $this->m_aColors['Week_Nr_Divider'] = $this->allocateColor(0xBF, 0xBC, 0xBC);

        $this->m_aColors[DecorationType::BIRTHDAY] = $this->allocateColor(0xFF, 0xFF, 0x00, 64);
        $this->m_aColors[DecorationType::CUSTOM] = $this->allocateColor(0x00, 0xFF, 0xFF, 64);
        $this->m_aColors[DecorationType::NATIONAL_HOLIDAY] = $this->allocateColor(0x99, 0x9A, 0x99, 64);
        $this->m_aColors[DecorationType::SCHOOL_HOLIDAY] = $this->allocateColor(0x99, 0x9A, 0x99);
        $this->m_aColors[DecorationType::SECULAR_HOLIDAY] = $this->allocateColor(0x00, 0x00, 0xFF, 64);

        return $this->m_aColors;
    }

    ////////////////////////////// Helper Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param \DateTime $p_oDate
     */
    private function calculateDateCoordinates(\DateTime $p_oDate)
    {
        $oDate = self::buildDateForDayNumbers($p_oDate);

        $iLeftOffset = DayBlockDimensions::getLeftOffset() + DayBlockDimensions::getLineWidth();
        $iTopOffset = DayBlockDimensions::getTopOffset() + DayBlockDimensions::getLineHeight();

        $iHeight = DayBlockDimensions::getBlockHeight() + DayBlockDimensions::getLineHeight();
        $iWidth = DayBlockDimensions::getBlockWidth() + DayBlockDimensions::getLineWidth();

        for ($t_iColumnCounter = 0; $t_iColumnCounter < 6; $t_iColumnCounter++) {
            for ($t_iRowCounter = 0; $t_iRowCounter < 7; $t_iRowCounter++) {
                $oDate->add($this->m_oOneDay);

                $iX = $iLeftOffset + $iWidth * $t_iRowCounter;
                $iY = $iTopOffset + $iHeight * $t_iColumnCounter;

                $this->storeDateCoordinates(
                    $oDate
                    , $t_iColumnCounter, $t_iRowCounter
                    , $iX, $iY
                );
            }
        }
    }

    /**
     * @param \DateTime $p_oDate
     *
     * @return \DateTime
     */
    private static function buildDateForDayNumbers(\DateTime $p_oDate)
    {
        $oDate = clone $p_oDate;

        $oDate->sub(new \DateInterval('P' . $oDate->format('w') . 'D'));

        if ($oDate->format('M') === $p_oDate->format('M')) {
            $oDate->sub(new \DateInterval('P1W'));
        }

        return $oDate;
    }

    /**
     * @param \DateTime $oDate
     * @param $p_iColumnCounter
     * @param $p_iRowCounter
     * @param $p_iX
     * @param $p_iY
     */
    private function storeDateCoordinates(
        \DateTime $oDate
        ,
        $p_iColumnCounter,
        $p_iRowCounter,
        $p_iX,
        $p_iY
    ) {
        $oDimensions = DayBlockDimensions::createFromParentDimensions();

        $oDimensions->setColumn($p_iColumnCounter);
        $oDimensions->setRow($p_iRowCounter);

        $oDimensions->setX($p_iX);
        $oDimensions->setY($p_iY);

        $this->m_aDateCoordinates[$oDate->format('Ymd')] = $oDimensions;
    }

    /**
     * @return bool
     */
    private function drawDecorationBackgrounds()
    {
        return $this->callFunctionOnDecorations('drawDecorationBackground');
    }

    /**
     * @param string $p_sFunction
     *
     * @return bool
     */
    private function callFunctionOnDecorations($p_sFunction)
    {
        $bTotalResult = true;
        $aDecorations = $this->getDecorations();

        /* @NOTE: Decorations need to be sorted by duration, so the longest
         *        decorations are drawn first, otherwise we'll get decorations
         *        overlapping, later on.
         */
        usort(
            $aDecorations
            , function (Decoration $p_oA, Decoration $p_oB) {
                if ($p_oA->getDuration() < $p_oB->getDuration()) {
                    $bResult = 1;
                } elseif ($p_oA->getDuration() > $p_oB->getDuration()) {
                    $bResult = -1;
                } else {
                    $bResult = 0;
                }

                return $bResult;
            }
        );

        foreach ($aDecorations as $t_oDecoration) {
            /** @var $t_oDecoration Decoration */
            /** @noinspection PhpUndefinedMethodInspection Method format() is actually defined in  \DateTime. Stupid IDE */
            $sStartDate = $t_oDecoration->getStartDate()->format('Ymd');
            /** @noinspection PhpUndefinedMethodInspection Method format() is actually defined in  \DateTime. Stupid IDE */
            $sEndDate = $t_oDecoration->getEndDate()->format('Ymd');

            $aDateCoordinates = $this->getDateCoordinates();
            $aDates = array_keys($aDateCoordinates);
            $sFirstDate = array_shift($aDates);

            $bCall = false;
            if (isset($aDateCoordinates[$sStartDate])) {
                // Decoration start date is this month
                $bCall = true;
            } elseif ($sStartDate < $sFirstDate && isset($aDateCoordinates[$sEndDate])) {
                // Decoration end date is this month, even if the start date is not
                $t_oDecoration->setStartDate(\DateTime::createFromFormat('Ymd', $sFirstDate));
                $bCall = true;
            } else {
                // Decoration end and start date are not this month
            }

            if ($bCall === true) {
                $bCallResult = $this->$p_sFunction($t_oDecoration);
                $bTotalResult = ($bTotalResult && $bCallResult);
            }
        }

        return $bTotalResult;
    }

    /**
     * @param \DateTime $p_oDate
     */
    private function writeMonth(\DateTime $p_oDate)
    {
        $this->setFontSize($this->getWidth() / 13.492307692308);

        $sMonth = $p_oDate->format('F - Y');

        $oBoundingBox = $this->getBoundingBoxForText($sMonth);

        $iX = ($this->getWidth() - $oBoundingBox->getWidth()) / 2;
        $iY = ($this->getHeight() / 10.80) - $this->getFontSize() / 2.5;

        $this->debug();

        $this->writeText($sMonth, $iX, $iY, $this->m_aColors['black']);
    }

    /**
     * @param null $p_sMethodName
     */
    private function debug($p_sMethodName = null)
    {
        if ($this->getDebug() === true) {
            $aTrace = debug_backtrace();

            if (!isset($p_sMethodName)) {
                $aCaller = $aTrace[1];
                $sMethodName = $aCaller['function'];
            } else {
                $sMethodName = $p_sMethodName;
            }

            $iThickness = $this->getWidth() / 350;

            switch ($sMethodName) {
                case 'writeMonth':
                    $this->drawRectangle(
                        0, 0
                        , $this->getWidth() - $iThickness
                        , $this->getHeight() / 10.80 - $iThickness
                        , $this->m_aColors['cyan']
                        , $iThickness
                    );
                    break;

                case 'writeDayNumbers':
                    $iX = $aTrace[0]['args'][1];
                    $iY = $aTrace[0]['args'][2];
                    /** @var \DateTime $oDate */
                    /*$oDate = $aTrace[0]['args'][3];*/

                    $this->drawRectangle(
                        $iX, $iY
                        ,
                        $iX + DayBlockDimensions::getBlockWidth() - $iThickness
                        ,
                        $iY + DayBlockDimensions::getBlockHeight() - $iThickness
                        , $this->m_aColors['magenta']
                        , $iThickness
                    );
                    /*$this->writeText(
                        $oDate->format('D M')
                        , $iX
                        , $iY
                        , $this->m_aColors['magenta']
                    );*/
                    break;

                case 'drawDecorationText':
                    $iX = $aTrace[0]['args'][1];
                    $iY = $aTrace[0]['args'][2];
                    $iWidth = $aTrace[0]['args'][3];
                    $iHeight = $aTrace[0]['args'][4];
                    $sColor = $aTrace[0]['args'][5];

                    $this->drawRectangle(
                        $iX, $iY
                        , $iX + $iWidth - $iThickness
                        , $iY + $iHeight - $iThickness
                        , $this->m_aColors[$sColor]
                        , $iThickness
                    );
                    break;

                case 'writeWeekNumbers':
                    $iX = $aTrace[0]['args'][1];
                    $iY = $aTrace[0]['args'][2];

                    $this->drawRectangle(
                        $iX, $iY
                        ,
                        $iX + DayBlockDimensions::getBlockWidth() / 2 - $iThickness
                        ,
                        $iY + DayBlockDimensions::getBlockHeight() - $iThickness
                        , $this->m_aColors['yellow']
                        , $iThickness
                    );
                    break;

                default:
                    throw new \Exception('No debug method defined for "' . $sMethodName . '"');
                    break;
            }#switch
        }
    }

    /**
     * @param \DateTime $p_oDate
     */
    private function writeWeekNumbers(\DateTime $p_oDate)
    {
        $oDate = clone $p_oDate;

        $oOneWeek = new \DateInterval('P7D');

        $iOffsetTop = $this->getHeight() / 7.1556195965418;
        $iOffsetLeft = $this->getWidth() / 46.157894736842;

        $iWidth = DayBlockDimensions::getBlockWidth() / 2;
        $iHeight = DayBlockDimensions::getBlockHeight() + DayBlockDimensions::getLineHeight();

        $iBorderSize = $this->getWidth() / 877;

        $this->setFontSize($this->getWidth() / 29.2333333333334);

        for ($iCounter = 0; $iCounter < 6; $iCounter++) {
            $sWeek = $oDate->format('W');

            $iY = $iOffsetTop + ($iHeight * $iCounter);
            $this->debug(null, $iOffsetLeft, $iY);

            $oBoundingBox = $this->getBoundingBoxForText($sWeek);

            $iX = $iOffsetLeft + ($iWidth - $oBoundingBox->getWidth()) / 2;
            $iY = $iY + ($iHeight + $oBoundingBox->getHeight()) / 2;

            $this->writeTextWithBorder($sWeek, $iX, $iY,
                $this->m_aColors['Week_Nr']
                , $iBorderSize, $this->m_aColors['Week_Nr_Border']
            );

            $oDate->add($oOneWeek);
        }
    }

    /**
     * @param \DateTime $p_oDate
     */
    private function writeDayNumbers(\DateTime $p_oDate)
    {
        $oDate = self::buildDateForDayNumbers($p_oDate);

        $this->setFontSize($this->getWidth() / 29.2333333333334);

        $iLetterBorder = $this->getWidth() / 877;
        $iPadding = $this->getWidth() / 219.25;

        foreach ($this->m_aDateCoordinates as $oDateCoordinate) {
            /** @var $oDateCoordinate DayBlockDimensions */
            $oDate->add($this->m_oOneDay);

            $iX = $oDateCoordinate->getX();
            $iY = $oDateCoordinate->getY();

            $sDay = $oDate->format('j');

            $oBoundingBox = $this->getBoundingBoxForText($sDay);

            $this->debug(null, $iX, $iY, $oDate);

            if ($oDate->format('M') === $p_oDate->format('M')) {
                $this->writeText(
                    $sDay
                    , $iX + $iPadding,
                    $iY + $iPadding + $oBoundingBox->getHeight()
                    , $this->m_aColors['black']
                );
            } else {
                $this->writeTextWithBorder(
                    $sDay
                    , $iX + $iPadding,
                    $iY + $iPadding + $oBoundingBox->getHeight()
                    , $this->m_aColors['white']
                    , $iLetterBorder
                    , $this->m_aColors['black']
                );
            }
        }
    }

    /**
     * @return bool
     */
    private function drawDecorations()
    {
        return $this->callFunctionOnDecorations('drawDecoration');
    }

    /**
     * @param Decoration $p_oDecoration
     *
     * @return DayBlockDimensions
     */
    private function getDimensionsForDecoration(Decoration $p_oDecoration)
    {
        return $this->getDimensionsForDate($p_oDecoration->getStartDate());
    }

    /**
     * @param \DateTime $p_oDate
     *
     * @throws OutOfRangeException
     *
     * @return DayBlockDimensions
     */
    private function getDimensionsForDate(\DateTime $p_oDate)
    {
        //@FIXME: If the given date is out of scope the first or last dateCoordinate should be returned, respectively.
        $oDimensions = null;

        $aDateCoordinates = $this->getDateCoordinates();
        $sDate = $p_oDate->format('Ymd');

        if (!isset($aDateCoordinates[$sDate])) {
            $aDates = array_keys($aDateCoordinates);

            $sFirst = array_shift($aDates);
            $sLast = array_pop($aDates);

            if ($sDate < $sFirst) {
                $oDimensions = $aDateCoordinates[$sFirst];
            } elseif ($sDate > $sLast) {
                $oDimensions = $aDateCoordinates[$sLast];
            } else {
                throw new \OutOfRangeException('Given date is out of range and a min or max replacement could not be found.');
            }
        } else {
            /** @var $oDimensions DayBlockDimensions */
            $oDimensions = $aDateCoordinates[$sDate];
        }

        return $oDimensions;
    }

    /**
     * @param Decoration $p_oDecoration
     */
    private function drawDecorationBackground(Decoration $p_oDecoration)
    {
        // Secular holidays and birthdays should not have a background
        if ($this->getDebug() === true
            || $p_oDecoration->getType() == DecorationType::NATIONAL_HOLIDAY
            || $p_oDecoration->getType() == DecorationType::SCHOOL_HOLIDAY
        ) {
            // @TODO: Read background colour for custom events
            $sColor = $this->m_aColors[(string) $p_oDecoration->getType()];

            $oDate = clone $p_oDecoration->getStartDate();

            while ((int) $oDate->format('Ymd') < (int) $p_oDecoration->getEndDate()->format('Ymd')) {
                $oDimensions = $this->getDimensionsForDate($oDate);

                $iX = $oDimensions->getLeftOffset() + ($oDimensions->getWidth() + $oDimensions->getLineWidth()) * $oDimensions->getRow();
                $iY = $oDimensions->getTopOffset() + ($oDimensions->getHeight() + $oDimensions->getLineHeight()) * $oDimensions->getColumn();

                $this->drawRectangleFilled(
                    $iX,
                    $iY,
                    $iX + $oDimensions->getWidth(),
                    $iY + $oDimensions->getHeight(),
                    $sColor
                );

                $oDate->add($this->m_oOneDay);
            }#while
        }
    }

    /**
     * @param Decoration $p_oDecoration
     *
     * @throws Exception
     *
     * @return bool|null
     */
    private function drawDecoration(Decoration $p_oDecoration)
    {
        $uResult = null;

        if ($p_oDecoration->getTitle() === '') {
            throw new Exception('Title not set for decoration.');
        } else {
            switch ($p_oDecoration->getType()) {
                case DecorationType::BIRTHDAY:
                    $uResult = $this->drawBirthdayDecoration($p_oDecoration);
                    break;

                case DecorationType::NATIONAL_HOLIDAY:
                case DecorationType::SCHOOL_HOLIDAY:
                case DecorationType::SECULAR_HOLIDAY:
                    $uResult = $this->drawHolidayDecoration($p_oDecoration);
                    break;

                case DecorationType::CUSTOM:
                    $uResult = $this->drawCustomDecoration($p_oDecoration);
                    break;
            }#switch
        }

        return $uResult;
    }

    /**
     * @param Decoration $p_oDecoration
     *
     * @return bool
     */
    private function drawBirthdayDecoration(Decoration $p_oDecoration)
    {
        $this->setFontSize(ceil($this->getWidth() / 43.85));// = 40 pixels

        $iDateWidth = ceil($this->getWidth() / 21.925); //@TODO: Calculate DateWidth
        //$iDateHeight = ceil($this->getWidth()/29.2333333333334);// Value taken from $this->writeDayNumbers()

        $iYOffset = (-DayBlockDimensions::getBlockHeight()) + $this->getFontSize();

        return $this->drawDecorationText($p_oDecoration, -$iDateWidth,
            $iDateWidth, $iYOffset);
    }

    /**
     * @param Decoration $p_oDecoration
     * @param float $p_dCorrection
     * @param int $p_iXOffset
     * @param int $p_iYOffset
     *
     * @return bool
     */
    private function drawDecorationText(
        Decoration $p_oDecoration
        , $p_dCorrection = 0.0
        , $p_iXOffset = 0
        , $p_iYOffset = 0
    ) {
        //$bResult = false;

        $oDate = clone $p_oDecoration->getStartDate();

        $iBorderThickness = ceil($this->getWidth() / 584.66666666667); // 3 pixels

        $oBoundingBox = $this->getBoundingBoxForText($p_oDecoration->getTitle());
        $iTextWidth = $oBoundingBox->getWidth();

        $oDimensions = $this->getDimensionsForDate($p_oDecoration->getStartDate());
        $iDuration = $p_oDecoration->getDuration();
        if ($iDuration === 0) {
            $iDuration = 1;
        }

        /*
          @TODO: Take a decoration that spans more than one week into account

           Either the holiday is a single day or it is several days or weeks.
           In the latter case the holiday might be spread across several rows
           Extra logic will be needed for such cases to calculate how many days
           are on which row (week). The text should be written on the (first)
           row that has the most days.
        */
        if (7 - $oDimensions->getRow() < $iDuration) {
            // Decoration spans more than one week
            $iDuration = 7 - $oDimensions->getRow();
        }

        $iBoxWidth = DayBlockDimensions::getBlockWidth() * $iDuration;
        if ($p_dCorrection !== 0.0) {
            $iBoxWidth = $iBoxWidth + $p_dCorrection;
        }

        // @TODO: Add the ability to add a (custom) icon in front of text
        if ($iTextWidth > $iBoxWidth) {
            // Text is wider than the space it is supposed to occupy
            // so it needs to be shrunk
            $oScratchImage = $this->createScratchImageForDecoration(
                $p_oDecoration
                , $oBoundingBox
                , $iBorderThickness
            );
        } else {
            // Text is not wide enough to fill the space it is supposed to occupy
            // so it needs to be stretched
//                && $iTextWidth <= DayBlockDimensions::getBlockWidth() * $iDuration
            $iKerning = ($iBoxWidth - $iTextWidth) / strlen($p_oDecoration->getTitle());
            //@TODO: Replace hard-coded value for minimum-kerning with class field
            if ($iKerning < 2) {
                $iKerning = 0;
            }
        }

        $bSuccess = true;
        while ((int) $oDate->format('Ymd') <= (int) $p_oDecoration->getEndDate()->format('Ymd')) {

            $oDimensions = $this->getDimensionsForDate($oDate);

            $iX = round(self::calculateXFromDimension($oDimensions) + $p_iXOffset);
            $iY = round(self::calculateYFromDimension($oDimensions) + $p_iYOffset);

            $sDecorationLocation = $oDimensions->getRow() . '.' . $oDimensions->getColumn();
            if (
                in_array($sDecorationLocation, $this->m_aAppliedDecorations)
                && $p_oDecoration->getType() != DecorationType::BIRTHDAY    //@TODO: Remove this line once FIXME below is resolved.
            ) {
                //@FIXME: This logic needs to be expanded to take decorations that are drawn from the top into account  BMP/2012/10/21
                //        Like Birthdays, which are now skipped to avoid problems.
                $aValues = array_count_values($this->m_aAppliedDecorations);
                $iY = $iY - ($oBoundingBox->getHeight() * $aValues[$sDecorationLocation]);
            }

            array_push($this->m_aAppliedDecorations, $sDecorationLocation);

            if ((int) $oDate->format('Ymd') === (int) $p_oDecoration->getStartDate()->format('Ymd')) {
                if (isset($oScratchImage)) {
                    $iY = $iY + $oDimensions->getHeight() - $oBoundingBox->getHeight() + $oBoundingBox->getLowerRightY();

                    $this->debug(null, $iX, $iY, $iBoxWidth, $oScratchImage->getHeight(), 'blue');

                    $bCopied = $this->copyFromImage(
                        $oScratchImage
                        , $iX, $iY
                        , 0, 0
                        , $iBoxWidth, $oScratchImage->getHeight()
                        , $iTextWidth, $oBoundingBox->getHeight()
                    );

                    $bSuccess = ($bSuccess && $bCopied);
                } else {
                    if (!isset($iKerning)) {
                        $iKerning = 0;
                    } else {
                        $iX = $iX + ($iKerning / 2);
                    }

                    $this->debug(null, $iX,
                        $iY + DayBlockDimensions::getBlockHeight() - $this->getFontSize(),
                        $iBoxWidth, $this->getFontSize(), 'red');

                    $iY = $iY + $oDimensions->getHeight();
                    $this->writeTextWithBorder(
                        $p_oDecoration->getTitle()
                        , $iX, $iY
                        , $this->m_aColors['white']
                        , $iBorderThickness
                        , $this->m_aColors['black']
                        , $iKerning
                    );
                }
            }
            $oDate->add($this->m_oOneDay);
        }#while

        $bResult = $bSuccess;

        return $bResult;
    }

    /**
     * @param Decoration $p_oDecoration
     * @param BoundingBox $p_oBoundingBox
     * @param int $p_iBorderThickness
     *
     * @return \ScratchImage
     */
    private function createScratchImageForDecoration(
        Decoration $p_oDecoration
        , BoundingBox $p_oBoundingBox
        , $p_iBorderThickness
    ) {
        $oScratchImage = new ScratchImage($p_oBoundingBox);
        $oScratchImage->setFontDirectory($this->getFontDirectory());
        $oScratchImage->setFontPath($this->getFontPath());
        $oScratchImage->setFontSize($this->getFontSize());

        if ($p_oDecoration->getTitle() !== '') {
            $oScratchImage->writeTextWithBorder(
                $p_oDecoration->getTitle()
                , 0,
                $p_oBoundingBox->getHeight() - $p_oBoundingBox->getLowerRightY() - 1
                , $this->m_aColors['white']
                , $p_iBorderThickness
                , $this->m_aColors['black']
            );
        }

        return $oScratchImage;
    }

    /**
     * @param DayBlockDimensions $oDimensions
     *
     * @return int
     */
    private static function calculateXFromDimension(
        DayBlockDimensions $oDimensions
    ) {
        return ($oDimensions->getWidth() + $oDimensions->getLineWidth())
        * $oDimensions->getRow()
        + $oDimensions->getLeftOffset();
    }

    /**
     * @param DayBlockDimensions $oDimensions
     *
     * @return int
     */
    private static function calculateYFromDimension(
        DayBlockDimensions $oDimensions
    ) {
        return ($oDimensions->getHeight() + $oDimensions->getLineHeight())
        * $oDimensions->getColumn()
        + $oDimensions->getTopOffset();
    }

    /**
     * @param Decoration $p_oDecoration
     *
     * @return bool
     */
    private function drawHolidayDecoration(Decoration $p_oDecoration)
    {
        $this->setFontSize(ceil($this->getWidth() / 40) + 1); // = 45 pixels

        return $this->drawDecorationText($p_oDecoration);
    }

    /**
     * @param Decoration $p_oDecoration
     *
     * @return bool
     */
    private function drawCustomDecoration(Decoration $p_oDecoration)
    {
        $this->setFontSize(ceil($this->getWidth() / 40) + 1); // = 45 pixels

        return $this->drawDecorationText($p_oDecoration/*, -$iDateWidth, $iDateWidth, $iYOffset*/);
    }

    private function drawBase()
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
}

/////////////////////////////// Writing Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
/////////////////////////////// Drawing Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
/////////////////////////////// Calculate Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
///////////////////////////////// Debug Methods \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

/*EOF*/
