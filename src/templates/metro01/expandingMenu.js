/* This script and many more are available free online at
The JavaScript Source :: http://javascript.internet.com
Created by: Travis Beckham :: http://www.squidfingers.com | http://www.podlob.com
version date: 06/02/03 :: If want to use this code, feel free to do so,
but please leave this message intact. (Travis Beckham) */

// Node Functions

if(!window.Node){
  var Node = {ELEMENT_NODE : 1, TEXT_NODE : 3};
}

function checkNode(node, filter){
  return (filter == null || node.nodeType == Node[filter] || node.nodeName.toUpperCase() == filter.toUpperCase());
}

function getChildren(node, filter){
  var result = new Array();
  if (!node) { return result; }
  var children = node.childNodes;
  for(var i = 0; i < children.length; i++){
    if(checkNode(children[i], filter)) result[result.length] = children[i];
  }
  return result;
}

function getChildrenByElement(node){
  return getChildren(node, "ELEMENT_NODE");
}

function getFirstChild(node, filter){
  var child;
  var children = node.childNodes;
  for(var i = 0; i < children.length; i++){
    child = children[i];
    if(checkNode(child, filter)) return child;
  }
  return null;
}

function getFirstChildByText(node){
  return getFirstChild(node, "TEXT_NODE");
}

function getNextSibling(node, filter){
  for(var sibling = node.nextSibling; sibling != null; sibling = sibling.nextSibling){
    if(checkNode(sibling, filter)) return sibling;
  }
  return null;
}
function getNextSiblingByElement(node){
        return getNextSibling(node, "ELEMENT_NODE");
}

// Menu Functions & Properties

var activeMenu = null;

function showMenu() {
  if(activeMenu){
    activeMenu.className = "";
    getNextSiblingByElement(activeMenu).style.display = "none";
  }
  if(this == activeMenu){
    activeMenu = null;
  } else {
    if (getNextSiblingByElement(this).style.display == "block") {
	    getNextSiblingByElement(this).style.display = "none";
	    this.className = "";
    } else {
	    this.className = "active";
	    getNextSiblingByElement(this).style.display = "block";
            activeMenu = this;
    }
  }
  return false;
}

//added menu Id varaible, initMenu() is called from the jsfx() function 
// from body onload
function initMenu(menuId){
  var menus, menu, text, a, i, anchor;
  menus = getChildrenByElement(document.getElementById(menuId));
  for(i = 0; i < menus.length; i++){
    //ALL LIs have links in them
    // only replace anchors for LIs that have # as href
    // if an LI does nto have a link, then add one
    text = null;
    a = document.createElement("a");
    a.href = "#";
    a.onclick = showMenu;
//a.style.border = '1px solid red;';
    a.onfocus = function(){this.blur()};

    menu = menus[i];
    anchor = getFirstChild(menu, "ELEMENT_NODE");
    if ( anchor.href) { //we found a link
	    if ( anchor.href.substr( (anchor.href.length - 1), 1) =="#") {
		text = anchor;
    		menu.replaceChild(a, text);
    		a.appendChild(getFirstChild(text));
	    }
    } else {
		//no link
        text = getFirstChildByText(menu);
    	menu.replaceChild(a, text);
    	a.appendChild(text);
    }
  }
}

