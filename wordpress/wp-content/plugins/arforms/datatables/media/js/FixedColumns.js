
var FixedColumns;

(function($, window, document) {



FixedColumns = function ( oDT, oInit ) {
	if ( ! this instanceof FixedColumns )
	{
		alert( "FixedColumns warning: FixedColumns must be initialised with the 'new' keyword." );
		return;
	}
	
	if ( typeof oInit == 'undefined' )
	{
		oInit = {};
	}
	
	
	this.s = {
		
		"dt": oDT.fnSettings(),
		
		
		"iTableColumns": oDT.fnSettings().aoColumns.length,
		
		
		"aiWidths": [],
		
		
		"bOldIE": ($.browser.msie && ($.browser.version == "6.0" || $.browser.version == "7.0"))
	};
	
	
	this.dom = {
		
		"scroller": null,
		
		
		"header": null,
		
		
		"body": null,
		
		
		"footer": null,

		
		"grid": {
			
			"wrapper": null,

			
			"dt": null,

			
			"left": {
				"wrapper": null,
				"head": null,
				"body": null,
				"foot": null
			},

			
			"right": {
				"wrapper": null,
				"head": null,
				"body": null,
				"foot": null
			}
		},
		
		
		"clone": {
			
			"left": {
				
				"header": null,
		  	
				
				"body": null,
		  	
				
				"footer": null
			},
			
			
			"right": {
				
				"header": null,
		  	
				
				"body": null,
		  	
				
				"footer": null
			}
		}
	};

	
	this.s.dt.oFixedColumns = this;
	
	
	this._fnConstruct( oInit );
};



FixedColumns.prototype = {
	
	"fnUpdate": function ()
	{
		this._fnDraw( true );
	},
	
	

	"fnRedrawLayout": function ()
	{
		this._fnGridLayout();
	},
	

	"fnRecalculateHeight": function ( nTr )
	{
		nTr._DTTC_iHeight = null;
		nTr.style.height = 'auto';
	},
	
	

	"fnSetRowHeight": function ( nTarget, iHeight )
	{
		var jqBoxHack = $(nTarget).children(':first');
		var iBoxHack = jqBoxHack.outerHeight() - jqBoxHack.height();

		
		if ( $.browser.mozilla || $.browser.opera )
		{
			nTarget.style.height = iHeight+"px";
		}
		else
		{
			$(nTarget).children().height( iHeight-iBoxHack );
		}
	},
	
	

	"_fnConstruct": function ( oInit )
	{
		var i, iLen, iWidth,
			that = this;
		
	
		if ( typeof this.s.dt.oInstance.fnVersionCheck != 'function' ||
		     this.s.dt.oInstance.fnVersionCheck( '1.8.0' ) !== true )
		{
			alert( "FixedColumns "+FixedColumns.VERSION+" required DataTables 1.8.0 or later. "+
				"Please upgrade your DataTables installation" );
			return;
		}
		
		if ( this.s.dt.oScroll.sX === "" )
		{
			this.s.dt.oInstance.oApi._fnLog( this.s.dt, 1, "FixedColumns is not needed (no "+
				"x-scrolling in DataTables enabled), so no action will be taken. Use 'FixedHeader' for "+
				"column fixing when scrolling is not enabled" );
			return;
		}
		
		
		this.s = $.extend( true, this.s, FixedColumns.defaults, oInit );

		
		this.dom.grid.dt = $(this.s.dt.nTable).parents('div.dataTables_scroll')[0];
		this.dom.scroller = $('div.dataTables_scrollBody', this.dom.grid.dt )[0];

		var iScrollWidth = $(this.dom.grid.dt).width();
		var iLeftWidth = 0;
		var iRightWidth = 0;

		$('tbody>tr:eq(0)>td', this.s.dt.nTable).each( function (i) {
			iWidth = $(this).outerWidth();
			that.s.aiWidths.push( iWidth );
			if ( i < that.s.iLeftColumns )
			{
				iLeftWidth += iWidth;
			}
			if ( that.s.iTableColumns-that.s.iRightColumns <= i )
			{
				iRightWidth += iWidth;
			}
		} );

		if ( this.s.iLeftWidth === null )
		{
			this.s.iLeftWidth = this.s.sLeftWidth == 'fixed' ?
				iLeftWidth : (iLeftWidth/iScrollWidth) * 100; 
		}
		
		if ( this.s.iRightWidth === null )
		{
			this.s.iRightWidth = this.s.sRightWidth == 'fixed' ?
				iRightWidth : (iRightWidth/iScrollWidth) * 100;
		}
		
		
		this._fnGridSetup();

		
		for ( i=0 ; i<this.s.iLeftColumns ; i++ )
		{
			this.s.dt.oInstance.fnSetColumnVis( i, false );
		}
		for ( i=this.s.iTableColumns - this.s.iRightColumns ; i<this.s.iTableColumns ; i++ )
		{
			this.s.dt.oInstance.fnSetColumnVis( i, false );
		}

		
		$(this.dom.scroller).scroll( function () {
			that.dom.grid.left.body.scrollTop = that.dom.scroller.scrollTop;
			if ( that.s.iRightColumns > 0 )
			{
				that.dom.grid.right.body.scrollTop = that.dom.scroller.scrollTop;
			}
		} );

		$(window).resize( function () {
			that._fnGridLayout.call( that );
		} );
		
		var bFirstDraw = true;
		this.s.dt.aoDrawCallback = [ {
			"fn": function () {
				that._fnDraw.call( that, bFirstDraw );
				that._fnGridHeight( that );
				bFirstDraw = false;
			},
			"sName": "FixedColumns"
		} ].concat( this.s.dt.aoDrawCallback );
		
		
		this._fnGridLayout();
		this._fnGridHeight();
		this.s.dt.oInstance.fnDraw(false);
	},
	
	
	
	"_fnGridSetup": function ()
	{
		var that = this;

		this.dom.body = this.s.dt.nTable;
		this.dom.header = this.s.dt.nTHead.parentNode;
		this.dom.header.parentNode.parentNode.style.position = "relative";
		
		var nSWrapper = 
			$('<div class="DTFC_ScrollWrapper" style="position:relative; clear:both;">'+
				'<div class="DTFC_LeftWrapper" style="position:absolute; top:0; left:0;">'+
					'<div class="DTFC_LeftHeadWrapper" style="position:relative; top:0; left:0; overflow:hidden;"></div>'+
					'<div class="DTFC_LeftBodyWrapper" style="position:relative; top:0; left:0; overflow:hidden;"></div>'+
					'<div class="DTFC_LeftFootWrapper" style="position:relative; top:0; left:0; overflow:hidden;"></div>'+
			  	'</div>'+
				'<div class="DTFC_RightWrapper" style="position:absolute; top:0; left:0;">'+
					'<div class="DTFC_RightHeadWrapper" style="position:relative; top:0; left:0; overflow:hidden;"></div>'+
					'<div class="DTFC_RightBodyWrapper" style="position:relative; top:0; left:0; overflow:hidden;"></div>'+
					'<div class="DTFC_RightFootWrapper" style="position:relative; top:0; left:0; overflow:hidden;"></div>'+
			  	'</div>'+
			  '</div>')[0];
		nLeft = nSWrapper.childNodes[0];
		nRight = nSWrapper.childNodes[1];

		this.dom.grid.wrapper = nSWrapper;
		this.dom.grid.left.wrapper = nLeft;
		this.dom.grid.left.head = nLeft.childNodes[0];
		this.dom.grid.left.body = nLeft.childNodes[1];

		if ( this.s.iRightColumns > 0 )
		{
			this.dom.grid.right.wrapper = nRight;
			this.dom.grid.right.head = nRight.childNodes[0];
			this.dom.grid.right.body = nRight.childNodes[1];
		}
		
		if ( this.s.dt.nTFoot )
		{
			this.dom.footer = this.s.dt.nTFoot.parentNode;
			this.dom.grid.left.foot = nLeft.childNodes[2];
			if ( this.s.iRightColumns > 0 )
			{
				this.dom.grid.right.foot = nRight.childNodes[2];
			}
		}

		nSWrapper.appendChild( nLeft );
		this.dom.grid.dt.parentNode.insertBefore( nSWrapper, this.dom.grid.dt );
		nSWrapper.appendChild( this.dom.grid.dt );

		this.dom.grid.dt.style.position = "absolute";
		this.dom.grid.dt.style.top = "0px";
		this.dom.grid.dt.style.left = this.s.iLeftWidth+"px";
		this.dom.grid.dt.style.width = ($(this.dom.grid.dt).width()-this.s.iLeftWidth-this.s.iRightWidth)+"px";
	},
	
	
	
	"_fnGridLayout": function ()
	{
		var oGrid = this.dom.grid;
		var iTotal = $(oGrid.wrapper).width();
		var iLeft = 0, iRight = 0, iRemainder = 0;

		if ( this.s.sLeftWidth == 'fixed' )
		{
			iLeft = this.s.iLeftWidth;
		}
		else
		{
			iLeft = ( this.s.iLeftWidth / 100 ) * iTotal;
		}

		if ( this.s.sRightWidth == 'fixed' )
		{
			iRight = this.s.iRightWidth;
		}
		else
		{
			iRight = ( this.s.iRightWidth / 100 ) * iTotal;
		}

		iRemainder = iTotal - iLeft - iRight;

		oGrid.left.wrapper.style.width = iLeft+"px";
		oGrid.dt.style.width = iRemainder+"px";
		oGrid.dt.style.left = iLeft+"px";

		if ( this.s.iRightColumns > 0 )
		{
			oGrid.right.wrapper.style.width = iRight+"px";
			oGrid.right.wrapper.style.left = (iTotal-iRight)+"px";
		}
	},
	
	
	
	"_fnGridHeight": function ()
	{
		var oGrid = this.dom.grid;
		var iHeight = $(this.dom.grid.dt).height();

		oGrid.wrapper.style.height = iHeight+"px";
		oGrid.left.body.style.height = $(this.dom.scroller).height()+"px";
		oGrid.left.wrapper.style.height = iHeight+"px";
		
		if ( this.s.iRightColumns > 0 )
		{
			oGrid.right.wrapper.style.height = iHeight+"px";
			oGrid.right.body.style.height = $(this.dom.scroller).height()+"px";
		}
	},
	
	
	
	"_fnDraw": function ( bAll )
	{
		this._fnCloneLeft( bAll );
		this._fnCloneRight( bAll );

		
		if ( this.s.fnDrawCallback !== null )
		{
			this.s.fnDrawCallback.call( this, this.dom.clone.left, this.dom.clone.right );
		}

		
		$(this).trigger( 'draw', { 
			"leftClone": this.dom.clone.left,
			"rightClone": this.dom.clone.right
		} );
	},
	
	

	"_fnCloneRight": function ( bAll )
	{
		if ( this.s.iRightColumns <= 0 )
		{
			return;
		}
		
		var that = this,
			i, jq,
			aiColumns = [];

		for ( i=this.s.iTableColumns-this.s.iRightColumns ; i<this.s.iTableColumns ; i++ )
		{
			aiColumns.push( i );
		}

		this._fnClone( this.dom.clone.right, this.dom.grid.right, aiColumns, bAll );
	},
	
	
	
	"_fnCloneLeft": function ( bAll )
	{
		if ( this.s.iLeftColumns <= 0 )
		{
			return;
		}
		
		var that = this,
			i, jq,
			aiColumns = [];
		
		for ( i=0 ; i<this.s.iLeftColumns ; i++ )
		{
			aiColumns.push( i );
		}

		this._fnClone( this.dom.clone.left, this.dom.grid.left, aiColumns, bAll );
	},
	
	
	
	"_fnCopyLayout": function ( aoOriginal, aiColumns )
	{
		var aReturn = [];
		var aClones = [];
		var aCloned = [];

		for ( var i=0, iLen=aoOriginal.length ; i<iLen ; i++ )
		{
			var aRow = [];
			aRow.nTr = $(aoOriginal[i].nTr).clone(true)[0];

			for ( var j=0, jLen=this.s.iTableColumns ; j<jLen ; j++ )
			{
				if ( $.inArray( j, aiColumns ) === -1 )
				{
					continue;
				}

				var iCloned = $.inArray( aoOriginal[i][j].cell, aCloned );
				if ( iCloned === -1 )
				{
					var nClone = $(aoOriginal[i][j].cell).clone(true)[0];
					aClones.push( nClone );
					aCloned.push( aoOriginal[i][j].cell );

					aRow.push( {
						"cell": nClone,
						"unique": aoOriginal[i][j].unique
					} );
				}
				else
				{
					aRow.push( {
						"cell": aClones[ iCloned ],
						"unique": aoOriginal[i][j].unique
					} );
				}
			}
			
			aReturn.push( aRow );
		}

		return aReturn;
	},
	
	
	
	"_fnClone": function ( oClone, oGrid, aiColumns, bAll )
	{
		var that = this,
			i, iLen, j, jLen, jq, nTarget, iColumn, nClone, iIndex;

		
		if ( bAll )
		{
			if ( oClone.header !== null )
			{
				oClone.header.parentNode.removeChild( oClone.header );
			}
			oClone.header = $(this.dom.header).clone(true)[0];
			oClone.header.className += " DTFC_Cloned";
			oClone.header.style.width = "100%";
			oGrid.head.appendChild( oClone.header );
			
			
			var aoCloneLayout = this._fnCopyLayout( this.s.dt.aoHeader, aiColumns );
			var jqCloneThead = $('>thead', oClone.header);
			jqCloneThead.empty();

			
			for ( i=0, iLen=aoCloneLayout.length ; i<iLen ; i++ )
			{
				jqCloneThead[0].appendChild( aoCloneLayout[i].nTr );
			}

			
			this.s.dt.oApi._fnDrawHead( this.s.dt, aoCloneLayout, true );
		}
		else
		{
			
			var aoCloneLayout = this._fnCopyLayout( this.s.dt.aoHeader, aiColumns );
			var aoCurrHeader=[];

			this.s.dt.oApi._fnDetectHeader( aoCurrHeader, $('>thead', oClone.header)[0] );
			
			for ( i=0, iLen=aoCloneLayout.length ; i<iLen ; i++ )
			{
				for ( j=0, jLen=aoCloneLayout[i].length ; j<jLen ; j++ )
				{
					aoCurrHeader[i][j].cell.className = aoCloneLayout[i][j].cell.className;
					
					var oSettings = that.s.dt;
					
					$('span.DataTables_sort_icon', aoCurrHeader[i][j].cell).each( function () {
					
					
					if(oSettings.aoColumns[j].bSortable)
					{ 
					var aaSort = that.s.dt.aaSorting;
					var oClasses = that.s.dt.oClasses;
					var sClass,j1; 
					if ( oSettings.bJUI )
					{	
					
					var jqSpan = $(this);
					jqSpan.removeClass(oClasses.sSortJUIAsc +" "+ oClasses.sSortJUIDesc +" "+ 
					oClasses.sSortJUI +" "+ oClasses.sSortJUIAscAllowed +" "+ oClasses.sSortJUIDescAllowed );
					
					var iFound = -1;
					for ( j1=0 ; j1<aaSort.length ; j1++ )
					{
					if ( aaSort[j1][0] == j )
					{
					sClass = ( aaSort[j1][1] == "asc" ) ?
					oClasses.sSortAsc : oClasses.sSortDesc;
					iFound = j1;
					break;
					}
					}
					
					var sSpanClass;
					if ( iFound == -1 )
					{
					sSpanClass = oSettings.aoColumns[j].sSortingClassJUI;
					}
					else if ( aaSort[iFound][1] == "asc" )
					{
					sSpanClass = oClasses.sSortJUIAsc;
					}
					else
					{
					sSpanClass = oClasses.sSortJUIDesc;
					}
					
					jqSpan.addClass( sSpanClass );
					} 
					}
				
					} );
				}
			}
			
						
					}
		this._fnEqualiseHeights( 'thead', this.dom.header, oClone.header );
		
		
		if ( this.s.sHeightMatch == 'auto' )
		{
			
			$('>tbody>tr', that.dom.body).css('height', 'auto');
		}
		
		if ( oClone.body !== null )
		{
			oClone.body.parentNode.removeChild( oClone.body );
			oClone.body = null;
		}
		
		oClone.body = $(this.dom.body).clone(true)[0];
		oClone.body.className += " DTFC_Cloned";
		oClone.body.style.paddingBottom = this.s.dt.oScroll.iBarWidth+"px";
		oClone.body.style.marginBottom = (this.s.dt.oScroll.iBarWidth*2)+"px"; 
		if ( oClone.body.getAttribute('id') !== null )
		{
			oClone.body.removeAttribute('id');
		}
		
		$('>thead>tr', oClone.body).empty();
		$('>tfoot', oClone.body).remove();
		
		var nBody = $('tbody', oClone.body)[0];
		$(nBody).empty();
		if ( this.s.dt.aiDisplay.length > 0 )
		{
			
			var nInnerThead = $('>thead>tr', oClone.body)[0];
			for ( iIndex=0 ; iIndex<aiColumns.length ; iIndex++ )
			{
				iColumn = aiColumns[iIndex];

				nClone = this.s.dt.aoColumns[iColumn].nTh;
				nClone.innerHTML = "";

				oStyle = nClone.style;
				oStyle.paddingTop = "0";
				oStyle.paddingBottom = "0";
				oStyle.borderTopWidth = "0";
				oStyle.borderBottomWidth = "0";
				oStyle.height = 0;
				oStyle.width = "120px";

				nInnerThead.appendChild( nClone );
			}

			
			$('>tbody>tr', that.dom.body).each( function (z) {
				var n = this.cloneNode(false);
				var i = that.s.dt.oFeatures.bServerSide===false ?
					that.s.dt.aiDisplay[ that.s.dt._iDisplayStart+z ] : z;
				for ( iIndex=0 ; iIndex<aiColumns.length ; iIndex++ )
				{
					iColumn = aiColumns[iIndex];
					if ( typeof that.s.dt.aoData[i]._anHidden[iColumn] != 'undefined' )
					{
						nClone = $(that.s.dt.aoData[i]._anHidden[iColumn]).clone(true)[0];
						n.appendChild( nClone );
					}
				}
				nBody.appendChild( n );
			} );
		}
		else
		{
			$('>tbody>tr', that.dom.body).each( function (z) {
				nClone = this.cloneNode(true);
				nClone.className += ' DTFC_NoData';
				$('td', nClone).html('');
				nBody.appendChild( nClone );
			} );
		}
		
		oClone.body.style.width = "100%";
		oGrid.body.appendChild( oClone.body );

		this._fnEqualiseHeights( 'tbody', that.dom.body, oClone.body );
		
		
		if ( this.s.dt.nTFoot !== null )
		{
			if ( bAll )
			{
				if ( oClone.footer !== null )
				{
					oClone.footer.parentNode.removeChild( oClone.footer );
				}
				oClone.footer = $(this.dom.footer).clone(true)[0];
				oClone.footer.className += " DTFC_Cloned";
				oClone.footer.style.width = "100%";
				oGrid.foot.appendChild( oClone.footer );

				
				var aoCloneLayout = this._fnCopyLayout( this.s.dt.aoFooter, aiColumns );
				var jqCloneTfoot = $('>tfoot', oClone.footer);
				jqCloneTfoot.empty();
	
				for ( i=0, iLen=aoCloneLayout.length ; i<iLen ; i++ )
				{
					jqCloneTfoot[0].appendChild( aoCloneLayout[i].nTr );
				}
				this.s.dt.oApi._fnDrawHead( this.s.dt, aoCloneLayout, true );
			}
			else
			{
				var aoCloneLayout = this._fnCopyLayout( this.s.dt.aoFooter, aiColumns );
				var aoCurrFooter=[];

				this.s.dt.oApi._fnDetectHeader( aoCurrFooter, $('>tfoot', oClone.footer)[0] );

				for ( i=0, iLen=aoCloneLayout.length ; i<iLen ; i++ )
				{
					for ( j=0, jLen=aoCloneLayout[i].length ; j<jLen ; j++ )
					{
						aoCurrFooter[i][j].cell.className = aoCloneLayout[i][j].cell.className;
					}
				}
			}
			this._fnEqualiseHeights( 'tfoot', this.dom.footer, oClone.footer );
		}

		
		var anUnique = this.s.dt.oApi._fnGetUniqueThs( this.s.dt, $('>thead', oClone.header)[0] );
		$(anUnique).each( function (i) {
			iColumn = aiColumns[i];
			this.style.width = "120px";
		} );

		if ( that.s.dt.nTFoot !== null )
		{
			anUnique = this.s.dt.oApi._fnGetUniqueThs( this.s.dt, $('>tfoot', oClone.footer)[0] );
			$(anUnique).each( function (i) {
				iColumn = aiColumns[i];
				this.style.width = "120px";
			} );
		}
	},
	
	
	
	"_fnGetTrNodes": function ( nIn )
	{
		var aOut = [];
		for ( var i=0, iLen=nIn.childNodes.length ; i<iLen ; i++ )
		{
			if ( nIn.childNodes[i].nodeName.toUpperCase() == "TR" )
			{
				aOut.push( nIn.childNodes[i] );
			}
		}
		return aOut;
	},

	
	
	"_fnEqualiseHeights": function ( nodeName, original, clone )
	{
		if ( this.s.sHeightMatch == 'none' && nodeName !== 'thead' && nodeName !== 'tfoot' )
		{
			return;
		}
		
		var that = this,
			i, iLen, iHeight, iHeight2, iHeightOriginal, iHeightClone,
			rootOriginal = original.getElementsByTagName(nodeName)[0],
			rootClone    = clone.getElementsByTagName(nodeName)[0],
			jqBoxHack    = $('>'+nodeName+'>tr:eq(0)', original).children(':first'),
			iBoxHack     = jqBoxHack.outerHeight() - jqBoxHack.height(),
			anOriginal   = this._fnGetTrNodes( rootOriginal ),
		 	anClone      = this._fnGetTrNodes( rootClone );
		
		for ( i=0, iLen=anClone.length ; i<iLen ; i++ )
		{
			if ( this.s.sHeightMatch == 'semiauto' && typeof anOriginal[i]._DTTC_iHeight != 'undefined' && 
				anOriginal[i]._DTTC_iHeight !== null )
			{
				
				if ( $.browser.msie )
				{
					$(anClone[i]).children().height( anOriginal[i]._DTTC_iHeight-iBoxHack );
				}
				continue;
			}
			
			iHeightOriginal = anOriginal[i].offsetHeight;
			iHeightClone = anClone[i].offsetHeight;
			iHeight = iHeightClone > iHeightOriginal ? iHeightClone : iHeightOriginal;
			
			if ( this.s.sHeightMatch == 'semiauto' )
			{
				anOriginal[i]._DTTC_iHeight = iHeight;
			}
			
			
			if ( $.browser.msie && $.browser.version < 8 )
			{
				$(anClone[i]).children().height( iHeight-iBoxHack );
				$(anOriginal[i]).children().height( iHeight-iBoxHack );	
			}
			else
			{
				anClone[i].style.height = iHeight+"px";
				anOriginal[i].style.height = iHeight+"px";
			}
		}
	}
};




FixedColumns.defaults = {
	
	"iLeftColumns": 1,
	
	
	"iRightColumns": 0,
	
	
	"fnDrawCallback": null,
	
	
	"sLeftWidth": "fixed",
	
	
	"iLeftWidth": null,
	
	
	"sRightWidth": "fixed",
	
	
	"iRightWidth": null,
	
	
	"sHeightMatch": "semiauto"
};





FixedColumns.prototype.CLASS = "FixedColumns";


FixedColumns.VERSION = "2.0.3";




})(jQuery, window, document);
