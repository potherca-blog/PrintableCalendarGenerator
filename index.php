<?php

    define('LIBRARY_DIRECTORY', realpath('./lib/') . '/');

    /**
     *
     */
    class CustomException extends ErrorException
    {
        /**
         * @param $code
         * @param $string
         * @param $file
         * @param $line
         *
         * @throws CustomException
         */
        public static function errorHandlerCallback($code, $string, $file, $line /*, $context*/)
        {
            $oException = new self($string, $code);
            $oException->line = $line;
            $oException->file = $file;

            throw $oException;
        }
    }

    set_error_handler(array('CustomException', 'errorHandlerCallback'), E_ALL | E_STRICT);

    spl_autoload_register(function ($p_sClassName){
        $sClassPath = LIBRARY_DIRECTORY . 'class.' . $p_sClassName . '.php';

        if(file_exists($sClassPath)){
            require $sClassPath;
        }
    });

/**
 *
 */
class DecorationParser
{
    /**
     * @return \DecorationParser
     */
    static public function getInstance()
    {
        static $self;
        if(!isset($self))
        {
            $self = new self;
        }

        return $self;
    }
    /**
     * @param $iYear
     *
     * @return array
     *
     * @throws CustomException
     */
    public function buildDecorations ($iYear)
    {
        $aDecorationList = array();

        $aXmlFiles = $this->fetchXmlFileList();
        $aIcsFiles = $this->fetchIcsFileList();

        $aXmlDecorations = $this->retrieveDecorationsFromXmlFiles($aXmlFiles, $iYear);
        $aIcsDecorations = $this->retrieveDecorationsFromIcsFiles($aIcsFiles, $iYear);

        $aDecorationList = array_merge(
              $aDecorationList
            , $aXmlDecorations
            , $aIcsDecorations
        );

        return $aDecorationList;
    }

    /**
     * @return array
     */
    protected function fetchXmlFileList()
    {
        return $this->fetchFileList('xml');
    }

    /**
     * @return array
     */
    protected function fetchIcsFileList()
    {
        return $this->fetchFileList('ics');
    }

    /**
     * @param $p_sExtension
     *
     * @return array
     */
    protected function fetchFileList($p_sExtension)
    {
        $aFiles = glob('./decorations/*.' . $p_sExtension);
        return $aFiles;
    }


    /**
     * @param $p_aFiles
     * @param $p_iYear
     *
     * @throws Exception
     *
     * @return array
     */
    protected function retrieveDecorationsFromIcsFiles($p_aFiles, $p_iYear)
    {
        $aDecorationList = array();

        foreach ($p_aFiles as $t_sFilePath)
        {
            $oIcal   = new ICalReader($t_sFilePath);
            $aEvents = $oIcal->events();

            foreach ($aEvents as $aEvent)
            {
                if (empty($aEvent['SUMMARY'])) {
                    throw new Exception('No Title set for event on ' . $aEvent['DTSTART']);
                }
                else {
                    //$oInterval = new DateInterval('P' . $iDuration . 'D');

                    $oStartDate = new Datetime($aEvent['DTSTART']);
                    $oEndDate = new Datetime($aEvent['DTEND']);

                    $oInterval = $oStartDate->diff($oEndDate);

                    $oDecoration  = new Decoration($oStartDate, $oInterval, $oEndDate);

                    $oDecoration->setTitle($aEvent['SUMMARY']);

                    if($oInterval->days > 0)
                    {
                        $sType = 'SCHOOL_HOLIDAY';
                    }
                    else
                    {
                            $sType = 'CUSTOM';
                    }#if

                    $oDecoration->setType(new DecorationType($sType));

                    $aDecorationList[] = $oDecoration;
                }#if
            }#foreach
        }#foreach

        return $aDecorationList;
    }

    /**
     * @param $p_aFiles
     * @param $p_iYear
     *
     * @throws CustomException
     *
     * @return array
     */
    protected function retrieveDecorationsFromXmlFiles($p_aFiles, $p_iYear)
    {
        $aDecorationList = array();

        foreach ($p_aFiles as $t_sFilePath)
        {
            $oDocument = new DOMDocument();
            $oDocument->preserveWhiteSpace = false;
            $oDocument->load($t_sFilePath);

            try {
                $bValid = $oDocument->validate();
            }
            catch (CustomException $eInvalid) {
                $bValid = false;
            }#catch

            if ($bValid === false) {
                throw new CustomException('XML "' . $t_sFilePath . '" does either not use "decorations.dtd" or does not follow the structure outlined therein.');
            }#if

            $oBirthdayDecorations = $oDocument->getElementsByTagName('birthday');
            $oHolidayDecorations  = $oDocument->getElementsByTagName('holiday');

            $aDecorationList = array_merge(
                $aDecorationList
                , $this->BuildDecorationArrayFromDOMNodeList($oBirthdayDecorations, $p_iYear)
                , $this->BuildDecorationArrayFromDOMNodeList($oHolidayDecorations, $p_iYear)
            );
        }#foreach

        return $aDecorationList;
    }

