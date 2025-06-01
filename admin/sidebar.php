<div class="l-navbar" id="nav-bar">
    <nav class="nav">
        <div>
            <a href="#" class="nav_logo">
                <img src="imgs/logo.png" alt="Parish Logo" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                <span class="nav_logo-name">Parish of the Holy Cross</span>
            </a>
            <div class="nav_list">
                <a href="dashboard_admin.php" class="nav_link">
                    <i class='bx bxs-dashboard nav_icon'></i> 
                    <span class="nav_name">Dashboard</span>
                </a>
                <a href="priest.php" class="nav_link">
                    <i class='bx bxs-church nav_icon'></i> 
                    <span class="nav_name">Priest Schedule</span>
                </a>
                <a href="events.php" class="nav_link">
                    <i class='bx bx-calendar nav_icon'></i> 
                    <span class="nav_name">Events</span>
                </a>
                <a href="walkin.php" class="nav_link">
                    <i class='bx bx-walk nav_icon'></i> 
                    <span class="nav_name">Walk-in</span>
                </a>
                <a href="accounts.php" class="nav_link">
                    <i class='bx bx-user-circle nav_icon'></i> 
                    <span class="nav_name">Viewer Accounts</span>
                </a>
                <a href="notif.php" class="nav_link" id="notification-link">
                    <i class='bx bx-bell nav_icon'></i>
                    <span class="nav_name">Notifications</span>
                    <span id="unread-count" class="unread-badge" style="display: none;"></span>
                </a>
                <a href="reports.php" class="nav_link">
                    <i class='bx bx-file nav_icon'></i> 
                    <span class="nav_name">Reports</span>
                </a>
                <a href="adminprofile.php" class="nav_link">
                    <i class='bx bx-user nav_icon'></i> 
                    <span class="nav_name">Admin Profile</span>
                </a>
            </div>
        </div>
        <a href="#" class="nav_link" id="logout">
            <i class='bx bx-log-out nav_icon'></i> 
            <span class="nav_name">Sign Out</span>
        </a>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById("logout").addEventListener("click", function (event) {
    event.preventDefault();

    Swal.fire({
        title: "Are you sure?",
        text: "You will be logged out!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, log me out!"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "logout.php";
        }
    });
});
</script>
<script>
$(document).ready(function () {
    function updateUnreadCount() {
        $.ajax({
            url: "notif.php?fetch_unread_count=1", // Correct AJAX URL
            type: "GET",
            dataType: "json",
            success: function (response) {
                if (response.unread_count > 0) {
                    $("#unread-count").text(response.unread_count).css({
                        "display": "inline-block",
                        "background": "red",
                        "color": "white",
                        "border-radius": "50%",
                        "padding": "4px 8px",
                        "font-size": "12px",
                        "margin-left": "5px"
                    });
                } else {
                    $("#unread-count").hide();
                }
            },
            error: function () {
                console.error("Error fetching unread notifications.");
            }
        });
    }

    updateUnreadCount();

    setInterval(updateUnreadCount, 3000);

    $("#notification-link").click(function (event) {
        event.preventDefault();
        $.ajax({
            url: "notif.php?mark_read=1",
            type: "GET",
            dataType: "json",
            success: function () {
                $("#unread-count").hide();
                window.location.href = "notif.php";
            }
        });
    });
});
</script>


<style>
.l-navbar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 250px; 
    background: #2c3e50;
    padding: 20px 10px;
    transition: 0.3s ease-in-out;
    overflow-y: auto; 
}

.l-navbar.collapsed {
    width: 70px;
}

.nav {
    display: flex;
    flex-direction: column;
    height: 100%;
    justify-content: space-between;
}

.nav_logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: white;
    font-size: 18px;
    font-weight: bold;
    padding-bottom: 20px;
}

.nav_logo img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 50%;
}

.nav_list {
    flex-grow: 1;
}

.nav_link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    text-decoration: none;
    color: white;
    font-size: 16px;
    border-radius: 5px;
    transition: 0.3s;
}

.nav_link:hover, .nav_link.active {
    background: #34495e;
}

#logout {
    background: #e74c3c;
    color: white;
    text-align: center;
}

#logout:hover {
    background: #c0392b;
}

.l-navbar.hide-text {
    width: 90px;
}

.l-navbar .nav_logo-name,
.l-navbar .nav_name {
    transition: opacity 0.3s ease-in-out;
}

.l-navbar.hide-text .nav_logo-name,
.l-navbar.hide-text .nav_name {
    opacity: 0;
    pointer-events: none;
}

.l-navbar.show {
    width: 270px;
}

@media screen and (max-width: 768px) {
    .l-navbar {
        width: 70px;
    }

    .l-navbar.show {
        width: 270px;
    }

    .l-navbar .nav_logo-name,
    .l-navbar .nav_name {
        display: none;
    }

    .l-navbar.show .nav_logo-name,
    .l-navbar.show .nav_name {
        display: block;
    }
}
</style>