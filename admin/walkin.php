<?php
session_start();
include 'db_connection.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parish of the Holy Cross</title>
    <link rel="stylesheet" href="stylesd.css">
    <link rel="stylesheet" href="buttons.css">
    <link rel="icon" href="imgs/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.js"></script>
    <script src="scriptd.js"></script>
</head>
<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        title: "Walk-In Registration",
        text: "Since it's a walk-in registration, if the receipt is not available but already paid, there's no need to upload the receipt.",
        icon: "info",
        confirmButtonText: "Okay"
    });
});
</script>

<body id="bodyTag">
    <header class="header" id="header">
        <div class="header_toggle">
            <i class='bx bx-menu' id="header-toggle"></i>
        </div>
    </header>

    <?php include 'sidebar.php'; ?>
    </header><br>       
    <div class="admin-greeting">Good Day, Admin!</div>
    <center><div id="datetime" class="datetime"></div> </center>
    <?php include 'sidebar.php'; ?>
    <section class="about-us">
    <h2>Walk-In Registration</h2>
    <p class="justified">
    This section allows you to register new <b>walk-in requestors</b> for parish events or services.  
    Remember to enter the payment information to complete the request process.  
    Ensuring the payment is recorded properly will help maintain accurate records and facilitate a smooth transaction.  
    </p>

</section>
<script>
<?php if (!empty($alertMessage)) echo $alertMessage; ?>

function updateDateTime() {
    let now = new Date();
    let options = { timeZone: 'Asia/Manila', hour12: true, weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    document.getElementById('datetime').innerHTML = new Intl.DateTimeFormat('en-PH', options).format(now);
}

updateDateTime();
setInterval(updateDateTime, 60000); 
</script>

    <section class="main-content">
    <div class="calendar-container" style="display: flex; justify-content: space-between; gap: 20px; padding: 20px;">
        <div id="calendar" style="flex: 1; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
        </div>

        <div class="services-buttons" style="flex: 0.3; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <button id="pamisa" class="service-btn" style="background-color: #2c3e50; color: white; border: none; padding: 12px 20px; font-size: 16px; border-radius: 5px; cursor: pointer; transition: background 0.3s; width: 100%; margin-bottom: 10px;">Pamisa</button>
        <button id="baptismBtn" class="service-btn" style="background-color: #2c3e50; color: white; border: none; padding: 12px 20px; font-size: 16px; border-radius: 5px; cursor: pointer; transition: background 0.3s; width: 100%; margin-bottom: 10px;">Baptism</button>
        <button id="blessing" class="service-btn" style="background-color: #2c3e50; color: white; border: none; padding: 12px 20px; font-size: 16px; border-radius: 5px; cursor: pointer; transition: background 0.3s; width: 100%; margin-bottom: 10px;">Blessing</button>
        <button id="wedding" class="service-btn" style="background-color: #2c3e50; color: white; border: none; padding: 12px 20px; font-size: 16px; border-radius: 5px; cursor: pointer; transition: background 0.3s; width: 100%; margin-bottom: 10px;">Wedding</button>
    


<!-- Wedding Modal -->
<div id="weddingModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Wedding Service</h2>
        <p style="color: red; font-weight: bold; font-size: 15px;"> You are allowed to set a wedding request at least 3 months after the seminar. </p>
        <p style="color: black; font-size: 15px;"> Payment for Wedding is P7,000.00</p>
        <p style="color: black; font-size: 15px;"> GCash Account : CH***** AL***** - 09457745210</p>
        <br>

        <!-- Wedding Form -->
        <form id="weddingForm" enctype="multipart/form-data">
            <label for="brideName">Bride's Name:</label>
            <input type="text" id="brideName" name="brideName" required>

            <label for="groomName"><br>Groom's Name:</label>
            <input type="text" id="groomName" name="groomName" required>

            <label for="contact"><br>Contact Number:</label>
            <input type="text" id="contact" name="contact" required>

            <label for="weddingDate"><br>Select Wedding Date:</label>
            <input type="date" id="weddingDate" name="weddingDate" required>

            <label for="gcashReceipt"><br>Upload GCash Receipt:</label>
            <input type="file" id="gcashReceipt" name="gcashReceipt" accept="image/png, image/jpeg, image/jpg">

            <button type="submit" style="background-color: #2c3e50; 
                border: none;
                color: white;
                padding: 15px 70px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                margin: 4px 2px;
                cursor: pointer;
                border-radius: 12px;">Submit Request</button>
        </form>

        <button id="closeModalBtn" style="background-color:rgb(189, 32, 32);
                border: none;
                color: white;
                padding: 15px 105px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                margin: 4px 2px;
                cursor: pointer;
                border-radius: 12px;">Close</button>
    </div>
</div>

<script>
    document.getElementById("weddingForm").addEventListener("submit", function(event) {
        event.preventDefault();

        let formData = new FormData(this);

        fetch("wedding_request.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                Swal.fire("Success!", data.message, "success").then(() => {
                    document.getElementById("weddingModal").style.display = "none";
                    document.getElementById("weddingForm").reset();
                });
            } else {
                Swal.fire("Error!", data.message, "error");
            }
        })
        .catch(error => console.error("Error:", error));
    });
