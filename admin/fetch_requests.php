<?php
include 'db_connection.php';

if (isset($_POST['date'])) {
    $selectedDate = $_POST['date'];

    // Fetch online baptism requests (user_id != 0) with receipt_path
    $query = "
        SELECT 
            br.id, 
            COALESCE(br.user_id, '') AS user_id, 
            br.baptized_name, 
            COALESCE(br.parents_name, '') AS parents_name, 
            COALESCE(br.ninongs_ninangs, '') AS ninongs_ninangs, 
            COALESCE(br.request_date, '') AS request_date, 
            COALESCE(br.selected_date, '') AS selected_date, 
            COALESCE(br.status, '') AS status, 
            COALESCE(br.price, '') AS price, 
            'Online' AS type,
            COALESCE(bp.receipt_path, '') AS receipt_path
        FROM baptism_requests br
        LEFT JOIN baptism_payments bp ON br.id = bp.baptism_request_id
        WHERE br.selected_date = ? AND br.user_id != 0";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch walk-in baptism requests (user_id = 0)
    $walkinQuery = "
        SELECT 
            id, 
            COALESCE(user_id, '') AS user_id,  
            baptized_name, 
            COALESCE(parents_name, '') AS parents_name, 
            COALESCE(ninongs_ninangs, '') AS ninongs_ninangs, 
            COALESCE(request_date, '') AS request_date,  
            COALESCE(selected_date, '') AS selected_date, 
            COALESCE(status, '') AS status, 
            COALESCE(price, '') AS price,  
            'Walk-in' AS type,
            '' AS receipt_path
        FROM baptism_requests 
        WHERE selected_date = ? AND user_id = 0";

    $walkinStmt = $conn->prepare($walkinQuery);
    $walkinStmt->bind_param("s", $selectedDate);
    $walkinStmt->execute();
    $walkinResult = $walkinStmt->get_result();

    if ($result->num_rows > 0 || $walkinResult->num_rows > 0) {
        echo "<style>
            table {
                width: 100%;
                border-collapse: collapse;
                font-family: Arial, sans-serif;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: center;
            }
            th {
                background-color: #4CAF50;
                color: white;
            }
            tr:nth-child(even) {
                background-color: #f2f2f2;
            }
            tr:hover {
                background-color: #ddd;
            }
            .editable {
                background-color: pink;
                border: none;
                padding: 5px;
            }
            .save-btn {
                background-color: #007bff;
                color: white;
                padding: 5px 10px;
                border: none;
                cursor: pointer;
                transition: 0.3s;
            }
            .save-btn:hover {
                background-color: #0056b3;
            }
            .delete-btn {
                background-color: red;
                color: white;
                padding: 5px 10px;
                border: none;
                cursor: pointer;
            }
            .receipt-link {
                color: #007bff;
                text-decoration: none;
            }
            .receipt-link:hover {
                text-decoration: underline;
            }
        </style>";

        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Baptized Name</th>
                <th>Parents' Names</th>
                <th>Ninongs/Ninangs</th>
                <th>Request Date</th>
                <th>Selected Date</th>
                <th>Receipt</th>
                <th>Status</th>
                <th>Price</th>
                <th>Type</th>
                <th>Actions</th>
              </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr data-id='{$row['id']}' data-type='Online'>
                    <td>{$row['id']}</td>
                    <td>{$row['user_id']}</td>
                    <td contenteditable='true' class='editable' data-column='baptized_name'>{$row['baptized_name']}</td>
                    <td contenteditable='true' class='editable' data-column='parents_name'>{$row['parents_name']}</td>
                    <td contenteditable='true' class='editable' data-column='ninongs_ninangs'>{$row['ninongs_ninangs']}</td>
                    <td>{$row['request_date']}</td>
                    <td>{$row['selected_date']}</td>
                    <td>" . (!empty($row['receipt_path']) ? "<a href='../{$row['receipt_path']}' target='_blank' class='receipt-link'>View Receipt</a>" : "-") . "</td>
                    <td>
                        <select class='status-dropdown' data-column='status'>
                            <option value='Pending' " . ($row['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                            <option value='Accepted' " . ($row['status'] == 'Accepted' ? 'selected' : '') . ">Accepted</option>
                            <option value='Rejected' " . ($row['status'] == 'Rejected' ? 'selected' : '') . ">Rejected</option>
                        </select>
                    </td>
                    <td>{$row['price']}</td>
                    <td><b>{$row['type']}</b></td>
                    <td>
                        <button class='save-btn'>Save</button>
                        <button class='delete-btn'>Delete</button>
                    </td>
                  </tr>";
        }

        while ($row = $walkinResult->fetch_assoc()) {
            echo "<tr data-id='{$row['id']}' data-type='Walk-in'>
                    <td>{$row['id']}</td>
                    <td>{$row['user_id']}</td>
                    <td contenteditable='true' class='editable' data-column='baptized_name'>{$row['baptized_name']}</td>
                    <td contenteditable='true' class='editable' data-column='parents_name'>{$row['parents_name']}</td>
                    <td contenteditable='true' class='editable' data-column='ninongs_ninangs'>{$row['ninongs_ninangs']}</td>
                    <td>{$row['request_date']}</td>
                    <td>{$row['selected_date']}</td>
                    <td>-</td>
                    <td>
                        <select class='status-dropdown' data-column='status'>
                            <option value='Pending' " . ($row['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                            <option value='Accepted' " . ($row['status'] == 'Accepted' ? 'selected' : '') . ">Accepted</option>
                            <option value='Rejected' " . ($row['status'] == 'Rejected' ? 'selected' : '') . ">Rejected</option>
                        </select>
                    </td>
                    <td>{$row['price']}</td>
                    <td><b>{$row['type']}</b></td>
                    <td>
                        <button class='save-btn'>Save</button>
                        <button class='delete-btn'>Delete</button>
                    </td>
                  </tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No baptism requests for this date.</p>";
    }
}
?>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.querySelectorAll('.save-btn').forEach(button => {
    button.addEventListener('click', function() {
        let row = this.closest('tr');
        let id = row.getAttribute('data-id');
        let type = row.getAttribute('data-type');

        let data = {
            id: id,
            baptized_name: row.querySelector("[data-column='baptized_name']").innerText.trim(),
            parents_name: row.querySelector("[data-column='parents_name']").innerText.trim(),
            ninongs_ninangs: row.querySelector("[data-column='ninongs_ninangs']").innerText.trim(),
            status: row.querySelector("[data-column='status']").value,
            type: type
        };

        fetch('update_baptism.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(response => {
            if (response.status === "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message,
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Something went wrong!',
            });
            console.error('Error:', error);
        });
    });
});

// DELETE FUNCTIONALITY
document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
        let row = this.closest('tr');
        let id = row.getAttribute('data-id');
        let type = row.getAttribute('data-type');

        Swal.fire({
            title: "Are you sure?",
            text: "This record will be permanently deleted!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('delete_baptism.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, type: type })
                })
                .then(response => response.json())
                .then(response => {
                    if (response.status === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        row.remove(); 
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message,
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong!',
                    });
                    console.error('Error:', error);
                });
            }
        });
    });
});
</script>