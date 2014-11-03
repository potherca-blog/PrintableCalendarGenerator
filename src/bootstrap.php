<?php
define('LIBRARY_DIRECTORY', __DIR__ . '/');

require LIBRARY_DIRECTORY . 'Base/abstract.Base.php';

spl_autoload_register(function ($p_sClassName){
    $sClassPath = LIBRARY_DIRECTORY . 'class.' . $p_sClassName . '.php';

    if(file_exists($sClassPath)){
        require $sClassPath;
    }
});

set_error_handler(array('CustomException', 'errorHandlerCallback'), E_ALL | E_STRICT);

/*EOF*/
