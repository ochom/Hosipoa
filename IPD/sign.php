<html>
<head>
<title>Snackbar</title>
<?php 
    include 'links.php';
?>
<style>

</style>
</head>
<body>

     <!-- Use a button to open the snackbar -->
<button onclick="SnackNotice(true,'Some text here bla bla... Some text here bla bla...Some text here bla bla...vvSome text here bla bla...Some text here bla bla...')">Show Snackbar</button>

<!-- The actual snackbar -->
<div id="snackbar">
    <div class="row">
        <div class="col-12 bg-success" id="snackbar_title">Success</div>
        <div class="col-12" id="snackbar_message">Some text here bla bla...</div>
    </div>
</div> 
<script>

    function SnackNotice(success,message) {
        var x = document.getElementById('snackbar_title');
        if (success) {
            $('#snackbar_title').text("Success");
            x.className = x.className.replace("danger", "success");
        }else{
            $('#snackbar_title').text("Failed");
            x.className = x.className.replace("success", "danger");
        }

        $('#snackbar_message').text(message);
        $('#snackbar').toggleClass("show");

        setTimeout(function(){
           $('#snackbar').toggleClass("show");
        },3000);
    }

</script>
</body>
</html>

