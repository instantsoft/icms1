function repairTreesRoot(){
	if(confirm(LANG_AD_REPAIR_CONFIRM)){
		$('#go_repair').val('1');
		$('#repairform').submit();
	}
}
function repairTrees(){
	if(confirm(LANG_AD_REPAIR_TOTREE_CONFIRM)){
        $('#go_repair_tree').val('1');
		$('#repairform').submit();
	}
}