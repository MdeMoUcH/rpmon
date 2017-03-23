<?php

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
	}elseif(strpos($_GET['time'],'yesterday') !== false){
		$result = $rpmon->getData(date('Y-m-d',strtotime('-24 hours')));
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
}else{
	$s_grafica = '<p>No data.</p>';
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
	</div>
	<input type="hidden" id="aaSorting" value="1" />


	<div id="enlaces" class="info_box big_info_box ui-state-default">
		<p style="text-align:center;">
			<a href='?time=today'>Today</a>
			&nbsp;&nbsp;&nbsp;
			<a href='?time=9pm'>Since 9:00<small>PM</small></a>
			&nbsp;&nbsp;&nbsp;
			<a href='?time=1hours'>1 hour ago</a>
			&nbsp;&nbsp;&nbsp;
			<a href='?time=2hours'>2 hours ago</a>
			&nbsp;&nbsp;&nbsp;
			<a href='?time=3hours'>3 hours ago</a>
		</p>
	</div>

	
	<br/><br/><br/>
	<div id="multidialog" title="" class="dialogo"></div>
	
	<script src="js/fusioncharts.js"></script>
	<script src="js/fusioncharts.charts.js"></script>
	<script src="js/fusioncharts.powercharts.js"></script>
	<script src="js/jquery-1.11.3.min.js"></script>
	<?=$s_grafica?>
</body>
</HTML>















