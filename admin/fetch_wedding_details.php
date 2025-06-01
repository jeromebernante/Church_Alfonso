<?php
include 'db_connection.php';

if (isset($_POST['requestId']) && isset($_POST['type'])) {
    $requestId = $_POST['requestId'];
    $type = $_POST['type'];

    if ($type === "Online") {
        $query = "SELECT id, bride_name, groom_name, contact, wedding_date, payment_receipt, status FROM wedding_requests WHERE id = ?";
    } else {
        $query = "SELECT id, bride_name, groom_name, contact, wedding_date, payment_receipt FROM walkin_wedding_requests WHERE id = ?";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $brideName = htmlspecialchars($row['bride_name']);
        $groomName = htmlspecialchars($row['groom_name']);
        $contact = htmlspecialchars($row['contact']);
        $weddingDate = htmlspecialchars($row['wedding_date']);
        $paymentReceipt = $row['payment_receipt'] ? 
            "<a class='receipt-link' href='../uploads/{$row['payment_receipt']}' target='_blank'>View Receipt</a>" : 
            "<span class='not-found'>No receipt uploaded</span>";

        echo "<div class='blessing-details' id='wedding-details-$requestId'>";
        echo "<h2>Wedding Request Details</h2>";
        echo "<p><b>Bride:</b> <input type='text' id='bride_name_$requestId' value='$brideName' disabled></p>";
        echo "<p><b>Groom:</b> <input type='text' id='groom_name_$requestId' value='$groomName' disabled></p>";
        echo "<p><b>Contact:</b> <input type='text' id='contact_$requestId' value='$contact' disabled></p>";
        echo "<p><b>Wedding Date:</b> <input type='date' id='wedding_date_$requestId' value='$weddingDate' disabled></p>";
        echo "<p><b>Payment Receipt:</b> $paymentReceipt</p>";

        if ($type === "Online") {
            $statusColor = ($row['status'] === "Accepted") ? "green" : (($row['status'] === "Rejected") ? "red" : "orange");
            echo "<p><b>Status:</b> <span style='color: $statusColor;'>{$row['status']}</span></p>";
        }

        echo "<button id='editButton' onclick='enableEditing($requestId)'>Edit</button>";
        echo "<button id='saveButton' onclick='saveWedding($requestId, \"$type\")' style='display:none;'>Save</button>";
        echo "<button id='deleteButton' onclick='deleteWedding($requestId, \"$type\")'>Delete</button>";

        echo "</div>";
    } else {
        echo "<p class='not-found'>No details found for this request.</p>";
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function enableEditing(requestId) {
    document.getElementById(`bride_name_${requestId}`).disabled = false;
    document.getElementById(`groom_name_${requestId}`).disabled = false;
    document.getElementById(`contact_${requestId}`).disabled = false;
    document.getElementById(`wedding_date_${requestId}`).disabled = false;

    document.getElementById("editButton").style.display = "none";
    document.getElementById("saveButton").style.display = "inline-block";
}


function saveWedding(requestId, type) {
    let brideName = document.getElementById(`bride_name_${requestId}`).value;
    let groomName = document.getElementById(`groom_name_${requestId}`).value;
    let contact = document.getElementById(`contact_${requestId}`).value;
    let weddingDate = document.getElementById(`wedding_date_${requestId}`).value;

    $.ajax({
        url: "update_request.php",
        type: "POST",
        data: {
            action: "edit",
            requestId: requestId,
            type: type,
            bride_name: brideName,
            groom_name: groomName,
            contact: contact,
            wedding_date: weddingDate
        },
        success: function(response) {
            if (response === "success") {
                Swal.fire({
                    title: "Success!",
                    text: "Wedding details updated successfully.",
                    icon: "success"
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: "Error!",
                    text: "Error updating details.",
                    icon: "error"
                });
            }
        }
    });
}

function deleteWedding(requestId, type) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "update_request.php",
                type: "POST",
                data: {
                    action: "delete",
                    requestId: requestId,
                    type: type
                },
                success: function(response) {
                    if (response === "success") {
                        Swal.fire({
                            title: "Deleted!",
                            text: "Wedding request has been deleted.",
                            icon: "success"
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: "Error deleting request.",
                            icon: "error"
                        });
                    }
                }
            });
        }
    });
}

</script>

<style>
button {
    padding: 8px 16px;
    font-size: 14px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    transition: 0.3s ease;
    display: inline-block;
    margin: 5px;
}

#editButton { background-color: #f4a261; color: white; }
#editButton:hover { background-color: #e76f51; }

#saveButton { background-color: #2a9d8f; color: white; }
#saveButton:hover { background-color: #21867a; }

#deleteButton { background-color: #e63946; color: white; }
#deleteButton:hover { background-color: #b71c1c; }

input[type="text"], input[type="date"] {
    width: 100%;
    padding: 8px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 5px;
    outline: none;
    transition: 0.3s ease;
    background-color: #f9f9f9;
}

input:focus { border-color: #4CAF50; background-color: #fff; }

.blessing-details {
    max-width: 400px;
    background: #fff;
    padding: 20px;
    border-left: 5px solid #4CAF50;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
    font-family: 'Arial', sans-serif;
    color: #333;
    margin-left: 20px;
}

.blessing-details h2 { margin-top: 0; font-size: 22px; color: #4CAF50; }

.receipt-link { color: #007bff; text-decoration: none; font-weight: bold; }
.receipt-link:hover { text-decoration: underline; }

.not-found { color: #d9534f; font-style: italic; margin-left: 20px; }
</style>
