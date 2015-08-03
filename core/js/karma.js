function plusKarma(ktarget, kitem_id){
	$("#karmapoints").load("/core/ajax/karma.php", {cd: "1", opt: "plus", target: ktarget, item_id: kitem_id}, kmLoaded());
}
function minusKarma(ktarget, kitem_id){
	$("#karmapoints").load("/core/ajax/karma.php", {cd: "1", opt: "minus", target: ktarget, item_id: kitem_id}, kmLoaded());
}
function kmLoaded(){
    $("#karmactrl").html("");
}