<?php
include 'db_connection.php';

if (isset($_POST['requestId'])) {
    $requestId = $_POST['requestId'];

    $query = "SELECT * FROM blessings_requests WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo "<div class='blessing-details'>";
        echo "<h2>Blessing Details</h2>";

        echo "<form id='editForm' method='POST'>";
        echo "<input type='hidden' name='requestId' value='" . htmlspecialchars($row['id']) . "'>";
        echo "<p><strong>Name of Blessed:</strong> <input type='text' name='name_of_blessed' value='" . htmlspecialchars($row['name_of_blessed']) . "' readonly></p>";
        echo "<p><strong>Type of Blessing:</strong> <input type='text' name='type_of_blessing' value='" . htmlspecialchars($row['type_of_blessing']) . "' readonly></p>";
        echo "<p><strong>Blessing Time:</strong> <input type='text' name='blessing_time' value='" . htmlspecialchars($row['blessing_time']) . "' readonly></p>";
        echo "<p><strong>Amount:</strong> <input type='text' name='amount' value='" . htmlspecialchars($row['amount']) . "'></p>";
        
        echo "<p><strong>Status:</strong><br><select name='status'>";
        $options = ['Pending', 'Accepted'];
        foreach ($options as $option) {
            $selected = ($row['status'] == $option) ? 'selected' : '';
            echo "<option value='" . htmlspecialchars($option) . "' $selected>$option</option>";
        }
        echo "</select></p>";

        // Updated receipt display logic
        if (!empty($row['receipt_path'])) {
            echo "<p><strong>Receipt:</strong> <a href='../" . htmlspecialchars($row['receipt_path']) . "' target='_blank' class='receipt-link'>View Receipt</a></p>";
        } else {
            echo "<p class='not-found'>No receipt uploaded.</p>";
        }

        echo "<button type='button' id='editButton'>Edit</button>";
        echo "<button type='submit' id='saveButton' style='display:none;'>Save Changes</button>";
        echo "<button type='button' id='deleteButton' data-id='" . htmlspecialchars($row['id']) . "' class='delete-btn'>Delete</button>";

        echo "</form>";
        echo "</div>";
    } else {
        echo "<p class='not-found'>Details not found.</p>";
    }
}
?>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $(document).on("click", "#editButton", function() {
        $("input[name='name_of_blessed'], input[name='type_of_blessing'], input[name='blessing_time']").prop("readonly", false);
        $("#editButton").hide();
        $("#saveButton").show();
    });

    $(document).on("submit", "#editForm", function(event) {
        event.preventDefault();

        $.ajax({
            url: "update_blessing.php",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: "Details updated successfully.",
                    confirmButtonColor: "#4CAF50"
                }).then(() => {
                    $("#saveButton").hide();
                    $("#editButton").show();
                    $("input[name='name_of_blessed'], input[name='type_of_blessing'], input[name='blessing_time']").prop("readonly", true);
                });
            },
            error: function() {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "There was an error updating the details.",
                    confirmButtonColor: "#d33"
                });
            }
        });
    });

    $(document).on("click", "#deleteButton", function() {
        var requestId = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "This blessing request will be permanently deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "delete_blessing.php",
                    type: "POST",
                    data: { requestId: requestId },
                    success: function(response) {
                        Swal.fire({
                            icon: "success",
                            title: "Deleted!",
                            text: "Blessing request deleted successfully.",
                            confirmButtonColor: "#4CAF50"
                        }).then(() => {
                            $(".blessing-details").remove(); 
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "There was an error deleting the request.",
                            confirmButtonColor: "#d33"
                        });
                    }
                });
            }
        });
    });
});


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

    #editButton {
        background-color: #f4a261;
        color: white;
    }

    #editButton:hover {
        background-color: #e76f51;
    }

    #saveButton {
        background-color: #2a9d8f;
        color: white;
    }

    #saveButton:hover {
        background-color: #21867a;
    }

    #deleteButton {
        background-color: #e63946;
        color: white;
    }

    #deleteButton:hover {
        background-color: #b71c1c;
    }

    input[type="text"], select{
        width: 100%;
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 5px;
        outline: none;
        transition: 0.3s ease;
        background-color: #f9f9f9;
    }

    input[type="text"]:focus {
        border-color: #4CAF50;
        background-color: #fff;
    }

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

    .blessing-details h2 {
        margin-top: 0;
        font-size: 22px;
        color: #4CAF50;
    }

    .blessing-details p {
        margin: 8px 0;
        font-size: 16px;
    }

    .receipt-link {
        color: #007bff;
        text-decoration: none;
        font-weight: bold;
    }

    .receipt-link:hover {
        text-decoration: underline;
    }

    .not-found {
        color: #d9534f;
        font-style: italic;
        margin-left: 20px;
    }
</style>
