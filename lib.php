<?php
/*****************************
 * RPMON
 * Desarrollado por MdeMoUcH
 * mdemouch@gmail.com
 * http://www.lagranm.com/
 *****************************/

require_once('bbdd.php');
require_once('fusioncharts.php');


class RaspberryPiMon extends bbdd {
	public $data;


	function __construct($b_save = false){
		parent::__construct();
		
		$this->data = new StdClass();
		
		$this->data->load = 0;
		$this->data->users = 0;
		$this->data->temp = 0;
		$this->data->mem_used = 0;
		$this->data->mem_total = 0;
		$this->data->since = '';
		$this->data->uptime = '';
		
		$this->updateInfo();
		if($b_save){
			$this->saveInfo();
		}
	}


	function updateInfo(){
		$this->load();
		$this->temp();
		$this->ram();
		$this->data->since = trim(shell_exec('uptime -s'));
		$this->data->uptime = trim(shell_exec('uptime -p'));
	}


	function load(){
		$a_uptime = explode(' ',shell_exec('uptime'));
		$cores = shell_exec('nproc --all');

		$while = 2;//2 para cada 5 min, 3 para cada 1 min
		
		$this->data->load = str_replace(',','.',substr($a_uptime[count($a_uptime)-$while],0,strlen($a_uptime[count($a_uptime)-$while])-1)) * 100 / $cores; 
		$this->data->users = str_replace(',','',$a_uptime[count($a_uptime)-8]);
	}


	function ram(){
		if(strpos(shell_exec("free -m"),'Memoria:') != 0){
			$this->data->mem_total = trim(shell_exec("free -m | grep 'Memoria:' | awk {'print $2'}"));
		}else{
			$this->data->mem_total = trim(shell_exec("free -m | grep 'Mem:' | awk {'print $2'}"));
		}
		$this->data->mem_used = trim(shell_exec("free -m | grep '+' | awk {'print $3'}"));
	}


	function temp(){
		if(file_exists('/opt/vc/bin/vcgencmd')){
			$s_temp = shell_exec('/opt/vc/bin/vcgencmd measure_temp');
			$this->data->temp = trim(str_replace('temp=','',str_replace("'C",'',$s_temp)));
		}else{
			/* //$a_temp = explode(PHP_EOL,shell_exec('acpi -t'));
			$a_temp = explode(' ',shell_exec('inxi -s'));
			$this->data->temp = str_replace('C','',$a_temp[6]); */
			/* Para instalar los sensores:
			sudo apt-get install lm-sensors
			sudo sensors-detect */
			$s_temp = shell_exec('sensors');
			if(strpos($s_temp,'Physical') !== false){
				$this->data->temp = trim(substr($s_temp, strpos($s_temp, 'Physical')+17,4));
			}else{
				$this->data->temp = 0;
			}
		}
	}	
	
	
	function saveInfo(){
		$s_sql = 'INSERT INTO rpmon (fecha,carga,usuarios,temp,mem_used,mem_total,desde) VALUES ("'.date('Y-m-d H:i:s').'",'.$this->data->load.','.$this->data->users.','.$this->data->temp.','.$this->data->mem_used.','.$this->data->mem_total.',"'.$this->data->since.'");';
		return $this->insertUpdate($s_sql);
	}
	
	
	function getData($day = '', $start = '00:00:00', $end = '23:59:59'){
		if($day == ''){
			$day = date('Y-m-d');
		}
		$s_sql = 'SELECT * FROM rpmon WHERE fecha >= "'.$day.' '.$start.'" AND fecha <= "'.$day.' '.$end.'" ORDER BY fecha ASC;';
		return $this->consulta($s_sql);
	}


