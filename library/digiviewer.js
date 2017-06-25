/*
  DigiViewer 0.2 copyright 2007 Joerg Breitbart, brejoe at web.de
  licensed under GNU General Public License

  tested with prototype.js v1.6
 */

// TODO: - smaller version for inline-ajax-load?
//       - special image handling functions, cookies?
//       - globals sections e.g. source path to images
//       - anchor handling: how do better?

// for the next version:
//       - key navigation
//       - internationalisation
//       - flip horizontal/vertical
//       - zoom - fit to window size, original size
//       - fullscreen / back button
//       - one page / two pages view
//       - plugins into separat files, see scriptaculous --> image/css path

// packed with: #>perl jsPacker.pl -i digiviewer.js -e0 -fq > digiviewer_packed.js

var DigiViewer = Class.create({
	initialize: function(id, opt) {
		this.id = id;
		this.opt = {};
		if (opt) this.opt = opt;
		if (this.opt['navbar']!=false) this.dv_navbar_items = new Array();
		if (this.opt['sidebar']!=false) this.dv_sidebar_items = new Array();
		this.pageList = new Array();
		this.page_actual = false;
		this.page_old = false;
		this.dumpedPage = false;
		// inner main div "dv_root"
		this.dv_root = new Element('div', {'id': 'dv_root'});
	},
	// method to add nav bar elements
	addToNavbar: function(elem) {
		if (elem._class == 'navbar_element')
			this.dv_navbar_items.push(elem);
	},
	// method to add side bar elements
	addToSidebar: function(elem) {
		if (elem._class == 'sidebar_element')
			this.dv_sidebar_items.push(elem);
	},
	// method to add page to digiviewer
	addPage: function(elem) {
		if (elem._class == 'page_instance' && this.pageList.indexOf(elem)==-1) {
			this.pageList.push(elem);
		}
	},
	// replace page in digiviewer
	replacePage: function(page_old, page_new) {
		index = this.pageList.indexOf(page_old);
		if (index == -1 || page_old == this.page_actual) return(false);
		this.pageList.splice(index, 1, page_new);
		return(true);
	},
	getActualPage: function() { return(this.page_actual); },
	getOldPage: function() { return(this.page_old) },
	getDumpedPage: function() { return(this.dumpedPage) },
	getActualPageElement: function() { return(this.page_actual.getElement()) },
	getOldPageElement: function() { return(this.page_old.getElement()) },
	// load page into container
	loadPage: function(page, dump) {
		if (!this.page_actual && this.opt['useAnchors']) {
			nerv = false;
			documentTitle = document.title;
			currentAnchor = window.location.hash;
			if (currentAnchor && currentAnchor.indexOf('#DV_') == 0) {
				for (var index = 0; index < this.pageList.length; ++index) {
					if (this.pageList[index].id == currentAnchor.replace('#DV_',''))
						break;
				}
				if (index < this.pageList.length)
					page = this.pageList[index];
			}
		}
		this.page_old = this.page_actual;
		this.page_actual = page;
		if (!dump) this.dumpedPage = this.page_actual;
		this.page_actual.getElement().setStyle('visibility:hidden'); // gwhats wrong here?
		this.dv_page_cont.update(this.page_actual.getElement());
		$('dv_root').fire('dv_root:pageChanged');
		Positioner({'sender': 'new'});
		if (nerv)
// email-bug: was ist hier kaputt an prototype?
//			if (Prototype.Gecko || Prototype.IE || Prototype.Webkit) // IE, FF and Safari handle this right
				document.location.replace(window.location.pathname+'#DV_' + page.id);
//			else // Opera and konqueror stick to this with history update
//				document.location.replace(document.location.pathname + '#DV_' + page.id);
		else nerv = true;
		document.title = documentTitle + ", S. " +  page.pageNum;
	},
	//show page in browser
	_showPage: function() {
		this.page_actual.getElement().setStyle('visibility:visible');
	},
	// put it all together and display it
	show: function() {
		// navigation bar
		if (this.opt['navbar']!=false) {
			var dv_navbar = new Element('div', {'id': 'dv_navbar'});
			this.dv_root.insert(dv_navbar);
			this.dv_navbar_items.each( function(item) { dv_navbar.insert(item.content); });
		}
		// side bar
		if (this.opt['sidebar']!=false) {
			var dv_sidebar = new Element('div', {'id': 'dv_sidebar'});
			this.dv_root.insert(dv_sidebar);
			this.dv_sidebar_items.each( function(item) { dv_sidebar.insert(item.content); });
			var dv_window_cont = new Element('div', {'id': 'dv_window_cont'});
			this.dv_sidebar_items.each( function(item) { dv_window_cont.insert(item.slideWindow); });
			this.dv_root.insert(dv_window_cont);
			DV_shownSideElement = false;
		}
		// page container
		this.dv_page_cont = new Element('div', {'id': 'dv_page_cont'});
		this.dv_root.insert(this.dv_page_cont);
		var drag = new MyDrag(this.dv_page_cont);

		// hook it into the DOM tree, attach event listener
		$(this.id).update(this.dv_root);
		this.dv_root.observe('page:ready', this._showPage.bindAsEventListener(this) );
	}
//	loadAnchor: function() {
//		currentAnchor = window.location.hash;
//		this.loadPage(this.pageList[currentAnchor.replace('#DV_','')]);
//		document.title =  window.location.hash;
//	},
//	anchorRecover: function(time) {
//		var that = this;
//		currentUrlAnchor = window.location.hash;
//		window.setInterval(function(){ that._observeAnchor(); }, time);
//	},
//	_observeAnchor: function() {
//		alert(window.location.hash);
//		window.focus();
//		if (currentUrlAnchor != window.location.hash) {
//			alert("anker gewechselt");
//			viewer.loadAnchor();
//			currentUrlAnchor = window.location.hash;
//		}
//	}
});

