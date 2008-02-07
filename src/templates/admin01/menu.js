var pointerOn = false;

function showMenuDrop(left) {
	document.getElementById('menu_drop').style.display='block'; 
	if (left > 0 ) {
		document.getElementById('menu_drop').style.left= (left) + "px";
	}
	pointerOn = true;
}


function closeMenuDrop() {
	pointerOn = false;
	setTimeout("realClose()", 500);
}


function realClose() {
	if (!pointerOn) {
		document.getElementById('menu_drop').style.display='none'; 
	}
}