	function calculateDay($dia_anterior = ''){
		if($dia_anterior == ''){
			$dia_anterior = date('Y-m-d',strtotime('-1 days'));
			$dia_actual = date('Y-m-d');
		}else{
			$dia_actual = date('Y-m-d',strtotime('+1 day',strtotime($dia_anterior)));
		}

		$s_sql = 'SELECT * FROM rpmon_day WHERE fecha like "%'.$dia_anterior.'%";';

		if(!$this->consulta($s_sql)){
			$s_sql = 'SELECT count(id) as total, SUM(carga) as carga, SUM(temp) as temp, SUM(mem_used) as mem_used, SUM(mem_total) as mem_total, SUM(usuarios) as usuarios FROM rpmon WHERE fecha BETWEEN "'.$dia_anterior.'" AND "'.$dia_actual.'";';
			if($this->consulta($s_sql)){
				$s_sql = 'INSERT INTO rpmon_day (fecha,carga,usuarios,temp,mem_used,mem_total) VALUES ("'.
					$dia_anterior.'",'.
					round($this->resultado[0]['carga']/$this->resultado[0]['total'],2).','.
					round($this->resultado[0]['usuarios']/$this->resultado[0]['total'],2).','.
					round($this->resultado[0]['temp']/$this->resultado[0]['total'],2).','.
					round($this->resultado[0]['mem_used']/$this->resultado[0]['total'],2).','.
					round($this->resultado[0]['mem_total']/$this->resultado[0]['total'],2).');';
				return $this->insertUpdate($s_sql);
			}else{
				return false;
			}
		}else{
			return true;
		}
	}
	
	
	function getDailyData(){
		$s_sql = 'SELECT * FROM (SELECT * FROM rpmon_day ORDER BY fecha DESC LIMIT 200) as aux ORDER BY aux.fecha ASC;';

		return $this->consulta($s_sql);
	}


	function showData(){
		//$this->data->load = 12.1;
		
		if(strpos($this->data->load,'.') === false){
			$this->data->load = $this->data->load.'.00';
		}elseif(strlen($this->data->load) < 5){
			if(strpos($this->data->load,'.') === 1){
				$this->data->load = ' '.$this->data->load;
			}elseif(strpos($this->data->load,'.') === 2){
				$this->data->load = $this->data->load.'0';
			}
		}

		return "
		<p>
			<b>SYSTEM STATUS:</b>
			&nbsp;&nbsp;
			<b>CPU:</b> ".$this->data->load ."%
			&nbsp;&nbsp;
			<b>RAM:</b> ".$this->data->mem_used ."/".$this->data->mem_total ." <small>MB</small>
			&nbsp;&nbsp;
			<b>Users:</b> ".$this->data->users ."
			&nbsp;&nbsp;
			<b>Temperature:</b> ".$this->data->temp ."ºC
			&nbsp;&nbsp;
			<b>Uptime:</b> ".$this->data->uptime."
		</p>";
	}
}//class















function getChart($a_data = array(),$s_tipo = 'column2d',$s_titulo = '',$s_subtitulo = '',$s_container = 'chart-container'){
	$a_json = array('chart'=> array(
						'caption'=>$s_titulo,
						'subCaption'=>$s_subtitulo,
						"xAxisname" => "Time",
						"yAxisName" => "%",
						"numberPrefix" => "",
						"showBorder" => "0",
						"showValues" => "0",
						"paletteColors" => "#0075c2,#f2c500,#1aaf5d",
						//"paletteColors" => "#1aaf5d,#E9967A,#B22222",
						"bgColor" => "#ffffff",
						"showCanvasBorder" => "0",
						"canvasBgColor" => "#ffffff",
						"captionFontSize" => "14",
						"subcaptionFontSize" => "14",
						"subcaptionFontBold" => "0",
						"divlineColor" => "#999999",
						"divLineIsDashed" => "1",
						"divLineDashLen" => "1",
						"divLineGapLen" => "1",
						"showAlternateHGridColor" => "0",
						"usePlotGradientColor" => "0",
						"toolTipColor" => "#ffffff",
						"toolTipBorderThickness" => "0",
						"toolTipBgColor" => "#000000",
						"toolTipBgAlpha" => "80",
						"toolTipBorderRadius" => "2",
						"toolTipPadding" => "5",
						"legendBgColor" => "#ffffff",
						"legendBorderAlpha" => '0',
						"legendShadow" => '0',
						"legendItemFontSize" => '10',
						"legendItemFontColor" => '#666666'
						),
					'categories' => array(array('category' => $a_data['categories']['category'])), 'dataset' => $a_data['dataset']);
	
	$s_json =  json_encode($a_json);
	
	$o_chart = new FusionCharts($s_tipo, $s_container.'_id', '100%', '340', $s_container, 'json', $s_json);
	
	return $o_chart->render();
}




function format_hora($fecha){
	$a_fecha = explode(' ',$fecha);
	$a_hora = explode(':',$a_fecha[1]);

	return $a_hora[0].':'.$a_hora[1];
}







function muere($s_msg = '', $b_obj = true, $b_die = true){if($b_obj){echo '<pre>';print_r($s_msg);echo '</pre>';}else{print_r($s_msg);}if($b_die){die;}}//fun