// NavbarElement - class for nav bar elements
var NavbarElement = Class.create({
	initialize: function(content) {
		// name this as a navbar_element
		this._class = 'navbar_element';
		// create a container div class="sep" around element
		container_div = new Element('div', {'class': 'sep_left'});
		if (content) { container_div.insert(content); }
		// set element's content to display further
		this.content = container_div;
	},
	alignRight: function() {
		this.content.writeAttribute('class', 'sep_right');
	},
	alignLeft: function() {
		this.content.writeAttribute('class', 'sep_left');
	}
});

// SidebarElement - class for side bar elements
var SidebarElement = Class.create({
	initialize: function(opts) {
		if (!opts['id'])
			return;
		this.id = opts['id'];
		if (!opts['iconSource'])
			return;
		this.iconSource = opts['iconSource'];
		if (!opts['slideWidth'])
			return;
		this.slideWidth = opts['slideWidth'];

		this._class = 'sidebar_element';
		container_link = new Element('a', {'id': this.id, 'class': 'link_side', 'onclick': 'return false;', 'href': 'javascript:function(){return false;}'});
		if (opts['title'])
			container_link.writeAttribute('title', opts['title']);
		this.bar_icon = new Element('img', {'src': this.iconSource, 'style': 'border:none'});
		container_link.insert(this.bar_icon);
		
		this.slideWindow = new Element('div', {'id': this.id + '_slideWindow', 'class': 'slideWindow'});
		this.slideWindow.setStyle('display:none;width:' + this.slideWidth + 'px');

		if (opts['content']) { this.slideWindow.insert(opts['content']); };
		this.content = container_link;

		container_link.observe('click', this.toggleWindow.bindAsEventListener(this) );
		this.stayActive = false;
		this.firstrun = true;
	},
	toggleWindow: function() {
		if (this.slideWindow.style.display == 'none') {
			this.slideWindow.setStyle('display:block');
			this.content.setStyle('background: #ccc');
			this.stayActive = true;
			DV_shownSideElement = this.slideWindow;
		}
		else {
			this.slideWindow.setStyle('display:none');
			this.content.setStyle('background: #fff');
			DV_shownSideElement = false;
		};
		if (this.firstrun) {
			$('dv_sidebar').observe('sidebar:closeWindow', this.closeWindow.bindAsEventListener(this) );
			this.firstrun = false;
		}
		$('dv_sidebar').fire('sidebar:closeWindow');
	},
	closeWindow: function() {
		if (this.stayActive) this.stayActive = false;
		else if (this.slideWindow.style.display == 'block') {
			this.slideWindow.setStyle('display:none');
			this.content.setStyle('background:#fff');
		}
	}
});

