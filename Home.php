<?php
include('ConnectionClass.php');
include('db_class.php');
session_start();

$db = new CRUD();

if (!(isset($_SESSION['Username']))) {
  header("refresh:0, url=../index.php");
  return;
}
$hospital = $db->ReadOne("SELECT * FROM tbl_hospital")['hospital_name'];
$patient_register = $db->ReadOne("SELECT count(*) as total FROM tbl_patient")['total'];
$out_patient_files = $db->ReadOne("SELECT count(*) as total FROM tbl_opd_visits")['total'];
$in_patient_files = $db->ReadOne("SELECT count(*) as total FROM tbl_ipd_admission")['total'];
?>
<!DOCTYPE html>
<html>
<head>
  <!--Links-->
  <?php 
    include('sub_links.php');
  ?>
  <!--//Links-->
  <style type="text/css">
    span{
        width: calc((100% - 80px)/3); height: 100px; border-radius: 5px;  margin: 2px 10px;
        background-color: #114422; position: relative; border: none; color: #fff; padding: 5px 15px;
    }
    span p{
       text-align: center;
    }
    span p b{
        font-size: 25px; margin-left: 20px;
    }
    span img{
        position: absolute; right: 3px; bottom: 3px; width: 50%; height: 60px;
    }
    .col-lg-6{
        padding:  5px; height: auto;  margin-top: 10px; position: relative;
    }
    .col-lg-12{
        padding:  5px; height: auto;  margin-top: 10px; position: relative;
    }
    .print_me{
        position: absolute;top: 7px; right:7px; border: none; background-color: transparent; border-radius: 3px; cursor: pointer;
    }
    .print_me:hover{
        background-color: #ccc;
    }
    canvas{
        background-color: #fff; width: 100%; height: auto; border-radius: 5px; border: 1px solid #555; position: relative;
    }
    
    @media print{
        @page { size: auto; margin: 0px;}
        body  { margin: 0px; padding: 0px;} 
    }
  </style>
</head>
<body style="background: url('bgg.jpg'); background-size: 100% 100%;">
<div class="d-flex" id="wrapper">
<!-- Sidebar -->
<?php
  include('sidebar.php');
?>
<!-- /#sidebar-wrapper -->

<!-- Page Content -->
<div id="page-content-wrapper">
    <div style="height: auto; background-color: rgba(255,255,255,0.9); border-bottom: 1px solid #ccc;">
        <i class="oi oi-menu" id="menu-toggle" style="margin: 20px;"></i> Dashboard
    </div>
    <div class="container-fluid">
        <div class="page_scroller row" style="height: calc(100vh - 80px);">
            <span>
                <p>Patient Register <br><b><?= $patient_register?></b></p>
                <img src="/../images/chart.png">
            </span>
            <span style="background-color: #55aa22;">
                <p>Out-Patient Files <br><b><?= $out_patient_files ?></b></p>
                <img src="/../images/chart.png">
            </span>
            <span style="background-color: #5522aa;">
                <p>In-Patient Enrolment <br><b><?= $in_patient_files ?></b></p>
                <img src="/../images/chart.png">
            </span>
            <div class="col-md-12 col-lg-6">
                <button class="print_me" onclick="printChart('daily')"><i class="oi oi-print"></i> Print</button>
                <canvas id="daily"></canvas>
            </div>
            <div class="col-md-12 col-lg-6">
                <button class="print_me" onclick="printChart('yearly')"><i class="oi oi-print"></i> Print</button>
                <canvas id="yearly"></canvas>
            </div>
            <div class="col-md-12 col-lg-12">
                <button class="print_me" onclick="printChart('monthy')"><i class="oi oi-print"></i> Print</button>
                <canvas id="monthy"></canvas>
            </div>
        </div>
    </div>
