<?php
/*****************************
 * RPMON
 * Desarrollado por MdeMoUcH
 * mdemouch@gmail.com
 * http://www.lagranm.com/
 *****************************/

include_once('lib.php');



$rpmon = new RaspberryPiMon();

$hostname = shell_exec('hostname');

$result = $rpmon->getDailyData();


if($result){
	$a_categories = array();
	$a_dataset = array();

	$a_carga = array();
	$a_mem = array();

	foreach($rpmon->resultado as $element){
		$a_categories['category'][] = array('label' => $element['fecha']);

		$a_carga[] = array('value' => $element['carga']);

		$a_temp[] = array('value' => $element['temp']);

		$a_mem[] = array('value' => round($element['mem_used']*100/$element['mem_total'],2));
	}

	$a_dataset[] = array('seriesname' => 'RAM (%)', 'data' => $a_mem);
	$a_dataset[] = array('seriesname' => 'CPU (%)', 'renderAs' => 'area', 'data' => $a_carga);
	$a_dataset[] = array('seriesname' => 'Temp. (ºC)', 'renderAs' => 'line', 'data' => $a_temp);

	$a_data = array('categories' => $a_categories, 'dataset' => $a_dataset);


	$s_grafica = getChart($a_data,'mscombi2d','Daily','','chart-grafica');
	$s_nodata = '';
}else{
	$s_grafica = '';
	$s_nodata = '<p class="centered">No data</p>';
}





?><!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?=$hostname?></title>
	<link rel="stylesheet" href="css/jquery-ui.css">
	<link rel="stylesheet" href="css/main.css">
	<meta http-equiv="refresh" content="300" >
</head>
<body>
	<h2><?=$hostname?></h2>

	<div>
		<p>
			<b>SYSTEM STATUS:</b>
			&nbsp;&nbsp;
			<b>CPU:</b> <?=$rpmon->data->load ?>%
			&nbsp;&nbsp;
			<b>RAM:</b> <?=$rpmon->data->mem_used ?>/<?=$rpmon->data->mem_total ?> <small>MB</small>
			&nbsp;&nbsp;
			<b>Users:</b> <?=$rpmon->data->users ?>
			&nbsp;&nbsp;
			<b>Temperature:</b> <?=$rpmon->data->temp ?>ºC
			&nbsp;&nbsp;
			<b>Uptime:</b> <?=$rpmon->data->uptime ?>
		</p>
	</div>

	<div id="chart-grafica" class="info_box big_info_box ui-state-default">
		<?=$s_nodata?>
	</div>

	<div id="enlaces" class="info_box big_info_box ui-state-default">
		<p class="centered">
			<a href='index.php'>Back</a>
			&nbsp;&nbsp;&nbsp;
		</p>
	</div>

	<!--<div class="info_box footer"><p>&copy; 2017 <a target="_blank" href="http://www.twitter.com/mdemouch">MdeMoUcH</a> <a target="_blank" href="http://www.lagranm.com/">lagranm.com</a></p></div>-->
	
	<script src="js/fusioncharts.js"></script>
	<script src="js/fusioncharts.charts.js"></script>
	<script src="js/fusioncharts.powercharts.js"></script>
	<script src="js/jquery-1.11.3.min.js"></script>
	<?=$s_grafica?>
</body>
</HTML>















