//MAPS_DRAWING VIEW

//view load
jQuery(function(){
	//enable drawing manager
	enable_drawing_manager();
	//bind to view nav and dsiable drawing manager
	hplugin_event_subscribe_once('view-nav','disable_drawing_manager','');
	//bind input change listener
	setTimeout(function(){
		jQuery('input').on('change keyup paste', function(){
			setTimeout(function(){
				update_drawing_manager_defaults();
			}, 100);
		});
	}, 100);
	//bind drawing listeners
	bind_drawing_listeners();
});

//enable drawing manager
function enable_drawing_manager(){
	drawingManager = new google.maps.drawing.DrawingManager({
		drawingControl: true,
		drawingControlOptions: {
			position: google.maps.ControlPosition.BOTTOM_CENTER,
			drawingModes: [
				google.maps.drawing.OverlayType.POLYLINE,
				google.maps.drawing.OverlayType.CIRCLE,
				google.maps.drawing.OverlayType.POLYGON,
				google.maps.drawing.OverlayType.RECTANGLE
			]
		},
		polylineOptions: {
			strokeColor: main_object.map_drawing.polylineOptions.strokeColor,
			strokeOpacity: main_object.map_drawing.polylineOptions.strokeOpacity,
			strokeWeight: main_object.map_drawing.polylineOptions.strokeWeight,
			geodesic: main_object.map_drawing.polylineOptions.geodesic,
			draggable: true,
			clickable: true,
			editable: true,
			zIndex: 1,
			suppressUndo: true
		},
		circleOptions: {
			fillColor: main_object.map_drawing.circleOptions.fillColor,
			fillOpacity: main_object.map_drawing.circleOptions.fillOpacity,
			strokeColor: main_object.map_drawing.circleOptions.strokeColor,
			strokeOpacity: main_object.map_drawing.circleOptions.strokeOpacity,
			strokeWeight: main_object.map_drawing.circleOptions.strokeWeight,
			draggable: true,
			clickable: true,
			editable: true,
			zIndex: 1,
			suppressUndo: true
		},
		polygonOptions: {
			fillColor: main_object.map_drawing.polygonOptions.fillColor,
			fillOpacity: main_object.map_drawing.polygonOptions.fillOpacity,
			strokeColor: main_object.map_drawing.polygonOptions.strokeColor,
			strokeOpacity: main_object.map_drawing.polygonOptions.strokeOpacity,
			strokeWeight: main_object.map_drawing.polygonOptions.strokeWeight,
			draggable: true,
			clickable: true,
			editable: true,
			zIndex: 1,
			suppressUndo: true
		},
		rectangleOptions: {
			fillColor: main_object.map_drawing.rectangleOptions.fillColor,
			fillOpacity: main_object.map_drawing.rectangleOptions.fillOpacity,
			strokeColor: main_object.map_drawing.rectangleOptions.strokeColor,
			strokeOpacity: main_object.map_drawing.rectangleOptions.strokeOpacity,
			strokeWeight: main_object.map_drawing.rectangleOptions.strokeWeight,
			draggable: true,
			clickable: true,
			editable: true,
			zIndex: 1,
			suppressUndo: true
		}
	});
	drawingManager.setMap(google_map);
}

//disable drawing manager
function disable_drawing_manager(){
	drawingManager.setMap(null);
	drawingManager = null;
}

//update drawing manager defaults
function update_drawing_manager_defaults(){
	var drawing_options = {
		drawingControl: true,
		drawingControlOptions: {
			position: google.maps.ControlPosition.BOTTOM_CENTER,
			drawingModes: [
				google.maps.drawing.OverlayType.POLYLINE,
				google.maps.drawing.OverlayType.CIRCLE,
				google.maps.drawing.OverlayType.POLYGON,
				google.maps.drawing.OverlayType.RECTANGLE
			]
		},
		polylineOptions: {
			strokeColor: main_object.map_drawing.polylineOptions.strokeColor,
			strokeOpacity: main_object.map_drawing.polylineOptions.strokeOpacity,
			strokeWeight: main_object.map_drawing.polylineOptions.strokeWeight,
			geodesic: main_object.map_drawing.polylineOptions.geodesic,
			draggable: true,
			clickable: true,
			editable: true,
			zIndex: 1,
			suppressUndo: true
		},
		circleOptions: {
			fillColor: main_object.map_drawing.circleOptions.fillColor,
			fillOpacity: main_object.map_drawing.circleOptions.fillOpacity,
			strokeColor: main_object.map_drawing.circleOptions.strokeColor,
			strokeOpacity: main_object.map_drawing.circleOptions.strokeOpacity,
			strokeWeight: main_object.map_drawing.circleOptions.strokeWeight,
			draggable: true,
			clickable: true,
			editable: true,
			zIndex: 1,
			suppressUndo: true
		},
		polygonOptions: {
			fillColor: main_object.map_drawing.polygonOptions.fillColor,
			fillOpacity: main_object.map_drawing.polygonOptions.fillOpacity,
			strokeColor: main_object.map_drawing.polygonOptions.strokeColor,
			strokeOpacity: main_object.map_drawing.polygonOptions.strokeOpacity,
			strokeWeight: main_object.map_drawing.polygonOptions.strokeWeight,
			draggable: true,
			clickable: true,
			editable: true,
			zIndex: 1,
			suppressUndo: true
		},
		rectangleOptions: {
			fillColor: main_object.map_drawing.rectangleOptions.fillColor,
			fillOpacity: main_object.map_drawing.rectangleOptions.fillOpacity,
			strokeColor: main_object.map_drawing.rectangleOptions.strokeColor,
			strokeOpacity: main_object.map_drawing.rectangleOptions.strokeOpacity,
			strokeWeight: main_object.map_drawing.rectangleOptions.strokeWeight,
			draggable: true,
			clickable: true,
			editable: true,
			zIndex: 1,
			suppressUndo: true
		}
	}
	if(typeof drawingManager == 'object' && drawingManager != null){
		drawingManager.setOptions(drawing_options);
	}
}

