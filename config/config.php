    <?php
    function conn(){
        $hostName = "localhost";
        $username = "root";
        $password = "";
        $db = "grading_system3";

        // Create connection
        $conn = mysqli_connect($hostName, $username, $password, $db);

        // Check connection
        if (mysqli_connect_error()) {   
            // Connection failed, display error
            die("Connection failed: " . mysqli_connect_error());
        }

        return $conn;
    }
