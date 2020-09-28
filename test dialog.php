<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sjmh";

try{
	$conn = new PDO("mysql:host=$servername;dbname=$dbname",$username,$password);
	$conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
	$e->Message();
}
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	 <?php 
    	include('links.php');
  	?>
	<style type="text/css">
		.confirm_dialog_cover{
			width: 100%; height: 100%; position: fixed; top: 0; left: 0; z-index: 999999; 
			background-color: rgba(240,230,240,0.3); display: none
		}

		.message_dialog_cover{
			width: 100%; height: 100%; position: fixed; top: 0; left: 0; z-index: 999999; 
			background-color: rgba(240,230,240,0.3); display: none			
		}

		.dialog_box{
			width: auto; max-width: 500px; height: 200px; max-height: 200px; background-color: #fff; margin: auto; border-radius: 5px;
			position: relative; margin-top: 100px; box-shadow: 3px 3px 5px #ccc;
		}
		.dialog_close{
	        position: absolute;top: 7px; right:7px; border: none; background-color: transparent; 
			border-radius: 3px; cursor: pointer; font-weight: normal; font-size: 13px;
	    }
	    .dialog_close:hover{
	        background-color: #ccc;
	    }
	    .dialog_title{
	    	border-bottom: 1px solid grey; padding: 5px 20px; font-size: 20px; font-weight: bold;
	    }
	    .dialog_body{
	    	padding: 20px;
	    }
	    .dialog_buttons{
	    	position: absolute; bottom: 0; left: 0; padding: 10px; width: 100%;
	    }
	    .dialog_buttons button{
	    	float: right; cursor: pointer; margin-left: 20px; padding: 2px 20px; font-weight: bold;
	    }
	</style>
</head>
<body style="background-image: url('bgg.jpg');">
	<div class="confirm_dialog_cover">
		<div class="dialog_box">
			<button class="dialog_close" onclick="$('.confirm_dialog_cover').hide();"><i class="oi oi-x"></i></button>
			<div class="dialog_title">
				Some text Here
			</div>
			<div class="dialog_body">
				Some text Here
			</div>
			<div class="dialog_buttons">
				<button class="btn btn-sm btn-danger" onclick="$('.confirm_dialog_cover').hide();" id="btnNo">No</button>
				<button class="btn btn-sm btn-success" onclick="$('.confirm_dialog_cover').hide();" id="btnYes">Yes</button>
			</div>
		</div>
	</div>
	<div class="message_dialog_cover">
		<div class="dialog_box">
			<button class="dialog_close" onclick="$('.message_dialog_cover').hide();"><i class="oi oi-x"></i></button>
			<div class="dialog_title">
				Some text Here
			</div>
			<div class="dialog_body">
				Some text Here
			</div>
			<div class="dialog_buttons">
				<button class="btn btn-sm btn-secondary" onclick="$('.message_dialog_cover').hide();">Ok</button>
			</div>
		</div>
	</div>
	<button onclick="RunCallback1()">Run Call Back 1</button>
	<button onclick="RunCallback2()">Run Call Back 2</button>
<script type="text/javascript">
	$(document).ready(function(){
		//$('.message_dialog_cover').show();
		/*RitchConfirm("Worse Code","Are you sure you want to send this request ?<br>This is irrreversible",function(){
			console.log('clicked yes');
		});*/
	});
	function RitchConfirm(title,message){
	    $('.confirm_dialog_cover').show();
	    $('.dialog_title').html(title);
	    $('.dialog_body').html(message);

		var defered = $.Deferred();

		$('.confirm_dialog_cover')

		//Turn off any events pre issued to click butttons
		.off('click.prompt')
		//Resolve the defered
		.on('click.prompt','#btnYes',function(){defered.resolve();})
		//reject the derrefed
		.on('click.prompt','#btnNo',function(){defered.jectet();});
	    
	    return defered.promise();
	}
	function RunCallback1(){
		RitchConfirm("Proceed ?","Some functing message").then(
			function(){
				console.log("suucess 2");
			}
		);

	}

	function RunCallback2(){
		var data_2 = 7;
		RitchConfirm("Proceed ?","Some functing message",function(data2){
			console.log("suucess 2");
		});
	}

</script>
</body>
</html>