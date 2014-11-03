<?php

/**
 *
 */
class DecorationParser
{
    /**
     * @var string
     */
    private $m_sDecorationsDirectory;

    /**
     * @return string
     */
    public function getDecorationsDirectory()
    {
        return $this->m_sDecorationsDirectory;
    }

    /**
     * @param string $p_sDecorationsDirectory
     */
    public function setDecorationsDirectory($p_sDecorationsDirectory)
    {
        $this->m_sDecorationsDirectory = (string) $p_sDecorationsDirectory;
    }

    /**
     * @param $iYear
     *
     * @return array
     *
     * @throws CustomException
     */
    public function buildDecorations($iYear)
    {
        $aDecorationList = array();

        $aXmlFiles = $this->fetchXmlFileList();
        $aIcsFiles = $this->fetchIcsFileList();

        $aXmlDecorations = $this->retrieveDecorationsFromXmlFiles($aXmlFiles, $iYear);
        $aIcsDecorations = $this->retrieveDecorationsFromIcsFiles($aIcsFiles,$iYear);

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
     * @param $p_sExtension
     *
     * @return array
     */
    protected function fetchFileList($p_sExtension)
    {
        $aFiles = glob($this->m_sDecorationsDirectory .'*.' . $p_sExtension);
        return $aFiles;
    }

    /**
     * @return array
     */
    protected function fetchIcsFileList()
    {
        return $this->fetchFileList('ics');
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

        foreach ($p_aFiles as $t_sFilePath) {
            $oDocument = new DOMDocument();
            $oDocument->preserveWhiteSpace = false;
            $oDocument->load($t_sFilePath);

            try {
                $bValid = $oDocument->validate();
            } catch (CustomException $eInvalid) {
                $bValid = false;
            }

            if ($bValid === false) {
                throw new CustomException('XML "' . $t_sFilePath . '" does either not use "decorations.dtd" or does not follow the structure outlined therein.');
            }

            $oBirthdayDecorations = $oDocument->getElementsByTagName('birthday');
            $oHolidayDecorations = $oDocument->getElementsByTagName('holiday');

            $aDecorationList = array_merge(
                $aDecorationList
                , $this->BuildDecorationArrayFromDOMNodeList($oBirthdayDecorations, $p_iYear)
                , $this->BuildDecorationArrayFromDOMNodeList($oHolidayDecorations, $p_iYear)
            );
        }

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
    protected function BuildDecorationArrayFromDOMNodeList(DOMNodeList $p_oDomNodeList,  $iYear)
    {
        $aDecorationList = array();

        for ($t_iCounter = 0; $t_iCounter < $p_oDomNodeList->length; $t_iCounter++) {
            /** @var $oDecoration DOMElement */
            $oDecoration = $p_oDomNodeList->item($t_iCounter);

            $sType = $this->getAttributeValue($oDecoration, 'type');
            if (empty($sType)) {
                $sType = strtoupper($oDecoration->nodeName);
            }

            $iDuration = $this->getAttributeValue($oDecoration, 'duration');

            /** @var $oChildren DOMNodeList */
            $oChildren = $oDecoration->childNodes;

            $aDecoration = array();

            for ($t_iSubCounter = 0; $t_iSubCounter < $oChildren->length; $t_iSubCounter++) {
                $oNode = $oChildren->item($t_iSubCounter);
                $aDecoration[$oNode->nodeName] = $oNode->nodeValue;
            }

            $iDuration = isset($iDuration) ? $iDuration : 1;
            $oInterval = new DateInterval('P' . $iDuration . 'D');
            $sDate = $aDecoration['date'];
            try {
                if ($aDecoration['name'] === '') {
                    throw new Exception('No Title set on line ' . $oDecoration->getLineNo());
                }

                /* Add current year if date does not mention a year */
                if (preg_match('/[0-9]{4}/', $sDate) === 0) {
                    $sDate .= ' ' . $iYear;
                } elseif ($oDecoration->tagName === 'birthday') {
                    /* replace birth year with current year */
                    $sDate = preg_replace('/[0-9]{4}/', $iYear, $sDate);
                }

                $oStartDate = new Datetime($sDate);

                $oEndDate = clone $oStartDate;
                $oEndDate->add($oInterval);

                /** @var Decoration $oDecoration */
                $oDecoration = new Decoration($oStartDate, $oInterval, $oEndDate);
                $oDecoration->setType(new DecorationType($sType));
                $oDecoration->setTitle($aDecoration['name']);

                $aDecorationList[] = $oDecoration;
            } catch (Exception $oException) {
                //@TODO: Warn user that there is a date we cannot parse
                // echo $oException->getMessage();
            }
        }

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
        if ($p_oDOMElement->hasAttribute($p_sName)) {
            /** @var $DOMNode DOMNode */
            $DOMNode = $p_oDOMElement->attributes->getNamedItem($p_sName);
            $mValue = $DOMNode->nodeValue;
            if (is_numeric($mValue)) {
                $mValue = (int) $mValue;
            }
        }

        return $mValue;
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

        foreach ($p_aFiles as $t_sFilePath) {
            $oIcal = new ICalReader($t_sFilePath);
            $aEvents = $oIcal->events();

            foreach ($aEvents as $aEvent) {
                if (isset($aEvent['SUMMARY']) === false) {
                    $aKeys = array_keys($aEvent);

                    foreach ($aKeys as $sKey) {
                        if (strpos($sKey, 'SUMMARY') === 0) {
                            break;
                        } else {
                            unset($sKey);
                        }
                    }

                    if (isset($sKey) === true) {
                        /** @noinspection PhpUndefinedVariableInspection *///Reason: STUPID IDE
                        $aEvent['SUMMARY'] = $aEvent[$sKey];
                    } else {
                        throw new Exception('No SUMMARY set for event dated ' . $aEvent['DTSTART']);
                    }
                } elseif (empty($aEvent['SUMMARY'])) {
                    throw new Exception('SUMMARY set but empty on event dated ' . $aEvent['DTSTART']);
                } else {
                    // There is nothing else
                }

                //$oInterval = new DateInterval('P' . $iDuration . 'D');

                $oStartDate = new Datetime($aEvent['DTSTART']);
                $oEndDate = new Datetime($aEvent['DTEND']);

                $oInterval = $oStartDate->diff($oEndDate);

                $oDecoration = new Decoration($oStartDate, $oInterval, $oEndDate);

                $oDecoration->setTitle($aEvent['SUMMARY']);

                if (isset($aEvent['DECORATION-TYPE'])) {
                    $sType = $aEvent['DECORATION-TYPE'];
                } elseif ($oInterval->days > 0) {
                    $sType = 'SCHOOL_HOLIDAY';
                } else {
                    $sType = 'CUSTOM';
                }

                $oDecoration->setType(new DecorationType($sType));

                $aDecorationList[] = $oDecoration;
            }
        }

        return $aDecorationList;
    }
}
