var selected_row = -1;

var starts = [];
var ends = [];

var selected_start = null;
var selected_end = null;
var update_row = -1;
var manager_view = false;

$(function (){
  var $table = $("#avail_table");

  $.ajax({
    type: "GET",
    url: "crud.php?q="+"as",
    success: function(data) {
      var obj = $.parseJSON(data);

      if (!obj["manager"]) {
        //if the session's employee is not a manager, remove the look up form
        $("#avails_lookup").remove();
      }

      $table.append(obj["tbl"]);
      starts = obj["starts"];
      ends = obj["ends"];
    }
  });
});

$("#avails_lookup_btn").on("click", function() {
  //perform a GET request to retrieve availabilities for given employee ID
  //set the current employee ID (not in $_SESSION, in avail.js) to given employee ID for future updates
  var emp_id = $("#avails_lookup_field").val();
  var table = $("#avail_table");

  if (emp_id.length == 7 && $.isNumeric(emp_id)) {
    //the input is valid, as in is a 7 character numeric string (but may not be an existing emp_id)
    //perform a POST request to retrieve availabilities for given employee ID
    $.ajax({
      type: "POST",
      url: "crud.php?q="+"asb",
      data: {user_id: emp_id},
      success: function(data) {
        var obj = $.parseJSON(data);

        if (obj["tbl"]) {
          //data was returned meaning the employee ID was valid and existed
          //clear the table of the old availabilities and add the new ones
          //redo starts/ends arrays for the new employee's roster
          table.empty();
          table.append(obj["tbl"]);
          starts = obj["starts"];
          ends = obj["ends"];
          manager_view = true;
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

$("#avails_reset").on("click", function() {
  refreshTable();
});

$("#avail_table").on("click", "tr", function(e) {
    if ($(e.currentTarget).index() > 0) {
      jQuery(".highlight").removeClass("highlight");
      jQuery(e.currentTarget).addClass("highlight");

      selected_row = $(e.currentTarget).index();
    }
});

$("#avail_table").on("click", "td", function(e) {
  //when a cell is clicked
  var col = $(this).parent().children().index($(this)) - 1;
  var row = $(this).parent().parent().children().index($(this).parent()) - 1;
  var cellIndex = [col, row];

  if (manager_view) {
    alert("In manager view availabilities cannot be updated");
  } else if (update_row != row && (selected_start || selected_end)) {
    //the user has selected a different row, reset update_row, deselect start/end cells
    refreshTable();

    update_row = -1;
    selected_start = null;
    selected_end = null;
  } else if (isStart(cellIndex)) {
    //start cell clicked
    if (selected_end) {
      //remove availabilities for this day
      removeAvails(row);
    } else if (!selected_start) {
      //select the start cell
      e.currentTarget.style.backgroundColor = "yellow";
      update_row = row;
      selected_start = cellIndex;
    } else {
      //deselect the start cell
      e.currentTarget.style.backgroundColor = "green";
      update_row = -1;
      selected_start = null;
    }
  } else if (isEnd(cellIndex)) {
    //end cell clicked
    if (selected_start) {
      //remove availabilities for this day
      removeAvails(row);
    } else if (!selected_end) {
      //select the end cell
      e.currentTarget.style.backgroundColor = "yellow";
      update_row = row;
      selected_end = cellIndex;
    } else {
      //deselect the end cell
      e.currentTarget.style.backgroundColor = "green";
      selected_end = null;
    }
  } else if (selected_start) {
    newStart(row, col);
  } else if (selected_end) {
    newEnd(row, col);
  } else if (noStart(row)) {
    //a row with no start availability has been selected, add some
    var days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
    addDefaults(row);
    alert("New default availabilities added to "+days[row]);
  }
});

function addDefaults(day) {
  //a day with no availabilities has been selected, add default availabilities to it
  $.ajax({
    type: "POST",
    url: "crud.php?q="+"ad",
    data: {day:day},
    success: function(data) {
      refreshTable();

      update_row = -1;
      selected_start = null;
      selected_end = null;
    },
    error: function() {
      alert("Error Updating Availabilities");
    }
  });
}

function removeAvails(day) {
  //perform a post request to remove availabilities for given day(int) from the database
  $.ajax({
    type: "POST",
    url: "crud.php?q="+"ra",
    data: {day:day},
    success: function(data) {
      refreshTable();

      update_row = -1;
      selected_start = null;
      selected_end = null;
    },
    error: function() {
      alert("Error Updating Availabilities");
    }
  });
}

function newStart(day, time) {
  //perform a post request to update the start time for given day(int) and time(int) in the database
  $.ajax({
    type: "POST",
    url: "crud.php?q="+"ns",
    data: {day:day, time:time},
    success: function(data) {
      refreshTable();

      update_row = -1;
      selected_start = null;
      selected_end = null;
    },
    error: function() {
      alert("Error Updating Availabilities");
    }
  });
}

function newEnd(day, time) {
  //perform a post request to update the end time for given day(int) and time(int) in the database
  $.ajax({
    type: "POST",
    url: "crud.php?q="+"ne",
    data: {day:day, time:time},
    success: function(data) {
      refreshTable();

      update_row = -1;
      selected_start = null;
      selected_end = null;
    },
    error: function() {
      alert("Error Updating Availabilities");
    }
  });
}

function refreshTable() {
  //refresh the table to the data from the server
  var $table = $("#avail_table");

  $.ajax({
    type: "GET",
    url: "crud.php?q="+"as",
    success: function(data) {
      var obj = $.parseJSON(data);

      $table.empty();
      $table.append(obj["tbl"]);
      starts = obj["starts"];
      ends = obj["ends"];
    }
  });
}

function noStart(day) {
  //check if given day has a start availability
  for (var i = 0; i < starts.length; i++) {
    if (day == starts[i][1]) {
      return false;
    }
  }
  return true;
}

function isStart(cellI) {
  //check if given cellI is a start cell
  for (var i = 0; i < starts.length; i++) {
    if (starts[i][0] == cellI[0] && starts[i][1] == cellI[1]) {
      return true;
    }
  }
  return false;
}

function isEnd(cellI) {
  //check if given cellI is an end cell
  for (var i = 0; i < starts.length; i++) {
    if (ends[i][0] == cellI[0] && ends[i][1] == cellI[1]) {
      return true;
    }
  }
  return false;
}
