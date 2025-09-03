<!-- <!DOCTYPE html>

<html>

<head>

  <meta charset="utf-8">

  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title></title>

  

   <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

   



</head>



<body>



<div class="container"><div class="row"><br>

<div class ="col-md-12"><form id="user_form"> 





         <input type="checkbox" class="Present 1" name="attedence[]" id ="1"  value="P" required>

          <input type="checkbox" class="Present 1" name="attedence[]" id ="1" value="A" required>

        <input type="checkbox" class="Present 1" name="attedence[]" id ="1"  value="H" required>

        <input type="checkbox" class="Present 1" name="attedence[]" id ="1"  value="F" required>



<br>

       

       <input type="checkbox" class="Present 2" name="attedence[]" id ="2"  value="P" required>

          <input type="checkbox" class="Present 2" name="attedence[]" id ="2" value="A" required>

        <input type="checkbox" class="Present 2" name="attedence[]" id ="2"  value="H" required>

        <input type="checkbox" class="Present 2" name="attedence[]" id ="2"  value="F" required>





 </tbody>

  </table></form></div></div></div>

</body>

</html>

<script type="text/javascript">

  $('input:checkbox').click(function(){

   var id = ($(this).attr('id'));

   // alert(id)

    var $inputs = $("#id")

        if($(this).is(':checked')){

            // $("."+id).prop('disabled', true); // <-- disable all but checked one

             $("."+id).not(this).prop('disabled',true);

        }else{

           // $inputs.prop('disabled',false); // <--

           $("."+id).prop('disabled', false);

        }

    })

</script>
</html> -->



<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Attendance Form</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
</head>
<body>

<div class="container">
  <div class="row">
    <div class="col-md-12">
      <?php
      // Database connection
      $servername = "localhost";
      $username = "root";
      $password = "";
      $dbname = "arrowgrub";

      $conn = new mysqli($servername, $username, $password, $dbname);

      if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
      }

      // Function to check availability
      function checkAvailability($conn, $bookingDate, $bookingTime) {
          $stmt = $conn->prepare("SELECT attendance_code FROM attendance WHERE booking_date = ? AND booking_time = ?");
          $stmt->bind_param("ss", $bookingDate, $bookingTime);
          $stmt->execute();
          $result = $stmt->get_result();

          $availability = array(
              'P' => false,
              'A' => false,
              'H' => false,
              'F' => false
          );

          while ($row = $result->fetch_assoc()) {
              $availability[$row['attendance_code']] = true;
          }

          $stmt->close();

          return $availability;
      }

      // Handle form submission
      if ($_SERVER["REQUEST_METHOD"] == "POST") {
          $bookingDate = $_POST['booking_date'];
          $bookingTime = $_POST['booking_time'];

          // Check availability for the selected date and time
          $availability = checkAvailability($conn, $bookingDate, $bookingTime);

          // Display form with availability status
          echo '<form id="user_form" method="post" action="">';
          echo '<label for="booking_date">Booking Date:</label>';
          echo '<input type="date" id="booking_date" name="booking_date" value="' . $bookingDate . '" required><br>';
          echo '<label for="booking_time">Booking Time:</label>';
          echo '<input type="time" id="booking_time" name="booking_time" value="' . $bookingTime . '" required><br>';

          echo '<label>Attendance:</label><br>';
          echo '<input type="checkbox" name="attendance[]" value="P" ' . ($availability['P'] ? 'disabled' : '') . '> Present<br>';
          echo '<input type="checkbox" name="attendance[]" value="A" ' . ($availability['A'] ? 'disabled' : '') . '> Absent<br>';
          echo '<input type="checkbox" name="attendance[]" value="H" ' . ($availability['H'] ? 'disabled' : '') . '> Half Day<br>';
          echo '<input type="checkbox" name="attendance[]" value="F" ' . ($availability['F'] ? 'disabled' : '') . '> Full Day<br>';

          echo '<button type="submit">Submit Attendance</button>';
          echo '</form>';

          // Process form submission
          if (isset($_POST['attendance']) && is_array($_POST['attendance'])) {
              $user_id = 1; // Assuming fixed user ID for demo
              $attendance_codes = $_POST['attendance'];

              foreach ($attendance_codes as $attendance_code) {
                  $stmt = $conn->prepare("INSERT INTO attendance (user_id, booking_date, booking_time, attendance_code) VALUES (?, ?, ?, ?)");
                  $stmt->bind_param("isss", $user_id, $bookingDate, $bookingTime, $attendance_code);
                  $stmt->execute();
                  $stmt->close();
              }

              echo "<p>Attendance recorded successfully.</p>";
          }
      }

      // Close database connection
      $conn->close();
      ?>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Function to handle availability check on date and time change
    $('#booking_date, #booking_time').change(function() {
        var bookingDate = $('#booking_date').val();
        var bookingTime = $('#booking_time').val();

        $.post('', {
            booking_date: bookingDate,
            booking_time: bookingTime
        }, function(response) {
            var availability = JSON.parse(response);

            // Disable checkboxes based on availability
            $('input[type="checkbox"]').each(function() {
                var value = $(this).val();
                $(this).prop('disabled', availability[value]);
            });
        });
    });
});
</script>

</body>
</html>