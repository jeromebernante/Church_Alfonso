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

        $('#service-info').html('<strong>Service:</strong> Pamisa<br>Please select a date.');
        $('#pamisa-form').show();
        $('#baptism-form').hide();
        $('#proceed-payment').hide();
    }, 100);
});

$('#blessings').click(function() {
    $('#calendar').fullCalendar('destroy'); // Reset Calendar
    $('#pamisa-form, #baptism-form, #proceed-payment').hide(); // Hide all forms
    $('#service-info').html('<strong>Service:</strong> Blessings<br>Please select a date.'); // Reset service info

    setTimeout(function() { 
        renderBlessingsCalendar(); 
        $('#calendar').fullCalendar('removeEvents'); 

        $.getJSON('fetch_booked_dates.php', function(data) {
            $('#calendar').fullCalendar('addEventSource', data);
        });

    }, 300);
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

function renderBlessingsCalendar() {
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
            let dayOfWeek = date.day(); // 5 = Friday, 6 = Saturday
            if (dayOfWeek !== 5 && dayOfWeek !== 6) {
                cell.css('background-color', '#F08080');
                cell.append('<br><div style="color: white; font-size: 13px; text-align: center; font-weight: bold;">Not Available</div>');
            }
        },

        select: function(start, end) {
            let selectedDate = start.format('YYYY-MM-DD');
            let selectedDay = moment(selectedDate).day();

            if (selectedDay !== 5 && selectedDay !== 6) {
                Swal.fire("Invalid Selection", "Blessings are only available on Fridays and Saturdays.", "error");
                $('#calendar').fullCalendar('unselect');
                return;
            }

            let availableTimes = ["10:00AM", "11:00AM", "1:00PM", "2:00PM", "3:00PM"]; // 1-hour gaps

            let timeSelect = $('#blessings-time');
            timeSelect.empty();
            availableTimes.forEach(time => {
                timeSelect.append(new Option(time, time));
            });

            $('#service-info').html('<strong>Selected Date:</strong> ' + selectedDate + '.<br>');
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

                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: "Baptism request saved. Please proceed to payment.",
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
                        text: "Baptism request saved. Please proceed to payment.",
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
                Swal.fire("Error", "Invalid response from the server. Please contact support.", "error");
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