document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.getElementById("header-toggle"),
          nav = document.getElementById("nav-bar"),
          body = document.getElementById("bodyTag"),
          header = document.getElementById("header");

    // Retrieve sidebar state from localStorage
    let isSidebarExpanded = localStorage.getItem("sidebarExpanded") === "true";
    console.log(isSidebarExpanded);
    function updateSidebarState(expanded) {
        if (expanded) {
            nav.classList.add("show");
            body.classList.add("body-pd");
            header.classList.add("body-pd");
            toggle.classList.add("bx-x");
            nav.classList.remove("hide-text");
        } else {
            nav.classList.remove("show");
            body.classList.remove("body-pd");
            header.classList.remove("body-pd");
            toggle.classList.remove("bx-x");
            nav.classList.add("hide-text");
        }
        localStorage.setItem("sidebarExpanded", expanded);
    }

    // Apply the stored sidebar state on page load
    updateSidebarState(isSidebarExpanded);

    if (toggle) {
        toggle.addEventListener("click", () => {
            isSidebarExpanded = !isSidebarExpanded;
            updateSidebarState(isSidebarExpanded);
        });
    }

    // Active link highlighting
    const linkColor = document.querySelectorAll(".nav_link");
    function colorLink() {
        linkColor.forEach(l => l.classList.remove("active"));
        this.classList.add("active");
    }
    linkColor.forEach(l => l.addEventListener("click", colorLink));
});

// Logout Confirmation
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
