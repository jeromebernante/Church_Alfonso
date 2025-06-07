<?php
include '../db_connection.php';

if (isset($_POST['requestId']) && isset($_POST['type'])) {
    $requestId = $_POST['requestId'];
    $type = $_POST['type'];

    if ($type === "Online") {
        $query = "SELECT * FROM pamisa_requests WHERE id = ?";
    } else {
        $query = "SELECT * FROM pamisa_requests WHERE id = ? AND user_id = 0";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $updateUrl = ($type === "Online") ? "update_pamisa.php" : "update_pamisa.php";

        echo "<div class='pamisa-details'>";
        echo "<h2>Pamisa Details</h2>";

        echo "<form id='editPamisaForm' method='POST' action='" . $updateUrl . "'>";
        echo "<input type='hidden' name='requestId' value='" . htmlspecialchars($row['id']) . "'>";
        echo "<p><strong>Name of Intended:</strong> <input type='text' name='name_of_intended' value='" . htmlspecialchars($row['name_of_intended']) . "' readonly></p>";
        echo "<p><strong>Pamisa Type:</strong> <input type='text' name='pamisa_type' value='" . htmlspecialchars($row['pamisa_type']) . "' readonly></p>";
        echo "<p><strong>Pamisa Time:</strong> <input type='text' name='selected_time' value='" . date("h:i A", strtotime($row['selected_time'])) . "' readonly></p>";
        echo "<p><strong>Price:</strong> ₱<input type='text' name='price' value='" . number_format($row['price'], 2) . "' readonly></p>";
        echo "<p><strong>Status:</strong> <select name='status'>";
        $options = ['Pending', 'Accepted'];
        foreach ($options as $option) {
            $selected = ($row['status'] == $option) ? 'selected' : '';
            echo "<option value='" . htmlspecialchars($option) . "' $selected>$option</option>";
        }
        echo "</select></p>";

        if (!empty($row['payment_receipt'])) {
            if ($type === "Online") {
               echo "<p><strong>Receipt:</strong> <a href='../" . htmlspecialchars($row['payment_receipt']) . "' target='_blank' class='receipt-link'>View Receipt</a></p>";
            } else {
                echo "<p><strong>Receipt:</strong> <a href='../" . htmlspecialchars($row['payment_receipt']) . "' target='_blank' class='receipt-link'>View Receipt</a></p>";
            }
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



<script>
$(document).ready(function() {
    $(document).on("click", "#editButton", function() {
        let form = $(this).closest("form"); 
        form.find("input[name='name_of_intended'], input[name='pamisa_type'], input[name='selected_time'], input[name='price']").prop("readonly", false);
        
        $(this).hide(); 
        form.find("#saveButton").show(); 
    });

    $(document).on("submit", "#editPamisaForm", function(event) {
        event.preventDefault();
        let form = $(this);
        let formAction = form.attr("action");

        $.ajax({
            url: formAction,
            type: "POST",
            data: form.serialize(),
            success: function(response) {
                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: response,
                    confirmButtonColor: "#4CAF50"
                }).then(() => {
                    form.find("#saveButton").hide();
                    form.find("#editButton").show();
                    form.find("input").prop("readonly", true);
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
            text: "This pamisa request will be permanently deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "delete_pamisa.php",
                    type: "POST",
                    data: { requestId: requestId },
                    success: function(response) {
                        Swal.fire({
                            icon: "success",
                            title: "Deleted!",
                            text: "Pamisa request deleted successfully.",
                            confirmButtonColor: "#4CAF50"
                        }).then(() => {
                            $(".pamisa-details").remove();
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

    input[type="text"], select {
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

    .pamisa-details {
        max-width: 400px;
        background: #fff;
        padding: 20px;
        border-left: 5px solid #4CAF50;
        box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        font-family: 'Arial', sans-serif;
        color: #333;
        margin-left: 20px;
    }

    .pamisa-details h2 {
        margin-top: 0;
        font-size: 22px;
        color: #4CAF50;
    }

    .pamisa-details p {
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

