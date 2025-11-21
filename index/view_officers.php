<?php
// Include database connection
include('../config/db_connect.php'); // Ensure correct path


if (isset($_GET['id'])) {
    $officer_id = $_GET['id'];

    // Query from officers table
    $query_officer = "SELECT * FROM officers WHERE id = $officer_id";
    $result_officer = mysqli_query($conn, $query_officer);
    $officer = mysqli_fetch_assoc($result_officer);

    // Query from members table (assuming member_id in officers table corresponds to members table)
    $member_id = $officer['member_id']; // Assuming there is a reference in the officers table
    $query_member = "SELECT * FROM members WHERE id = $member_id";
    $result_member = mysqli_query($conn, $query_member);
    $member = mysqli_fetch_assoc($result_member);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Use the same background as the officer list */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: url('images/image1.jpg') no-repeat center center;
            background-size: cover;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
        }
        .officer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .officer-table th, .officer-table td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }
        .officer-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .officer-img {
            width: 150px;
            height: 200px;
            object-fit: cover;
            display: block;
            margin: 10px auto;
            border-radius: 10px;
        }
        .back-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: gray;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Officer Details</h2>

        <table class="officer-table">
            <tr>
                <th>Designation</th>
                <th>Rank</th>
                <th>Name</th>
            </tr>
            <tr>
                <td><?php echo $officer['position']; ?></td>
                <td>Officer</td>
                <td>
                    <img src="uploads/<?php echo $officer['image']; ?>" alt="Officer Image" class="officer-img"><br>
                    <?php echo ucfirst($officer['name']); ?>
                </td>
            </tr>
        </table>

        <table class="officer-table">
            <tr>
                <th>Contact</th>
                <th>Address</th>
                <th>Term Start</th>
                <th>Term End</th>
            </tr>
            <tr>
                <td><?php echo $member['contact']; ?></td>
                <td><?php echo ucfirst($member['address']); ?></td>
                <td><?php echo $officer['term_start']; ?></td>
                <td><?php echo $officer['term_end']; ?></td>
            </tr>
        </table>

        <button class="back-btn" onclick="history.back()">Back</button>
    </div>

</body>
</html>
