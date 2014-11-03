<?php

require '../src/bootstrap.php';

$iWidth = isset($_GET['width']) ? (int) $_GET['width'] : 1754;
$iHeight = $iWidth * 1.41619156214365;
$bDebug = isset($_GET['debug']);

if (isset($_GET['month'])) {
    // Output specific month
    $oDimensions = new CalendarDimensions($iWidth, $iHeight);

    $iYear = ($_GET['month'] < 10 ? 2015 : 2014);
    $sDate = $iYear . '-' . $_GET['month'];
    $oDate = new DateTime($sDate);

    $oCalendar = new Calendar($oDimensions);
    $oCalendar->setDebug($bDebug);
    $oCalendar->setSourcePath(__DIR__ . '/../calender_empty.png');
    $oCalendar->setFontDirectory(__DIR__ . '/../fonts/');
    $oCalendar->setFontPath('erasblkb.pfb');

    $oDecorationParser = new DecorationParser();
    $oDecorationParser->setDecorationsDirectory(__DIR__ . '/../decorations/');
    $aDecorations = $oDecorationParser->buildDecorations($iYear);
    $oCalendar->setDecorations($aDecorations);

    $sOutput = $oCalendar->render($oDate);
} else {
    // Output all months
    $sOutput = '';

    $t_iCounter = 8;
    while ($t_iCounter !== false) {
        $t_iCounter++;

        if ($t_iCounter === 13) {
            $t_iCounter = 1;
        }
        $sOutput .= '<a href="?month=' . $t_iCounter . '">'
            . '<img src="'
            . '?month=' . $t_iCounter
            . '&width=' . $iWidth
            . ($bDebug ? '&debug=true' : '')
            . '">'
            . '</a>';

        if ($t_iCounter === 8) {
            $t_iCounter = false;
        }
    }#while
    $sOutput .= '<style type="text/css"> a {display: block; width: 16%; height: 56%; float: left;} img {width: 100%;};</style>';
}#if

die($sOutput);

/*EOF*/
