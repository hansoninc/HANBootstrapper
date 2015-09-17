(function() {
	/**
	 * @exports HAN.controllers.home
	 * @requires HBS
	 */
	var module = {};

	module.init = function() {
		console.log('Hi from home.init()');
	};

	HBS.namespace('HAN.controllers.home', module);
}());