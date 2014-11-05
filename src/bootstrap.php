<?php

namespace Potherca\PrintableCalendarGenerator;


require __DIR__ . '/../vendor/autoload.php';

set_error_handler(function ($p_iCode, $p_sMessage, $p_sFile, $p_iLine ) {
    throw new \Potherca\PrintableCalendarGenerator\Exception($p_sMessage, 0, $p_iCode, $p_sFile, $p_iLine);
});

/*EOF*/
