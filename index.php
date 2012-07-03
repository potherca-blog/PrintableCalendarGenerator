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
        require LIBRARY_DIRECTORY . 'class.' . $p_sClassName . '.php';
    });

    /**
     * @param $iYear
     *
     * @return array
     */
    function BuildDecorations ($iYear)
    {
//
//        $aExampleDecorations = array(
//            array(
//                  'iDate'  => 20111010          // Date the Decoration starts in YYYYMMDD version
//                , 'sTitle' => 'Test Decoration'
////                , 'iDuration'  => 1             // Amount of days this Decoration Lasts, set to 1 by default
//                , 'sType'  => 'BIRTHDAY'        // Type of Decoration, BIRTHDAY | SECULAR_HOLIDAY | NATIONAL_HOLIDAY | SCHOOL_HOLIDAY
//            )
//        );
        $aDecorationList = array();

        $aFiles = glob('./decorations/*.xml');
        foreach($aFiles as $t_sFilePath)
        {
            $oDocument = new DOMDocument();
            $oDocument->preserveWhiteSpace = false;
            $oDocument->load($t_sFilePath);
            //@TODO: Validate XML using $oDocument->validate(); or $oDocument->schemaValidate('./decorations/decorations.dtd');
            $oBirthdayDecorations = $oDocument->getElementsByTagName('birthday');
            $oHolidayDecorations = $oDocument->getElementsByTagName('holiday');

            $aDecorationList = array_merge(
                  $aDecorationList
                , BuildDecorationArrayFromDOMNodeList($oBirthdayDecorations, $iYear)
                , BuildDecorationArrayFromDOMNodeList($oHolidayDecorations, $iYear)
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
    function BuildDecorationArrayFromDOMNodeList(DOMNodeList $p_oDomNodeList, $iYear)
    {
        $aDecorationList = array();
        for($t_iCounter=0; $t_iCounter<$p_oDomNodeList->length; $t_iCounter++)
        {
            /** @var $oDecoration DOMElement */
            $oDecoration = $p_oDomNodeList->item($t_iCounter);

            $sType = getAttributeValue($oDecoration, 'type');
            if(empty($sType))
            {
                $sType = strtoupper($oDecoration->nodeName);
            }#if

            $iDuration = getAttributeValue($oDecoration, 'duration');

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
    function getAttributeValue(DOMElement $p_oDOMElement, $p_sName)
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

    function run()
    {
        $iWidth  = isset($_GET['width'])?$_GET['width']:1754;
        $iHeight = $iWidth * 1.41619156214365;

        if(isset($_GET['month']))
        {
            $oDimensions = new CalendarDimensions($iWidth, $iHeight);

            $iYear = ($_GET['month']<9?2012:2011);
            $sDate = $iYear . '-' . $_GET['month'];
            $oDate = new DateTime($sDate);

            $oCalendar = new Calendar($oDimensions);
            $oCalendar->setSourcePath('calender_empty.png');
            $oCalendar->setDecorations(BuildDecorations($iYear));

            $sOutput = $oCalendar->render($oDate);
        }
        else
        {
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