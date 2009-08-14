function addMega(){
  $(this).addClass("hovering");
  }

function removeMega(){
  $(this).removeClass("hovering");
  }
var megaConfig = {     
    interval: 100, 
    sensitivity: 20,
    over: addMega,
    timeout: 300,
    out: removeMega
};