</div>
</div>
  <script>
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
    var hospital = "<?= $hospital?>";
    function printChart(canvas_id){
        var img_url = document.getElementById(canvas_id).toDataURL();
        var print_area = ""
            +"<div style='padding:50px; height:100%; width:100%'>"
            +"<h3 align='center'>"+hospital+"</h3>"
            +"<p align='center'>"
            +"<img src='"+img_url+"' style='width:90%; height:17cm; border:1px solid #555;'>"
            +"</p>"
            +"<p align='right'>"
            +"<b>Printed: <?= date('d/m/Y H:i:s')?> by <?= $_SESSION['Fullname']?>"
            +"</p>"
            +"</div>";
        $('body').css("background","none");
        $('body').html(print_area);
        window.print();
        location.href = location.href;
    }
    createCharts();
    setInterval(function(){
       createCharts();
    },60000);

    function createCharts(){
        $.ajax({
            method:'post',
            url:'crud.php',
            data:{GetGraphData:'1'},
            success:function(response){
                var y_max = 0;
                var return_data = JSON.parse(response);
                //console.log(return_data);
            /*Daily Chart*/
                y_max = Math.max.apply(null,(return_data.daily_data));
                //console.log(y_max);
                createHoriGraph(return_data.daily_data,'daily',"<?= date('l, d/m/Y')?> Patient Visits Chart",y_max);
            /*Monthly Chart*/
                var chartData = {
                    labels: return_data.monthly_legends,
                    datasets: [{
                        label: 'OPD Visits',
                        data: return_data.monthly_opd,
                        backgroundColor: '#55aa22',
                        borderColor: '#55aa22',
                        fill: true,
                        borderWidth: 2
                    },{
                        label: 'IPD Admissions',
                        data: return_data.monthly_ipd,
                        backgroundColor: '#5522aa',
                        borderColor: '#5522aa',
                        fill: true,
                        borderWidth: 2
                    }]
                };
                y_max = Math.max.apply(null,(return_data.monthly_opd).concat(return_data.monthly_ipd));
                //console.log(y_max);
                var months = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
                createGraph(chartData,'monthy', months[parseInt("<?= date('m')?>")]+", <?= date('Y')?> Patient Visits Chart",y_max);
            /*Yearly Chart*/
            var chartData = {
                    labels: return_data.yearly_legends,
                    datasets: [{
                        label: 'OPD Visits',
                        data: return_data.yearly_opd,
                        backgroundColor: '#55aa22',
                        borderColor: '#55aa22',
                        fill: true,
                        borderWidth: 2
                    },{
                        label: 'IPD Admissions',
                        data: return_data.yearly_ipd,
                        backgroundColor: '#5522aa',
                        borderColor: '#5522aa',
                        fill: true,
                        borderWidth: 2
                    }]
                };
                y_max = Math.max.apply(null,(return_data.yearly_opd).concat(return_data.yearly_ipd));
                //console.log(y_max);
                createGraph(chartData,'yearly',"<?= date('Y')?> Patient Visits Chart",y_max);
            /**/
            }
        });
    }

    function createHoriGraph(graph_data,canvas_id,title,y_max){
        var ctx = $('#'+canvas_id).get(0).getContext('2d');
        var myChart = new Chart(ctx, {
            type:'horizontalBar',
            data: {
                datasets: [{
                    label:'OPD',
                    data: [graph_data[0]],
                    backgroundColor: '#55aa22',
                    borderColor: '#55aa22',
                    fill: true,
                },{
                    label:'IPD',
                    data: [graph_data[1]],
                    backgroundColor: '#5522aa',
                    borderColor: '#5522aa',
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: title
                },
                scales: {
                    xAxes:[{
                        ticks:{
                            beginAtZero: true,
                            autoSkip:false,
                            maxRotation:90,
                            suggestedMax: y_max+5,
                            minRotation:70
                        },
                        gridLines: {
                            drawOnChartArea: true,
                            color:'#777'
                        },
                    }],
                    yAxes: [{
                        stacked:false,
                        display: true,
                        ticks: { 
                            beginAtZero: true,
                            maxRotation:90,
                            minRotation:90,
                            color:'#000'
                        },
                        gridLines: {
                            drawOnChartArea: true,
                            color:'#000'
                        },
                    }],
                },
                legend:{
                    position:'right'
                },
                animation:{
                    onComplete:createCaptionsHori
                }
            }
        });
    }

    function createGraph(data,canvas_id,title,y_max){
        var ctx = $('#'+canvas_id).get(0).getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
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
                        ticks: { 
                            beginAtZero: true, 
                            suggestedMax: y_max+5,
                        },
                        gridLines: {
                            drawOnChartArea: true,
                            color:'#777'
                        },
                    }],
                },
                animation:{
                    onComplete:createCaptions
                }
            }
        });
    }

    var createCaptionsHori = function(){
        var chartInstance = this.chart;
        ctx = chartInstance.ctx;

        ctx.fillStyle = '#f00';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'bottom';

        this.data.datasets.forEach(function(dataset,i){
            var meta = chartInstance.controller.getDatasetMeta(i);
            meta.data.forEach(function(bar,index){
                var data = dataset.data[index];
                if (data != 0) {
                    ctx.fillText(data,bar._model.x+15,bar._model.y);
                }
            });
        });
    }

    var createCaptions = function(){
        var chartInstance = this.chart;
        ctx = chartInstance.ctx;

        ctx.fillStyle = '#f00';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'bottom';

        this.data.datasets.forEach(function(dataset,i){
            var meta = chartInstance.controller.getDatasetMeta(i);
            meta.data.forEach(function(bar,index){
                var data = dataset.data[index];
                if (data != 0) {
                    ctx.fillText(data,bar._model.x,bar._model.y-5);
                }
            });
        });
    }

  </script>
</body>
</html>