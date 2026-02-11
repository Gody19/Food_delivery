<?php
include 'include/check_login.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Management</title>
    <!-- Bootstrap CSS -->
    <link href="../Assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../Assets/fontawesome/css/all.min.css">
    <style>
        .inbox-container {
            max-width: 1200px;
            margin: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }

        .inbox-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inbox-table th {
            background: #f1f3f5;
            padding: 12px;
            text-align: left;
            font-size: 14px;
            color: #495057;
        }

        .inbox-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .inbox-table .unread {
            background: #fff3e0;
            font-weight: 500;
        }

        .avatar {
            margin-right: 8px;
        }

        .btn-icon {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            margin: 0 2px;
            opacity: 0.6;
        }

        .btn-icon:hover {
            opacity: 1;
        }
    </style>
</head>

<body>
    <?php include 'include/header.php'; ?>
    <?php include 'include/aside.php'; ?>
    <div id="content">
        <!-- Recent Orders Card -->
        <div class="dashboard-card">
            <div class="card-header">
                <span>Contact management</span>

            </div>
            <?php
            include '../config/connection.php';
            ?>
            <div class="inbox-container">
                <table class="inbox-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox"></th>
                            <th>Sender</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message Preview</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM messages ORDER BY created_at DESC";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $isUnread = $row['is_read'] == 0 ? 'unread' : '';
                        ?>
                        <tr class="<?php echo $isUnread; ?>">
                            <td><input type="checkbox"></td>
                            <td><span class="avatar"></span> <?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['email']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['message'], 0, 50)); ?>...</td>
                            <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                            <td>
                                <button class="btn-icon">Read</button>
                                <button class="btn-icon">Delete</button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>



        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="../Assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>