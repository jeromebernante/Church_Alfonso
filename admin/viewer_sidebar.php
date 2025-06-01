<div class="l-navbar" id="nav-bar">
    <nav class="nav">
        <div>
            <a href="#" class="nav_logo">
                <img src="imgs/logo.png" alt="Parish Logo" class="nav_logo-img">
                <span class="nav_logo-name">Parish of the Holy Cross</span>
            </a>
            <div class="nav_list">
                <a href="dashboard_viewer.php" class="nav_link">
                    <i class='bx bxs-dashboard nav_icon'></i>
                    <span class="nav_name">Dashboard</span>
                </a>
                <a href="viewer_priest.php" class="nav_link">
                    <i class='bx bxs-church nav_icon'></i>
                    <span class="nav_name">Priest Schedule</span>
                </a>
                <a href="viewer_events.php" class="nav_link">
                    <i class='bx bx-calendar nav_icon'></i>
                    <span class="nav_name">Events</span>
                </a>
                <a href="viewer_reports.php" class="nav_link">
                    <i class='bx bx-file nav_icon'></i>
                    <span class="nav_name">Reports</span>
                </a>

            </div>
        </div>
        <a href="" class="nav_link logout" id="logout">
            <i class='bx bx-log-out nav_icon'></i>
            <span class="nav_name">Sign Out</span>
        </a>
    </nav>
</div>

<script>
    document.getElementById("logout").addEventListener("click", function(event) {
        event.preventDefault();

        Swal.fire({
            title: "Are you sure?",
            text: "You will be logged out of your account.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, log me out",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "logout.php"; 
            }
        });
    });
</script>


<style>
.l-navbar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 260px;
    background: linear-gradient(135deg, #2c3e50, #2a5298);
    padding: 20px 10px;
    transition: 0.3s;
    overflow-y: auto;
    box-shadow: 5px 0 15px rgba(0, 0, 0, 0.2);
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
    gap: 12px;
    text-decoration: none;
    color: white;
    font-size: 18px;
    font-weight: bold;
    padding-bottom: 20px;
}

.nav_logo-img {
    width: 45px;
    height: 45px;
    object-fit: cover;
    border-radius: 50%;
}

.nav_list {
    flex-grow: 1;
}

.nav_link {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    text-decoration: none;
    color: white;
    font-size: 16px;
    border-radius: 8px;
    transition: 0.3s;
    position: relative;
}

.nav_link:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateX(5px);
    box-shadow: 2px 2px 10px rgba(255, 255, 255, 0.2);
}

.logout {
    background: #e74c3c;
    color: white;
    text-align: center;
    border-radius: 8px;
    padding: 14px;
    transition: 0.3s;
}

.logout:hover {
    background: #c0392b;
    transform: scale(1.05);
}

@media screen and (max-width: 768px) {
    .l-navbar {
        width: 80px;
    }
    .nav_logo-name,
    .nav_name {
        display: none;
    }
    .nav_link:hover .nav_name {
        display: block;
        position: absolute;
        left: 70px;
        background: rgba(0, 0, 0, 0.8);
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
    }
}
</style>
