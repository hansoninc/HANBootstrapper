(function() {

	// IE console nerfing
	window.console = window.console || {log : function(){}};

	/**
	 * @requires HBS
	 */
	var module = {};

	/**
	 * Global init code for the whole application
	 */
	module.init = function() {
		console.log('Init');
	};

	/**
	 * Initialize the app and run the bootstrapper
	 */
	$(document).ready(function() {
		HBS.initPage();
		module.init();
	});

	HBS.namespace('HAN.main', module);
}());