// Page - page representing class
// TODO: support for tiled images
var Page = Class.create({
	initialize: function(id, opt) {
		this._class = 'page_instance';
		this.id = id;
		this.opt = opt;
		this.href = '#';
		if (this.opt['href']) this.href = this.opt['href'];
		this.width = 0;
		if (this.opt['width']) this.width = this.opt['width'];
		this.height = 0;
		if (this.opt['height']) this.height = this.opt['height'];
		this.pageNum = '';
		if (this.opt['pageNum']) this.pageNum = this.opt['pageNum'];
		this.element = false;
	},
	getElement: function() {
		if (!this.element) {
			this.element = new Element('img',{'id': this.id, 'src': this.opt['href'], 'class': 'img_page'});
			this.element.setStyle('height:'+this.opt['height']+'px;width:'+this.opt['width']+'px;visibility:hidden;vertical-align:bottom');
		};
		return(this.element);
	}
});

///////////////////////////
// Some helper functions //
///////////////////////////

// Positioner - function to put page in viewer to a proper position
function Positioner(args) {
	var winSize = $('dv_root').getDimensions();
	var sidebarWidth = 0;
	if ($('dv_sidebar')) {
		if (DV_shownSideElement)
			sidebarWidth = $('dv_sidebar').getWidth() + DV_shownSideElement.getWidth();
		else
			sidebarWidth = $('dv_sidebar').getWidth();
	}
	var navbarHeight = 0;
	if ($('dv_navbar'))
		navbarHeight = $('dv_navbar').getHeight();
	var elem = $('dv_page_cont');
	var elemPos = findPos(elem);
	var w = elem.getWidth();
	var h = elem.getHeight();
	// for a new page
	if (args['sender']=='new') {
		if (w < winSize['width'] - sidebarWidth)
			elem.style.left = ((winSize['width'] + sidebarWidth - w) / 2) + 'px';
		else
			elem.style.left = sidebarWidth + 'px';
		if (h < winSize['height'] - navbarHeight)
			elem.style.top = ((winSize['height'] + navbarHeight - h) / 2) + 'px';
		else
			elem.style.top = navbarHeight + 'px';
	};
	// mouse wheel + panel zoom
	if (args['sender']=='wheel' || args['sender']=='panel') {
		var new_left = (winSize['width']/2 - sidebarWidth/2 - w/args['old_width'] * (winSize['width']/2 - sidebarWidth/2 - elemPos['left']));
		var new_top = (winSize['height']/2 - navbarHeight/2 - h/args['old_height'] * (winSize['height']/2 - navbarHeight/2 - elemPos['top']));
		// correct false positioning
		if (w < winSize['width']) {
			if (new_left < sidebarWidth)
				elem.style.left = sidebarWidth + 'px';
			else if (new_left + w > winSize['width'])
				elem.style.left = (winSize['width'] - w) + 'px';
			else
				elem.style.left = new_left + 'px';
		 } else {
			if (new_left < winSize['width'] - w)
				elem.style.left = (winSize['width'] - w) + 'px';
			else if (new_left > sidebarWidth)
				elem.style.left = sidebarWidth + 'px';
			else
				elem.style.left = new_left + 'px';
		};
		if (h < winSize['height']) {
			if (new_top < navbarHeight)
				elem.style.top = navbarHeight + 'px';
			else if (new_top + h > winSize['height'])
				elem.style.top = (winSize['height'] - h) + 'px';
			else
				elem.style.top = new_top + 'px';
		} else {
			if (new_top < winSize['height'] - h)
				elem.style.top = (winSize['height'] - h) + 'px';
			else if (new_top > navbarHeight)
				elem.style.top = navbarHeight + 'px';
			else
				elem.style.top = new_top + 'px';
		};
	};
	// fire event to show the page
	$('dv_root').fire("page:ready");
};

// position of obj
function findPos(obj) {
	return {'left': obj.offsetLeft, 'top': obj.offsetTop};
};

