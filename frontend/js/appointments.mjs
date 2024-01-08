import {apiUrl, appointmentContainer, appointmentsContainer, backButton, toggleFormButton} from "./constants.js";
import {displayAppointment} from "./appointment.mjs";

const fetchAppointments = async () => {
    const response = await fetch(apiUrl + '/appointments');
    if (!response.ok) {
        const error = new Error(`Error ${response.status}: ${response.statusText}`);
        error.status = response.status;
        throw error;
    }
    return await response.json();
}

const renderAppointments = async (appointments) => {
    if (appointments.length === 0) {
        const noAppointmentsHeader = $('<h1 class="text-center w-50 my-3 text-white mx-auto"></h1>');
        $(noAppointmentsHeader).text('No appointments found...');
        $(appointmentsContainer).append(noAppointmentsHeader);
        return;
    }
    const currentDate = new Date();
    const oneDay = 24 * 60 * 60 * 1000; // One day in milliseconds

    appointments.forEach(appointment => {
        const expirationDate = new Date(appointment.expiration_date);
        const timeDiff = expirationDate.getTime() - currentDate.getTime();
        const daysDiff = Math.ceil(timeDiff / oneDay);
        const isExpired = currentDate > expirationDate;
        const isExpiringToday = daysDiff === 0;
        const isExpiringTomorrow = daysDiff === 1;
        const isExpiringThisWeek = daysDiff > 1 && daysDiff <= 7;

        const card = `
    <div class="col mx-auto">
        <div class="card h-100 ${isExpired ? 'expired' : ''}">
            <div class="card-body">
                <h5 class="card-title clickable" data-appointment-id="${appointment.id}">${appointment.title}</h5><br />
                <h6 class="card-subtitle mb-2 text-muted">Location: ${appointment.location}</h6><br />
                <p class="card-text">${appointment.description}</p> <br />
               
                ${isExpired ? '<p class="card-text text-danger">Expired</p>' :
            isExpiringToday ? '<p class="card-text text-warning"><b>Expiring today!</b></p>' :
                isExpiringTomorrow ? '<p class="card-text text-warning"><b>Expiring tomorrow!</b></p>' :
                    isExpiringThisWeek ? `<p class="card-text text-warning"><b>Expiring in ${daysDiff} days</b></p>` :
                        ''}
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <p class="mb-0"><b><small class="text-muted">Expiration Date: ${appointment.expiration_date}</small></b></p>
                <div class="my-auto clickable" data-appointment-id="${appointment.id}">
                    <i class="fa-solid fa-trash"></i>
                </div>
            </div>
        </div>              
    </div>
`;

        $(appointmentsContainer).append(card);
    });

    $('h5.clickable[data-appointment-id]').on('click', (event) => displayAppointment(event.target.dataset.appointmentId));
    $('div[data-appointment-id]').on('click', (event) => {
        const appointmentId = $(event.target).closest('[data-appointment-id]').data('appointment-id');
        deleteAppointment(appointmentId);
    });

};


const dateFormatter = (date) => {
    const pad = (num) => (num < 10 ? '0' + num : num);
    const year = date.getFullYear();
    const month = pad(date.getMonth() + 1); // Months are 0-indexed, so add 1 to get the correct month number
    const day = pad(date.getDate());

    const formattedDate = `${year}-${month}-${day}`;
    return formattedDate;
}

const deleteAppointment = async (appointmentId) => {
    const response = await fetch(apiUrl + '/appointments/' + appointmentId + '/', {
        method: 'DELETE',
    });
    // Create flash message div
    const flashMessage = $('<div class="alert fade show text-center mx-auto" role="alert"></div>');
    if (!response.ok) {
        $(flashMessage).addClass('alert-danger');
        $(flashMessage).text('Could not delete, something went wrong...');
    } else {
        $(flashMessage).addClass('alert-success');
        $(flashMessage).text('Appointment deleted successfully!');
        // Remove with fading column with card where h5 has data-appointment-id equal to appointmentId
        $(`h5[data-appointment-id="${appointmentId}"]`).parent().parent().parent().fadeOut(1000, () => {
            $(this).remove();
        });
    }
    // Show flash message between header and appointments container and fade it out after 3 seconds with deleting it
    $(flashMessage).insertAfter('h1').delay(1000).fadeOut(1000, () => {
        $(this).remove();
    });
}


