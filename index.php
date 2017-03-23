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

if(@$_GET['time'] != ''){
	if(strpos($_GET['time'],'hours') != 0){
		$horas = str_replace('hours','',$_GET['time']);
		$result = $rpmon->getData('',date('H:i:s',strtotime('-'.$horas.' hours')));
	}elseif(strpos($_GET['time'],'pm') != 0){
		$hora = str_replace('pm','',$_GET['time']);
		if(strlen($hora) < 2){
			$hora = '0'.$hora;
		}
		$result = $rpmon->getData('',$hora.':00:00');
	}elseif(strpos($_GET['time'],'today') !== false){
		$result = $rpmon->getData();
	}elseif(strpos($_GET['time'],'daysago') !== false){
		$dia = str_replace('daysago','',$_GET['time']);
		$result = $rpmon->getData(date('Y-m-d',strtotime('-'.$dia.' days')));
	}else{
		$result = $rpmon->getData();
	}
}else{
	$result = $rpmon->getData('',date('H:i:s',strtotime('-6 hour')));
}


if($result){
	$a_categories = array();
	$a_dataset = array();

	$a_carga = array();
	$a_mem = array();

	foreach($rpmon->resultado as $element){
		$a_categories['category'][] = array('label' => format_hora($element['fecha']));

		$a_carga[] = array('value' => $element['carga']);

		$a_temp[] = array('value' => $element['temp']);

		$a_mem[] = array('value' => round($element['mem_used']*100/$element['mem_total'],2));
	}

	$a_dataset[] = array('seriesname' => 'RAM (%)', 'data' => $a_mem);
	$a_dataset[] = array('seriesname' => 'CPU (%)', 'renderAs' => 'area', 'data' => $a_carga);
	$a_dataset[] = array('seriesname' => 'Temp. (ºC)', 'renderAs' => 'line', 'data' => $a_temp);

	$a_data = array('categories' => $a_categories, 'dataset' => $a_dataset);


	$s_grafica = getChart($a_data,'mscombi2d','','','chart-grafica');
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
			<b>Uptime:</b> <?=$rpmon->data->since ?>
		</p>
	</div>

	<div id="chart-grafica" class="info_box big_info_box ui-state-default">
		<?=$s_nodata?>
	</div>

	<div id="enlaces" class="info_box big_info_box ui-state-default">
		<p class="centered">
			<a href='?time=1hours'>1 hour ago</a>
			&nbsp;&nbsp;&nbsp;
			<a href='?time=2hours'>2 hours ago</a>
			&nbsp;&nbsp;&nbsp;
			<a href='?time=3hours'>3 hours ago</a>
			&nbsp;&nbsp;&nbsp;
			<a href='?time=9pm'>Since 9:00<small>PM</small></a>
		</p>
		<p class="centered">
			<a href='?time=today'>Today</a>
			&nbsp;&nbsp;&nbsp;
			<a href='?time=1daysago'>Yesterday</a>
			&nbsp;&nbsp;&nbsp;
			<a href='?time=2daysago'>2 days ago</a>
			&nbsp;&nbsp;&nbsp;
			<a href='?time=3daysago'>3 days ago</a>
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















