/*

Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.

For licensing, see LICENSE.html or http://ckeditor.com/license

*/



CKEDITOR.editorConfig = function( config )

{

	// Define changes to default configuration here. For example:

	// config.language = 'fr';

	// config.uiColor = '#AADC6E';
	
	config.toolbar_Zen =
	[
	    { name: 'document',    items : [ 'Source', 'Save', 'Maximize'] },
	    { name: 'clipboard',   items : [ 'Cut','Copy','Paste','-','Undo','Redo' ] },
	    { name: 'editing',     items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
	    '/',
	    { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
	    { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
	    { name: 'links',       items : [ 'Link','Unlink'] },
	    { name: 'insert',      items : [ 'Table','SpecialChar'] },
	    '/',
	    { name: 'styles',      items : [ 'Styles','Format','Font','FontSize' ] },
	    { name: 'colors',      items : [ 'TextColor','BGColor' ] },
	];

};

