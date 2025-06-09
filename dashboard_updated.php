<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connection.php'; 
$user_id = $_SESSION['user_id'];

$sql = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();

date_default_timezone_set('Asia/Manila');
$current_datetime = date("l, F j, Y g:i A");
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
</head>
<body id="bodyTag">
    <header class="header" id="header">
    </header>

    <div class="l-navbar" id="nav-bar">
        <nav class="nav">
            <div>
                <a href="#" class="nav_logo">
                    <img src="imgs/logo.png" alt="Parish Logo" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                    <span class="nav_logo-name">Parish of the Holy Cross</span>
                </a>
                <div class="nav_list">
                    <a href="#" class="nav_link active"><i class='bx bx-grid-alt nav_icon'></i> <span class="nav_name">Dashboard</span></a>
                    <a href="profile.php" class="nav_link"><i class='bx bx-user nav_icon'></i> <span class="nav_name">My Profile</span></a>
                    <a href="history.php" class="nav_link"><i class='bx bx-message-square-detail nav_icon'></i> <span class="nav_name">History</span></a>
                </div>
            </div>
            <a href="#" class="nav_link" id="logout"><i class='bx bx-log-out nav_icon'></i> <span class="nav_name">Sign Out</span></a>
        </nav>
    </div>

    <section class="welcome">
        <center><h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2></center>
        <center><p>Current Date and Time: <?php echo $current_datetime; ?></p></center>
    </section>
    <section class="about-us">
    <h2>About Us</h2>
    <p class="justified">
           We are a community dedicated to spreading love, peace, and faith. Our parish in Valenzuela City is a place for everyone to come together, learn, grow, and find inspiration. With a rich history, we continue to serve with passion, welcoming all who wish to be a part of our mission. Join us in our journey as we strive to make a positive impact on our community and the world.
    </p>
</section>
    <section class="main-content">
    <div class="calendar-container" style="display: flex; justify-content: space-between; gap: 20px; padding: 20px;">
        <div id="calendar" style="flex: 1; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
        </div>





        <div class="services-buttons" style="flex: 0.3; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
    <button id="pamisa" class="service-btn">Mass</button>
    <button id="baptismBtn" class="service-btn">Baptism</button>
    <button id="wedding" class="service-btn">Wedding</button>
    <button id="blessing" class="service-btn">Blessing</button>

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

            <label for="name-of-intended" style="font-weight: bold; margin-bottom: 5px;">Name of the Intended:</label>
            <input type="text" id="name-of-intended" class="input-field" placeholder="Enter name" required 
                style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

            <label for="name-of-requestor" style="font-weight: bold; margin-bottom: 5px;">Name of the Requester:</label>
            <input type="text" id="name-of-requestor" class="input-field" placeholder="Enter name" required 
                style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">

            <label for="pamisa-type" style="font-weight: bold; margin-bottom: 5px;">Mass Intention:</label>
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
                style="background-color: #4CAF50; color: white; border: none; padding: 12px 20px; 
                    font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; 
                    transition: background 0.3s ease-in-out; box-shadow: 2px 2px 5px rgba(0,0,0,0.2);">
                Save Mass
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
                style="background-color: #4CAF50; color: white; border: none; padding: 12px 20px; 
                    font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer; 
                    transition: background 0.3s ease-in-out; box-shadow: 2px 2px 5px rgba(0,0,0,0.2);">
                Save Baptism
            </button>

        </form>

        <button id="proceed-payment" style="display: none; margin-top: 15px;">Proceed to Payment</button>
    </div>
</div>


<div id="payment-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" id="close-modal">&times;</span>
        <h2>GCash Payment</h2>
        <p>Please upload a screenshot of your GCash payment.</p>
        <p>Amount to Pay: <strong>₱100 per name</strong></p>
        <p>GCash Account: <strong>09911895057</strong></p>
        <p>GCash Name: <strong>CHR**** AL****</strong></p>

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
        },
        select: function(start, end) {
            let selectedDate = start.format('YYYY-MM-DD');

            let cell = $(`td[data-date="${selectedDate}"]`);
            if (cell.hasClass('fully-booked')) {
                Swal.fire("Fully Booked", "This date is already fully booked. Please select another date.", "error");
                $('#calendar').fullCalendar('unselect');
                return;
            }

            $.getJSON('fetch_slots.php', { date: selectedDate }, function(data) {
                if (data.slots_remaining > 0) {
                    $('#service-info').html(`<strong>Selected Date:</strong> ${selectedDate} (Slots left: ${data.slots_remaining})`);
                } else {
                    Swal.fire("Fully Booked", "No slots left for this date.", "warning");
                    $('#calendar').fullCalendar('unselect');
                }
            });
        
            },
                select: function(start, end) {
                    let selectedDate = start.format('YYYY-MM-DD');
                    let selectedDay = moment(selectedDate).format('dddd');

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

        $('#service-info').html('<strong>Service:</strong> Mass<br>Please select a date.');
        $('#pamisa-form').show();
        $('#baptism-form').hide();
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
        $('#service-info').html('<strong>Service:</strong> Baptism<br>Please select a Sunday.');
        $('#proceed-payment').hide();
        $('#calendar').fullCalendar('destroy'); 
        renderCalendar(); 
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
                    $('#proceed-payment-baptism').show();
                } else {
                    Swal.fire("Error", res.message, "error");
                }
            } catch (e) {
                Swal.fire("Success", "Baptism request saved. Please proceed to payment.", "success");
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
    </script>





<style>
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
</style> 
    <script src="scriptd.js"></script>
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


<script>
function calculatePayment() {
    let basePrice = 500;
    let freeSponsors = 2;
    let sponsorFee = 30;

    let sponsorCount = parseInt(document.getElementById("sponsorCount").value) || 0;
    let additionalSponsors = Math.max(0, sponsorCount - freeSponsors);
    let totalAmount = basePrice + (additionalSponsors * sponsorFee);

    document.getElementById("totalAmount").innerText = "Total Payment: ₱" + totalAmount;
    document.getElementById("proceedToPayment").style.display = "block";
}

function showPaymentModal() {
    document.getElementById("paymentModal").style.display = "block";
}

function closePaymentModal() {
    document.getElementById("paymentModal").style.display = "none";
}
</script>

<!-- Payment Section -->
<input type="number" id="sponsorCount" placeholder="Enter number of Ninong/Ninang" oninput="calculatePayment()">
<p id="totalAmount">Total Payment: ₱500</p>
<button id="proceedToPayment" style="display:none;" onclick="showPaymentModal()">Proceed to Payment</button>

<!-- Payment Modal -->
<div id="paymentModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:20px; border-radius:10px;">
    <h2>GCash Payment</h2>
    <p>Send your payment to: <strong>09XXXXXXXXX</strong></p>
    <p id="modalTotalAmount"></p>
    <button onclick="closePaymentModal()">Close</button>
</div>
