<?php
session_start();
include 'db_connection.php';

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Prepare delete statement
    $stmt = $conn->prepare("DELETE FROM user_type_church WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);

    if ($stmt->execute()) {
        $_SESSION['alertMessage'] = "<script>
                                        Swal.fire({
                                            title: 'Deleted!',
                                            text: 'Viewer account has been deleted.',
                                            icon: 'success',
                                            confirmButtonText: 'OK'
                                        });
                                    </script>";
    } else {
        $_SESSION['alertMessage'] = "<script>
                                        Swal.fire({
                                            title: 'Error!',
                                            text: 'Failed to delete account.',
                                            icon: 'error',
                                            confirmButtonText: 'OK'
                                        });
                                    </script>";
    }
    
    // Redirect to accounts.php
    header("Location: accounts.php");
    exit();
} else {
    $_SESSION['alertMessage'] = "<script>
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'Invalid request.',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                </script>";
    header("Location: accounts.php");
    exit();
}
?>
