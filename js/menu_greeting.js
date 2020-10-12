$(function (){
  var welcome = $("#welcome");

  $.ajax({
    type: "GET",
    url: "crud.php?q="+"sn",
    success: function(data) {
      var obj = $.parseJSON(data);

      if (obj["manager"] == 1) {
        welcome.html("Welcome to the Retlaw Manager Hub, ");
      }
      welcome.append(obj["name"]);
    }
  });
});