// MyDrag - from scratch written drag class to fullfill
//          specific drag container behavior
var MyDrag = Class.create({
	initialize: function(elem) {
		this.mouse_down = false;
		this.elem = elem;
		this.elem.makePositioned();
		this.mouseDown = this._mouseDown.bindAsEventListener(this);
		this.mouseMove = this._mouseMove.bindAsEventListener(this);
		this.mouseUp = this._mouseUp.bindAsEventListener(this);
		Event.observe(elem, 'mousedown', this.mouseDown);
		Event.observe(elem, 'dragstart', function (event) {Event.stop(event);} );
	},
	_mouseDown: function(event) {
		this.mouse_down = true;
		Event.observe(document, 'mousemove', this.mouseMove);
		Event.observe(document, 'mouseup', this.mouseUp);
		var elemPos = findPos(this.elem);
		this.subX = Event.pointerX(event) - elemPos['left'];
		this.subY = Event.pointerY(event) - elemPos['top'];
		this.elem.setStyle('cursor:move');
		this.sidebarWidth = 0;
		if ($('dv_sidebar')) {
			if (DV_shownSideElement)
				this.sidebarWidth = $('dv_sidebar').getWidth() + DV_shownSideElement.getWidth();
			else
				this.sidebarWidth = $('dv_sidebar').getWidth();
		}
		this.navbarHeight = 0;
		if ($('dv_navbar'))
			this.navbarHeight = $('dv_navbar').getHeight();
		Event.stop(event);
	},
	_mouseUp: function(event) {
		if (this.mouse_down) {
			this.mouse_down = false;
			Event.stopObserving(document, 'mousemove', this.mouseMove);
			Event.stopObserving(document, 'mouseup', this.mouseUp);
			this.elem.setStyle('cursor:pointer');
		}
	},
	_mouseMove: function(event) {
		if (this.mouse_down) {
			var winSize = $('dv_root').getDimensions();
			var elemPos = findPos(this.elem);
			var w = this.elem.getWidth();
			var h = this.elem.getHeight();
			// width is smaller than window
			if (w < winSize['width'] - this.sidebarWidth) {
				if (Event.pointerX(event) - this.subX < this.sidebarWidth)
					this.elem.style.left = this.sidebarWidth + 'px';
				else
					if (Event.pointerX(event) - this.subX + w > winSize['width'])
						this.elem.style.left = (winSize['width'] - w) + 'px';
					else
						this.elem.style.left = (Event.pointerX(event) - this.subX) + 'px';
			// width is bigger than window
			} else {
				var w_oversize = w - winSize['width'] + this.sidebarWidth;
				if (Event.pointerX(event) - this.subX < 0 - w_oversize + this.sidebarWidth)
					this.elem.style.left = (0 - w_oversize + this.sidebarWidth) + 'px';
				else
					if (Event.pointerX(event) - this.subX > this.sidebarWidth)
						this.elem.style.left = this.sidebarWidth + 'px';
					else
						this.elem.style.left = (Event.pointerX(event) - this.subX) + 'px';
			};
			// height is smaller than window height
			if (h < winSize['height'] - this.navbarHeight) {
				if (Event.pointerY(event) - this.subY < this.navbarHeight)
					this.elem.style.top = this.navbarHeight + 'px';
				else
					if (Event.pointerY(event) - this.subY + h > winSize['height'])
						this.elem.style.top = (winSize['height'] - h) + 'px';
					else
						this.elem.style.top = (Event.pointerY(event) - this.subY) + 'px';
			// height is bigger than window
			} else {
				var h_oversize = h - winSize['height'] + this.navbarHeight;
				if (Event.pointerY(event) - this.subY < 0 - h_oversize + this.navbarHeight)
					this.elem.style.top = (0 - h_oversize + this.navbarHeight) + 'px';
				else
					if (Event.pointerY(event) - this.subY > this.navbarHeight)
						this.elem.style.top = this.navbarHeight + 'px';
					else
						this.elem.style.top = (Event.pointerY(event) - this.subY) + 'px';
			};
		}
	}
});


////////////////////////////////////////
// predefined elements for DigiViewer //
////////////////////////////////////////
//
// These are some predefined elements with often used functionality
// within a document viewer. They are all implemented as plugins for
// the navbar or the sidebar and do not depend on each other. Without
// some of them the viewer will be pretty much useless. If you are
// not satisfied with their functional range, feel free to change them
// or write your very own plugin for your needs.

//// Navbar plugins

