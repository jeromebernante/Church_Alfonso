

document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.getElementById("header-toggle"),
          nav = document.getElementById("nav-bar"),
          body = document.getElementById("bodyTag"),
          header = document.getElementById("header");

    const isSidebarExpanded = localStorage.getItem("sidebarExpanded") === "true";
    console.log(isSidebarExpanded);
    
    if (!isSidebarExpanded) {
        nav.classList.add("hide-text");
    } else {
        nav.classList.add("show");
        body.classList.add("body-pd");
        header.classList.add("body-pd");
        toggle.classList.add("bx-x");
    }

    if (toggle && nav && body && header) {
        toggle.addEventListener("click", () => {
            const isNowExpanded = !nav.classList.contains("show");

            nav.classList.toggle("show");
            body.classList.toggle("body-pd");
            header.classList.toggle("body-pd");
            toggle.classList.toggle("bx-x");
            nav.classList.toggle("hide-text", !nav.classList.contains("show"));

            localStorage.setItem("sidebarExpanded", isNowExpanded);
        });
    }

    const linkColor = document.querySelectorAll(".nav_link");

    function colorLink() {
        linkColor.forEach(l => l.classList.remove("active"));
        this.classList.add("active");
    }

    linkColor.forEach(l => l.addEventListener("click", colorLink));
});

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