    /**
     * @param DOMNodeList $p_oDomNodeList
     * @param $iYear
     *
     * @return array
     *
     * @throws Exception
     */
    protected function BuildDecorationArrayFromDOMNodeList(DOMNodeList $p_oDomNodeList, $iYear)
    {
        $aDecorationList = array();
        for($t_iCounter=0; $t_iCounter<$p_oDomNodeList->length; $t_iCounter++)
        {
            /** @var $oDecoration DOMElement */
            $oDecoration = $p_oDomNodeList->item($t_iCounter);

            $sType = $this->getAttributeValue($oDecoration, 'type');
            if(empty($sType))
            {
                $sType = strtoupper($oDecoration->nodeName);
            }#if

            $iDuration = $this->getAttributeValue($oDecoration, 'duration');

            /** @var $oChildren DOMNodeList */
            $oChildren = $oDecoration->childNodes;

            $aDecoration = array();

            for($t_iSubCounter=0;$t_iSubCounter<$oChildren->length;$t_iSubCounter++)
            {
                $oNode = $oChildren->item($t_iSubCounter);
                $aDecoration[$oNode->nodeName] = $oNode->nodeValue;
            }#for

            $iDuration = isset($iDuration)?$iDuration:1;
            $oInterval = new DateInterval('P' . $iDuration . 'D');
            $sDate = $aDecoration['date'];
            try
            {
                if($aDecoration['name'] === '')
                {
                    throw new Exception('No Title set on line ' . $oDecoration->getLineNo());
                }

                if(preg_match('/[0-9]{4}/', $sDate) === 0)
                {
                    $sDate .= ' ' . $iYear;
                }
                $oStartDate = new Datetime($sDate);

                $oEndDate = clone $oStartDate;
                $oEndDate->add($oInterval);

                $oDecoration  = new Decoration($oStartDate, $oInterval, $oEndDate);
                $oDecoration->setType(new DecorationType($sType));
                $oDecoration->setTitle($aDecoration['name']);

                $aDecorationList[] = $oDecoration;
            }
            catch(Exception $oException)
            {
                //@TODO: Warn user that there is a date we cannot parse
                // echo $oException->getMessage();
            }#catch
        }#for

        return $aDecorationList;
    }

    /**
     * @param DOMElement $p_oDOMElement
     * @param $p_sName
     *
     * @return int
     */
    protected function getAttributeValue(DOMElement $p_oDOMElement, $p_sName)
    {
        $mValue = null;
        if($p_oDOMElement->hasAttribute($p_sName))
        {
            /** @var $DOMNode DOMNode */
            $DOMNode = $p_oDOMElement->attributes->getNamedItem($p_sName);
            $mValue = $DOMNode->nodeValue;
            if(is_numeric($mValue))
            {
                $mValue = (int) $mValue;
            }#if
        }#if

        return $mValue;
    }
}
    function run()
    {
        $iWidth  = isset($_GET['width'])?$_GET['width']:1754;
        $iHeight = $iWidth * 1.41619156214365;

        if(isset($_GET['month']))
        {
            // Output specific month
            $oDimensions = new CalendarDimensions($iWidth, $iHeight);

            $iYear = ($_GET['month']<9?2013:2012);
            $sDate = $iYear . '-' . $_GET['month'];
            $oDate = new DateTime($sDate);

            $oCalendar = new Calendar($oDimensions);
            $oCalendar->setSourcePath('calender_empty.png');
            $oCalendar->setDecorations(DecorationParser::getInstance()->buildDecorations($iYear));

            $sOutput = $oCalendar->render($oDate);
        }
        else
        {
            // Output all months
            $sOutput = '';

            $t_iCounter=8;
            while($t_iCounter!==false)
            {
                $t_iCounter++;

                if($t_iCounter === 13)
                {
                    $t_iCounter=1;
                }
                $sOutput .= '<a href="?month=' . $t_iCounter . '"><img src="?month=' . $t_iCounter . '&width=200"></a>';

                if($t_iCounter === 8)
                {
                    $t_iCounter=false;
                }
            }#while
        }#if

        echo ($sOutput);
    }

    run();

#EOF
