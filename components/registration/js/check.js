function checkLogin(){
	userlogin = $("#logininput").val();
    $("#logincheck").load("/core/ajax/registration.php", {opt: "checklogin", data:userlogin});
}

function checkPasswords(){
	var pass1 = $("#pass1input").val();
	var pass2 = $("#pass2input").val();
	if (pass1 != pass2) {
		$('#passcheck').html('<span style="color:red">'+LANG_WRONG_PASS+'</span>');
	}
}