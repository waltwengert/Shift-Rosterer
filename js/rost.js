var btns = [];
var starts = [];
var ends = [];
var areas = [];

$(function (){
  var table = $("#roster_table");

  $.ajax({
    type: "GET",
    url: "crud.php?q="+"rt",
    success: function(data) {
      var obj = $.parseJSON(data);

      if (!obj["manager"]) {
        //if the session's employee is not a manager, remove the look up form
        $("#roster_lookup").remove();
      }

      table.append(obj["tbl"]);
      btns = obj["btn"];
      starts = obj["starts"];
      ends = obj["ends"];
      areas = obj["areas"];
    }
  });
});

$("#roster_lookup_btn").on("click", function() {
  var emp_id = $("#roster_lookup_field").val();
  var table = $("#roster_table");

  if (emp_id.length == 7 && $.isNumeric(emp_id)) {
    //the input is valid, as in is a 7 character numeric string (but may not be an existing emp_id)
    //perform a POST request to retrieve roster for given employee ID
    $.ajax({
      type: "POST",
      url: "crud.php?q="+"rtb",
      data: {user_id: emp_id},
      success: function(data) {
        var obj = $.parseJSON(data);

        if (obj["tbl"]) {
          //data was returned meaning the employee ID was valid and existed
          //clear the table of the old roster and add the new one (re-add table headings)
          //redo shift details/buttons arrays for the new employee's roster
          table.empty()
          table.append("<tr><th>Mon</th><th>Tues</th><th>Wed</th><th>Thurs</th><th>Fri</th><th>Sat</th><th>Sun</th></tr>");
          table.append(obj["tbl"]);
          btns = obj["btn"];
          starts = obj["starts"];
          ends = obj["ends"];
          areas = obj["areas"];
        } else {
          //the data returned was empty and so the employee ID does not exist, display error
          alert("Employee ID does not exist");
        }
      }
    });
  } else {
    //display error for invalid employee ID format
    alert("Invalid Employee ID");
  }
});

$("#roster_reset").on("click", function() {
  var table = $("#roster_table");

  $.ajax({
    type: "GET",
    url: "crud.php?q="+"rt",
    success: function(data) {
      var obj = $.parseJSON(data);

      if (!obj["manager"]) {
        //if the session's employee is not a manager, remove the look up form
        $("#roster_lookup").remove();
      }

      table.empty();
      table.append("<tr><th>Mon</th><th>Tues</th><th>Wed</th><th>Thurs</th><th>Fri</th><th>Sat</th><th>Sun</th></tr>");
      table.append(obj["tbl"]);
      btns = obj["btn"];
      starts = obj["starts"];
      ends = obj["ends"];
      areas = obj["areas"];
    }
  });
});

$("#roster_table").on("click", "td", function(e) {
  var col = $(this).parent().children().index($(this));
  var row = $(this).parent().parent().children().index($(this).parent());
  var cellIndex = [col, row-1];

  for (var i = 0; i < btns.length; i++) {
    if (btns[i][0] == cellIndex[0] && btns[i][1] == cellIndex[1]) {
      var area = "";
      if (areas[i] == "S") {
        area = "Service Desk";
      } else if (areas[i] == "R") {
        area = "Registers";
      } else if (areas[i] == "H") {
        area = "Home";
      } else if (areas[i] == "C") {
        area = "Clothing";
      } else if (areas[i] == "B") {
        area = "Backdock";
      }

      alert("Shift Details\n\nStart Time: "+starts[i]+"\n"+"Length: "+ends[i]+" hours\n"+"Area: "+area);
    }
  }
});