</script>

<script>
    document.getElementById("wedding").addEventListener("click", function() {
        document.getElementById("weddingModal").style.display = "flex";
    });

    document.querySelector(".close").addEventListener("click", function() {
        document.getElementById("weddingModal").style.display = "none";
    });

    document.getElementById("closeModalBtn").addEventListener("click", function() {
        document.getElementById("weddingModal").style.display = "none";
    });

    window.addEventListener("click", function(event) {
        let modal = document.getElementById("weddingModal");
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    document.getElementById("weddingDate").addEventListener("input", function() {
        let selectedDate = new Date(this.value);
        let minDate = new Date();
        minDate.setMonth(minDate.getMonth() + 3);

        if (selectedDate < minDate) {
            Swal.fire({
                icon: "warning",
                title: "Invalid Date",
                text: "Please select a wedding date at least 3 months from today.",
                confirmButtonColor: "#d33"
            });
            this.value = ""; 
        }
    });

    document.getElementById("gcashReceipt").addEventListener("change", function() {
        let file = this.files[0];
        let allowedExtensions = ["image/png", "image/jpeg", "image/jpg"];

        if (file && !allowedExtensions.includes(file.type)) {
            Swal.fire({
                icon: "error",
                title: "Invalid File Type",
                text: "Please upload an image file (PNG, JPG, JPEG) only.",
                confirmButtonColor: "#d33"
            });
            this.value = "";
        }
    });

    document.getElementById("weddingForm").addEventListener("submit", function(event) {
    event.preventDefault();

    let brideName = document.getElementById("brideName").value.trim();
    let groomName = document.getElementById("groomName").value.trim();
    let contact = document.getElementById("contact").value.trim();
    let weddingDate = document.getElementById("weddingDate").value;

    if (!brideName || !groomName || !contact || !weddingDate) {
        Swal.fire({
            icon: "error",
            title: "Missing Information",
            text: "Please fill in all required fields before submitting.",
            confirmButtonColor: "#d33"
        });
        return;
    }

    let gcashReceipt = document.getElementById("gcashReceipt").files[0];
    let receiptMessage = gcashReceipt ? `<strong>GCash Receipt Uploaded</strong> ✅` : `<strong>No GCash Receipt Uploaded</strong> ❌ (Optional)`;

    Swal.fire({
        icon: "success",
        title: "Request Submitted",
        html: `<strong>Bride:</strong> ${brideName}<br>
               <strong>Groom:</strong> ${groomName}<br>
               <strong>Contact Number:</strong> ${contact}<br>
               <strong>Date:</strong> ${weddingDate}<br>
               ${receiptMessage}`,
        confirmButtonColor: "#28a745"
    }).then(() => {
        document.getElementById("weddingModal").style.display = "none";
        document.getElementById("weddingForm").reset();
    });
});

</script>


    <div id="service-details" style="padding: 20px; margin-top: 20px; border-top: 1px solid #ccc;">
        <center><h3>Service Details</h3></center><br>
        <p id="service-info">Please select a date and a service.</p>

        <!-- Pamisa Form -->
        <form id="pamisa-form" style="display: none; margin-top: 20px;">
            <label for="pamisa-time" style="font-weight: bold; margin-bottom: 5px;">Time:</label>
            <select id="pamisa-time" class="dropdown-field" required 
                style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
                <option value="8:00AM">8:00AM</option>
                <option value="10:00AM">10:00AM</option>
            </select>

            <label for="name-of-intended" style="font-weight: bold; margin-bottom: 5px;">Pangalan ng Ipapamisa:</label>
            <input type="text" id="name-of-intended" class="input-field" placeholder="Enter name" required 
                style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

            <label for="name-of-requestor" style="font-weight: bold; margin-bottom: 5px;">Pangalan ng Nagpamisa:</label>
            <input type="text" id="name-of-requestor" class="input-field" placeholder="Enter name" required 
                style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

            <label for="pamisa-type" style="font-weight: bold; margin-bottom: 5px;">Type of Pamisa:</label>
            <select id="pamisa-type" class="dropdown-field" required 
                style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
                <option value="Thanksgiving">Thanksgiving</option>
                <option value="Birthday">Birthday</option>
                <option value="Special Intention">Special Intention</option>
                <option value="Speedy Recovery">Speedy Recovery</option>
                <option value="Wedding Anniversary">Wedding Anniversary</option>
                <option value="Souls">Souls</option>
            </select>

            <button type="submit" id="save-pamisa" class="service-submit-btn" 
                style="background-color: #2c3e50; color: white; border: none; padding: 12px 20px; 
                    font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; 
                    transition: background 0.3s ease-in-out; box-shadow: 2px 2px 5px rgba(0,0,0,0.2);">
                Save Pamisa
            </button>

        </form>

        <!-- Baptism Form -->
        <form id="baptism-form" style="display: none; margin-top: 20px;">
            <label for="baptized-name" style="font-weight: bold; margin-bottom: 5px;">Name of Baptized Person:</label>
            <input type="text" id="baptized-name" class="input-field" placeholder="Enter name" required 
                style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

            <label for="parents-name" style="font-weight: bold; margin-bottom: 5px;">Name of Parents:</label>
            <input type="text" id="parents-name" class="input-field" placeholder="Enter parents' names" required 
                style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

            <div id="ninongNinangFields">
                <label for="ninong-ninang" style="font-weight: bold; margin-bottom: 5px;">Ninong/Ninang:</label>
                <input type="text" name="ninong_ninang[]" class="input-field" placeholder="Enter name" required 
                    style="width: 100%; padding: 12px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
            </div>

            <br><button type="button" id="addMore" class="add-more-btn" 
                style="background-color: #007BFF; color: white; border: none; padding: 12px 20px; 
                    font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; 
                    transition: background 0.3s ease-in-out; box-shadow: 2px 2px 5px rgba(0,0,0,0.2);">
                Add More Ninong/Ninang
            </button>
            <br><br>

            <button type="submit" id="save-baptism" class="service-submit-btn" 
                style="background-color: #2c3e50; color: white; border: none; padding: 12px 20px; 
                    font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; 
                    transition: background 0.3s ease-in-out; box-shadow: 2px 2px 5px rgba(0,0,0,0.2);">
                Save Baptism
            </button>

        </form>

        <button id="proceed-payment" 
            style="
                display: none; 
                margin-top: 15px; 
                padding: 10px 20px; 
                background-color: #007bff; 
                color: white; 
                font-size: 16px; 
                border: none; 
                border-radius: 5px; 
                cursor: pointer; 
                transition: background-color 0.3s ease;">
            Proceed to Payment
        </button>

        <script>
            document.getElementById('proceed-payment').addEventListener('mouseover', function() {
                this.style.backgroundColor = '#0056b3';
            });

            document.getElementById('proceed-payment').addEventListener('mouseout', function() {
                this.style.backgroundColor = '#007bff';
            });
        </script>

    


<!-- Blessing Form -->
<form id="blessing-form" style="display: none; margin-top: 20px;">
    <label for="blessing-time" style="font-weight: bold; margin-bottom: 5px;">Time:</label>
    <select id="blessing-time" name="blessing_time" class="dropdown-field" required 
        style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
        <option value="9:00AM">9:00AM</option>
        <option value="11:00AM">11:00AM</option>
        <option value="1:00PM">1:00PM</option>
        <option value="3:00PM">3:00PM</option>
    </select>

    <label for="name-of-blessed" style="font-weight: bold; margin-bottom: 5px;">Pangalan ng Ipapabless:</label>
    <input type="text" id="name-of-blessed" name="name_of_blessed" class="input-field" placeholder="Enter name" required 
        style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

    <label for="name-of-requestor-blessing" style="font-weight: bold; margin-bottom: 5px;">Pangalan ng Nagpabless:</label>
    <input type="text" id="name-of-requestor-blessing" name="name_of_requestor" class="input-field" placeholder="Enter name" required 
        style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

        <label for="type-of-blessing" style="font-weight: bold; margin-bottom: 5px;">Type of Blessing:</label>
    <select id="type-of-blessing" name="type_of_blessing" class="dropdown-field" required
        style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">
        <option value="For House">For House</option>
        <option value="For Deceased">For Deceased</option>
        <option value="For Car">For Car</option>
        <option value="For Business">For Business</option>
    </select>

    <label for="donation-receipt" style="font-weight: bold; margin-bottom: 5px;">GCash or Bank Transfer Receipt for Donation:</label>
    <input type="file" id="donation-receipt" name="donation_receipt" class="input-field" accept="image/*,application/pdf"
        style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

    <button type="submit" id="save-blessing" class="service-submit-btn" 
        style="background-color: #2c3e50; color: white; border: none; padding: 12px 20px; 
            font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; 
            transition: background 0.3s ease-in-out; box-shadow: 2px 2px 5px rgba(0,0,0,0.2);">
        Save Blessing
    </button>
</form>
</div>
</div>

<div id="payment-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" id="close-modal">&times;</span>
        <h2>GCash or Bank Transfer Payment</h2>
        <p>Please upload a screenshot of your payment.</p>
        <p>Amount to Pay: <strong>₱100 per name</strong></p>
        <p>GCash Account: <strong>09911895057</strong></p>
        <p>GCash Name: <strong>CHR**** AL****</strong></p>
        <p>Bank Account: <strong>***********</strong></p>
        <p>Bank Name: <strong>CHR**** AL****</strong></p>

        <input type="file" id="gcash-receipt" accept="image/*" required>
        <button id="submit-payment">Submit Payment</button>
    </div>
</div>
</div>


<div id="loading-spinner" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 9999;">
    <div style="width: 50px; height: 50px; border: 5px solid #fff; border-top-color: #3498db; border-radius: 50%; animation: spin 1s linear infinite;"></div>
</div>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<!-- Baptism Payment Modal -->
<div id="payment-modal-baptism" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; padding: 20px; border-radius: 8px; width: 400px; text-align: center;">
    <h2>Upload GCash Receipt or Bank Transfer Receipt</h2>
    <p>Please upload a screenshot of your payment.</p>
        
        <input type="file" id="gcash-receipt-baptism" accept="image/*" style="margin-bottom: 15px;">
        
        <br>
        <button id="submit-payment-baptism" style="background-color: #2c3e50; color: white; border: none; padding: 10px 20px; font-size: 16px; font-weight: bold; border-radius: 5px; cursor: pointer;">Submit Payment</button>
        <button id="close-modal-baptism" style="margin-left: 10px; background-color: #ccc; color: black; border: none; padding: 10px 20px; font-size: 16px; font-weight: bold; border-radius: 5px; cursor: pointer;">Close</button>
    </div>
</div>


<script>
    $(document).ready(function() {
        function renderCalendar() {
    $('#calendar').fullCalendar({
        selectable: true,
        selectHelper: true,
        timezone: 'Asia/Manila',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        events: [],
        dayRender: function(date, cell) {
            if ($('#baptism-form').is(':visible')) {
                if (date.day() === 0) { 
                    cell.css('background-color', '#7ea67b');
                    
                    $.getJSON('fetch_slots.php', { date: date.format('YYYY-MM-DD') }, function(data) {
                        let slotsText = data.slots_remaining > 0 
                            ? `${data.slots_remaining} SLOTS REMAINING`
                            : `Fully Booked`;

                        cell.append(`<br><div style="color: white; font-size: 20px; text-align: center; font-weight: bold;">${slotsText}</div>`);

                        if (data.slots_remaining <= 0) {
                            cell.addClass('fully-booked');
                        }
                    });

                } else {
                    cell.css('background-color', '#F08080');
                    cell.append('<br><div style="color: white; font-size: 13px; text-align: center; font-weight: bold;">Not Allowed for Baptism Schedule</div>');
                }
            }
            else if ($('#blessing-form').is(':visible')) {
                if (selectedDay === 5 || selectedDay === 6) { 
                    cell.css('background-color', '#7ea67b'); 

                    $.getJSON('fetch_slots.php', { date: date.format('YYYY-MM-DD'), service: 'blessing' }, function(data) {
                        let slotsText = data.slots_remaining > 0 ? `${data.slots_remaining} SLOTS REMAINING` : `Fully Booked`;
                        cell.append(`<br><div style="color: white; font-size: 20px; text-align: center; font-weight: bold;">${slotsText}</div>`);
                        if (data.slots_remaining <= 0) cell.addClass('fully-booked');
                    });

                } else {
                    cell.css('background-color', '#F08080');
                    cell.append('<br><div style="color: white; font-size: 13px; text-align: center; font-weight: bold;">Not Allowed for Blessings</div>');
                }
            }

        },

        select: function(start, end) {
        let selectedDate = start.format('YYYY-MM-DD');
        let selectedDay = moment(selectedDate).format('dddd');

        let selectedCell = $('.fc-day[data-date="' + selectedDate + '"]');

        if (selectedCell.hasClass('fully-booked')) {
            Swal.fire("Fully Booked", "This date is already fully booked. Please choose another date.", "warning");
            $('#calendar').fullCalendar('unselect');
            return;
        }

        let events = $('#calendar').fullCalendar('clientEvents', function(event) {
            return event.start.format('YYYY-MM-DD') === selectedDate;
        });

        if ($('#baptism-form').is(':visible')) {
            if (selectedDay !== "Sunday") {
                Swal.fire("Invalid Selection", "Only Sundays are allowed for Baptism bookings.", "error");
                $('#calendar').fullCalendar('unselect');
                return;
            }
        }

        let timeOptions = {
            "Monday": ["6:30AM"],
            "Tuesday": ["6:30AM"],
            "Wednesday": ["6:00PM"],
            "Thursday": ["6:30AM"],
            "Friday": ["6:30AM"],
            "Saturday": ["6:30AM", "5:00PM"],
            "Sunday": ["6:00AM", "8:00AM", "10:00AM", "5:00PM", "6:30PM"]
        };

        let availableTimes = timeOptions[selectedDay] || [];

        if (availableTimes.length === 0) {
            Swal.fire("No Available Time", "No mass schedules for " + selectedDay, "error");
            $('#calendar').fullCalendar('unselect');
            return;
        }

        if (selectedDay === "Saturday" || selectedDay === "Sunday") {
            if (events.length === availableTimes.length) {
                Swal.fire("Fully Booked", "All time slots for this day are booked. Please choose another date.", "warning");
                $('#calendar').fullCalendar('unselect');
                return;
            }
        } else {
            if (events.length > 0) {
                Swal.fire("Date Not Available", "This date is already booked. Please choose another date.", "warning");
                $('#calendar').fullCalendar('unselect');
                return;
            }
        }

        $('#service-info').html('<strong>Selected Date:</strong> ' + selectedDate + '.<br>');

        let timeSelect = $('#pamisa-time');
        timeSelect.empty();
        availableTimes.forEach(time => {
            if (!events.some(event => event.start.format('HH:mm A') === time)) {
                timeSelect.append(new Option(time, time));
            }
        });

        $('#proceed-payment').hide();
    }

            });
        }

        renderCalendar();

    $('#pamisa').click(function() {
    $('#calendar').fullCalendar('destroy');
    
    setTimeout(function() { 
        renderPamisaCalendar(); 
        $('#calendar').fullCalendar('removeEvents'); 

        $.getJSON('fetch_booked_dates.php', function(data) {
            $('#calendar').fullCalendar('addEventSource', data);
        });

        $('#service-info').html('<strong>Service:</strong> Pamisa<br>Please select a date.');
        $('#pamisa-form').show();
        $('#baptism-form').hide();
        $('#blessing-form').hide();
        $('#proceed-payment').hide();
    }, 100);
});



function renderPamisaCalendar() {
    $('#calendar').fullCalendar({
        selectable: true,
        selectHelper: true,
        timezone: 'Asia/Manila',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        events: [],

        dayRender: function(date, cell) {
            cell.css('background-color', ''); 
            cell.find('div').remove();
        },

        select: function(start, end) {
            let selectedDate = start.format('YYYY-MM-DD');
            let selectedDay = moment(selectedDate).format('dddd');

            let events = $('#calendar').fullCalendar('clientEvents', function(event) {
                return event.start.format('YYYY-MM-DD') === selectedDate;
            });

            let timeOptions = {
                "Monday": ["6:30AM"],
                "Tuesday": ["6:30AM"],
                "Wednesday": ["6:00PM"],
                "Thursday": ["6:30AM"],
                "Friday": ["6:30AM"],
                "Saturday": ["6:30AM", "5:00PM"],
                "Sunday": ["6:00AM", "8:00AM", "10:00AM", "5:00PM", "6:30PM"]
            };

            let availableTimes = timeOptions[selectedDay] || [];

            if (availableTimes.length === 0) {
                Swal.fire("No Available Time", "No mass schedules for " + selectedDay, "error");
                $('#calendar').fullCalendar('unselect');
                return;
            }

            if (selectedDay === "Saturday" || selectedDay === "Sunday") {
                if (events.length === availableTimes.length) {
                    Swal.fire("Fully Booked", "All time slots for this day are booked. Please choose another date.", "warning");
                    $('#calendar').fullCalendar('unselect');
                    return;
                }
            } else {
                if (events.length > 0) {
                    Swal.fire("Date Not Available", "This date is already booked. Please choose another date.", "warning");
                    $('#calendar').fullCalendar('unselect');
                    return;
                }
            }

            $('#service-info').html('<strong>Selected Date:</strong> ' + selectedDate + '.<br>');

            let timeSelect = $('#pamisa-time');
            timeSelect.empty();
            availableTimes.forEach(time => {
                if (!events.some(event => event.start.format('HH:mm A') === time)) {
                    timeSelect.append(new Option(time, time));
                }
            });

            $('#proceed-payment').hide();
        }
    });
}

function renderBlessingCalendar() {
    $('#calendar').fullCalendar({
        selectable: true,
        selectHelper: true,
        timezone: 'Asia/Manila',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        events: function(start, end, timezone, callback) {
            $.ajax({
                url: "fetch_blessings.php",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    callback(response.events);
                    window.bookedSlots = response.booked_slots; // Store booked slots globally
                }
            });
        },
        displayEventTime: false, 
        eventRender: function(event, element) {
            element.find('.fc-title').text(event.title);
        },
        dayRender: function (date, cell) {
            if (date.day() === 5 || date.day() === 6) {
                cell.css('background-color', '#fcfcfc');
            } else {
                cell.css('background-color', '#F08080');
                cell.append('<br><div style="color: white; font-size: 13px; text-align: center; font-weight: bold;">Not Allowed for Blessings</div>');
            }
        },
        select: function (start) {
            let selectedDate = start.format('YYYY-MM-DD');
            let selectedDay = moment(selectedDate).format('dddd');

            if (!(start.day() === 5 || start.day() === 6)) {
                Swal.fire("Invalid Selection", "Blessings can only be scheduled on Fridays and Saturdays.", "error");
                $('#calendar').fullCalendar('unselect');
                return;
            }

            // Check if the selected date has 4 full booked slots
            let bookedTimes = window.bookedSlots[selectedDate] || [];
            let requiredTimes = ["09:00:00", "11:00:00", "13:00:00", "15:00:00"];
            let allSlotsBooked = requiredTimes.every(time => bookedTimes.includes(time));

            if (allSlotsBooked) {
                Swal.fire("Fully Booked", `The selected date (${selectedDate}) is already full. Please choose another date.`, "error");
                $('#calendar').fullCalendar('unselect');
                return;
            }

            $('#service-info').html(`<strong>Selected Date:</strong> ${selectedDate}`);

            Swal.fire("Date Selected", `You have chosen ${selectedDay}, ${selectedDate} for a blessing.`, "success");
        }
    });
}


$(document).ready(function () {
    $("#blessing-form").submit(function (e) {
        e.preventDefault();
        let formData = new FormData(this);

        let selectedDate = $("#service-info").text().replace("Selected Date:", "").trim();
        if (!selectedDate) {
            Swal.fire("Error", "Please select a blessing date first.", "error");
            return;
        }

        formData.append("blessing_date", selectedDate);

        $.ajax({
            url: "save_blessing.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
            console.log("Server Response:", response); 
            try {
                let res = JSON.parse(response);

                Swal.fire({
                    title: res.status.toUpperCase(),
                    text: res.message,
                    icon: res.status
                }).then(() => {
                    if (res.status === "success") {
                        $("#blessing-form")[0].reset();
                    }
                });
            } catch (error) {
                console.error("JSON Parsing Error:", error, response);
                Swal.fire("Success", "Blessing request was successfully sent.", "success");

            }
        }

        });
    });
});





        $('#pamisa-form').submit(function(e) {
            e.preventDefault();
            $('#loading-spinner').css('display', 'flex');

            let data = {
                name_of_intended: $('#name-of-intended').val(),
                name_of_requestor: $('#name-of-requestor').val(),
                pamisa_type: $('#pamisa-type').val(),
                selected_date: $('#service-info').text().match(/\d{4}-\d{2}-\d{2}/)[0],
                selected_time: $('#pamisa-time').val()
            };

            $.post('save_pamisa.php', data, function(response) {
                let res = JSON.parse(response);
                $('#loading-spinner').hide();

                if (res.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: res.message
                    }).then(() => {
                        $('#proceed-payment').show();
                        $('#calendar').fullCalendar('refetchEvents');
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: res.message
                    });
                }
            }).fail(function() {
                $('#loading-spinner').hide();
                Swal.fire("Oops...", "Something went wrong. Please try again!", "error");
            });
        });

        $('#baptismBtn').click(function() {
        $('#pamisa-form').hide();
        $('#baptism-form').show();
        $('#blessing-form').hide();
        $('#service-info').html('<strong>Service:</strong> Baptism<br>Please select a Sunday.');
        $('#proceed-payment').hide();
        $('#calendar').fullCalendar('destroy'); 
        renderCalendar(); 
    });

        $('#blessing').click(function() {
        $('#pamisa-form, #baptism-form').hide();
        
        $('#blessing-form').show();
        $('#service-info').html('<strong>Service:</strong> Blessing<br>Please select a date.');
        $('#proceed-payment').hide();
        
        $('#calendar').fullCalendar('destroy'); 
        renderBlessingCalendar(); 
    });


        $('#addMore').click(function() {
            $('#ninongNinangFields').append(`
                <div>
                    <input type="text" name="ninong_ninang[]" class="input-field" placeholder="Enter name" required> 
                    <br><button type="button" class="remove">Remove</button>
                </div>
            `);
        });

        $(document).on("click", ".remove", function() {
            $(this).parent().remove();
        });

        
        $('#baptism-form').submit(function(e) {
        e.preventDefault();
        $('#loading-spinner').css('display', 'flex');

        let ninongsNinangs = [];
        $('input[name="ninong_ninang[]"]').each(function() {
            ninongsNinangs.push($(this).val());
        });

        let selectedDateMatch = $('#service-info').text().match(/\d{4}-\d{2}-\d{2}/);
        let selectedDate = selectedDateMatch ? selectedDateMatch[0] : null;

        let data = {
            baptized_name: $('#baptized-name').val(),
            parents_name: $('#parents-name').val(),
            ninongs_ninangs: ninongsNinangs,
            selected_date: selectedDate
        };

        $.ajax({
            url: 'save_baptism.php',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: "application/json",
            success: function(response) {
                $('#loading-spinner').hide();
                let res;
                try {
                    res = JSON.parse(response);
                    if (res.status === "success") {
                        let additionalNinongs = Math.max(0, ninongsNinangs.length - 2);
                        let totalAmount = 500 + (additionalNinongs * 30);

                        $('#total-payment').text(`Total Amount: ₱${totalAmount}`);

                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: "Baptism request saved. Close the payment if it's already paid but receipt is not available.",
                            allowOutsideClick: false
                        }).then(() => {
                            $('#payment-modal-baptism').fadeIn(); 
                        });

                    } else {
                        Swal.fire("Error", res.message, "error");
                    }
                } catch (e) {
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: "Baptism request saved. Close the payment if it's already paid but receipt is not available.",
                        allowOutsideClick: false
                    }).then(() => {
                        $('#payment-modal-baptism').fadeIn(); 
                    });
                }
            },
            error: function() {
                $('#loading-spinner').hide();
                Swal.fire("Oops...", "Something went wrong. Please try again!", "error");
            }
        });
    });


    $('#proceed-payment-baptism').click(function() {
        $('#payment-modal-baptism').fadeIn();
    });

    $('#close-modal-baptism').click(function() {
        $('#payment-modal-baptism').fadeOut();
    });

    $('#proceed-payment').click(function() {
        $('#payment-modal').fadeIn();
    });

    $('#close-modal').click(function() {
        $('#payment-modal').fadeOut();
    });

    $('#submit-payment').click(function() {
        let fileInput = $('#gcash-receipt')[0].files[0];
        if (!fileInput) {
            Swal.fire("Error", "Please upload a GCash receipt.", "error");
            return;
        }

        let formData = new FormData();
        formData.append('gcash_receipt', fileInput);

        $('#loading-spinner').css('display', 'flex');

        $.ajax({
            url: 'upload_payment.php', 
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#loading-spinner').hide();
                Swal.fire("Payment Submitted", "Your payment has been received. Please check your email.", "success");
            },
            error: function() {
                $('#loading-spinner').hide();
                Swal.fire("Oops...", "Something went wrong. Please try again!", "error");
            }
        });
    });