// DV_Pager() - pager for the navbar
var DV_Pager = Class.create(NavbarElement, {
	initialize: function($super) {
		$super();
		this.content.insert('<div id="dv_pager"><a id="pager_link_first" onclick="return(false)" href="#"><img id="pager_img_first" src="system/modules/digiviewer/library/images/first.gif" alt="|&lt;" title="First" /></a><a id="pager_link_prev" onclick="return(false)" href="#"><img id="pager_img_prev" src="system/modules/digiviewer/library/images/previous.gif" alt="&lt;" title="Previous" /></a><span id="pager_pagina" title="aktuelle Seite">###</span><a id="pager_link_next" onclick="return(false)" href="#"><img id="pager_img_next" src="system/modules/digiviewer/library/images/next.gif" alt="&gt;" title="Next" /></a><a id="pager_link_last" onclick="return(false)" href="#"><img id="pager_img_last" src="system/modules/digiviewer/library/images/last.gif" alt="&gt;|" title="Last" /></a></div>');
	},
	firstPage: function() {
		this.viewer_obj.loadPage(this.viewer_obj.pageList[0]);
	},
	previousPage: function() {
		index = this.viewer_obj.pageList.indexOf(this.viewer_obj.getDumpedPage());
		if (index < 1) return;
		this.viewer_obj.loadPage(this.viewer_obj.pageList[index-1]);
	},
	nextPage: function() {
		index = this.viewer_obj.pageList.indexOf(this.viewer_obj.getDumpedPage());
		if (index == -1) return;
		this.viewer_obj.loadPage(this.viewer_obj.pageList[index+1]);
	},
	lastPage: function() {
		this.viewer_obj.loadPage(this.viewer_obj.pageList[this.viewer_obj.pageList.length-1]);
	},
	_adjustView: function() {
		$('pager_pagina').innerHTML = this.viewer_obj.getDumpedPage().pageNum;
		index = this.viewer_obj.pageList.indexOf(this.viewer_obj.getDumpedPage());
		if (index == -1) {
			$('pager_link_first').setStyle('visibility:hidden');
			$('pager_link_prev').setStyle('visibility:hidden');
			$('pager_link_last').setStyle('visibility:hidden');
			$('pager_link_next').setStyle('visibility:hidden');
			$('pager_pagina').innerHTML = this.viewer_obj.getDumpedPage().pageNum;
			return;
		}
		$('pager_link_first').setStyle('visibility:visible');
		$('pager_link_prev').setStyle('visibility:visible');
		$('pager_link_last').setStyle('visibility:visible');
		$('pager_link_next').setStyle('visibility:visible');
		$('pager_link_first').href = window.location.pathname+'#DV_' + this.viewer_obj.pageList[0].id;
		if (index > 0) $('pager_link_prev').href = window.location.pathname+'#DV_' + this.viewer_obj.pageList[index-1].id;
		if (index < this.viewer_obj.pageList.length-1) $('pager_link_next').href = window.location.pathname+'#DV_' + this.viewer_obj.pageList[index+1].id;
		$('pager_link_last').href = window.location.pathname+'#DV_' + this.viewer_obj.pageList[this.viewer_obj.pageList.length-1].id;
		if (index == 0) {
			$('pager_link_first').setStyle('visibility:hidden');
			$('pager_link_prev').setStyle('visibility:hidden');
		}
		else if (index == this.viewer_obj.pageList.length-1) {
			$('pager_link_last').setStyle('visibility:hidden');
			$('pager_link_next').setStyle('visibility:hidden');
		}
	},
	connect: function(obj) {
		this.viewer_obj = obj;
		$('pager_link_first').observe('click', this.firstPage.bindAsEventListener(this) );
		$('pager_link_prev').observe('click', this.previousPage.bindAsEventListener(this) );
		$('pager_link_next').observe('click', this.nextPage.bindAsEventListener(this) );
		$('pager_link_last').observe('click', this.lastPage.bindAsEventListener(this) );
		$('dv_root').observe('dv_root:pageChanged', this._adjustView.bindAsEventListener(this) );
	}
});

