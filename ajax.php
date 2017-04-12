<?php
/*****************************
 * RPMON
 * Desarrollado por MdeMoUcH
 * mdemouch@gmail.com
 * http://www.lagranm.com/
 *****************************/

include_once('lib.php');

$rpmon = new RaspberryPiMon();

die($rpmon->showData());