$('#submit-payment-baptism').click(function() {
    let fileInput = $('#gcash-receipt-baptism')[0].files[0];
    if (!fileInput) {
        Swal.fire("Error", "Please upload a GCash receipt for Baptism.", "error");
        return;
    }

    let formData = new FormData();
    formData.append('gcash_receipt_baptism', fileInput);

    $('#loading-spinner').css('display', 'flex');

    $.ajax({
        url: 'upload_payment_baptism.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#loading-spinner').hide();

            try {
                let jsonResponse = JSON.parse(response);

                if (jsonResponse.status === "success") {
                    Swal.fire("Payment Submitted", "Your Baptism payment has been received. Please check your email.", "success")
                        .then(() => {
                            $('#payment-modal-baptism').fadeOut();
                        });
                } else {
                    Swal.fire("Error", jsonResponse.message, "error");
                }
            } catch (e) {
                console.log("Invalid JSON response:", response);
                Swal.fire("Success", "Baptism payment saved.", "success");
            }
        },
        error: function(xhr, status, error) {
            $('#loading-spinner').hide();
            console.error("AJAX Error:", error);
            Swal.fire("Oops...", "Something went wrong. Please try again!", "error");
        }
    });
});


    function fetchAvailableSlots(selectedDate) {
        return $.getJSON('fetch_slots.php', { date: selectedDate });
    }

    $('#calendar').fullCalendar({
        selectable: true,
        select: function(start) {
            let selectedDate = start.format('YYYY-MM-DD');

            fetchAvailableSlots(selectedDate).done(function(data) {
                if (data.slots_remaining > 0) {
                    $('#service-info').html(`<strong>Selected Date:</strong> ${selectedDate} (Slots left: ${data.slots_remaining})`);
                } else {
                    Swal.fire("Fully Booked", "No slots left for this date.", "warning");
                    $('#calendar').fullCalendar('unselect');
                }
            });
        }
    });
});
    </script>


