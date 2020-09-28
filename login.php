<?php
session_start();
if (isset($_SESSION['Username'])) {
	session_destroy();
}

$file=fopen("ritch.xml", "w");
fwrite($file, "kilodhi wololo");
fclose($file);
?>
<!DOCTYPE html>
<html>
<head>
	<!--Links-->
  <?php 
    include('links.php');
  ?>
  <!--//Links-->
  <style type="text/css">  	
		.box2{
			background-color: #f80; height: 5px;
			animation: bar-enlarge 2s linear infinite;
		}
		@keyframes bar-enlarge{
			0%{
				width: 30%;
			}
			30%{
				width: 60%; margin-left: 25%;
			}
			100%{
				width: 30%; margin-left: 100%;
			}
		}
  </style>
</head>
<body style="background-color: #fff;">
	<div class="card col-xs-10 col-sm-9 col-md-6 col-lg-4" style="margin:auto; padding: 0px; overflow: hidden; margin-top: 5%;">
		<div style="background-color: #eee;" id="progressBar"><div class="box2"></div></div>
		<article class="card-body">
			<h4 class="card-title text-center mb-4 mt-1">Sign in</h4>
			<hr>
			<p class="text-success text-center">Use you username or email  and Password for login</p>
			<div class="form-group">
				<div class="input-group">
					<div class="input-group-prepend">
					    <span class="input-group-text"> <i class="oi oi-person"></i> </span>
					 </div>
					<input class="form-control" id="username" placeholder="Username" >
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<div class="input-group-prepend">
					    <span class="input-group-text"> <i class="oi oi-lock-locked"></i> </span>
					 </div>
				    <input class="form-control" id="password" placeholder="********" type="password">
				</div>
			</div>
			<p id="login_message" class="text-danger text-center"></p>
			<div class="form-group">
				<button id="btnLogin" class="btn btn-primary btn-block" onclick="Login()"> Login  </button>
			</div>
			<p class="text-center"><a href="#" class="btn">Forgot password?</a></p>
		</article>
	</div> 
<script type="text/javascript">

	$('.admin').hide();
	$('#progressBar').hide();
	window.addEventListener('keyup',function(e){
		if (e.keyCode ===13) {
			Login();
		}
	});

	function Login(){
		var username = $('#username').val();
		var password = $('#password').val();

		if (username.length===0) {$('#login_message').text('Username cannot be empty');$('#username').focus(); return;}
		if (password.length===0) {$('#login_message').text('Password cannot be empty'); $('#password').focus();return;}
		$('#login_message').text('');
		$('#progressBar').show();
		$.ajax({			
			method:'POST',
			url:'CRUD.php',
			data:{Login:'1',username:username,password:password},
			success:function(response){
				console.log('success >> s at'+response.indexOf('s')+ 'And s at'+ response.lastIndexOf('s'));
				$('#progressBar').hide();			
				if (response.includes('success')) {
					location.href='home.php';
				}else{
					$('#login_message').text(response);
				}
			}
		});
	}

</script>
</body>
</html>