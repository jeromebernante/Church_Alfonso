document.addEventListener("DOMContentLoaded", function () {
    const calendar = document.getElementById("calendar");
    const monthYear = document.getElementById("month-year");
    const prevBtn = document.getElementById("prev-month");
    const nextBtn = document.getElementById("next-month");

    let currentDate = new Date();
    let unavailableDates = {};

    function fetchUnavailableDates() {
        fetch("schedule.php")
            .then(response => response.json())
            .then(data => {
                unavailableDates = data.schedule || {}; 
                renderCalendar(currentDate);
            })
            .catch(error => console.error("Error fetching schedule:", error));
    }

    function saveUnavailableDate(date, priestName) {
        fetch("schedule.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ date: date, priest_name: priestName }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                Swal.fire("Success", "Schedule updated!", "success");
                fetchUnavailableDates(); 
            } else {
                Swal.fire("Error", "Could not update schedule!", "error");
            }
        })
        .catch(error => console.error("Error updating schedule:", error));
    }

    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();

        monthYear.textContent = new Intl.DateTimeFormat("en-US", { month: "long", year: "numeric" }).format(date);

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        let calendarHTML = "<tr>";
        for (let i = 0; i < firstDay; i++) {
            calendarHTML += "<td></td>";
        }

        for (let day = 1; day <= daysInMonth; day++) {
            if ((firstDay + day - 1) % 7 === 0 && day !== 1) {
                calendarHTML += "</tr><tr>";
            }

            let dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            let isUnavailable = unavailableDates.hasOwnProperty(dateStr);
            let priestName = isUnavailable ? unavailableDates[dateStr] : "Available";
            let bgColor = isUnavailable ? "red" : "green";

            calendarHTML += `<td class="calendar-day" data-date="${dateStr}" style="background-color: ${bgColor}; color: white; cursor: pointer;">
                                ${day}<br><small>${priestName}</small>
                            </td>`;
        }
        calendarHTML += "</tr>";

        calendar.innerHTML = calendarHTML;

        document.querySelectorAll(".calendar-day").forEach(day => {
            day.addEventListener("click", function () {
                let date = this.getAttribute("data-date");
                let isUnavailable = unavailableDates.hasOwnProperty(date);

                fetch("schedule.php?action=getPriests")
                    .then(response => response.json())
                    .then(data => {
                        let priests = data.priests;
                        let priestOptions = priests.map(priest => `<option value="${priest}">${priest}</option>`).join("");
                        priestOptions += `<option value="All Priests Unavailable">All Priests Unavailable</option>`;

                        Swal.fire({
                            title: `Set Availability for ${date}`,
                            html: isUnavailable
                                ? `<p>This date is currently unavailable. Do you want to make it available?</p>`
                                : `<p>Select the priest for this unavailable date:</p>
                                    <select id="priest-dropdown">${priestOptions}</select>`,
                            showCancelButton: true,
                            confirmButtonText: isUnavailable ? "Make Available" : "Save",
                            cancelButtonText: "Cancel",
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                if (isUnavailable) {
                                    saveUnavailableDate(date, "Available");
                                } else {
                                    let selectedPriest = document.getElementById("priest-dropdown").value;
                                    saveUnavailableDate(date, selectedPriest);
                                }
                            }
                        });
                    });
            });
        });
    }

    prevBtn.addEventListener("click", function () {
        currentDate.setMonth(currentDate.getMonth() - 1);
        fetchUnavailableDates();
    });

    nextBtn.addEventListener("click", function () {
        currentDate.setMonth(currentDate.getMonth() + 1);
        fetchUnavailableDates();
    });

    fetchUnavailableDates();
});