// DV_Zoomer() - zoom functionality in navbar
// TODO: fit page to window height/width
var DV_Zoomer = Class.create(NavbarElement, {
	initialize: function($super, opts) {
		$super();
		this._name = 'DV_Zoomer';
		this.opts = {};
		if (opts) this.opts = opts;
		this.steps = [50, 100, 200];
		if (this.opts['steps']) this.steps = this.opts['steps'];
		this.resett = true;
		if (this.opts['reset']==false) this.resett = false;
		this.zoom_actual = -1;
		option_list = '';
		this.steps.each( function(item) {option_list = option_list + '<option value="' + item + '">' + item + '%</option>'} );
		this.content.insert('<div id="dv_zoomer"><a id="zoomer_link_zoomOut" onclick="return(false)" href="javascript:function(){return false;}"><img src="system/modules/digiviewer/library/images/zoom-out.gif" alt="minus" title="zoom out" /></a><select id="zoomer_select" onchange="">' + option_list + '</select><a id="zoomer_link_zoomIn" onclick="return(false)" href="javascript:function(){return false;}"><img src="system/modules/digiviewer/library/images/zoom-in.gif" alt="plus" title="zoom in" /></a></div>');
	},
	_doZoom: function(args) {
		pageObj = this.viewer_obj.getActualPage();
		pageElem = this.viewer_obj.getActualPageElement();
		args['old_width'] = pageElem.getWidth();
		args['old_height'] = pageElem.getHeight();
		pageElem.setStyle('visibility:hidden');
		pageElem.setStyle('width:' + pageObj.width*this.steps[this.zoom_actual]/100 + 'px;height:' + pageObj.height*this.steps[this.zoom_actual]/100 + 'px');
		Positioner(args);
	},
	_zoomChange: function() {
		this.zoom_actual = $('zoomer_select').selectedIndex;
		this._doZoom({'sender': 'panel'});
	},
	zoomIn: function(sender_args) {
		if (this.zoom_actual>-1 && this.zoom_actual<this.steps.size()-1) {
			this.zoom_actual = this.zoom_actual + 1;
			$('zoomer_select').selectedIndex = this.zoom_actual;
			if (sender_args['sender'] != 'wheel') sender_args = {'sender': 'panel'};
			this._doZoom(sender_args);
		}
	},
	zoomOut: function(sender_args) {
		if (this.zoom_actual>0 && this.zoom_actual<this.steps.size()) {
			this.zoom_actual = this.zoom_actual - 1;
			$('zoomer_select').selectedIndex = this.zoom_actual;
			if (sender_args['sender'] != 'wheel') sender_args = {'sender': 'panel'};
			this._doZoom(sender_args);
		}
	},
	// mouse wheel event handler
	_wheelHandle: function(event) {
		var delta = 0;
		if (!event) { event = window.event };
		// FF returns false koordinates on wheel event
		if (Prototype.Browser.Gecko) {
			var posX = event.screenX;
			var posY = event.screenY;
		} else {
			var posX = Event.pointerX(event);
			var posY = Event.pointerY(event);
		}
		if (event.wheelDelta) {
			delta = event.wheelDelta/120;
		} else {
			if (event.detail) {
				delta = -event.detail/3;
			}
		}
		if (delta) {
			if (delta < 0) {
				this.zoomOut( {'sender': 'wheel', 'pointerX': posX, 'pointerY': posY} );
			} else {
				this.zoomIn( {'sender': 'wheel', 'pointerX': posX, 'pointerY': posY} );
			}
		}
		if (event.preventDefault) { event.preventDefault() };
		event.returnValue = false;
	},
	_startupZoom: function() {
		// initial zoom setting on start / new page
		if (this.zoom_actual == -1 || this.resett) {
			if (this.opts['standard'] && this.steps.indexOf(this.opts['standard'])!=-1) {
				$('zoomer_select').selectedIndex = this.steps.indexOf(this.opts['standard']);
			} else {
				if (this.steps.indexOf(100)!=-1) {
					$('zoomer_select').selectedIndex = this.steps.indexOf(100);
				} else {
					$('zoomer_select').selectedIndex = 0;
				}
			}
			this.zoom_actual = $('zoomer_select').selectedIndex;
		};
		this._doZoom({'sender': 'panel'});
	},
	connect: function(obj) {
		this.viewer_obj = obj;
		$('dv_root').observe('dv_root:pageChanged', this._startupZoom.bindAsEventListener(this) );
		$('zoomer_select').observe('change', this._zoomChange.bindAsEventListener(this) );
		$('zoomer_link_zoomIn').observe('click', this.zoomIn.bindAsEventListener(this) );
		$('zoomer_link_zoomOut').observe('click', this.zoomOut.bindAsEventListener(this) );
		if (this.opts['wheelZoom']) {
			this.boundWheelHandle = this._wheelHandle.bindAsEventListener(this);
			$('dv_page_cont').observe('mousewheel', this.boundWheelHandle);
			$('dv_page_cont').observe('DOMMouseScroll', this.boundWheelHandle);
		}
	}
});