$(document).ready(function () {
    $('#toggleForm').click(function () {
        $('#appointment-form-container').toggle();
    });
});


document.getElementById("add-date-button").addEventListener("click", function (event) {
    event.preventDefault();
    addDateInput()
});

function addDateInput() {
    const appointmentDates = document.getElementById("appointment_dates");

    const dateInputDiv = document.createElement("div");
    dateInputDiv.classList.add("date-input");
    dateInputDiv.classList.add("mb-5");

    // create a new date input
    const dateInput = document.createElement("input");
    dateInput.type = "date";
    dateInput.classList.add("form-control");
    dateInput.required = true;
    dateInput.name = "date";
    dateInputDiv.appendChild(dateInput);

    // create a new start time input
    const startTimeInput = document.createElement("input");
    startTimeInput.type = "time";
    startTimeInput.classList.add("form-control");
    startTimeInput.required = true;
    startTimeInput.name = "start_time";
    dateInputDiv.appendChild(startTimeInput);

    // create a new end time input
    const endTimeInput = document.createElement("input");
    endTimeInput.type = "time";
    endTimeInput.classList.add("form-control");
    endTimeInput.required = true;
    endTimeInput.name = "end_time";
    dateInputDiv.appendChild(endTimeInput);

    appointmentDates.appendChild(dateInputDiv);
}


const addAppointment = async (event) => {
    event.preventDefault();
    let data = {
        title: document.getElementById("title").value,
        description: document.getElementById("description").value,
        location: document.getElementById("location").value,
        expiration_date: dateFormatter(new Date(document.getElementById("expiration_date").value)),
        appointment_dates: []
    }

    const dateInputs = document.querySelectorAll(".date-input");

    dateInputs.forEach((dateInput) => {
        const date = dateFormatter(new Date(dateInput.querySelector('input[name="date"]').value));
        const start_time = dateInput.querySelector('input[name="start_time"]').value;
        const end_time = dateInput.querySelector('input[name="end_time"]').value;
        data.appointment_dates.push({date, start_time, end_time});
    });

    const response = await fetch(apiUrl + '/appointments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    });

    // Create flash message div
    const flashMessage = $('<div class="alert fade show text-center mx-auto" role="alert"></div>');
    if (!response.ok) {
        $(flashMessage).addClass('alert-danger');
        $(flashMessage).text('Could not create appointment, something went wrong...');
    } else {
        $(flashMessage).addClass('alert-success');
        $(flashMessage).text('Appointment created successfully!');
    }
    $(flashMessage).insertAfter('h1').delay(1000).fadeOut(1000, () => {
        $(appointmentContainer).prepend(flashMessage);
    });

    $('#appointment-form-container').toggle(); // toggle the form container
    const createdAppointment = await response.json();

    $(flashMessage).alert();
    setTimeout(() => $(flashMessage).alert('close'), 3000);
    await displayAppointments();
}


const inputDate = document.getElementById("appointment_dates").value;
const year = new Date(inputDate).getFullYear();

if (year.toString().length > 4) {
    console.log("The year has more than 4 digits!");
}


const addButton = document.getElementById('add-date-button');
const dateInputs = document.querySelectorAll('.date-input');

addButton.addEventListener('click', () => {
    const lastDateInput = dateInputs[dateInputs.length - 1];
    const startDateInput = lastDateInput.querySelector('.start-time');
    const endDateInput = lastDateInput.querySelector('.end-time');

    startDateInput.addEventListener('change', () => {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (startDate > endDate) {
            alert('The end date must be later than the start date');
            endDateInput.value = '';
        }
    });
});


document.getElementById("clear-all-button").addEventListener("click", function () {
    document.getElementById("appointment-form").reset();
});


const prepareAppointments = () => {
    if (appointmentsContainer.children.length > 0) {
        $(appointmentsContainer).empty();
    }
    $(appointmentContainer).hide();
    $(appointmentsContainer).show();
    $(backButton).hide();
    $(toggleFormButton).show();

}

const displayAppointments = async () => {
    prepareAppointments();
    try {
        const appointments = await fetchAppointments();
        await renderAppointments(appointments);
    } catch (error) {
        let errorHeader = $('<h1 class="text-center w-50 my-3 text-white mx-auto"></h1>');
        $(errorHeader).text('Something went wrong...');
        $(appointmentsContainer).append(errorHeader);
    }
};

export {displayAppointments, addAppointment};

