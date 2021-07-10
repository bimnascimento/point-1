var undoManager = function(opts) {
	"use strict";
	
	var fallback = opts.fallback || '';
	var callback = opts.callback || null;
	var indexKey = 'undoManagerIndex_' + opts.key;
	var itemsKey = 'undoManagerItems_' + opts.key;
	
	var items = [fallback];
	var index = 0;
	
	var storage = localStorage || {};
	if( storage[itemsKey] ) {
		items = JSON.parse(storage[itemsKey]);
	}
	if( storage[indexKey] ) {
		index = parseInt(storage[indexKey]);
	}
	
	var initial_index = index;
	
	var self = {
		add: function(item) {
			var i = index+1;
			items.splice(i, items.length - i, item);
			index = items.length-1;
			
			storage[indexKey] = index;
			storage[itemsKey] = JSON.stringify(items);
			
			return this;
		},
		
		changed: function() {
			return fallback !== items[index];
		},
		hasUndo: function() {
			return index > 0;
		},
		hasRedo: function() {
			return index < items.length-1;
		},
		
		countUndo: function() {
			return index;
		},
		countRedo: function() {
			return items.length - index - 1;
		},
		
		clear: function( content ) {
			if( this.hasUndo() || this.hasRedo ) {
				index = -1;
				this.add( content );
				fallback = content;
				initial_index = index;
				storage.removeItem( indexKey );
				storage.removeItem( itemsKey );
			}
			return this;
		},
		undo: function() {
			if( this.hasUndo() ) {
				this.approve(index-1, function(){
					index--;
					storage[indexKey] = index;
				});
			}
			return this;
		},
		redo: function() {
			if( this.hasRedo() ) {
				this.approve( index+1, function(){
					index++;
					storage[indexKey] = index;
				});
			}
			return this;
		},
		
		approve: function( index, go ) {
			if( _.isFunction(callback) && _.isFunction(go) ) {
				var approved = callback.call(this, items[index], go);
				if( approved ) {
					go.call();
				}
			}
		},
		
		save: function() {
			fallback = items[index];
		},
		
		debug: function() {
			console.debug("Current index: " + index);
			console.debug("Current item: " + items[index]);
			console.debug("Fallback: " + fallback);
			console.debug(items);
		}
	};
	
	window.onunload = function(){
		if( self.changed() ) {
			storage[indexKey] = initial_index;
		}
	}
	
	return self;
}