function plusKarma(ktarget, kitem_id){
	$("#karmapoints").load("/core/ajax/karma.php", {cd: "1", opt: "plus", target: ktarget, item_id: kitem_id}, kmLoaded());
    return false;
}
function minusKarma(ktarget, kitem_id){
	$("#karmapoints").load("/core/ajax/karma.php", {cd: "1", opt: "minus", target: ktarget, item_id: kitem_id}, kmLoaded());
    return false;
}
function kmLoaded(){
    $("#karmactrl").html('');
    return false;
}