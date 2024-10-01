<?php

include 'db.php';
session_start();

// Retrieve the class ID from GET request
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch class details
$sql = mysqli_query($conn, "SELECT user_reg.nic, name, email FROM student_classes JOIN user_reg ON user_reg.nic = student_classes.nic WHERE student_classes.class_id = '{$id}'");
//$sql = mysqli_fetch_assoc($class_query);

// Fetch registered students for the class
//$sql = mysqli_query($conn, "SELECT student_id, student_name FROM registered_classes WHERE class_id = '{$id}'");

$profiles = [];
if (mysqli_num_rows($sql) > 0) {
    while ($row = mysqli_fetch_assoc($sql)) {
        $profiles[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Dashboard</title>
    <link rel="stylesheet" href="css/css.css">


    <style>
      
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        thead {
            background-color: #4CAF50;
            color: white;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            font-size: 1.1em;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #eaeaea;
        }
    </style>


</head>
<body>
    <div class="container">
        <h1>Class Details</h1>
        <?php if ($class): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($class['name']); ?></h3>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($class['date']); ?></p>
                <p><strong>Time:</strong> <?php echo htmlspecialchars($class['time']); ?></p>
                
                <!-- Display participants -->
               
                <?php if (!empty($profile)): ?>
                    <ul>
                        <?php foreach ($profile as $participant): ?>
                            <li><?php echo htmlspecialchars($participant['student_name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    
                <?php endif; ?>
                
                <a href="classes.php" class="back-button">Back to List</a>
            </div>
        <?php else: ?>
            <p class="error">Class not found.</p>
        <?php endif; ?>
    </div>
</body>


<h1>Participants List</h1>
    <table id="participantsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>

            </tr>
        </thead>
        <tbody>
        <?php foreach ($profiles as $profile): ?>
               <tr class="card">
                    <td><?php echo htmlspecialchars($profile['nic']); ?></td>
                    <td><strong>Date:</strong> <?php echo htmlspecialchars($profile['name']); ?></td>
        </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        // Fetch data from the PHP script
        fetch('fetch.php')
            .then(response => response.json())
            .then(data => {
                const tableBody = document.querySelector('#participantsTable tbody');
                data.forEach(participant => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${participant.student_id}</td>
                        <td>${participant.student_name}</td>
                    `;
                    tableBody.appendChild(row);
                });
            })
            .catch(error => console.error('Error fetching data:', error));
    </script>
</html>

<?php
// Close connection
$conn->close();
?>