// DV_Rotator() - rotate images control in navbar (0/90/180/270°)
var DV_Rotator = Class.create(NavbarElement, {
	initialize: function($super, opts) {
		$super();
		this.turn = 0;
		this.rotationsArray = new Array();
		this.content.insert('<div id="dv_rotator"><a id="rotator_link_left" onclick="return(false)" href="javascript:function(){return false;}"><img src="system/modules/digiviewer/library/images/object-rotate-left.png" alt="links" title="turn left" /></a><img style="" src="system/modules/digiviewer/library/images/doc.gif" alt="" title="turn page" /><a id="rotator_link_right" onclick="return(false)" href="javascript:function(){return false;}"><img src="system/modules/digiviewer/library/images/object-rotate-right.png" alt="rechts" title="turn right" /></a></div>');
	},
	_restorePage: function() {
		this.turn = 0;
	},
	addPageRotations: function(page, rotations) {
		page.rotations = {};
		for (var index = 0; index < rotations.length; ++index) {
			if (rotations[index]!=false && rotations[index]._class == 'page_instance')
				page.rotations[rotations[index].id] = page.id + "_" + rotations[index].id;
			this.rotationsArray[page.id + "_" + rotations[index].id] = rotations[index];
		}
	}, // this is hacky code
	rotateLeft: function() {
		if (this.viewer_obj.getActualPage().rotations)
			if (this.turn == 0) {
				this.viewer_obj.dumpedPage = this.viewer_obj.getActualPage();
				if (this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 270]) {
					this.viewer_obj.loadPage(this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 270], true);
					this.turn = 270;
					Positioner({'sender': 'new'});
				} else if (this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 180]) {
						this.viewer_obj.loadPage(this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 180], true);
						Positioner({'sender': 'new'});
						this.turn = 180;
					} else if (this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 90]) {
							this.viewer_obj.loadPage(this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 90], true);
							Positioner({'sender': 'new'});
							this.turn = 90;
						};
			return;
			};
			if (this.turn == 90) {
				this.viewer_obj.loadPage(this.viewer_obj.dumpedPage, true);
				this.turn = 0;
				Positioner({'sender': 'new'});
			return;
			};
			if (this.turn == 180) {
				if (this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 90]) {
					this.viewer_obj.loadPage(this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 90], true);
					this.turn = 90;
					Positioner({'sender': 'new'});
				} else {
					this.viewer_obj.loadPage(this.viewer_obj.dumpedPage, true);
					this.turn = 0;
					Positioner({'sender': 'new'});
				};
			return;
			};
			if (this.turn == 270) {
				if (this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 180]) {
					this.viewer_obj.loadPage(this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 180], true);
					this.turn = 180;
					Positioner({'sender': 'new'});
				} else if (this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 90]) {
						this.viewer_obj.loadPage(this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 90], true);
						Positioner({'sender': 'new'});
						this.turn = 90;
					} else {
						this.viewer_obj.loadPage(this.viewer_obj.dumpedPage, true);
						this.turn = 0;
						Positioner({'sender': 'new'});
					};
			};
	},
	rotateRight: function() {
		if (this.viewer_obj.getActualPage().rotations)
			if (this.turn == 0) {
				this.viewer_obj.dumpedPage = this.viewer_obj.getActualPage();
				if (this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 90]) {
					this.viewer_obj.loadPage(this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 90], true);
					this.turn = 90;
					Positioner({'sender': 'new'});
				} else if (this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 180]) {
						this.viewer_obj.loadPage(this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 180], true);
						Positioner({'sender': 'new'});
						this.turn = 180;
					} else if (this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 270]) {
							this.viewer_obj.loadPage(this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 270], true);
							Positioner({'sender': 'new'});
							this.turn = 270;
						};
			return;
			};
			if (this.turn == 270) {
				this.viewer_obj.loadPage(this.viewer_obj.dumpedPage, true);
				this.turn = 0;
				Positioner({'sender': 'new'});
			return;
			};
			if (this.turn == 180) {
				if (this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 270]) {
					this.viewer_obj.loadPage(this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 270], true);
					this.turn = 270;
					Positioner({'sender': 'new'});
				} else {
					this.viewer_obj.loadPage(this.viewer_obj.dumpedPage, true);
					this.turn = 0;
					Positioner({'sender': 'new'});
				};
			return;
			};
			if (this.turn == 90) {
				if (this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 180]) {
					this.viewer_obj.loadPage(this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 180], true);
					this.turn = 180;
					Positioner({'sender': 'new'});
				} else if (this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 270]) {
						this.viewer_obj.loadPage(this.rotationsArray[this.viewer_obj.getDumpedPage().id + "_" + 270], true);
						Positioner({'sender': 'new'});
						this.turn = 270;
					} else {
						this.viewer_obj.loadPage(this.viewer_obj.dumpedPage, true);
						this.turn = 0;
						Positioner({'sender': 'new'});
					};
			};
	},
	connect: function(obj) {
		this.viewer_obj = obj;
		$('dv_root').observe('dv_root:pageChanged', this._restorePage.bindAsEventListener(this) );
		$('rotator_link_left').observe('click', this.rotateLeft.bindAsEventListener(this) );
		$('rotator_link_right').observe('click', this.rotateRight.bindAsEventListener(this) );
	}
});

