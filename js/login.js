$("#login_btn").on("click", function() {
  var emp_id = $("#login_id").val();
  var emp_pass = $("#login_pass").val();
  var error = $("#login_error");

  $.ajax({
    type: "POST",
    url: "authenticate.php",
    data: {id: emp_id,
          password: emp_pass},
    success: function(data) {
      error.css("color", "red");
      if (data == "true") {
        error.append(data);
      } else {
        window.location.replace("menu.html");
      }
    }
  });
});
