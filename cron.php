<?php
/*****************************
 * RPMON
 * Desarrollado por MdeMoUcH
 * mdemouch@gmail.com
 * http://www.lagranm.com/
 *****************************/

include_once('lib.php');

if(trim(shell_exec('whoami')) != 'www-data'){
	$rpmon = new RaspberryPiMon(true);
	$rpmon->calculateDay();
}else{
	header('Location: index.php');
}


die('done.'.PHP_EOL);

