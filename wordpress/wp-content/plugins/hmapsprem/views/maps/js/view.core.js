//DROPDOWN VIEW CORE

//core config
var map_config = {
	"map_types": {
		"ROADMAP": {
			"title": "Roadmap",
			"show_theme": true
		},
		"SATELLITE": {
			"title": "Satellite",
			"show_theme": false
		},
		"HYBRID": {
			"title": "Hybrid",
			"show_theme": false
		},
		"TERRAIN": {
			"title": "Terrain",
			"show_theme": true
		}
	},
	"control_positions": {
		"DEFAULT": "Default",
		"TOP_CENTER": "Top Center",
		"TOP_LEFT": "Top Left",
		"TOP_RIGHT": "Top Right",
		"BOTTOM_CENTER": "Bottom Center",
		"BOTTOM_LEFT": "Bottom Left",
		"BOTTOM_RIGHT": "Bottom Right",
		"LEFT_CENTER": "Left Center",
		"RIGHT_CENTER": "Right Center"
	},
	"map_type_control_styles": {
		"DEFAULT": "Default",
		"HORIZONTAL_BAR": "Horizontal Bar",
		"DROPDOWN_MENU": "Dropdown Menu"
	},
	"marker_animation_types": {
		"DROP": "Drop",
		"BOUNCE": "Bounce",
		"DEFAULT": "Default"
	},
	"map_themes": {
		"Default": "",
		"Subtle Grayscale": [{"featureType":"landscape","stylers":[{"saturation":-100},{"lightness":65},{"visibility":"on"}]},{"featureType":"poi","stylers":[{"saturation":-100},{"lightness":51},{"visibility":"simplified"}]},{"featureType":"road.highway","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"road.arterial","stylers":[{"saturation":-100},{"lightness":30},{"visibility":"on"}]},{"featureType":"road.local","stylers":[{"saturation":-100},{"lightness":40},{"visibility":"on"}]},{"featureType":"transit","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"administrative.province","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":-25},{"saturation":-100}]},{"featureType":"water","elementType":"geometry","stylers":[{"hue":"#ffff00"},{"lightness":-25},{"saturation":-97}]}],
		"Unsaturated Browns": [{"elementType":"geometry","stylers":[{"hue":"#ff4400"},{"saturation":-68},{"lightness":-4},{"gamma":0.72}]},{"featureType":"road","elementType":"labels.icon"},{"featureType":"landscape.man_made","elementType":"geometry","stylers":[{"hue":"#0077ff"},{"gamma":3.1}]},{"featureType":"water","stylers":[{"hue":"#00ccff"},{"gamma":0.44},{"saturation":-33}]},{"featureType":"poi.park","stylers":[{"hue":"#44ff00"},{"saturation":-23}]},{"featureType":"water","elementType":"labels.text.fill","stylers":[{"hue":"#007fff"},{"gamma":0.77},{"saturation":65},{"lightness":99}]},{"featureType":"water","elementType":"labels.text.stroke","stylers":[{"gamma":0.11},{"weight":5.6},{"saturation":99},{"hue":"#0091ff"},{"lightness":-86}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"lightness":-48},{"hue":"#ff5e00"},{"gamma":1.2},{"saturation":-23}]},{"featureType":"transit","elementType":"labels.text.stroke","stylers":[{"saturation":-64},{"hue":"#ff9100"},{"lightness":16},{"gamma":0.47},{"weight":2.7}]}],
		"Rich Black": [{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#222222"},{"visibility":"on"}]}],
		"Blue water": [{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#46bcec"},{"visibility":"on"}]}],
		"Pale Dawn": [{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"on"},{"lightness":33}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2e5d4"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#c5dac6"}]},{"featureType":"poi.park","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":20}]},{"featureType":"road","elementType":"all","stylers":[{"lightness":20}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#c5c6c6"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#e4d7c6"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#fbfaf7"}]},{"featureType":"water","elementType":"all","stylers":[{"visibility":"on"},{"color":"#acbcc9"}]}],
		"Cobalt": [{"featureType":"all","elementType":"all","stylers":[{"invert_lightness":true},{"saturation":10},{"lightness":30},{"gamma":0.5},{"hue":"#00aaff"}]},{"featureType":"administrative.province","elementType":"geometry.stroke","stylers":[{"saturation":"100"},{"lightness":"27"}]},{"featureType":"landscape","elementType":"geometry.fill","stylers":[{"color":"#32373c"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"saturation":"100"},{"lightness":"69"},{"gamma":"1.40"}]},{"featureType":"road.highway","elementType":"labels.text.fill","stylers":[{"lightness":"100"},{"saturation":"100"}]},{"featureType":"road.highway.controlled_access","elementType":"labels.icon","stylers":[{"saturation":"100"}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"saturation":"43"},{"lightness":"51"}]},{"featureType":"road.arterial","elementType":"labels.text.fill","stylers":[{"saturation":"45"},{"lightness":"19"}]}],
		"Retro": [{"featureType":"administrative","stylers":[{"visibility":"off"}]},{"featureType":"poi","stylers":[{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"simplified"}]},{"featureType":"water","stylers":[{"visibility":"simplified"}]},{"featureType":"transit","stylers":[{"visibility":"simplified"}]},{"featureType":"landscape","stylers":[{"visibility":"simplified"}]},{"featureType":"road.highway","stylers":[{"visibility":"off"}]},{"featureType":"road.local","stylers":[{"visibility":"on"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"water","stylers":[{"color":"#84afa3"},{"lightness":52}]},{"stylers":[{"saturation":-17},{"gamma":0.36}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"color":"#3f518c"}]}],
		"Red Alert": [{"featureType":"all","elementType":"all","stylers":[{"visibility":"simplified"},{"saturation":"-100"},{"invert_lightness":true},{"lightness":"11"},{"gamma":"1.27"}]},{"featureType":"administrative.locality","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"landscape.man_made","elementType":"all","stylers":[{"hue":"#ff0000"},{"visibility":"simplified"},{"invert_lightness":true},{"lightness":"-10"},{"gamma":"0.54"},{"saturation":"45"}]},{"featureType":"poi.business","elementType":"all","stylers":[{"visibility":"simplified"},{"hue":"#ff0000"},{"saturation":"75"},{"lightness":"24"},{"gamma":"0.70"},{"invert_lightness":true}]},{"featureType":"poi.government","elementType":"all","stylers":[{"hue":"#ff0000"},{"visibility":"simplified"},{"invert_lightness":true},{"lightness":"-24"},{"gamma":"0.59"},{"saturation":"59"}]},{"featureType":"poi.medical","elementType":"all","stylers":[{"visibility":"simplified"},{"invert_lightness":true},{"hue":"#ff0000"},{"saturation":"73"},{"lightness":"-24"},{"gamma":"0.59"}]},{"featureType":"poi.park","elementType":"all","stylers":[{"lightness":"-41"}]},{"featureType":"poi.school","elementType":"all","stylers":[{"visibility":"simplified"},{"hue":"#ff0000"},{"invert_lightness":true},{"saturation":"43"},{"lightness":"-16"},{"gamma":"0.73"}]},{"featureType":"poi.sports_complex","elementType":"all","stylers":[{"hue":"#ff0000"},{"saturation":"43"},{"lightness":"-11"},{"gamma":"0.73"},{"invert_lightness":true}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":"45"},{"lightness":"53"},{"gamma":"0.67"},{"invert_lightness":true},{"hue":"#ff0000"},{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"simplified"},{"hue":"#ff0000"},{"saturation":"38"},{"lightness":"-16"},{"gamma":"0.86"}]}],
		"Spy map": [{"featureType":"all","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"saturation":"-66"},{"lightness":"1"}]},{"featureType":"all","elementType":"geometry.stroke","stylers":[{"visibility":"on"}]},{"featureType":"all","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"all","elementType":"labels.text.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"color":"#000000"},{"lightness":13}]},{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"administrative","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#144b53"},{"lightness":14},{"weight":1.4}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#223c35"},{"visibility":"on"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#172720"},{"lightness":5},{"visibility":"on"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#162723"}]},{"featureType":"poi","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"poi","elementType":"labels.icon","stylers":[{"visibility":"on"}]},{"featureType":"road","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"road","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"visibility":"on"}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"visibility":"on"},{"saturation":"14"},{"weight":"0.43"},{"color":"#357464"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#1f3222"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"lightness":25},{"color":"#133f19"}]},{"featureType":"road.highway","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"road.highway.controlled_access","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"road.highway.controlled_access","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.highway.controlled_access","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"road.arterial","elementType":"geometry.stroke","stylers":[{"lightness":16},{"color":"#1ad9ba"}]},{"featureType":"road.arterial","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"road.local","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"road.local","elementType":"geometry.fill","stylers":[{"color":"#26625a"}]},{"featureType":"road.local","elementType":"geometry.stroke","stylers":[{"visibility":"on"},{"color":"#48b697"}]},{"featureType":"road.local","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"color":"#233833"},{"visibility":"on"}]},{"featureType":"transit","elementType":"geometry.stroke","stylers":[{"visibility":"on"}]},{"featureType":"transit.line","elementType":"geometry.stroke","stylers":[{"visibility":"on"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#131d19"},{"visibility":"on"}]}],
		"Shades of Grey": [{"featureType":"all","elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#000000"},{"lightness":40}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#000000"},{"lightness":16}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":20}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":17},{"weight":1.2}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":20}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":21}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":17}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":29},{"weight":0.2}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":18}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":16}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":19}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":17}]}],
		"Subtle Grayscale": [{"featureType":"landscape","stylers":[{"saturation":-100},{"lightness":65},{"visibility":"on"}]},{"featureType":"poi","stylers":[{"saturation":-100},{"lightness":51},{"visibility":"simplified"}]},{"featureType":"road.highway","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"road.arterial","stylers":[{"saturation":-100},{"lightness":30},{"visibility":"on"}]},{"featureType":"road.local","stylers":[{"saturation":-100},{"lightness":40},{"visibility":"on"}]},{"featureType":"transit","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"administrative.province","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":-25},{"saturation":-100}]},{"featureType":"water","elementType":"geometry","stylers":[{"hue":"#ffff00"},{"lightness":-25},{"saturation":-97}]}],
		"Blue water": [{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#46bcec"},{"visibility":"on"}]}],
		"Pale Dawn": [{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"on"},{"lightness":33}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2e5d4"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#c5dac6"}]},{"featureType":"poi.park","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":20}]},{"featureType":"road","elementType":"all","stylers":[{"lightness":20}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#c5c6c6"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#e4d7c6"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#fbfaf7"}]},{"featureType":"water","elementType":"all","stylers":[{"visibility":"on"},{"color":"#acbcc9"}]}],
		"Blue Essence": [{"featureType":"landscape.natural","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#e0efef"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"hue":"#1900ff"},{"color":"#c0e8e8"}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":100},{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"visibility":"on"},{"lightness":700}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#7dcdcd"}]}],
		"Apple Maps-esque": [{"featureType":"landscape.man_made","elementType":"geometry","stylers":[{"color":"#f7f1df"}]},{"featureType":"landscape.natural","elementType":"geometry","stylers":[{"color":"#d0e3b4"}]},{"featureType":"landscape.natural.terrain","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi.business","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.medical","elementType":"geometry","stylers":[{"color":"#fbd3da"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#bde6ab"}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffe15f"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#efd151"}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"road.local","elementType":"geometry.fill","stylers":[{"color":"black"}]},{"featureType":"transit.station.airport","elementType":"geometry.fill","stylers":[{"color":"#cfb2db"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#a2daf2"}]}],
		"Midnight Commander": [{"featureType":"all","elementType":"labels.text.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"color":"#000000"},{"lightness":13}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#144b53"},{"lightness":14},{"weight":1.4}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#08304b"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#0c4152"},{"lightness":5}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#0b434f"},{"lightness":25}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"road.arterial","elementType":"geometry.stroke","stylers":[{"color":"#0b3d51"},{"lightness":16}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#000000"}]},{"featureType":"transit","elementType":"all","stylers":[{"color":"#146474"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#021019"}]}],
		"Cool Grey": [{"featureType":"landscape","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"stylers":[{"hue":"#00aaff"},{"saturation":-100},{"gamma":2.15},{"lightness":12}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"visibility":"on"},{"lightness":24}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":57}]}],
		"Neutral Blue": [{"featureType":"water","elementType":"geometry","stylers":[{"color":"#193341"}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#2c5a71"}]},{"featureType":"road","elementType":"geometry","stylers":[{"color":"#29768a"},{"lightness":-37}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#406d80"}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#406d80"}]},{"elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#3e606f"},{"weight":2},{"gamma":0.84}]},{"elementType":"labels.text.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"administrative","elementType":"geometry","stylers":[{"weight":0.6},{"color":"#1a3541"}]},{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#2c5a71"}]}],
		"Clean Cut": [{"featureType":"road","elementType":"geometry","stylers":[{"lightness":100},{"visibility":"simplified"}]},{"featureType":"water","elementType":"geometry","stylers":[{"visibility":"on"},{"color":"#C6E2FF"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"color":"#C5E3BF"}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"color":"#D1D1B8"}]}],
		"Red Hues": [{"stylers":[{"hue":"#dd0d0d"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":100},{"visibility":"simplified"}]}],
		"Pastel Tones": [{"featureType":"landscape","stylers":[{"saturation":-100},{"lightness":60}]},{"featureType":"road.local","stylers":[{"saturation":-100},{"lightness":40},{"visibility":"on"}]},{"featureType":"transit","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"administrative.province","stylers":[{"visibility":"off"}]},{"featureType":"water","stylers":[{"visibility":"on"},{"lightness":30}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ef8c25"},{"lightness":40}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#b6c54c"},{"lightness":40},{"saturation":-40}]},{}],
		"A Dark World": [{"stylers":[{"visibility":"simplified"}]},{"stylers":[{"color":"#131314"}]},{"featureType":"water","stylers":[{"color":"#131313"},{"lightness":7}]},{"elementType":"labels.text.fill","stylers":[{"visibility":"on"},{"lightness":25}]}],
		"Taste": [{"featureType":"water","elementType":"geometry","stylers":[{"color":"#a0d6d1"},{"lightness":17}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":20}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#dedede"},{"lightness":17}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#dedede"},{"lightness":29},{"weight":0.2}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#dedede"},{"lightness":18}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":16}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#f1f1f1"},{"lightness":21}]},{"elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#ffffff"},{"lightness":16}]},{"elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#333333"},{"lightness":40}]},{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#f2f2f2"},{"lightness":19}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#fefefe"},{"lightness":20}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#fefefe"},{"lightness":17},{"weight":1.2}]}],
		"Red Hat Antwerp": [{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.business","elementType":"geometry.fill","stylers":[{"visibility":"on"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#b4d4e1"},{"visibility":"on"}]}],
		"Light Grey & Blue": [{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#dde6e8"},{"visibility":"on"}]}],
		"Muted": [{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"administrative.locality","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"},{"visibility":"simplified"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"visibility":"simplified"},{"saturation":"-65"},{"lightness":"45"},{"gamma":"1.78"}]},{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"road","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.highway","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"saturation":"-33"},{"lightness":"22"},{"gamma":"2.08"}]},{"featureType":"transit.station.airport","elementType":"geometry","stylers":[{"gamma":"2.08"},{"hue":"#ffa200"}]},{"featureType":"transit.station.airport","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"transit.station.rail","elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"transit.station.rail","elementType":"labels.icon","stylers":[{"visibility":"simplified"},{"saturation":"-55"},{"lightness":"-2"},{"gamma":"1.88"},{"hue":"#ffab00"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#bbd9e5"},{"visibility":"simplified"}]}],
		"TOR OP1": [{"featureType":"landscape","elementType":"geometry","stylers":[{"saturation":"-100"}]},{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"labels.text.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"labels.text","stylers":[{"color":"#545454"}]},{"featureType":"road","elementType":"labels.text.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"saturation":"-87"},{"lightness":"-40"},{"color":"#ffffff"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road.highway.controlled_access","elementType":"geometry.fill","stylers":[{"color":"#f0f0f0"},{"saturation":"-22"},{"lightness":"-16"}]},{"featureType":"road.highway.controlled_access","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road.highway.controlled_access","elementType":"labels.icon","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road.local","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"saturation":"-52"},{"hue":"#00e4ff"},{"lightness":"-16"}]}],
		"blueTacticle": [{"featureType":"all","elementType":"labels.text","stylers":[{"color":"#a1f7ff"}]},{"featureType":"all","elementType":"labels.text.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"color":"#000000"},{"lightness":13}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#144b53"},{"lightness":14},{"weight":1.4}]},{"featureType":"administrative","elementType":"labels.text","stylers":[{"visibility":"simplified"},{"color":"#a1f7ff"}]},{"featureType":"administrative.province","elementType":"labels.text","stylers":[{"visibility":"simplified"},{"color":"#a1f7ff"}]},{"featureType":"administrative.locality","elementType":"labels.text","stylers":[{"visibility":"simplified"},{"color":"#a1f7ff"}]},{"featureType":"administrative.neighborhood","elementType":"labels.text","stylers":[{"visibility":"simplified"},{"color":"#a1f7ff"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#08304b"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#0c4152"},{"lightness":5}]},{"featureType":"poi.attraction","elementType":"labels","stylers":[{"invert_lightness":true}]},{"featureType":"poi.attraction","elementType":"labels.text","stylers":[{"visibility":"simplified"},{"color":"#a1f7ff"}]},{"featureType":"poi.park","elementType":"labels","stylers":[{"visibility":"on"},{"invert_lightness":true}]},{"featureType":"poi.park","elementType":"labels.text","stylers":[{"visibility":"simplified"},{"color":"#a1f7ff"}]},{"featureType":"road","elementType":"labels.text","stylers":[{"color":"#a1f7ff"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#0b434f"},{"lightness":25}]},{"featureType":"road.highway","elementType":"labels","stylers":[{"lightness":"0"},{"saturation":"0"},{"invert_lightness":true},{"visibility":"simplified"},{"hue":"#00e9ff"}]},{"featureType":"road.highway","elementType":"labels.text","stylers":[{"visibility":"simplified"},{"color":"#a1f7ff"}]},{"featureType":"road.highway.controlled_access","elementType":"labels.text","stylers":[{"color":"#a1f7ff"}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"road.arterial","elementType":"geometry.stroke","stylers":[{"color":"#0b3d51"},{"lightness":16}]},{"featureType":"road.arterial","elementType":"labels","stylers":[{"invert_lightness":true}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#000000"}]},{"featureType":"road.local","elementType":"labels","stylers":[{"visibility":"simplified"},{"invert_lightness":true}]},{"featureType":"transit","elementType":"all","stylers":[{"color":"#146474"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#021019"}]}],
		"NightRider": [{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#1e242b"},{"lightness":"5"}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#1e242b"},{"saturation":"0"},{"lightness":"30"}]},{"featureType":"administrative","elementType":"labels","stylers":[{"color":"#1e242b"},{"lightness":"30"}]},{"featureType":"administrative","elementType":"labels.text.stroke","stylers":[{"visibility":"off"}]},{"featureType":"administrative.province","elementType":"geometry.stroke","stylers":[{"color":"#1e242b"},{"lightness":"20"},{"weight":"1.00"}]},{"featureType":"administrative.neighborhood","elementType":"labels.text.fill","stylers":[{"lightness":"-20"}]},{"featureType":"administrative.land_parcel","elementType":"labels.text.fill","stylers":[{"lightness":"-20"}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#1e242b"}]},{"featureType":"landscape","elementType":"labels","stylers":[{"color":"#1e242b"},{"lightness":"30"}]},{"featureType":"landscape","elementType":"labels.text.stroke","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#1e242b"},{"lightness":"5"}]},{"featureType":"poi","elementType":"labels","stylers":[{"color":"#1e242b"},{"lightness":"30"}]},{"featureType":"poi","elementType":"labels.text.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"geometry","stylers":[{"visibility":"simplified"},{"color":"#1e242b"},{"lightness":"15"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#1e242b"},{"lightness":"6"}]},{"featureType":"transit","elementType":"labels","stylers":[{"color":"#1e242b"},{"lightness":"30"}]},{"featureType":"transit","elementType":"labels.text.stroke","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#010306"}]},{"featureType":"water","elementType":"labels.text.stroke","stylers":[{"visibility":"off"}]}],
		"Light and clean": [{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"on"},{"lightness":33}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f7f7f7"}]},{"featureType":"poi.business","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#deecdb"}]},{"featureType":"poi.park","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":"25"}]},{"featureType":"road","elementType":"all","stylers":[{"lightness":"25"}]},{"featureType":"road","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#ffffff"}]},{"featureType":"road.highway","elementType":"labels","stylers":[{"saturation":"-90"},{"lightness":"25"}]},{"featureType":"road.arterial","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#ffffff"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#ffffff"}]},{"featureType":"transit.line","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"transit.station","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"visibility":"on"},{"color":"#e0f1f9"}]}],
		"SAAB Navigation": [{"featureType":"all","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"all","elementType":"geometry","stylers":[{"color":"#004600"}]},{"featureType":"all","elementType":"labels.text","stylers":[{"saturation":"62"}]},{"featureType":"all","elementType":"labels.text.fill","stylers":[{"gamma":"1.81"},{"lightness":"100"},{"saturation":"100"},{"color":"#00ff0b"}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"saturation":"-100"},{"lightness":-33},{"weight":"2.53"},{"gamma":0.8},{"color":"#061d00"}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"lightness":30},{"saturation":30}]},{"featureType":"landscape","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"lightness":"-32"},{"saturation":"48"},{"color":"#000000"}]},{"featureType":"landscape.man_made","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"landscape.natural","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"landscape.natural.landcover","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"landscape.natural.terrain","elementType":"geometry.fill","stylers":[{"color":"#000000"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"saturation":20}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"lightness":20},{"saturation":-20}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":10},{"saturation":-30}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"lightness":"24"},{"saturation":"100"},{"color":"#39ff00"}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"saturation":25},{"lightness":25}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"color":"#2bff00"}]},{"featureType":"road","elementType":"labels.text.stroke","stylers":[{"color":"#000000"},{"weight":"4.46"}]},{"featureType":"transit","elementType":"geometry.fill","stylers":[{"saturation":"100"},{"lightness":"12"},{"color":"#148400"}]},{"featureType":"water","elementType":"all","stylers":[{"lightness":-20}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"lightness":"-100"},{"color":"#001a03"}]},{"featureType":"water","elementType":"geometry.stroke","stylers":[{"weight":"1.70"}]},{"featureType":"water","elementType":"labels.text.fill","stylers":[{"color":"#2bff00"}]}],
		"Light Gray & Light Blue": [{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f0f0f0"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"},{"color":"#ffffff"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#afdaec"},{"visibility":"on"}]}]
		/* https://snazzymaps.com/ */
	},
	"default_img": {
		"width": 37,
		"height": 55,
		"top_offset": 54,
		"left_offset": 18,
		"binary": "iVBORw0KGgoAAAANSUhEUgAAACUAAAA3CAYAAACLgIOTAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNui8sowAAAcQSURBVGiBvZnvb1tXGcc/z/GP/HCThmVT7WQFGonB0qLBJNaQxuv6AiF1ztRXFVQIkHjDKyTQJrIWobIXwNqtL/gP6FQQGkJMdgYCJNrZbtqOgYr6g06wdLSNs/5S28SOE/uehxfOTe2ba8eOM76SJd9znvPcz33Ouc95ji2qSqvKJcZiIF8RYbfCToFBhU0C8wo3BM4qesoqfx5MZXKt+pdWoHKJsa8isl8gDnwaCDUwLwFXFdLAb2LJ9J82FCqXGNsnIi8DzzTr2EdnLfxsIJl+qy2omcRYvyC/EOFAGzA1UtUTFvu9wdTpuy1DzSTGnjIibwKf2SigKl1RZX8slf5n01C58V2jYFICn/gYgCpS7qKaiE5mptaEmn0+vh1DmjWAjAgioAq2yke99npgio7FUpnLdaFy47t6BPMu8NlGMACFUol8qcxCqUzJWlQVESEUMHQFg0RCQbpDlZdzDbhL4rBzy9vpebchWEOIOdYIKGCE/FKZW4UF8qUS1irLjAigQLEMD4pLGCNsCoV4tLuLSCiIUx9sWAP6GvDdFQ43UjPj8V0GMn6jBBARbhcWuFkoYBUCLk0DOaoYgS2Rbvq7ulBV6qIpo9FUegrAuG0GfaWevRHho3yB2fkCIE0BgQsu5OYLfJQvrEx9HaqfuN9EVcklxr4oIn/3dWyE24UiM3PzBIzB61ZhGvSCILdU9TFEdghs89jgWGWgJ8Kj3Z041j9e1uoXBiYz54MAgnzNz0hEWCiVuZkvrAZS/gP6Y1Hnrd1n/pV3m/8yOhwJYvYJvAIMVfxXHu5mvkAkFKIjGMAvFYmR/cB5ubZ3JBg0wbPA06uiJMK1uXnuFRe9U5a1qvv2TF287fvIwDuj2x9T5PfAqNvmqNLX2cHWnk31Fv7fTCA8YoImsNN9Im+UimWHucUlL9B1a3mhERDAs6cv3lL0BYUbbpsRYX5xiaLjIP7ra8hxFr9kFNkJ9Hl7DZAvlfzm//CeMxfq7lvVeu70xTvAwwUMlK1SWCo/fMNq9YigzxjxmTZXxXLZ23QH5bfNALlStW8CNQ+xsNpvlczTBhjw67IoJWvxRPny7qkL91uB2jN16R5wyb0WgbJ1sPUz1qBRpce3S/Ebll/d1JRqxtnGtj1GhAXfLhFWZ6XVa69J1YwzIshq364KBpj26zFAKGC80XryryOf39IKzckvD0eBYfdagZBPEq7StAF9D7+ZEugMBr2tvcbYb7UChci3oXaJdK3260oV3jOqmgHueHutKpFQiJDxRksOnRzd3lQ1empk+AlBDlbfMWQMkVCoXjlzCyVr6Ij8Q+GKH3JHwNAbDnsd9IJMnhoZ3t4I6OTojh2ImaQqSlaV3nCY8Opl4d71Smy2/7yoKrOJ+MsIP/WaiAhlx/LBvfuUrfXs8joPchTleL6Q/+/e89P21FPbDF2RT2H4JvASEKkGChrDUN9mggHju/ehOhFNZV4VVWUmsWubiHlfPEUfVPa/e4tLXH8wh4jvO7NIJdJ3gUeAzwHhmnsBqsrW3h42d4Tr7XsltfpEbDJzdaXImx2P/wr4up91wAh3CkVy85V007guqpU79bFNEfq7OhtVoCeiyfQ3oKrIU2sPgxb9rB2r9Hd1srW3h6AxOLZBPnb9AY61BI1ha29PYyCloFYPu5crULHJ7PvA0Xo3cVTZ3BFmqG8z/d1dBESwqjjLH+/3gAj93V0M9fU2mjJXR2OTmX+7FzWnmZm9o90SCFwW+GS90SKCAZasJb9UYqFcpmwtVsEIBM3yaSYcImwMFvwX9UqQ+NAplZ98/I9TKzvLqnNfLhE/IMKJRo8FlTLEiMDyGU9xDxiVO1mUZn47UTgQS6Z/XePb/4Qcz0pVxfgxKhtNpse8jf61lvIivkXCRkrVqn3Rr8cXKpZKT4GuOYVt6o2BVPaMX0edqhQEDgLz9frb1Bwqh+p11oXaksxcU7RuimhHCkeiqfT1lqHcwcCHG8x0Fcd5rZFBQ6iBZKaoqhMbSaQwEXv7tO/O4aqp3zxnx+NZNiJFKJloKh1fy6xhpB460++3DQSo8INm7JqCiqYy54A32iKC47Fk+t1mDJuLFAB6sFLYrUtzqD24tllFTUNFk5nrID9fD5Eqr0ZT2RtrW7YIBYDVY8DVFpmmkdLrrQxoCSo6mVnA0lqKUCZiyTMNU4BXLf0342p2PP4Olf9n1iJKR5OZZ1v139r0LcuqNlNFqAXfKmAtrQtqIJU5p+jxhkSqvxxIZs7936AAsHIIeFCn975FfrRe1+uGik2mb6hyxK9PVY8MptJNp4ANgwJwHPu6au2vNgofaFiPteO3LajH/5AtCvywuk1UJwZ+l20pBXi1rpTg1WwifhJhN+ipaDLzXLv+2orUilRfAm4Kuq4U4ONPN+ST2zv2nY3y9T/gjn2nPp+k9QAAAABJRU5ErkJggg=="	
	}
}

//core globals
var object_id;
var object_name;
var default_map_width = 1000; //default map width for use when changing from responsive to fixed
var core_params;
var google_map;
var map_overlay;
var map_markers_object;
var map_markers_object_load_timer;
var map_animation_time = 800;
var map_object;
var currently_editing_icon_id;
var currently_editing_marker_object;
var marker_help_copy = 'Drag and drop one of the markers below onto the map';
var map_search_marker;
var autocomplete_initialised = false;
var hmapsprem_google_fonts = [];
var default_fonts = ['inherit', 'Arial', 'Verdana', 'Times New Roman', 'Times', 'Trebuchet MS', 'sans-serif', 'serif'];

//get google fonts
function get_google_fonts(){
    jQuery.ajax({
        url: 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyCe3XGw8IKuzIXe7bL6ZQc1xbe3MX5DR-s',
        type: "GET",
        dataType: "json"
    }).done(function(data){
        //get google fonts
        fonts = data.items;
        jQuery.each(fonts, function(key,val){
            hmapsprem_google_fonts.push(val.family);
        });
        //add default fonts
        jQuery.extend(hmapsprem_google_fonts, default_fonts);
        //sort font array
        hmapsprem_google_fonts.sort();
    });
}

//get object
function load_default_object(json){
	//get object id
	object_id = json.object_id;
	//get object
	hplugin_get_object(object_id, 'load_view_content');
	//highlight active
	setTimeout(function(){
		jQuery('.hero_sub #sub_item_row_'+ json.object_id).addClass('active_sidebar_elem');
	},400);
	//unlock core view
	unlock_core_view_reload();
}

function load_view_content(object){
    //get google fonts
    get_google_fonts();
	//set title
	object_name = object.object_name;
	//replace � with single quote
	var replacement_object = (object.object).replace(/&#39;/g,"'").replace(/(?:\r\n|\r|\n)/g, '<br>');
	//define main object
	main_object = JSON.parse(replacement_object);
	//update object to add carriage returns after parsing
	jQuery.each(main_object.map_markers, function(key,val){
		val.info_window_content = val.info_window_content.replace(/<br>/g, '\n').replace(/&#x5c;/g,"\\").replace(/&quot;/g, "\"").replace(/&#8217;/g, "'");
	});
	//initialise object manager
	hplugin_bind_view_components(object_id, main_object);
	//load sub view
	manual_load_view('dropdown_default');
	//initialise maps view
	initialise_maps_view();
}

//initialise maps view
function initialise_maps_view(){
	//load Google Maps and initialise map
	load_gmap_script();
	//get markers
	get_map_markers();
	//bind marker edit controls
	bind_marker_edit_controls();
	//set marker image droppable (in edit panel)
	set_marker_image_droppable();
	//disable marker image droppable by default
	disable_marker_image_droppable();
	//bind to tab navigation
	hplugin_event_subscribe('view-nav','disable_marker_icon_change','');
}

//get markers
function get_map_markers(){
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_get_markers'
		},
		dataType: "json"
	}).done(function(markers){
		//set view global
		map_markers_object = markers;
	});
}

//load Google Maps async
function load_gmap_script(){
	//check if API already loaded
	if(typeof google === 'object' && typeof google.maps === 'object'){
		initialise_map();
	}else{
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyAMwdih8kyoKK7LGzE6rw4-egHpd367DE8&libraries=places,drawing&callback=initialise_map";
		document.body.appendChild(script);
	}
}

//initialise map
function initialise_map(){
	//initialise map
	defaultLatLon = eval("new google.maps.LatLng("+ main_object.map_settings.map_center +");");
	var map_options = {
		zoom: main_object.map_advanced.map_load_zoom,
		scrollwheel: false,
		center: defaultLatLon,
		disableDefaultUI: true,
		mapTypeId: eval("google.maps.MapTypeId."+ main_object.map_settings.map_type +"")
	};
	//load map
	google_map = new google.maps.Map(document.getElementById("hero_map_main"),map_options);
	//add map overlay for pixel point detection
	map_overlay = new google.maps.OverlayView();
	map_overlay.draw = function(){};
	map_overlay.setMap(google_map);
	var center;
	function calculateCenter(){
		center = google_map.getCenter();
	}
	google.maps.event.addDomListener(google_map, 'idle', function(){
		calculateCenter();
		//location search
		if(!autocomplete_initialised){
			autocomplete_initialised = true;
			var input = document.getElementById('location_search');
			var autocomplete = new google.maps.places.Autocomplete(input);
			autocomplete.bindTo('bounds', google_map);
			google.maps.event.addListener(autocomplete, 'place_changed', function(){
				var place = autocomplete.getPlace();
				//place marker
				if(typeof map_search_marker != 'undefined'){
					map_search_marker.setMap(null);
					map_search_marker = undefined;
				}
				if(typeof place.geometry != 'undefined'){
					var icon_object = {
						url: plugin_url + 'assets/images/search_marker.png',
						size: new google.maps.Size(41, 26),
						origin: new google.maps.Point(0, 0),
						anchor: new google.maps.Point(21, 12)
					};
					map_search_marker = new google.maps.Marker({
						position: place.geometry.location,
						draggable: false,
						raiseOnDrag: false,
						icon: icon_object,
						map: google_map,
						zIndex: 2
					});
					if (place.geometry.viewport) {
						google_map.fitBounds(place.geometry.viewport);
					} else {
						google_map.setCenter(place.geometry.location);
						google_map.setZoom(17);
					}
				}
			});
			//add coordinate search
			jQuery('#location_search').on('keyup', function(e){
				//check if enter key pressed (assuming submission of coordinates)
				if(e.keyCode == 13){
					//check if valid coordinates
					var location_val = jQuery(this).val();
					if(check_valid_lat_lon(location_val)){
						//construct latlng pointer
						var latlng = location_val.split(',');
						var latlng_pointer = eval("new google.maps.LatLng("+ latlng[0] +","+ latlng[1] +");");
						//valid coordinates
						var icon_object = {
							url: plugin_url + 'assets/images/search_marker.png',
							size: new google.maps.Size(22, 22),
							origin: new google.maps.Point(0, 0),
							anchor: new google.maps.Point(11, 11)
						};
						//place search marker
						map_search_marker = new google.maps.Marker({
							position: latlng_pointer,
							draggable: false,
							raiseOnDrag: false,
							icon: icon_object,
							map: google_map,
							zIndex: 2
						});
						//set center
						google_map.setCenter(latlng_pointer);
						google_map.setZoom(17);
					}
				}
			});
		}
	});
	google.maps.event.addDomListener(window, 'resize', function(){
		google_map.setCenter(center);
	});
	//manage map controls
	manage_map_controls();
	//manage map theme
	manage_map_theme();
	//place existing markers
	place_existing_map_markers();
	//place existing shapes
	place_existing_shapes();
	//bind marker edit panel switch
	bind_marker_edit_panel_switch();	
}

//check for valid coordinates
function check_valid_lat_lon(latlng){
	//check if contains comma
	if(latlng.indexOf(',') > -1){
		//split and check length
		latlng = latlng.split(',');
		if(latlng.length == 2){
			//test regex
			var ck_lat = /^(-?[1-8]?\d(?:\.\d{1,18})?|90(?:\.0{1,18})?)$/;
			var ck_lon = /^(-?(?:1[0-7]|[1-9])?\d(?:\.\d{1,18})?|180(?:\.0{1,18})?)$/;
			var validLat = ck_lat.test(latlng[0]);
			var validLon = ck_lon.test(latlng[1]);
			//respond
			if(validLat && validLon){
				//return
				return true;
			}
		}
	}
	return false;
}

//manage map controls
function manage_map_controls(){
	var map_options = {};
	//street view
	if(main_object.map_controls.street_view){
		map_options.streetViewControl = true;
		eval("map_options.streetViewControlOptions = {position: google.maps.ControlPosition."+ main_object.map_controls.street_view_position +"};");
	}else{
		map_options.streetViewControl = false;
		map_options.streetViewControlOptions = {};
	}
	//map type
	if(main_object.map_controls.map_type){
		map_options.mapTypeControl = true;
		eval("map_options.mapTypeControlOptions = {position: google.maps.ControlPosition."+ main_object.map_controls.map_type_position +", style: google.maps.MapTypeControlStyle."+ main_object.map_controls.map_type_style +"};");
	}else{
		map_options.mapTypeControl = false;
		map_options.mapTypeControlOptions = {};
	}
	//rotate
	if(main_object.map_controls.rotate){
		map_options.rotateControl = true;
		eval("map_options.RotateControlOptions = {position: google.maps.ControlPosition."+ main_object.map_controls.rotate_position +"};");
	}else{
		map_options.rotateControl = false;
		map_options.rotateControlOptions = {};
	}
	//zoom
	if(main_object.map_controls.zoom){
		map_options.zoomControl = true;
		eval("map_options.zoomControlOptions = {position: google.maps.ControlPosition."+ main_object.map_controls.zoom_position +"};");
	}else{
		map_options.zoomControl = false;
		map_options.zoomControlOptions = {};
	}
	//scale
	if(main_object.map_controls.scale){
		map_options.scaleControl = true;
	}else{
		map_options.scaleControl = false;
	}
    if(main_object.map_controls.show_location){
        //construct custom controlvar
        locateMeControlDiv = document.createElement('div');
        new custom_my_location_control(locateMeControlDiv, google_map);
        locateMeControlDiv.index = 1;
        google_map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(locateMeControlDiv);
    }else{
        google_map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].clear();
        custom_my_location_visible = false;
    }
	//update map options
	google_map.setOptions(map_options);
	//custom parameter
	if(main_object.map_developers.javascript_callback){
		jQuery('#custom_param_container').css({
			'display': 'block',
			'visibility': 'visible'
		});
	}
}

//custom my location control
var custom_my_location_visible = false;
function custom_my_location_control(container){
    //check if already visible
    if(!custom_my_location_visible){
        custom_my_location_visible = true;
        //construct control
        var controlUI = document.createElement('div');
            controlUI.style.width = '29px';
            controlUI.style.height = '29px';
            controlUI.style.backgroundColor = '#FFF';
            controlUI.style.marginRight = '9px';
            controlUI.style.boxShadow = '0 1px 2px rgba(0,0,0,.2)';
            controlUI.style.borderRadius = '3px';
            controlUI.style.cursor = 'pointer';
        //append control to container
        container.appendChild(controlUI);
        //cnstruct control inner
        var controlInternal = document.createElement('div');
        controlInternal.innerHTML = '<img src="' + plugin_url + 'assets/images/locate_me.png">';
        jQuery(controlInternal).on('mouseenter', function(){
            this.innerHTML = '<img src="' + plugin_url + 'assets/images/locate_me_over.png">';
        }).on('mouseleave', function(){
            this.innerHTML = '<img src="' + plugin_url + 'assets/images/locate_me.png">';
        });
        //append control inner
        controlUI.appendChild(controlInternal);
        //bind event listener
        controlUI.addEventListener('click', function(){
        });
    }
}

//manage map theme
function manage_map_theme(){
	if(main_object.map_settings.map_theme == ''){
		var theme = null;
	}else{
		var theme = eval("map_config.map_themes['"+ main_object.map_settings.map_theme +"']");
	}
    google_map.setOptions({
        styles: theme
    });
}

//place existing markers
function place_existing_map_markers(){
	//get marker data
	if(typeof map_markers_object === 'undefined'){
		clearTimeout(map_markers_object_load_timer);
		map_markers_object_load_timer = setTimeout("place_existing_map_markers();",100);
	}else{
		clearTimeout(map_markers_object_load_timer);
		var bounds = new google.maps.LatLngBounds();
		var cur_marker_count = 0;
        //check for null marker object
        if(main_object.map_markers == null){
            main_object.map_markers = {};
        }
		var marker_count = (Object.keys(main_object.map_markers).length);
		var autofit = eval("main_object.map_settings.auto_fit");
		jQuery.each(main_object.map_markers, function(key,val){
			//get marker data
			if(val.marker_id != null){
				var marker_data = get_marker_data_from_object(val.marker_id);
				var width = marker_data.width;
				var height = marker_data.height;
				var top_offset = marker_data.top_offset;
				var left_offset = marker_data.left_offset;
				var img_binary = marker_data.img_binary;
			}else{
				var width = map_config.default_img.width;
				var height = map_config.default_img.height;
				var top_offset = map_config.default_img.top_offset;
				var left_offset = map_config.default_img.left_offset;
				var img_binary = map_config.default_img.binary
			}
			//place marker on map
			var icon_object = {
				url: 'data:image/png;base64,'+ img_binary,
				size: new google.maps.Size(width, height),
				origin: new google.maps.Point(0,0),
				anchor: new google.maps.Point(left_offset, top_offset)
			};
			var latlng_object = val.latlng.split(',');
			var latlng = new google.maps.LatLng(latlng_object[0] , latlng_object[1]);
			var map_marker = new google.maps.Marker({
				position: latlng,
				draggable: true,
				raiseOnDrag: true,
				icon: icon_object,
				map: google_map,
				zIndex: 2
			});
			//extend bounds
			bounds.extend(latlng);
			//bind marker listeners
			bind_marker_listeners(map_marker,key);
			//update gmp
			main_object.map_markers[key].gmp = map_marker;
			//fit map bounds
			cur_marker_count++;
			if(autofit && cur_marker_count == marker_count){
				if(cur_marker_count > 1){
					google_map.fitBounds(bounds);
				}else{
					//default to single marker center
					jQuery.each(main_object.map_markers, function(key,val){
						var latlng = val.latlng.split(',');
						var new_center = new google.maps.LatLng(latlng[0] , latlng[1]);				
						google_map.setCenter(new_center);
						google_map.setZoom(15);
						return false;
					});
				}
			}
			
		});
	}
}

//bind marker listeners
function bind_marker_listeners(map_marker,icon_id){
	//marker click
	google.maps.event.addListener(map_marker, "click", function(){
		//populate edit panel
		populate_edit_panel(main_object.map_markers[icon_id], icon_id);
		//show edit panel
		show_marker_edit_panel();
		//hide marker mass update panel
		if(typeof hide_marker_mass_update_panel !== 'undefined'){
			hide_marker_mass_update_panel();
		}
	});
	//marker drag start
	google.maps.event.addListener(map_marker, "dragstart", function(){
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	//marker drag end
	google.maps.event.addListener(map_marker, "dragend", function(){
		//get marker position
		var latlng = map_marker.getPosition();
		var latlng_string = latlng.lat() +','+ latlng.lng();
		//update marker in map_object
		main_object.map_markers[icon_id].latlng = latlng_string;
		//trigger coords changed
		jQuery('#location_marker_coords_change_listener').trigger('change');
		//update markers table
		if(typeof populate_markers_table !== 'undefined'){
			populate_markers_table(jQuery('#marker_table_category_selector').val());
		}
	});
}

//get marker data from map_markers_object
function get_marker_data_from_object(marker_id){
	//loop object
	var marker_data;
	jQuery.each(map_markers_object.categories, function(key,val){
		jQuery.each(val.links, function(key,val){
			jQuery.each(val.markers, function(key,val){
				if(val.marker_id == marker_id){
					marker_data = val;
					return false;
				}
			});
		});
	});
	return marker_data;
}

//show marker edit panel
function show_marker_edit_panel(){
	//hide injected content
	jQuery('.internal_shape_edit_content').fadeOut(0, function(){
		jQuery(this).empty();
	});
	//show edit content
	jQuery('.internal_edit_content').fadeIn(0);
	//show panel
	jQuery('.hero_maps_pro .marker_edit_panel').stop().animate({
		'right': 0
	}, 300);
}

//show shape edit panel
function show_shape_edit_panel(html_content, callback, shape_pointer){
	//hide content
	jQuery('.internal_shape_edit_content').fadeIn(0);
	//show edit content
	jQuery('.internal_edit_content').fadeOut(0);
	//inject html
	jQuery('.internal_shape_edit_content').empty().html(html_content);
	//switch components
	switch_components();
	//show panel
	jQuery('.hero_maps_pro .marker_edit_panel').stop().animate({
		'right': 0
	}, 300);
	//execute callback (used for object binding)
	if(typeof callback !== 'undefined' && callback !== 'undefined' && typeof shape_pointer !== 'undefined' && shape_pointer !== 'undefined'){
		eval(""+ callback +"('"+ shape_pointer +"');");
	}
}

//hide marker edit panel
function hide_marker_edit_panel(){
	jQuery('.hero_maps_pro .marker_edit_panel').stop().animate({
		'right': '-'+ 300 +'px'
	}, 300);
}

//populate edit panel
function populate_edit_panel(marker_data, icon_id){
	//disable marker icon change
	disable_marker_icon_change();
	//update currently editing pointer
	currently_editing_icon_id = icon_id;
	currently_editing_marker_object = marker_data;
	if(marker_data.marker_id != null){
		var img_binary = get_marker_data_from_object(marker_data.marker_id).img_binary;
	}else{
		var img_binary = map_config.default_img.binary
	}
	//marker image
	jQuery('.hero_maps_pro .marker_edit_panel .edit_top .marker_image_container .marker_img').css({
		'background-image': 'url(data:image/png;base64,'+ img_binary +')'
	});
	//location title
	jQuery('#location_title').val(marker_data.title);
	//location coordinates
	jQuery('#location_coordinates').val(marker_data.latlng);
	//info window show
	if(marker_data.info_window_show){
		jQuery('#info_window_show').removeAttr('checked').trigger('click');
		//info window content
		jQuery('#info_window_content').val(marker_data.info_window_content);
	}else{
		jQuery('#info_window_show').attr('checked','checked').trigger('click');
		jQuery('#info_window_content').val('');
	}
	//link show
	if(marker_data.link_show){
		jQuery('#link_show').removeAttr('checked').trigger('click');
		//link title
		jQuery('#link_title').val(marker_data.link_title);
		//link
		jQuery('#link').val(marker_data.link);
	}else{
		jQuery('#link_show').attr('checked','checked').trigger('click');
		jQuery('#link_title').val('');
		jQuery('#link').val('');
	}
	//link colour
	jQuery('#link_colour').val(marker_data.link_colour).trigger('change');
	//link target	
	jQuery('#link_target option').each(function(key,val){
		if(jQuery(this).val() == marker_data.link_target){
			jQuery(this).attr('selected',true);
		}else{
			jQuery(this).removeAttr('selected');
		}
	});
	update_select_component(jQuery('#link_target'));
	//sort categories
	main_object.map_marker_categories.sort();
	//populate marker category
	jQuery('#marker_category').empty();
	jQuery.each(main_object.map_marker_categories, function(key, val){
		jQuery('#marker_category').append('<option value="'+ val +'">'+ val +'</option>');
	});
	//marker category
	jQuery('#marker_category option').each(function(key,val){
		if(jQuery(this).val() == marker_data.marker_category){
			jQuery(this).attr('selected',true);
		}else{
			jQuery(this).removeAttr('selected');
		}
	});
	update_select_component(jQuery('#marker_category'));
	//custom param
	jQuery('#custom_param').val(marker_data.custom_param);
	//custom param display
	if(main_object.map_developers.javascript_callback){
		jQuery('#custom_param_container').css('display', 'block');
	}else{
		jQuery('#custom_param_container').css('display', 'none');
	}
	//bind location marker edit listener
	bind_location_marker_edit_listeners(marker_data, icon_id);
}

//bind location marker edit listener
function bind_location_marker_edit_listeners(marker_data, icon_id){
	//marker coords
	jQuery('#location_marker_coords_change_listener').off().on('change', function(){
		jQuery('#location_coordinates').val(marker_data.latlng);
	});
	//delete button
	jQuery('#del_location_marker_btn').off().on('click', function(){
		if(window.confirm('Are you sure you want to delete this marker?')){
			//mark marker deleted
			remove_location_marker_from_object(marker_data, icon_id);
			//hide panel
			hide_marker_edit_panel();
			//hide arrows
			hide_arrows();
			//hide tooltip
			hide_hplugin_tooltip();
			//disable marker icon change
			disable_marker_icon_change();
			//flag save
			flag_save_required('hplugin_persist_object_data');
		}
	});
	//close button
	jQuery('#done_location_marker_btn').off().on('click', function(){
		//disable marker icon change
		disable_marker_icon_change();
		//hide panel
		hide_marker_edit_panel();
	});
	//location title
	jQuery('#location_title').off('keyup.markeredit change.markeredit paste.markeredit').on('keyup.markeredit change.markeredit paste.markeredit', function(){
		//update object
		currently_editing_marker_object.title = jQuery(this).val();
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	//info window show
	jQuery('#info_window_show').off('change.markeredit').on('change.markeredit', function(){
		//flag save
		if(currently_editing_marker_object.info_window_show != Boolean(jQuery(this).is(':checked'))){
			flag_save_required('hplugin_persist_object_data');
		}
		//update object
		currently_editing_marker_object.info_window_show = Boolean(jQuery(this).is(':checked'));
	});
	//info window content
	jQuery('#info_window_content').off('keyup.markeredit change.markeredit paste.markeredit').on('keyup.markeredit change.markeredit paste.markeredit', function(){
		//update object
		currently_editing_marker_object.info_window_content = jQuery(this).val();
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	//link show
	jQuery('#link_show').off('change.markeredit').on('change.markeredit', function(){
		if(currently_editing_marker_object.link_show != Boolean(jQuery(this).is(':checked'))){
			//flag save
			flag_save_required('hplugin_persist_object_data');
		}
		//update object
		currently_editing_marker_object.link_show = Boolean(jQuery(this).is(':checked'));
	});
	//link title
	jQuery('#link_title').off('keyup.markeredit change.markeredit paste.markeredit').on('keyup.markeredit change.markeredit paste.markeredit', function(){
		//update object
		currently_editing_marker_object.link_title = jQuery(this).val();
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	//link
	jQuery('#link').off('keyup.markeredit change.markeredit paste.markeredit').on('keyup.markeredit change.markeredit paste.markeredit', function(){
		//update object
		currently_editing_marker_object.link = jQuery(this).val();
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	//link colour
	jQuery('#link_colour').off('keyup.markeredit change.markeredit paste.markeredit').on('keyup.markeredit change.markeredit paste.markeredit', function(){
		if(currently_editing_marker_object.link_colour != jQuery(this).val()){
			//flag save
			flag_save_required('hplugin_persist_object_data');
		}
		//update object
		currently_editing_marker_object.link_colour = jQuery(this).val();
	});
	//link target
	jQuery('#link_target').off('change.markeredit').on('change.markeredit', function(){
		var link_target;
		jQuery('#link_target option').each(function(key,val){
			if(jQuery(this).is(':selected')){
				link_target = jQuery(this).val();
				return false;
			}
		});
		//update object
		currently_editing_marker_object.link_target = link_target;
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	//marker category
	jQuery('#marker_category').off('change.markeredit').on('change.markeredit', function(){
		var marker_category;
		jQuery('#marker_category option').each(function(key,val){
			if(jQuery(this).is(':selected')){
				marker_category = jQuery(this).val();
				return false;
			}
		});
		//update object
		currently_editing_marker_object.marker_category = marker_category;
		//update markers table
		if(typeof populate_markers_table !== 'undefined'){
			populate_markers_table(jQuery('#marker_table_category_selector').val());
		}
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	//custom parameter
	jQuery('#custom_param').off('keyup.markeredit change.markeredit paste.markeredit').on('keyup.markeredit change.markeredit paste.markeredit', function(){
		//update object
		currently_editing_marker_object.custom_param = jQuery(this).val();
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
}

//mark marker deleted
function remove_location_marker_from_object(marker_data, icon_id){
	//remove marker from map
	marker_data.gmp.setMap(null);
	//remove marker from object
	eval("delete main_object.map_markers."+ icon_id +"");
	//update markers table
	if(typeof populate_markers_table !== 'undefined'){
		populate_markers_table(jQuery('#marker_table_category_selector').val());
	}
}

//bind marker edit panel switch(s)
function bind_marker_edit_panel_switch(){
	//info window
	jQuery('#info_window_show').on('click', function(){
		if(jQuery(this).is(':checked')){
			//show
			jQuery('#info_window_content').closest('.hidden_content').stop().animate({
				'height': jQuery('#info_window_content').closest('.hidden_content_inner').height() +'px'
			}, 300, function(){
				jQuery('#info_window_content').closest('.hidden_content').css({
					'overflow': 'visible'
				});
				jQuery('#info_window_content').closest('.hidden_content').stop().animate({
					'height': jQuery('#info_window_content').closest('.hidden_content_inner').height() +'px'
				}, 300);
			});
		}else{
			//hide
			jQuery('#info_window_content').closest('.hidden_content').stop().animate({
				'height': 0 +'px'
			}, 300, function(){
				jQuery('#info_window_content').closest('.hidden_content').css({
					'overflow': 'hidden'
				});
			});
		}
	});
	//link
	jQuery('#link_show').on('click', function(){
		if(jQuery(this).is(':checked')){
			//show
			jQuery('#link_title').closest('.hidden_content').stop().animate({
				'height': jQuery('#link_title').closest('.hidden_content_inner').height() +'px'
			}, 300, function(){
				jQuery('#link_title').closest('.hidden_content').css({
					'overflow': 'visible'
				});
				jQuery('#link_title').closest('.hidden_content').stop().animate({
					'height': jQuery('#link_title').closest('.hidden_content_inner').height() +'px'
				}, 300);
			});
		}else{
			//hide
			jQuery('#link_title').closest('.hidden_content').stop().animate({
				'height': 0 +'px'
			}, 300, function(){
				jQuery('#link_title').closest('.hidden_content').css({
					'overflow': 'hidden'
				});
			});
		}
	});
}

//bind marker edit controls
function bind_marker_edit_controls(){
	jQuery('#marker_edit_img_btn').off().on('click', function(){
		//navigate to markers view
		load_view_submenu(1, 1);
		//enable marker icon change
		enable_marker_icon_change();
	});
}
//bind marker edit cancel
function bind_marker_edit_cancel(){
	jQuery('#marker_edit_img_btn').off().on('click', function(){
		//disable marker icon change
		disable_marker_icon_change();
	});
}

//show arrows
var arrow_animation_played = false;
function show_arrows(){
	jQuery('.hero_maps_pro .arrow_container').fadeIn(0);
	if(!arrow_animation_played){
		arrow_animation_played = true;
		jQuery('.hero_maps_pro .arrow_down').effect('bounce', { direction:'down', times: 4 }, 1000, function(){
			jQuery('.hero_maps_pro .arrow_right').effect('bounce', { direction:'right', times: 4 } ,1000);
		});
	}else{
		jQuery('.hero_maps_pro .arrow_down').stop().fadeIn(300);
		jQuery('.hero_maps_pro .arrow_right').stop().fadeIn(300);
	}

}
//hide arrows
function hide_arrows(){
	jQuery('.hero_maps_pro .arrow_container').fadeOut(300);
	jQuery('.hero_maps_pro .arrow_down').stop().fadeOut(300);
	jQuery('.hero_maps_pro .arrow_right').stop().fadeOut(300);
}

//enable marker icon change
function enable_marker_icon_change(){
	//show arrows
	show_arrows();
	//enable marker image droppable
	enable_marker_image_droppable();
	//set help copy
	var help_copy = 'Select your new icon below and drag and drop it into the marker edit panel to change the marker image';
	marker_help_copy = help_copy;
	jQuery('.drag_copy p').html(help_copy);
	//edit copy
	jQuery('#marker_edit_img_btn').html('Cancel');
	//change binding
	bind_marker_edit_cancel();
}

//disable marker icon change
function disable_marker_icon_change(){
	//hide arrows
	hide_arrows();
	//disable marker image droppable
	disable_marker_image_droppable();
	//reset help copy
	var help_copy = 'Drag and drop one of the markers below onto the map';
	marker_help_copy = help_copy;
	jQuery('.drag_copy p').html(help_copy);
	//edit copy
	jQuery('#marker_edit_img_btn').html('Change Marker Image');
	//change binding
	bind_marker_edit_controls();
}

//disable marker image droppable
function disable_marker_image_droppable(){
	if(jQuery('.marker_edit_panel .edit_top').data('droppable')){
		jQuery('.marker_edit_panel .edit_top').droppable('disable');
	}
}
//enable marker image droppable
function enable_marker_image_droppable(){
	jQuery('.marker_edit_panel .edit_top').droppable('enable');
}

//set marker image droppable (in edit panel)
function set_marker_image_droppable(){
	jQuery('.marker_edit_panel .edit_top').droppable({
		tolerance: 'fit',
		over: function(event, ui){
			//set indication border
			jQuery('.marker_image_container').css({
				'border': '1px dashed #C6302A'
			});
			//disable map droppable
			disable_map_droppable();
		},
		out: function(event, ui){
			//reset indication border
			jQuery('.marker_image_container').css({
				'border': '1px solid #FFF'
			});
			//enable map droppable
			enable_map_droppable();
		},
		drop: function(event, ui){
			var marker = jQuery('#'+ ui.draggable.prop('id'));
			var width_resize_ratio = (30 / marker.data('width'));
			var new_height = parseInt(marker.data('height') * width_resize_ratio);
			marker.stop().animate({
				'width': 30 +'px',
				'height': new_height +'px'
			},200);
			//reset indication border
			jQuery('.marker_image_container').css({
				'border': '1px solid #FFF'
			});
			//get marker data id
			var marker_data_id = marker.data('id');
			//get marker data
			var new_marker_data = get_marker_data_from_object(marker_data_id);
			//update marker image
			update_marker_image(new_marker_data, marker.attr('src'));
			//disable marker icon change
			disable_marker_icon_change();
			//enable map droppable
			enable_map_droppable();
			//update markers table
			setTimeout(function(){
				if(typeof populate_markers_table !== 'undefined'){
					populate_markers_table(jQuery('#marker_table_category_selector').val());
				}
			}, 100);
		}
    });
}

//generate random string
function grs(){
    function _p8(s){
        var p = (Math.random().toString(16)+"000000000").substr(2,8);
        return s ? p.substr(0,4) + p.substr(4,4) : p ;
    }
    return 'grs'+ _p8() + _p8(true) + _p8(true) + _p8();
}

//place existing shapes
function place_existing_shapes(){
	jQuery.each(main_object.map_poly, function(key,val){
		switch(val.type){
			case 'polyline':
				//get polyline data
				var draw_path = [];
				jQuery.each(val.path, function(k,v){
					var latlng = v.split(',');
					draw_path.push(new google.maps.LatLng(latlng[0], latlng[1]));
				});
				var polylineOptions = {
					path: draw_path,
					strokeColor: val.strokeColor,
					strokeOpacity: val.strokeOpacity,
					strokeWeight: val.strokeWeight,
					geodesic: val.geodesic,
					map: google_map,
					draggable: true,
					clickable: true,
					editable: true,
					zIndex: 1,
					suppressUndo: true
				};
				//place circle on map
				polyline = new google.maps.Polyline(polylineOptions);
				//add gmd
				var object_ref = eval("main_object.map_poly."+ key +";");
				object_ref.gmd = polyline;
				//bind polyline listeners
				bind_polyline_listeners(key, polyline);
			break;
			case 'circle':
				//get circle data
				var latlng = val.latlng.split(',');
				var circleOptions = {
					strokeColor: val.strokeColor,
					strokeOpacity: val.strokeOpacity,
					strokeWeight: val.strokeWeight,
					fillColor: val.fillColor,
					fillOpacity: val.fillOpacity,
					map: google_map,
					center: new google.maps.LatLng(latlng[0], latlng[1]),
					radius: val.radius,
					draggable: true,
					clickable: true,
					editable: true,
					zIndex: 1,
					suppressUndo: true
				};
				//place circle on map
				circle = new google.maps.Circle(circleOptions);
				//add gmd
				var object_ref = eval("main_object.map_poly."+ key +";");
				object_ref.gmd = circle;
				//bind circle listeners
				bind_circle_listeners(key, circle);
			break;
			case 'polygon':
				//get polygon data
				var draw_path = [];
				jQuery.each(val.path, function(k,v){
					var latlng = v.split(',');
					draw_path.push(new google.maps.LatLng(latlng[0], latlng[1]));
				});
				var polygonOptions = {
					path: draw_path,
					strokeColor: val.strokeColor,
					strokeOpacity: val.strokeOpacity,
					strokeWeight: val.strokeWeight,
					fillColor: val.fillColor,
					fillOpacity: val.fillOpacity,
					map: google_map,
					draggable: true,
					clickable: true,
					editable: true,
					zIndex: 1,
					suppressUndo: true
				};
				//place polygon on map
				polygon = new google.maps.Polygon(polygonOptions);
				//add gmd
				var object_ref = eval("main_object.map_poly."+ key +";");
				object_ref.gmd = polygon;
				//bind polygon listeners
				bind_polygon_listeners(key, polygon);
			break;
			case 'rectangle':
				//get rectangle data
				var ne_latlng = val.NE.split(',');
				var sw_latlng = val.SW.split(',');
				var NE = new google.maps.LatLng(ne_latlng[0], ne_latlng[1]);
				var SW = new google.maps.LatLng(sw_latlng[0], sw_latlng[1])
				var draw_bounds = new google.maps.LatLngBounds(SW, NE);
				var rectangleOptions = {
					bounds: draw_bounds,
					strokeColor: val.strokeColor,
					strokeOpacity: val.strokeOpacity,
					strokeWeight: val.strokeWeight,
					fillColor: val.fillColor,
					fillOpacity: val.fillOpacity,
					map: google_map,
					draggable: true,
					clickable: true,
					editable: true,
					zIndex: 1,
					suppressUndo: true
				};
				//place rectangle on map
				rectangle = new google.maps.Rectangle(rectangleOptions);
				//add gmd
				var object_ref = eval("main_object.map_poly."+ key +";");
				object_ref.gmd = rectangle;
				//bind rectangle listeners
				bind_rectangle_listeners(key, rectangle);
			break;
		}				
	});
}


//POLYLINE
//bind polyline listeners
function bind_polyline_listeners(key, polyline){
	//click
	google.maps.event.addDomListener(polyline, 'click', function(e){
		//add edit functionality
		var inject_html  = '<div class="edit_top">';
				inject_html += '<div class="hero_col_12" style="width:100%; padding-right:0; border-bottom:1px solid #666; padding-bottom: 10px;">';
					inject_html += '<div class="hero_col_6">';
						inject_html += '<div style="font-size:14px; margin:9px 0 0 0; color:#666;">Edit polyline</div>';
					inject_html += '</div>';
					inject_html += '<div class="hero_col_6" style="width:50%; padding-right:0;">';
						inject_html += '<div style="float:right; padding:8px 11px 5px 10px; margin:0;" id="del_shape_btn" class="hero_button_auto red_button rounded_3"><img src="'+ plugin_url +'assets/images/admin/delete_btn_img.png"></div>';
						inject_html += '<div style="float:right; padding:8px 10px 6px 10px;" id="done_shape_btn" class="hero_button_auto green_button rounded_3">Close</div>';
					inject_html += '</div>';
				inject_html += '</div>';
				inject_html += '<div style="clear:both;"></div>';
			inject_html += '</div>';
			inject_html += '<div class="marker_edit_panel_inner marker_edit_input">';
				inject_html += '<div class="shape_edit_panel_inner">';
					inject_html += '<div style="padding-bottom:3px;">';
						inject_html += '<div class="label"><div>Stroke Color:</div></div>';
						inject_html += '<div class="holder">';
						var stroke_colour = eval("main_object.map_poly."+ key +".strokeColor");
							inject_html += '<input id="polyline_stroke_colour_edit" data-size="lrg" type="text" id="" value="'+ stroke_colour +'" class="color_picker" style="margin-bottom:3px;">';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:5px;">';
						inject_html += '<div class="label"><div>Stroke Opacity:</div></div>';
						inject_html += '<div class="holder">';
							inject_html += '<div class="hero_col_4">';
								var stroke_opacity = eval("main_object.map_poly."+ key +".strokeOpacity");
								inject_html += '<input type="text" data-size="lrg" data-hero_type="dec" id="polyline_stroke_opacity_edit" name="polyline_stroke_opacity_edit" value="'+ stroke_opacity +'">';
							inject_html += '</div>';
							inject_html += '<div class="hero_col_1">&nbsp;</div>';
							inject_html += '<div class="hero_col_7">';
								inject_html += '<div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="polyline_stroke_opacity_edit" id="polyline_stroke_opacity_slider_edit"></div>';
							inject_html += '</div>';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:3px;">';
						inject_html += '<div class="label"><div>Stroke Weight:</div></div>';
						inject_html += '<div class="holder">';
							inject_html += '<div class="hero_col_4">';
								var stroke_weight = eval("main_object.map_poly."+ key +".strokeWeight");
								inject_html += '<input type="text" data-size="lrg" data-hero_type="dec" id="polyline_stroke_weight_edit" name="polyline_stroke_weight_edit" value="'+ stroke_weight +'">';
							inject_html += '</div>';
							inject_html += '<div class="hero_col_1">&nbsp;</div>';
							inject_html += '<div class="hero_col_7">';
								inject_html += '<div class="hero_slider" data-min="0" data-max="20" data-step="1" data-bind_link="polyline_stroke_weight_edit" id="polyline_stroke_weight_slider_edit"></div>';
							inject_html += '</div>';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:3px;">';
						inject_html += '<div class="label"><div>Geodesic:</div></div>';
						inject_html += '<div class="holder">';
							var geodesic = eval("main_object.map_poly."+ key +".geodesic");
							inject_html += '<div style="padding-top:3px;">';
							if(geodesic){
								inject_html += '<input type="checkbox" data-size="sml" id="polyline_geodesic_edit" name="polyline_geodesic_edit" value="true" checked>';
							}else{
								inject_html += '<input type="checkbox" data-size="sml" id="polyline_geodesic_edit" name="polyline_geodesic_edit" value="true">';
							}
							inject_html += '</div>';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="clear:both;"></div>';
				inject_html += '</div>';
			inject_html += '</div>';
		show_shape_edit_panel(inject_html, 'bind_polyline_customisation', key);
	});
	//bind path change listeners
	var path = polyline.getPath();
	google.maps.event.addListener(path, 'insert_at', function(){
		//update polyline
		update_polyline(key, polyline);
	}); 
	google.maps.event.addListener(path, 'remove_at', function(){
		//update polyline
		update_polyline(key, polyline);
	}); 
	google.maps.event.addListener(path, 'set_at', function(){
		//update polyline
		update_polyline(key, polyline);
	});
}
//update polyline
function update_polyline(key, polyline){
	//get new paths
	var paths = polyline.getPath().getArray();
	//clear existing
	eval("main_object.map_poly."+ key +".path = [];");
	//update paths
	jQuery.each(paths, function(k, val){
		var latlng = val.lat() +','+ val.lng();
		eval("main_object.map_poly['"+ key +"'].path.push('"+ latlng +"');");
	});
	//flag save
	flag_save_required('hplugin_persist_object_data');
}
//bind polyline customisation
function bind_polyline_customisation(key){
	//bind buttons
	jQuery('#done_shape_btn').off().on('click', function(){
		//hide marker edit panel
		hide_marker_edit_panel();
	});
	jQuery('#del_shape_btn').off().on('click', function(){
		//delete shape request
		delete_shape_request('polyline', key);
	});
	//bind components
	jQuery('#polyline_stroke_colour_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".strokeColor = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"strokeColor": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#polyline_stroke_opacity_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".strokeOpacity = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"strokeOpacity": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#polyline_stroke_weight_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".strokeWeight = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"strokeWeight": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#polyline_geodesic_edit').on('change', function(){
		//get geodesic value
		if(jQuery(this).is(':checked')){
			var geodesic = true;
		}else{
			var geodesic = false;
		}
		//update object
		eval("main_object.map_poly."+ key +".geodesic = '"+ geodesic +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"geodesic": geodesic});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
}


//CIRCLE
//bind circle listeners
function bind_circle_listeners(key, circle){
	//click
	google.maps.event.addDomListener(circle, 'click', function(e){
		//add edit functionality
		var inject_html  = '<div class="edit_top">';
				inject_html += '<div class="hero_col_12" style="width:100%; padding-right:0; border-bottom:1px solid #666; padding-bottom: 10px;">';
					inject_html += '<div class="hero_col_6">';
						inject_html += '<div style="font-size:14px; margin:9px 0 0 0; color:#666;">Edit circle</div>';
					inject_html += '</div>';
					inject_html += '<div class="hero_col_6" style="width:50%; padding-right:0;">';
						inject_html += '<div style="float:right; padding:8px 11px 5px 10px; margin:0;" id="del_shape_btn" class="hero_button_auto red_button rounded_3"><img src="'+ plugin_url +'assets/images/admin/delete_btn_img.png"></div>';
						inject_html += '<div style="float:right; padding:8px 10px 6px 10px;" id="done_shape_btn" class="hero_button_auto green_button rounded_3">Close</div>';
					inject_html += '</div>';
				inject_html += '</div>';
				inject_html += '<div style="clear:both;"></div>';
			inject_html += '</div>';
			inject_html += '<div class="marker_edit_panel_inner marker_edit_input">';
				inject_html += '<div class="shape_edit_panel_inner">';
					inject_html += '<div style="padding-bottom:3px;">';
						inject_html += '<div class="label"><div>Fill Color:</div></div>';
						inject_html += '<div class="holder">';
						var fill_colour = eval("main_object.map_poly."+ key +".fillColor");
							inject_html += '<input id="circle_fill_colour_edit" data-size="lrg" type="text" id="" value="'+ fill_colour +'" class="color_picker" style="margin-bottom:3px;">';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:5px;">';
						inject_html += '<div class="label"><div>Fill Opacity:</div></div>';
						inject_html += '<div class="holder">';
							inject_html += '<div class="hero_col_4">';
								var fill_opacity = eval("main_object.map_poly."+ key +".fillOpacity");
								inject_html += '<input type="text" data-size="lrg" data-hero_type="dec" id="circle_fill_opacity_edit" name="circle_fill_opacity_edit" value="'+ fill_opacity +'">';
							inject_html += '</div>';
							inject_html += '<div class="hero_col_1">&nbsp;</div>';
							inject_html += '<div class="hero_col_7">';
								inject_html += '<div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="circle_fill_opacity_edit" id="circle_fill_opacity_slider_edit"></div>';
							inject_html += '</div>';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:3px;">';
						inject_html += '<div class="label"><div>Stroke Color:</div></div>';
						inject_html += '<div class="holder">';
						var stroke_colour = eval("main_object.map_poly."+ key +".strokeColor");
							inject_html += '<input id="circle_stroke_colour_edit" data-size="lrg" type="text" id="" value="'+ stroke_colour +'" class="color_picker" style="margin-bottom:3px;">';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:5px;">';
						inject_html += '<div class="label"><div>Stroke Opacity:</div></div>';
						inject_html += '<div class="holder">';
							inject_html += '<div class="hero_col_4">';
								var stroke_opacity = eval("main_object.map_poly."+ key +".strokeOpacity");
								inject_html += '<input type="text" data-size="lrg" data-hero_type="dec" id="circle_stroke_opacity_edit" name="circle_stroke_opacity_edit" value="'+ stroke_opacity +'">';
							inject_html += '</div>';
							inject_html += '<div class="hero_col_1">&nbsp;</div>';
							inject_html += '<div class="hero_col_7">';
								inject_html += '<div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="circle_stroke_opacity_edit" id="circle_stroke_opacity_slider_edit"></div>';
							inject_html += '</div>';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:3px;">';
						inject_html += '<div class="label"><div>Stroke Weight:</div></div>';
						inject_html += '<div class="holder">';
							inject_html += '<div class="hero_col_4">';
								var stroke_weight = eval("main_object.map_poly."+ key +".strokeWeight");
								inject_html += '<input type="text" data-size="lrg" data-hero_type="dec" id="circle_stroke_weight_edit" name="circle_stroke_weight_edit" value="'+ stroke_weight +'">';
							inject_html += '</div>';
							inject_html += '<div class="hero_col_1">&nbsp;</div>';
							inject_html += '<div class="hero_col_7">';
								inject_html += '<div class="hero_slider" data-min="0" data-max="20" data-step="1" data-bind_link="circle_stroke_weight_edit" id="circle_stroke_weight_slider_edit"></div>';
							inject_html += '</div>';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="clear:both;"></div>';
				inject_html += '</div>';
			inject_html += '</div>';
			inject_html += '';
		show_shape_edit_panel(inject_html, 'bind_circle_customisation', key);
	});
	//radius changed
	google.maps.event.addDomListener(circle, 'radius_changed', function(e){
		//update circle
		update_circle(key, circle);
	});
	//center changed
	google.maps.event.addDomListener(circle, 'center_changed', function(e){
		//update circle
		update_circle(key, circle);
	});
	//dragend
	google.maps.event.addDomListener(circle, 'dragend', function(e){
		//update circle
		update_circle(key, circle);
	});
}
//update circle
function update_circle(key, circle){
	//update circle data
	eval("main_object.map_poly['"+ key +"'].latlng = '"+ circle.getCenter().lat() +','+ circle.getCenter().lng() +"';");
	eval("main_object.map_poly['"+ key +"'].radius = "+ circle.getRadius() +";");
	//flag save
	flag_save_required('hplugin_persist_object_data');
}
//bind circle customisation
function bind_circle_customisation(key){
	//bind buttons
	jQuery('#done_shape_btn').off().on('click', function(){
		//hide marker edit panel
		hide_marker_edit_panel();
	});
	jQuery('#del_shape_btn').off().on('click', function(){
		//delete shape request
		delete_shape_request('circle', key);
	});
	//bind components
	jQuery('#circle_fill_colour_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".fillColor = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"fillColor": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#circle_fill_opacity_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".fillOpacity = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"fillOpacity": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#circle_stroke_colour_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".strokeColor = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"strokeColor": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#circle_stroke_opacity_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".strokeOpacity = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"strokeOpacity": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#circle_stroke_weight_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".strokeWeight = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"strokeWeight": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
}


//POLYGON
//bind polygon listeners
function bind_polygon_listeners(key, polygon){
	//click
	google.maps.event.addDomListener(polygon, 'click', function(e){
		//add edit functionality
		var inject_html  = '<div class="edit_top">';
				inject_html += '<div class="hero_col_12" style="width:100%; padding-right:0; border-bottom:1px solid #666; padding-bottom: 10px;">';
					inject_html += '<div class="hero_col_6">';
						inject_html += '<div style="font-size:14px; margin:9px 0 0 0; color:#666;">Edit polygon</div>';
					inject_html += '</div>';
					inject_html += '<div class="hero_col_6" style="width:50%; padding-right:0;">';
						inject_html += '<div style="float:right; padding:8px 11px 5px 10px; margin:0;" id="del_shape_btn" class="hero_button_auto red_button rounded_3"><img src="'+ plugin_url +'assets/images/admin/delete_btn_img.png"></div>';
						inject_html += '<div style="float:right; padding:8px 10px 6px 10px;" id="done_shape_btn" class="hero_button_auto green_button rounded_3">Close</div>';
					inject_html += '</div>';
				inject_html += '</div>';
				inject_html += '<div style="clear:both;"></div>';
			inject_html += '</div>';
			inject_html += '<div class="marker_edit_panel_inner marker_edit_input">';
				inject_html += '<div class="shape_edit_panel_inner">';
					inject_html += '<div style="padding-bottom:3px;">';
						inject_html += '<div class="label"><div>Fill Color:</div></div>';
						inject_html += '<div class="holder">';
						var fill_colour = eval("main_object.map_poly."+ key +".fillColor");
							inject_html += '<input id="polygon_fill_colour_edit" data-size="lrg" type="text" id="" value="'+ fill_colour +'" class="color_picker" style="margin-bottom:3px;">';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:5px;">';
						inject_html += '<div class="label"><div>Fill Opacity:</div></div>';
						inject_html += '<div class="holder">';
							inject_html += '<div class="hero_col_4">';
								var fill_opacity = eval("main_object.map_poly."+ key +".fillOpacity");
								inject_html += '<input type="text" data-size="lrg" data-hero_type="dec" id="polygon_fill_opacity_edit" name="polygon_fill_opacity_edit" value="'+ fill_opacity +'">';
							inject_html += '</div>';
							inject_html += '<div class="hero_col_1">&nbsp;</div>';
							inject_html += '<div class="hero_col_7">';
								inject_html += '<div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="polygon_fill_opacity_edit" id="polygon_fill_opacity_slider_edit"></div>';
							inject_html += '</div>';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:3px;">';
						inject_html += '<div class="label"><div>Stroke Color:</div></div>';
						inject_html += '<div class="holder">';
						var stroke_colour = eval("main_object.map_poly."+ key +".strokeColor");
							inject_html += '<input id="polygon_stroke_colour_edit" data-size="lrg" type="text" id="" value="'+ stroke_colour +'" class="color_picker" style="margin-bottom:3px;">';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:5px;">';
						inject_html += '<div class="label"><div>Stroke Opacity:</div></div>';
						inject_html += '<div class="holder">';
							inject_html += '<div class="hero_col_4">';
								var stroke_opacity = eval("main_object.map_poly."+ key +".strokeOpacity");
								inject_html += '<input type="text" data-size="lrg" data-hero_type="dec" id="polygon_stroke_opacity_edit" name="polygon_stroke_opacity_edit" value="'+ stroke_opacity +'">';
							inject_html += '</div>';
							inject_html += '<div class="hero_col_1">&nbsp;</div>';
							inject_html += '<div class="hero_col_7">';
								inject_html += '<div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="polygon_stroke_opacity_edit" id="polygon_stroke_opacity_slider_edit"></div>';
							inject_html += '</div>';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:3px;">';
						inject_html += '<div class="label"><div>Stroke Weight:</div></div>';
						inject_html += '<div class="holder">';
							inject_html += '<div class="hero_col_4">';
								var stroke_weight = eval("main_object.map_poly."+ key +".strokeWeight");
								inject_html += '<input type="text" data-size="lrg" data-hero_type="dec" id="polygon_stroke_weight_edit" name="polygon_stroke_weight_edit" value="'+ stroke_weight +'">';
							inject_html += '</div>';
							inject_html += '<div class="hero_col_1">&nbsp;</div>';
							inject_html += '<div class="hero_col_7">';
								inject_html += '<div class="hero_slider" data-min="0" data-max="20" data-step="1" data-bind_link="polygon_stroke_weight_edit" id="polygon_stroke_weight_slider_edit"></div>';
							inject_html += '</div>';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="clear:both;"></div>';
				inject_html += '</div>';
			inject_html += '</div>';
			inject_html += '';
		show_shape_edit_panel(inject_html, 'bind_polygon_customisation', key);
	});
	//bind path change listeners
	var path = polygon.getPath();
	google.maps.event.addListener(path, 'insert_at', function(){
		//update polygon
		update_polygon(key, polygon);
	}); 
	google.maps.event.addListener(path, 'remove_at', function(){
		//update polygon
		update_polygon(key, polygon);
	}); 
	google.maps.event.addListener(path, 'set_at', function(){
		//update polygon
		update_polygon(key, polygon);
	});
	//dragend
	google.maps.event.addDomListener(polygon, 'dragend', function(e){
		update_polygon(key, polygon);
	});
}
//update polygon
function update_polygon(key, polygon){
	//get new paths
	var paths = polygon.getPath().getArray();
	//clear existing
	eval("main_object.map_poly."+ key +".path = [];");
	//update paths
	jQuery.each(paths, function(k, val){
		var latlng = val.lat() +','+ val.lng();
		eval("main_object.map_poly['"+ key +"'].path.push('"+ latlng +"');");
	});	
	//flag save
	flag_save_required('hplugin_persist_object_data');
}
//bind polygon customisation
function bind_polygon_customisation(key){
	//bind buttons
	jQuery('#done_shape_btn').off().on('click', function(){
		//hide marker edit panel
		hide_marker_edit_panel();
	});
	jQuery('#del_shape_btn').off().on('click', function(){
		//delete shape request
		delete_shape_request('polygon', key);
	});
	//bind components
	jQuery('#polygon_fill_colour_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".fillColor = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"fillColor": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#polygon_fill_opacity_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".fillOpacity = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"fillOpacity": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#polygon_stroke_colour_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".strokeColor = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"strokeColor": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#polygon_stroke_opacity_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".strokeOpacity = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"strokeOpacity": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#polygon_stroke_weight_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".strokeWeight = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"strokeWeight": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
}


//RECTANGLE
//bind rectangle listeners
function bind_rectangle_listeners(key, rectangle){
	//click
	google.maps.event.addDomListener(rectangle, 'click', function(e){
		//add edit functionality
		var inject_html  = '<div class="edit_top">';
				inject_html += '<div class="hero_col_12" style="width:100%; padding-right:0; border-bottom:1px solid #666; padding-bottom: 10px;">';
					inject_html += '<div class="hero_col_6">';
						inject_html += '<div style="font-size:14px; margin:9px 0 0 0; color:#666;">Edit rectangle</div>';
					inject_html += '</div>';
					inject_html += '<div class="hero_col_6" style="width:50%; padding-right:0;">';
						inject_html += '<div style="float:right; padding:8px 11px 5px 10px; margin:0;" id="del_shape_btn" class="hero_button_auto red_button rounded_3"><img src="'+ plugin_url +'assets/images/admin/delete_btn_img.png"></div>';
						inject_html += '<div style="float:right; padding:8px 10px 6px 10px;" id="done_shape_btn" class="hero_button_auto green_button rounded_3">Close</div>';
					inject_html += '</div>';
				inject_html += '</div>';
				inject_html += '<div style="clear:both;"></div>';
			inject_html += '</div>';
			inject_html += '<div class="marker_edit_panel_inner marker_edit_input">';
				inject_html += '<div class="shape_edit_panel_inner">';
					inject_html += '<div style="padding-bottom:3px;">';
						inject_html += '<div class="label"><div>Fill Color:</div></div>';
						inject_html += '<div class="holder">';
						var fill_colour = eval("main_object.map_poly."+ key +".fillColor");
							inject_html += '<input id="rectangle_fill_colour_edit" data-size="lrg" type="text" id="" value="'+ fill_colour +'" class="color_picker" style="margin-bottom:3px;">';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:5px;">';
						inject_html += '<div class="label"><div>Fill Opacity:</div></div>';
						inject_html += '<div class="holder">';
							inject_html += '<div class="hero_col_4">';
								var fill_opacity = eval("main_object.map_poly."+ key +".fillOpacity");
								inject_html += '<input type="text" data-size="lrg" data-hero_type="dec" id="rectangle_fill_opacity_edit" name="rectangle_fill_opacity_edit" value="'+ fill_opacity +'">';
							inject_html += '</div>';
							inject_html += '<div class="hero_col_1">&nbsp;</div>';
							inject_html += '<div class="hero_col_7">';
								inject_html += '<div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="rectangle_fill_opacity_edit" id="rectangle_fill_opacity_slider_edit"></div>';
							inject_html += '</div>';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:3px;">';
						inject_html += '<div class="label"><div>Stroke Color:</div></div>';
						inject_html += '<div class="holder">';
						var stroke_colour = eval("main_object.map_poly."+ key +".strokeColor");
							inject_html += '<input id="rectangle_stroke_colour_edit" data-size="lrg" type="text" id="" value="'+ stroke_colour +'" class="color_picker" style="margin-bottom:3px;">';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:5px;">';
						inject_html += '<div class="label"><div>Stroke Opacity:</div></div>';
						inject_html += '<div class="holder">';
							inject_html += '<div class="hero_col_4">';
								var stroke_opacity = eval("main_object.map_poly."+ key +".strokeOpacity");
								inject_html += '<input type="text" data-size="lrg" data-hero_type="dec" id="rectangle_stroke_opacity_edit" name="rectangle_stroke_opacity_edit" value="'+ stroke_opacity +'">';
							inject_html += '</div>';
							inject_html += '<div class="hero_col_1">&nbsp;</div>';
							inject_html += '<div class="hero_col_7">';
								inject_html += '<div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="rectangle_stroke_opacity_edit" id="rectangle_stroke_opacity_slider_edit"></div>';
							inject_html += '</div>';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="padding-bottom:3px;">';
						inject_html += '<div class="label"><div>Stroke Weight:</div></div>';
						inject_html += '<div class="holder">';
							inject_html += '<div class="hero_col_4">';
								var stroke_weight = eval("main_object.map_poly."+ key +".strokeWeight");
								inject_html += '<input type="text" data-size="lrg" data-hero_type="dec" id="rectangle_stroke_weight_edit" name="rectangle_stroke_weight_edit" value="'+ stroke_weight +'">';
							inject_html += '</div>';
							inject_html += '<div class="hero_col_1">&nbsp;</div>';
							inject_html += '<div class="hero_col_7">';
								inject_html += '<div class="hero_slider" data-min="0" data-max="20" data-step="1" data-bind_link="rectangle_stroke_weight_edit" id="rectangle_stroke_weight_slider_edit"></div>';
							inject_html += '</div>';
						inject_html += '</div>';
						inject_html += '<div style="clear:both;"></div>';
					inject_html += '</div>';
					inject_html += '<div style="clear:both;"></div>';
				inject_html += '</div>';
			inject_html += '</div>';
			inject_html += '';
		show_shape_edit_panel(inject_html, 'bind_rectangle_customisation', key);
	});
	//bounds changed
	google.maps.event.addDomListener(rectangle, 'bounds_changed', function(e){
		//update rectangle
		update_rectangle(key, rectangle);
	});
	//dragend
	google.maps.event.addDomListener(rectangle, 'dragend', function(e){
		//update rectangle
		update_rectangle(key, rectangle);
	});
}
//update rectangle
function update_rectangle(key, rectangle){
	//update rectangle data
	eval("main_object.map_poly['"+ key +"'].NE = '"+ rectangle.getBounds().getNorthEast().lat() +","+ rectangle.getBounds().getNorthEast().lng() +"';");
	eval("main_object.map_poly['"+ key +"'].SW = '"+ rectangle.getBounds().getSouthWest().lat() +","+ rectangle.getBounds().getSouthWest().lng() +"';");
	//flag save
	flag_save_required('hplugin_persist_object_data');
}
//bind rectangle customisation
function bind_rectangle_customisation(key){
	//bind buttons
	jQuery('#done_shape_btn').off().on('click', function(){
		//hide marker edit panel
		hide_marker_edit_panel();
	});
	jQuery('#del_shape_btn').off().on('click', function(){
		//delete shape request
		delete_shape_request('rectangle', key);
	});
	//bind components
	jQuery('#rectangle_fill_colour_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".fillColor = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"fillColor": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#rectangle_fill_opacity_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".fillOpacity = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"fillOpacity": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#rectangle_stroke_colour_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".strokeColor = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"strokeColor": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#rectangle_stroke_opacity_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".strokeOpacity = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"strokeOpacity": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	jQuery('#rectangle_stroke_weight_edit').on('change', function(){
		//update object
		eval("main_object.map_poly."+ key +".strokeWeight = '"+ jQuery(this).val() +"';");
		//update shape
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setOptions({"strokeWeight": jQuery(this).val()});
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
}


//delete shape request
function delete_shape_request(type, key){
	if(window.confirm('Are you sure you want to delete this '+ type +'?')){
		//remove shape form map
		var object_ref = eval("main_object.map_poly."+ key +";");
		var object_pointer = object_ref.gmd;
		object_pointer.setMap(null);
		//delete from object
		eval("delete main_object.map_poly."+ key +"");
		//hide marker edit panel
		hide_marker_edit_panel();
		//flag save
		flag_save_required('hplugin_persist_object_data');
	}
}