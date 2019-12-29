<?php
// Base de datos
define('DB_NAME', 'crono');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_HOST', 'localhost');
if(isset($_SESSION['prefix'])) define('DB_PREFIX', $_SESSION['prefix']);
else define('DB_PREFIX', 'rcm_');

// Permisos
define('SUPERADMIN', 0);
define('ADMIN', 1);
define('USER', 2);
define('VISITOR', 10);

define('DB_CHARSET', 'utf8');

define('EMAIL_ADMIN', 'jfarevalo@live.com');

define('FISPATH', dirname(__FILE__));
define('ABSPATH', 'http://crono.rallyrcmadrid.com/'); // Por ejemplo -> $archivo = ABSPATH."upload/".$fichero;

//Ruta para guardar registro de errores
define('ERRORLOG_PATH',  dirname(__FILE__) . '\error.log');
?>