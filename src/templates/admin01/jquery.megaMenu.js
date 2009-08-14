function addMega(){
  $(this).addClass("hovering");
  }

function removeMega(){
  $(this).removeClass("hovering");
  }
var megaConfig = {     
    interval: 200, 
    sensitivity: 2, 
    over: addMega,
    timeout: 200,
    out: removeMega
};
