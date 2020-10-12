<?php
    session_start();

    include('db.php');

    $emp_id = $_SESSION["user_id"];

    $q = $_GET["q"];
    if ($q == "sn") {
      select_name();
    } elseif ($q == "as") {
      select_availabilities();
    } elseif ($q == "asb") {
      $emp_id = $_POST["user_id"];
      select_availabilities();
    } elseif ($q == "ad") {
      add_defaults();
    } elseif ($q == "ns") {
      update_start();
    } elseif ($q == "ne") {
      update_end();
    } elseif ($q == "ra") {
      remove_availabilities();
    } elseif ($q == "rt") {
      select_roster();
    } elseif ($q == "rtb") {
      $emp_id = $_POST["user_id"];
      select_roster();
    }

    function select_name() {
      //connect to db
      $conn = connect();

      //execute sql
      $emp_id = $_SESSION['user_id'];
      $sql = $conn->prepare("SELECT First_name, Manager FROM Employee WHERE Employee_ID=?");
      $sql->bind_param("s", $emp_id);
      $sql->execute();
      $sql->bind_result($firstname, $manager);

      //parse result
      $result = [];
      while ($sql->fetch()) {
        $result["name"] = $firstname;
        $result["manager"] = $manager;

        if ($manager == 1) {
          $_SESSION["manager"] = true;
        } else {
          $_SESSION["manager"] = false;
        }
      }

      echo json_encode($result);

      //close connection
      $sql->close();
      $conn->close();
    }

    function select_availabilities() {
      //use the global $emp_id
      global $emp_id;

      //connect to db
      $conn = connect();

      //execute sql
      $sql = $conn->prepare("SELECT Employee_ID, Mon_start, Mon_end, Tues_start, Tues_end, Wed_start, Wed_end, Thurs_start, Thurs_end,
        Fri_start, Fri_end, Sat_start, Sat_end, Sun_start, Sun_end FROM Availabilities WHERE Employee_ID=?");
      $sql->bind_param("s", $emp_id);
      $sql->execute();
      $sql->bind_result($employee_ID, $mon_start, $mon_end, $tues_start, $tues_end, $wed_start, $wed_end, $thurs_start, $thurs_end,
        $fri_start, $fri_end, $sat_start, $sat_end, $sun_start, $sun_end);

      //parse result
      $result = [];
      while ($sql->fetch()) {
        $result["Employee_ID"] = $employee_ID;
        $result["Mon_start"] = $mon_start;
        $result["Mon_end"] = $mon_end;
        $result["Tues_start"] = $tues_start;
        $result["Tues_end"] = $tues_end;
        $result["Wed_start"] = $wed_start;
        $result["Wed_end"] = $wed_end;
        $result["Thurs_start"] = $thurs_start;
        $result["Thurs_end"] = $thurs_end;
        $result["Fri_start"] = $fri_start;
        $result["Fri_end"] = $fri_end;
        $result["Sat_start"] = $sat_start;
        $result["Sat_end"] = $sat_end;
        $result["Sun_start"] = $sun_start;
        $result["Sun_end"] = $sun_end;
      }


      $row_heads = ["Mon_start", "Mon_end", "Tues_start", "Tues_end", "Wed_start", "Wed_end", "Thurs_start", "Thurs_end",
                          "Fri_start", "Fri_end", "Sat_start", "Sat_end", "Sun_start", "Sun_end"];

      //insert result into table
      $partial_result = "";
      $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

      $times = ["06:00:00", "07:00:00", "08:00:00", "09:00:00", "10:00:00", "11:00:00", "12:00:00", "13:00:00", "14:00:00",
      "15:00:00", "16:00:00", "17:00:00", "18:00:00", "19:00:00", "20:00:00", "21:00:00", "22:00:00"];
      $start_heads = ["Mon_start", "Tues_start", "Wed_start", "Thurs_start", "Fri_start", "Sat_start", "Sun_start"];
      $end_heads = ["Mon_end", "Tues_end", "Wed_end", "Thurs_end", "Fri_end", "Sat_end", "Sun_end"];

      $return_array = [];
      $return_array["starts"] = [];
      $return_array["ends"] = [];

      if ($result) {
        $tbl_string = "";

        $i = 0; //day counter, row
        foreach ($days as $day) {
          $time_cells = [];
          $avail_times = "";
          $j = 0; //time counter, column
          $fin = true;

          foreach ($times as $time) {
            if ($time == $result[$start_heads[$i]]) {
              //start cell
              $time_cells[] = "<td class='avail_working_se'></td>";
              $return_array["starts"][] = [$j, $i];
              $fin = false;
            } elseif ($time == $result[$end_heads[$i]]) {
              //end cell
              $time_cells[$j-1] = "<td class='avail_working_se'></td>";
              $return_array["ends"][] = [$j-1, $i];
              $fin = true;
            } elseif (!$fin) {
              //working cell
              $time_cells[] = "<td class='avail_working'></td>";
            } elseif (count($time_cells) < 16) {
              //blank cell
              $time_cells[] = "<td></td>";
            }
            $j++;
          }
          $i++;

          foreach ($time_cells as $time) {
            $avail_times = $avail_times.$time;
          }

          $partial_result = $partial_result."<tr><th class='avail_h'>".$day.$avail_times."</tr>";
        }

        $tbl_string = $tbl_string."<tr>";

        for ($i=6; $i < 23; $i++) {
          if ($i < 12) {
            $tbl_string = $tbl_string."<td class='avail_t'>".$i."am</td>";
          } elseif ($i == 12) {
            $tbl_string = $tbl_string."<td class='avail_t'>".$i."pm</td>";
          } else {
            $x = $i-12;
            $tbl_string = $tbl_string."<td class='avail_t'>".$x."pm</td>";
          }
        }

        $tbl_string = $tbl_string."</tr>".$partial_result;
      } else {
        $tbl_string = null;
      }

      $return_array["tbl"] = $tbl_string;

      $return_array["manager"] = $_SESSION["manager"];

      echo json_encode($return_array);

      //close connection
      $conn->close();
    }

    function add_defaults() {
      //connect to db
      $conn = connect();

      //execute sql
      $starts = ["Mon_start", "Tues_start", "Wed_start", "Thurs_start", "Fri_start", "Sat_start", "Sun_start"];
      $ends = ["Mon_end", "Tues_end", "Wed_end", "Thurs_end", "Fri_end", "Sat_end", "Sun_end"];

      $start = $starts[$_POST["day"]];
      $end = $ends[$_POST["day"]];
      $emp_id = $_SESSION['user_id'];

      $sql = "UPDATE Availabilities SET $start='07:00:00', $end='16:00:00' WHERE Employee_ID=$emp_id";

      $sql = $conn->prepare("UPDATE Availabilities SET $start='07:00:00', $end='16:00:00' WHERE Employee_ID=?");
      $sql->bind_param("s", $emp_id);
      $sql->execute();

      //parse result
      echo "Success";

      //close connection
      $conn->close();
    }

    function update_start() {
      //connect to db
      $conn = connect();

      //execute sql
      $days = ["Mon_start", "Tues_start", "Wed_start", "Thurs_start", "Fri_start", "Sat_start", "Sun_start"];
      $times = ["06:00:00", "07:00:00", "08:00:00", "09:00:00", "10:00:00", "11:00:00", "12:00:00", "13:00:00", "14:00:00",
      "15:00:00", "16:00:00", "17:00:00", "18:00:00", "19:00:00", "20:00:00", "21:00:00"];

      $day = $days[$_POST["day"]];
      $time = $times[$_POST["time"]];
      $emp_id = $_SESSION['user_id'];

      $sql = $conn->prepare("UPDATE Availabilities SET $day=? WHERE Employee_ID=?");
      $sql->bind_param("ss", $time, $emp_id);
      $sql->execute();

      //parse result
      echo "Success";

      //close connection
      $sql->close();
      $conn->close();
    }

    function update_end() {
      //connect to db
      $conn = connect();

      //execute sql
      $days = ["Mon_end", "Tues_end", "Wed_end", "Thurs_end", "Fri_end", "Sat_end", "Sun_end"];
      $times = ["07:00:00", "08:00:00", "09:00:00", "10:00:00", "11:00:00", "12:00:00", "13:00:00", "14:00:00",
      "15:00:00", "16:00:00", "17:00:00", "18:00:00", "19:00:00", "20:00:00", "21:00:00", "22:00:00"];

      $day = $days[$_POST["day"]];
      $time = $times[$_POST["time"]];
      $emp_id = $_SESSION['user_id'];

      $sql = $conn->prepare("UPDATE Availabilities SET $day=? WHERE Employee_ID=?");
      $sql->bind_param("ss", $time, $emp_id);
      $sql->execute();

      //parse result
      echo "Success";

      //close connection
      $conn->close();
    }

    function remove_availabilities() {
      //connect to db
      $conn = connect();

      //execute sql
      $starts = ["Mon_start", "Tues_start", "Wed_start", "Thurs_start", "Fri_start", "Sat_start", "Sun_start"];
      $ends = ["Mon_end", "Tues_end", "Wed_end", "Thurs_end", "Fri_end", "Sat_end", "Sun_end"];

      $start = $starts[$_POST["day"]];
      $end = $ends[$_POST["day"]];
      $emp_id = $_SESSION['user_id'];

      $sql = $conn->prepare("UPDATE Availabilities SET $start=null, $end=null WHERE Employee_ID=?");
      $sql->bind_param("s", $emp_id);
      $sql->execute();

      //parse result
      echo "Success";

      //close connection
      $conn->close();
    }

    function select_roster() {
      //use the global $emp_id
      global $emp_id;

      //connect to db
      $conn = connect();

      //execute sql
      $sql = "SELECT * FROM Roster WHERE Employee_ID=$emp_id";
      $result = $conn->query($sql);

      $sql = $conn->prepare("SELECT Employee_ID, Week_of, Mon_start, Mon_length, Mon_area, Tues_start, Tues_length, Tues_area,
        Wed_start, Wed_length, Wed_area, Thurs_start, Thurs_length, Thurs_area, Fri_start, Fri_length, Fri_area, Sat_start, Sat_length,
        Sat_area, Sun_start, Sun_length, Sun_area FROM Roster WHERE Employee_ID=?");
      $sql->bind_param("s", $emp_id);
      $sql->execute();
      $sql->bind_result($employee_ID, $week_of, $mon_start, $mon_end, $mon_area, $tues_start, $tues_end, $tues_area, $wed_start, $wed_end,
      $wed_area, $thurs_start, $thurs_end, $thurs_area, $fri_start, $fri_end, $fri_area, $sat_start, $sat_end, $sat_area,
      $sun_start, $sun_end, $sun_area);

      //parse result
      $result = [];
      $i=0;
      while ($sql->fetch()) {
        $result[$i] = [];
        //$row = $result[$i];
        $result[$i]["Employee_ID"] = $employee_ID;
        $result[$i]["Week_of"] = $week_of;
        $result[$i]["Mon_start"] = $mon_start;
        $result[$i]["Mon_length"] = $mon_end;
        $result[$i]["Mon_area"] = $mon_area;
        $result[$i]["Tues_start"] = $tues_start;
        $result[$i]["Tues_length"] = $tues_end;
        $result[$i]["Tues_area"] = $tues_area;
        $result[$i]["Wed_start"] = $wed_start;
        $result[$i]["Wed_length"] = $wed_end;
        $result[$i]["Wed_area"] = $wed_area;
        $result[$i]["Thurs_start"] = $thurs_start;
        $result[$i]["Thurs_length"] = $thurs_end;
        $result[$i]["Thurs_area"] = $thurs_area;
        $result[$i]["Fri_start"] = $fri_start;
        $result[$i]["Fri_length"] = $fri_end;
        $result[$i]["Fri_area"] = $fri_area;
        $result[$i]["Sat_start"] = $sat_start;
        $result[$i]["Sat_length"] = $sat_end;
        $result[$i]["Sat_area"] = $sat_area;
        $result[$i]["Sun_start"] = $sun_start;
        $result[$i]["Sun_length"] = $sun_end;
        $result[$i]["Sun_area"] = $sun_area;
        $i++;
      }

      $current_date_md = date("m/d");
      $current_day = date("l");
      if ($current_day == "Tuesday") {
        $current_date_md = date("m/d", strtotime("-1 day"));
      } elseif ($current_day == "Wednesday") {
        $current_date_md = date("m/d", strtotime("-2 day"));
      } elseif ($current_day == "Thursday") {
        $current_date_md = date("m/d", strtotime("-3 day"));
      } elseif ($current_day == "Friday") {
        $current_date_md = date("m/d", strtotime("-4 day"));
      } elseif ($current_day == "Saturday") {
        $current_date_md = date("m/d", strtotime("-5 day"));
      } elseif ($current_day == "Sunday") {
        $current_date_md = date("m/d", strtotime("-6 day"));
      }

      //$week1_date_md = strtotime($current_date_md."-1 week");
      //$week3_date_md = strtotime($current_date_md."+1 week");
      //$week4_date_md = strtotime($current_date_md."+2 week");
      $return_array = [];
      $return_array["tbl"] = "";
      $return_array["btn"] = [];
      $return_array["starts"] = [];
      $return_array["ends"] = [];
      $return_array["areas"] = [];


      foreach ($result as $row) {
        $mon_s=$tues_s=$wed_s=$thurs_s=$fri_s=$sat_s=$sun_s = null;
        if ($row['Mon_start']) {
          $mon_s = date('g:ia', strtotime($row['Mon_start']));
          $mon_l = $row['Mon_length'];
        }
        if ($row['Tues_start']) {
          $tues_s = date('g:ia', strtotime($row['Tues_start']));
          $tues_l = $row['Tues_length'];
        }
        if ($row['Wed_start']) {
          $wed_s = date('g:ia', strtotime($row['Wed_start']));
          $wed_l = $row['Wed_length'];
        }
        if ($row['Thurs_start']) {
          $thurs_s = date('g:ia', strtotime($row['Thurs_start']));
          $thurs_l = $row['Thurs_length'];
        }
        if ($row['Fri_start']) {
          $fri_s = date('g:ia', strtotime($row['Fri_start']));
          $fri_l = $row['Fri_length'];
        }
        if ($row['Sat_start']) {
          $sat_s = date('g:ia', strtotime($row['Sat_start']));
          $sat_l = $row['Sat_length'];
        }
        if ($row['Sun_start']) {
          $sun_s = date('g:ia', strtotime($row['Sun_start']));
          $sun_l = $row['Sun_length'];
        }

        $mon_month = date("m", strtotime($row['Week_of']));
        $months = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

        $mon_date = date("d", strtotime($row['Week_of']))+1-1;
        $tues_date=$wed_date=$thurs_date=$fri_date=$sat_date=$sun_date = 0;

        if (($mon_date+1)>$months[$mon_month-1]) {
          $tues_date = ($mon_date+1)-$months[$mon_month-1];
        } else {$tues_date = $mon_date + 1;}
        if (($mon_date+2)>$months[$mon_month-1]) {
          $wed_date = ($mon_date+2)-$months[$mon_month-1];
        } else {$wed_date = $mon_date + 2;}
        if (($mon_date+3)>$months[$mon_month-1]) {
          $thurs_date = ($mon_date+3)-$months[$mon_month-1];
        } else {$thurs_date = $mon_date + 3;}
        if (($mon_date+4)>$months[$mon_month-1]) {
          $fri_date = ($mon_date+4)-$months[$mon_month-1];
        } else {$fri_date = $mon_date + 4;}
        if (($mon_date+5)>$months[$mon_month-1]) {
          $sat_date = ($mon_date+5)-$months[$mon_month-1];
        } else {$sat_date = $mon_date + 5;}
        if (($mon_date+6)>$months[$mon_month-1]) {
          $sun_date = ($mon_date+6)-$months[$mon_month-1];
        } else {$sun_date = $mon_date + 6;}

        $mon_date_md = date("m/d", strtotime($row['Week_of']));

        $mon_class=$tues_class=$wed_class=$thurs_class=$fri_class=$sat_class=$sun_class = "";

        if ($mon_date == date("d")) {
          $mon_class = "current_day";
        } elseif ($tues_date == date("d")) {
          $tues_class = "current_day";
        } elseif ($wed_date == date("d")) {
          $wed_class = "current_day";
        } elseif ($thurs_date == date("d")) {
          $thurs_class = "current_day";
        } elseif ($fri_date == date("d")) {
          $fri_class = "current_day";
        } elseif ($sat_date == date("d")) {
          $sat_class = "current_day";
        } elseif ($sun_date == date("d")) {
          $sun_class = "current_day";
        }

        if ($mon_date_md == $current_date_md || $mon_date_md == date("m/d", strtotime($current_date_md."-7 days"))
        || $mon_date_md == date("m/d", strtotime($current_date_md."+7 days"))
        || $mon_date_md == date("m/d", strtotime($current_date_md."+14 days"))) {
          $week = 0;
          if ($mon_date_md == date("m/d", strtotime($current_date_md."-7 days"))) {
            $week = 0;
          } elseif ($mon_date_md == $current_date_md) {
            $week = 1;
          } elseif ($mon_date_md == date("m/d", strtotime($current_date_md."+7 days"))) {
            $week = 2;
          } elseif ($mon_date_md == date("m/d", strtotime($current_date_md."+14 days"))) {
            $week = 3;
          }

          if ($row['Mon_area']) {
            $return_array["btn"][] = [0, $week];
            $return_array["starts"][] = $mon_s;
            $return_array["ends"][] = $mon_l;
            $return_array["areas"][] = $row['Mon_area'];
          }
          if ($row['Tues_area']) {
            $return_array["btn"][] = [1, $week];
            $return_array["starts"][] = $tues_s;
            $return_array["ends"][] = $tues_l;
            $return_array["areas"][] = $row['Tues_area'];
          }
          if ($row['Wed_area']) {
            $return_array["btn"][] = [2, $week];
            $return_array["starts"][] = $wed_s;
            $return_array["ends"][] = $wed_l;
            $return_array["areas"][] = $row['Wed_area'];
          }
          if ($row['Thurs_area']) {
            $return_array["btn"][] = [3, $week];
            $return_array["starts"][] = $thurs_s;
            $return_array["ends"][] = $thurs_l;
            $return_array["areas"][] = $row['Thurs_area'];
          }
          if ($row['Fri_area']) {
            $return_array["btn"][] = [4, $week];
            $return_array["starts"][] = $fri_s;
            $return_array["ends"][] = $fri_l;
            $return_array["areas"][] = $row['Fri_area'];
          }
          if ($row['Sat_area']) {
            $return_array["btn"][] = [5, $week];
            $return_array["starts"][] = $sat_s;
            $return_array["ends"][] = $sat_l;
            $return_array["areas"][] = $row['Sat_area'];
          }
          if ($row['Sun_area']) {
            $return_array["btn"][] = [6, $week];
            $return_array["starts"][] = $sun_s;
            $return_array["ends"][] = $sun_l;
            $return_array["areas"][] = $row['Sun_area'];
          }

          $tbl_string= "<tr>
                <td class='day ".$mon_class."'><div class='date'>".$mon_date."</div><br><button class='area_".$row['Mon_area']."'>".$mon_s."</button></td>
                <td class='day ".$tues_class."'><div class='date'>".$tues_date."</div><br><button class='area_".$row['Tues_area']."'>".$tues_s."</button></td>
                <td class='day ".$wed_class."'><div class='date'>".$wed_date."</div><br><button class='area_".$row['Wed_area']."'>".$wed_s."</button></td>
                <td class='day ".$thurs_class."'><div class='date'>".$thurs_date."</div><br><button class='area_".$row['Thurs_area']."'>".$thurs_s."</button></td>
                <td class='day ".$fri_class."'><div class='date'>".$fri_date."</div><br><button class='area_".$row['Fri_area']."'>".$fri_s."</button></td>
                <td class='day ".$sat_class."'><div class='date'>".$sat_date."</div><br><button class='area_".$row['Sat_area']."'>".$sat_s."</button></td>
                <td class='day ".$sun_class."'><div class='date'>".$sun_date."</div><br><button class='area_".$row['Sun_area']."'>".$sun_s."</button></td>
                </tr>";

          $return_array["tbl"] = $return_array["tbl"].$tbl_string;
        }
      }

      $return_array["manager"] = $_SESSION["manager"];

      echo json_encode($return_array);

      //close connection
      $conn->close();
    }
?>
