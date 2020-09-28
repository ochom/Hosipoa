var toggled = false;
var title = null;
var label = null;
var background_color = null;
var border_color = null;
var myChart = null;

$('.close_me').click(function(){
	if (toggled){
		($(this).parent('div')).parent('div').toggleClass('in');
		toggled = false;
	}
});

$('.print_me').click(function(){
	var page_content = $('body').html();

	var img_url = document.getElementById('MonitorChart').toDataURL();
	var body_to_print = ""
	+"<div style='padding:20px;'>"
	+"<h1 align='center'> MINISTRY OF HEALTH</h1>"
	+"<h3 align='center'>"+title+"</h3>"
	+"<h5 align='center' style='border-bottom:2px solid #000;'><i>"+hospital+"</i></h5>"
	+"<table style='width:100%;'>"
		+"<tr>"
			+"<td><b>Name:</b> "+name+"</td>"+"<td><b>Age:</b> "+age+"</td>"
		+"</tr>"
		+"<tr>"
			+"<td><b>IP No.:</b> "+fileno+"</td>"+"<td><b>Admission Date:</b> "+adm_date+"</td>"
		+"</tr>"
	+"</table>"
	+"<img src='"+img_url+"' style='width:100%; height:5in;'>"
	+"</div>";

	$('body').html(body_to_print);

	window.print();

});

window.onload = function(){
	window.onafterprint = function (){
		location.reload();
	};
	window.matchMedia('print').addListener(function(media){
		if (media.matches) {

		}else{
			media.preventDefault();
			window.location.reload(true);
		}
	});
}


function initMonitors(monitor){
	if (myChart !== null) {myChart.destroy();}
	 legends = null;
	 graph_data = null;
	 graph_data1 = null;
	 graph_data2 = null;
	 background_color = null;
	 border_color = null;

	if (!toggled) {$('.SlideMore').toggleClass('in'); toggled=true}
//Get datasets into array
	$.ajax({
		method:'post',
		url:'crud.php',
		data:{GetGraphData:'1',fileno:fileno},
		success:function(response){	
			var data = JSON.parse(response);
			switch(monitor){
				case "bp":
					title = 'Blood Pressure Chart  (MOH 31A)';
					createMultiGraph(data);
					break;
				case "temperature":
					title = 'Temperature Chart (MOH 318A)';
					label = 'Temperature Celcius';
					background_color = 'rgba(0,80,255,0.3)';
					border_color = 'rgba(0,80,255,1)';
					createGraph(data.legends,data.Temp);
					break;
				case "weight":
					title = 'Weight Monitor Chart';
					label = 'Weight (Kg)';
					background_color = 'rgba(255,80,0,0.3)';
					border_color = 'rgba(255,80,0,1)';
					createGraph(data.legends,data.Weight);
					break;
				case "pulse":
					title = 'Pulse Monitor Chart';
					label = 'Pulse (bpm)';
					background_color = 'rgba(80,255,0,0.3)';
					border_color = 'rgba(80,255,0,1)';
					createGraph(data.legends,data.Pulse);
					break;
				default:
					break;
			}
		}
	});

	
}


function createGraph(legends,graph_data){
	var box = $('#canvas_container');
    var canvas = $('#MonitorChart');
    var ctx = canvas.get(0).getContext('2d');
    canvas.width = box.width;
    canvas.height = box.height;
    myChart = Chart.Line(ctx, {
        data: {
            labels: legends,
            datasets: [{
            	label: label,
                data: graph_data,
                backgroundColor: background_color,
                borderColor: border_color,
                fill: true,
                borderWidth: 2
            }]
        },
        options: {
			responsive: true,
			hoverMode: 'index',
			stacked: false,
			title: {
				display: true,
				text: title
			},
			scales: {
				xAxes:[{
					ticks:{
						autoSkip:false,
						maxRotation:90,
						minRotation:70
					},
					gridLines: {
						drawOnChartArea: true,
						color:'#777'
					},
				}],
				yAxes: [{
					display: true,
					position: 'left',
					id: 'y-axis-1',
					ticks: { beginAtZero: true},
					gridLines: {
						drawOnChartArea: true,
						color:'#777'
					},
				}],
			}
		}
    });
}


function createMultiGraph(data){
	var lineChartData = {
		labels: data.legends,
		datasets: [{
			label: 'BP Systolic (mmHg)',
			borderColor: 'rgba(0, 0, 255, 1)',
			backgroundColor: 'rgba(0, 0, 255, 0.1)',
			fill: true,
			data: data.Systolic,
            borderWidth: 2,
			yAxisID: 'y-axis-1',

		}, {
			label: 'BP Diastolic (mmHg)',
			borderColor: 'rgba(0, 255, 0, 1)',
			backgroundColor: 'rgba(0, 255, 0, 0.1)',
			fill: true,
			data: data.Diastolic,
			borderWidth: 2
		}]
	};

    var box = $('#canvas_container');
    var canvas = $('#MonitorChart');
    var ctx = canvas.get(0).getContext('2d');
    canvas.width = box.width;
    canvas.height = box.height;

    myChart = Chart.Line(ctx, {
		data: lineChartData,
		options: {
			responsive: true,
			hoverMode: 'index',
			stacked: false,
			title: {
				display: true,
				text: 'Blood Pressure Monitor Tool'
			},
			scales: {
				xAxes:[{
					ticks:{
						fontColor:'#000',
						autoSkip:false,
						maxRotation:90,
						minRotation:70
					},
					gridLines: {
						drawOnChartArea: true,
						color:'#777'
					},
				}],
				yAxes: [{
					display: true,
					position: 'left',
					id: 'y-axis-1',
					ticks: { beginAtZero: true},
					gridLines: {
						drawOnChartArea: true,
						color:'#777'
					},
				}],
			}
		}
	});
}
