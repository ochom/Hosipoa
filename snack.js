function SnackNotice(success,message) {
    $('.message_dialog_cover').show();
    if (success) {
        $('.dialog_title').html("Success :)");
        $('.dialog_title').css("color","green");
    }else{
       $('.dialog_title').html("Sorry :(");
        $('.dialog_title').css("color","red");
    }
    $('.dialog_body').html(message);
}

function RichUrl(element,data){
    $.ajax({
        method:'post',
        url:'crud.php',
        data:data,
        success:function(respose){
            element.html(respose);
        }
    });
}

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
