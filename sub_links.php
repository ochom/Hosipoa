<title>HosiPoa - Lysofts inc.</title>

<meta charset="utf-8" />
<meta name="keywords" content="HTML, CSS, XML, XHTML, JavaScript">
<meta name="description" content="Hospital Management System">
<meta name="author" content="Richard Ochom">
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">


<link rel="icon" href="../logo2.png" type="image/x-icon" />
<link rel="shortcut icon" href="../logo2.png" type="image/x-icon" />
<link rel="stylesheet" href="../open-iconic-master/font/css/open-iconic-bootstrap.css">
<link rel="stylesheet" href="../bootstrap-4.0.0-dist/css/bootstrap.min.css"> <!--This is boostrap4 css--> 
<link rel="stylesheet" href="../jquery-ui-1.12.1/jquery-ui.css"/>
<script src="../jquery/jquery-3.4.1.js"></script>  
<script src="../jquery-ui-1.12.1/jquery-ui.js"></script>   
<script src="../bootstrap-4.0.0-dist/js/bootstrap.min.js"></script>
<script src="../chartsjs/charts.js"></script>

<script src="../snack.js"></script>
<link rel="stylesheet" type="text/css" href="../sidebarCss.css">


<!-- Proccessing dialog -->
<div class="modal modal-static" id="processDialog" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" >
    <div style="background-color: #eee;" id="progressline"><div class="box2"></div></div>  
</div>



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