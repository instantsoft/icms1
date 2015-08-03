function loadUserIp(){
	$.ajax({
	  type: "POST",
	  url: "/core/ajax/getip.php",
	  data: "user_id="+$('#user_id').val(),
	  success: function(msg){
		$('#ip').val(msg);
	  }
	});
}

