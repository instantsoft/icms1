CKEDITOR.editorConfig = function( config ) {
	config.toolbarGroups = [
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'tools' },
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo', 'PasteFromWord' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'forms' },
		{ name: 'styles' },
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'colors' },
		{ name: 'others' }
	];
    config.forcePasteAsPlainText = true;
    config.allowedContent = true;
    config.removeButtons = 'Cut,Copy,Paste';
    config.removeDialogTabs = 'link:advanced;link:upload';
};