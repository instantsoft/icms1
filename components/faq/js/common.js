function sendQuestion(){
	if($('#faq_message').val().length < 10){
	 	core.alert(LANG_ERR_QUESTION, LANG_ERROR);
	} else {
		document.questform.submit();
	}
}