<script>
$(document).ready(function () {
    $('#notification-link').on('click', function (e) {
        $.ajax({
            url: 'clear_notifications.php',
            method: 'POST',
            success: function () {
                $('.notification-badge').fadeOut();
            }
        });
    });
});
</script>


    <style>
        .header {
            background: #2c3e50;
            color: white;
        }


        .datetime {
            text-align: center;
            font-size: 18px;
            color: #555;
            margin-bottom: 20px;
        }

        .admin-greeting {
            text-align: center;
            font-size: 35px;
            font-weight: bold;
            color: rgb(88, 177, 90);
        }

        body {
            font-family: Arial, sans-serif;
            background-color: rgb(241, 243, 240); 
        }
        .notification-badge {
            background: red;
            color: white;
            padding: 3px 8px;
            border-radius: 50%;
            font-size: 12px;
            margin-left: 5px;
        }

    .input-field {
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 6px;
        width: 250px;
        margin-right: 10px;
    }

    .remove {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 8px 12px;
        font-size: 14px;
        font-weight: bold;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.3s ease-in-out;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
    }

    .remove:hover {
        background-color: #c82333;
    }

    #ninongNinangFields div {
        margin-bottom: 10px;
    }

    input {
        padding: 8px;
        width: 80%;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        overflow-y: auto; 
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto; 
        position: relative;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 25px;
        cursor: pointer;
    }

    .submit-btn {
        background-color: #2c3e50;
        color: white;
        border: none;
        cursor: pointer;
        padding: 10px 15px;
        border-radius: 6px;
    }

    .close-btn {
        background-color: #d32f2f;
        color: white;
        border: none;
        cursor: pointer;
        padding: 10px 15px;
        border-radius: 6px;
    }

    .submit-btn:hover, .close-btn:hover {
        opacity: 0.8;
    }

    .notice {
        color: red;
        font-weight: bold;
        font-size: 15px;
    }

    .payment-info {
        color: black;
        font-size: 15px;
    }
    </style>


<footer>
    <div class="footer-container">
        <div class="footer-about">
        <h3>About Parish of the Holy Cross</h3>
            <p>
                The Parish of the Holy Cross is a sacred place of worship, where the community comes together to celebrate faith, hope, and love. Whether you're seeking spiritual growth, a peaceful moment of reflection, or a place to connect with others, our church provides a welcoming environment for all.
            </p>

        </div>
        <div class="footer-contact">
            <h3>Contact Us</h3>
            <p>Email: holycrossparish127@yahoo.com</p>
            <p>Phone: 28671581</p>
            <p>Address: Gen. T. De Leon, Valenzuela, Philippines, 1442 </p>
        </div>
        <div class="footer-socials">
            <h3>Follow Us</h3>
            <a href="https://www.facebook.com/ParishoftheHolyCrossValenzuelaCityOfficial/">Facebook</a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 Parish of the Holy Cross. All rights reserved.</p>
    </div>
</footer>
</body>
</html>