// DV_NavSeparatorSpace() - vertical separator space in navbar
function DV_NavSeparatorSpace(width) {
	if (!width) { width=15; }
	var sep_space = new NavbarElement('<div style="width:' + width + 'px"></div>');
	return(sep_space);
};

// DV_NavSeparatorLine() - vertical separator line in navbar
function DV_NavSeparatorLine(image) {
	if (image) {
		var sep_line = new NavbarElement('<div>' + image + '</div>');
	} else {
		var sep_line = new NavbarElement('<div class="line"></div>');
	}
	return(sep_line);
};

//// Sidebar plugins

// DV_Thumbs() - thumb view in sidebar
var DV_Thumbs = Class.create(SidebarElement, {
	initialize: function($super, slideWidth) {
		$super({'id':'dv_thumb', 'iconSource': 'system/modules/digiviewer/library/images/image.gif', 'slideWidth': slideWidth, 'title': 'Preview'});
		this.slideWindow.setStyle('visibility:hidden;display:block');
		this.thumbArray = new Array();
		this.selfCalled = false;
		this.thumbActive = false;
	},
	toggleWindow: function($super) {
		if (this.slideWindow.style.visibility == 'hidden') {
			this.slideWindow.setStyle('visibility:visible');
			this.content.setStyle('background: #ccc');
			this.stayActive = true;
			DV_shownSideElement = this.slideWindow;
		} else {
			this.slideWindow.setStyle('visibility:hidden');
			this.content.setStyle('background: #fff');
			DV_shownSideElement = false;
		}
		if (this.firstrun) {
			$('dv_sidebar').observe('sidebar:closeWindow', this.closeWindow.bindAsEventListener(this) );
			this.firstrun = false;
		}
		$('dv_sidebar').fire('sidebar:closeWindow');
	},
	closeWindow: function($super) {
		if (this.slideWindow.style.visibility == 'visible')
			this.slideWindow.setStyle('visibility:hidden;display:block');
		else this.slideWindow.setStyle('visibility:visible;display:none');
		$super();
		if (this.slideWindow.style.display == 'block') this.slideWindow.setStyle('visibility:visible');
		else this.slideWindow.setStyle('visibility:hidden;display:block');
	},
	addThumb: function(page, src) {
		if (page._class == 'page_instance' && src) {
			this.slideWindow.insert('<a id="thumb_' + page.id + '" class="dv_thumb_link" onclick="return(false)" href="'+window.location.pathname+'#DV_' + page.id + '"><img src="' + src + '" /><span><br />' + page.pageNum + '</span></a>');
			this.thumbArray['thumb_' + page.id] = page;
			}
	},
	_callPage: function(event) {
		elem = Event.element(event);
		if (elem.tagName.toLowerCase() != 'a')
			if (elem.parentNode.tagName.toLowerCase() != 'a') return;
			else elem = elem.parentNode;
		this.selfCalled = true;
		this.viewer_obj.loadPage(this.thumbArray[elem.id]);
	},
	scrollSlide: function() {
		if (this.thumbActive)
			this.thumbActive.setStyle('background:#ddd');
		this.thumbActive = $('thumb_' + this.viewer_obj.getDumpedPage().id);
		this.thumbActive.setStyle('background:#b4cff5');
		if (this.selfCalled) this.selfCalled = false;
		else {
			pos = findPos( this.thumbActive )['top'] - $('dv_thumb_slideWindow').getHeight()/3;
			$('dv_thumb_slideWindow').scrollTop = pos;
		}
	},
	connect: function(obj) {
		this.viewer_obj = obj;
		$('dv_thumb_slideWindow').observe('click', this._callPage.bindAsEventListener(this) );
		$('dv_root').observe('dv_root:pageChanged', this.scrollSlide.bindAsEventListener(this) );
	}
});

// DV_Settings() - settings in sidebar
//TODO: abstract the slideWindow content
function DV_Help() {
	var helper = new SidebarElement({'id': 'dv_settings', 'iconSource': 'images/help.gif', 'slideWidth': 250, 'title': 'Help'});
	helper.slideWindow.insert('<div class="testdiv"><h2>Help</h2><p><span style="font-weight:bold">Your help goes here.</p></div>');
	return(helper);
};
