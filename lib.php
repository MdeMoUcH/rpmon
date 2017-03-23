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
			$this->data->temp = trim(substr($s_temp, strpos($s_temp, 'Physical')+17,4));
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





