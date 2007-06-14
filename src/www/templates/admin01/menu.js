var pointerOn = false;

function showMenuDrop() {
	document.getElementById('menu_drop').style.display='block'; 
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