//bind drawing listeners
function bind_drawing_listeners(){
	//polyline
	google.maps.event.addDomListener(drawingManager, 'polylinecomplete', function(polyline){
		//get polyline data
		var polyline_data = {
			"type": "polyline",
			"path": [],
			"strokeColor": main_object.map_drawing.polylineOptions.strokeColor,
			"strokeOpacity": main_object.map_drawing.polylineOptions.strokeOpacity,
			"strokeWeight": main_object.map_drawing.polylineOptions.strokeWeight,
			"geodesic": main_object.map_drawing.polylineOptions.geodesic,
			"gmd": polyline,
			"suppressUndo": true
		};
		//get polyline paths
		var paths = polyline.getPath().getArray();
		jQuery.each(paths, function(key, val){
			polyline_data.path.push(val.lat() +','+ val.lng());
		});
		//generate random string as id
		var polyline_id = grs();
		//save polyline
		main_object.map_poly[polyline_id] = polyline_data;
		//bind polyline listeners
		bind_polyline_listeners(polyline_id, polyline);
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	//circle
	google.maps.event.addDomListener(drawingManager, 'circlecomplete', function(circle){
		//get circle data
		var circle_data = {
			"type": "circle",
			"latlng": circle.getCenter().lat() +','+ circle.getCenter().lng(),
			"radius": circle.getRadius(),
			"fillColor": main_object.map_drawing.circleOptions.fillColor,
			"fillOpacity": main_object.map_drawing.circleOptions.fillOpacity,
			"strokeColor": main_object.map_drawing.circleOptions.strokeColor,
			"strokeOpacity": main_object.map_drawing.circleOptions.strokeOpacity,
			"strokeWeight": main_object.map_drawing.circleOptions.strokeWeight,
			"gmd": circle,
			"suppressUndo": true
		};
		//generate random string as id
		var circle_id = grs();
		//save circle
		main_object.map_poly[circle_id] = circle_data;
		//bind circle listeners
		bind_circle_listeners(circle_id, circle);
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	//polygon
	google.maps.event.addDomListener(drawingManager, 'polygoncomplete', function(polygon){
		//get polygon data
		var polygon_data = {
			"type": "polygon",
			"path": [],
			"fillColor": main_object.map_drawing.polygonOptions.fillColor,
			"fillOpacity": main_object.map_drawing.polygonOptions.fillOpacity,
			"strokeColor": main_object.map_drawing.polygonOptions.strokeColor,
			"strokeOpacity": main_object.map_drawing.polygonOptions.strokeOpacity,
			"strokeWeight": main_object.map_drawing.polygonOptions.strokeWeight,
			"gmd": polygon,
			"suppressUndo": true
		};
		//get polygon paths
		var paths = polygon.getPath().getArray();
		jQuery.each(paths, function(key, val){
			polygon_data.path.push(val.lat() +','+ val.lng());
		});
		//generate random string as id
		var polygon_id = grs();
		//save polygon
		main_object.map_poly[polygon_id] = polygon_data;
		//bind polygon listeners
		bind_polygon_listeners(polygon_id, polygon);
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	//rectangle
	google.maps.event.addDomListener(drawingManager, 'rectanglecomplete', function(rectangle){
		//get rectangle data
		var rectangle_data = {
			"type": "rectangle",
			"NE": rectangle.getBounds().getNorthEast().lat() +","+ rectangle.getBounds().getNorthEast().lng(),
			"SW": rectangle.getBounds().getSouthWest().lat() +","+ rectangle.getBounds().getSouthWest().lng(),
			"fillColor": main_object.map_drawing.rectangleOptions.fillColor,
			"fillOpacity": main_object.map_drawing.rectangleOptions.fillOpacity,
			"strokeColor": main_object.map_drawing.rectangleOptions.strokeColor,
			"strokeOpacity": main_object.map_drawing.rectangleOptions.strokeOpacity,
			"strokeWeight": main_object.map_drawing.rectangleOptions.strokeWeight,
			"gmd": rectangle,
			"suppressUndo": true
		};
		//generate random string as id
		var rectangle_id = grs();
		//save rectangle
		main_object.map_poly[rectangle_id] = rectangle_data;
		//bind rectangle listeners
		bind_rectangle_listeners(rectangle_id, rectangle);
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
}