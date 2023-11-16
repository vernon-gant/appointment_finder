import * as constants from "./constants.js";
import {apiUrl, appointmentContainer, appointmentsContainer, backButton, form, toggleFormButton} from "./constants.js";

const fetchAppointment = async (appointmentId) => {
    const appointment = await fetch(apiUrl + '/appointments/' + appointmentId);
    const appointmentJson = await appointment.json();
    const appointmentDates = await fetch(apiUrl + '/appointments/' + appointmentId + '/appointment-dates');
    appointmentJson.appointmentDates = await appointmentDates.json();
    const votes = await fetch(apiUrl + '/appointments/' + appointmentId + '/votes');
    appointmentJson.votes = await votes.json();
    const comments = await fetch(apiUrl + '/appointments/' + appointmentId + '/comments');
    appointmentJson.comments = await comments.json();
    return appointmentJson;
}

const setTemplate = () => {
    $(appointmentContainer).html(`<br /> <br />
        <div class="appointment-all"><br /> 
        <ul>
             <h2 class="appointment-title mx-auto" id="appointment-title"><b></b></h2> <br /><br />
             <b><h6 style="font-size: 20px;" class="text-muted" id="appointment-location"></h6></b><br />
             <p style="font-size: 24px;" id="appointment-description"></p>    
             <p style="font-size: 24px;">
                 <br /><b><small class="text-muted" id="appointment-expiration-date"></small></b>
             </p>      
        </ul><br />
        
        
        <div class="alert alert-success" role="alert" id="flash-message" style="display: none"></div>
       
        <br /><form id="vote-form" class="w-100">
            <table class="table table-hover  " id="appointment-table">
                <thead >
                    <tr>
                        <th class="calendar-cell"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table> <br /> <br /> 
        </form> <br /><br /> <br />
           
   
        
       <div class="header-bar"> <h2 class="h3 font-weight-bold " name="logo"><b> &nbsp;Comments</b></h2>  </div> <br /> 
            <ul class="list-group list-group-light fade-in-text" style="
                font-family: 'Quicksand';
                box-shadow: rgba(0, 0, 0, 0.1) 0px 1px 2px 0px; 
                border-top: 5px double #15accc;
                border-bottom: 6px double #ffb50b;
                background: #ffffff;
                border-width: medium;
                " id="appointment-comments"></ul>`);


}

const prepareAppointment = () => {
    $(appointmentsContainer).hide();
    $(backButton).show();
    $('#toggleForm').hide();
    $('#appointment-form-container').hide();
    setTemplate();
}

const renderAppointmentHeader = (title, location, description, expiration_date, appointmentId) => {
    // Add data-appointment-id to the appointment title
    $('#appointment-title').text(title).attr('data-appointment-id', appointmentId);
    $('#appointment-location').text(`Location: ${location}`);
    $('#appointment-description').text(description);
    $('#appointment-expiration-date').text(`Expiration Date: ${expiration_date}`);
}

const renderDateHeaders = (appointmentDates) => {
    const dateHeaders = appointmentDates.map(date => `
        <th class="calendar-cell">${date.date}<br>${date.start_time.substring(0, 5)} - ${date.end_time.substring(0, 5)}</th>
    `).join('');

    $('#appointment-table thead tr').html(`<th></th>${dateHeaders}`);
};

const renderUserRows = (votes, appointmentDates) => {
    const userRows = Object.entries(votes).map(([userName, userVotes]) => {
        const userCells = appointmentDates.map(date => {
            const voted = userVotes.some(vote => vote.appointment_date_id === date.id);
            return `<td class="text-center">${voted ? '✔️' : ''}</td>`;
        }).join('');

        return `
            <tr>
                <th class="ps-2">${userName}</th>
                ${userCells}
            </tr>
        `;
    }).join('');

    $('#appointment-table tbody').html(userRows);
};

const renderForm = (appointmentDates) => {
    const footerForm = `<tfoot>
                                   <tr class="table-light">
                                       <th>
                                           <input type="text" id="user-name" name="user_name" style="font-size: larger" placeholder="Your name" required>
                                       </th>
                                   </tr>
                               </tfoot>`;
    const checkboxes = appointmentDates.map(date => `
        <td class="text-center">      
        <div class="checkbox-wrapper-30">
          <span class="checkbox">
             <input type="checkbox" name="appointmentDateIds[]" value="${date.id}" />
                <svg>
                  <use xlink:href="#checkbox-30" class="checkbox"></use>
                </svg>
          </span>
            <svg xmlns="http://www.w3.org/2000/svg" style="display:none">
            <symbol id="checkbox-30" viewBox="0 0 22 22">
            <path fill="none" stroke="currentColor" d="M5.5,11.3L9,14.8L20.2,3.3l0,0c-0.5-1-1.5-1.8-2.7-1.8h-13c-1.7,0-3,1.3-3,3v13c0,1.7,1.3,3,3,3h13 c1.7,0,3-1.3,3-3v-13c0-0.4-0.1-0.8-0.3-1.2"/>
            </symbol>
            </svg>
            </div>
        </td>
    `).join('');
    const commentAndVoteButton = `<div class="d-flex justify-content-center pt-3 pb-2"> 
                <input type="text" id="comment" name="comment" placeholder="+ Add a comment (optional)" class="form-control addtxt"> </div><br />
            <div class="d-flex justify-content-between align-items-center">
                <div class="form-outline"></div>
                <!-- <br /><input type="text" id="comment" name="comment" placeholder="Add a comment (optional)"> -->
                <button type="submit" class="btn btn-info">Vote</button>
            </div><br />`;
    // Append to fooler's form tr
    $('#appointment-table').append(footerForm);
    $('#appointment-table tfoot tr').append(checkboxes);
    $('#vote-form').append(commentAndVoteButton);
};

const renderComments = (comments) => {
    // Map to li only if comment.comment is not empty
    const commentsList = comments.map(comment => {
        if (comment.comment) {
            return `<li class="list-group-item">
                        <strong>${comment.username}</strong> (${comment.created_at}): ${comment.comment}
                    </li>`;
        } else {
            return '';
        }
    }).join('');

    $('#appointment-comments').html(commentsList);
};


const validateVote = (userName, appointmentDateIds) => {
    let isValid = true;
    let message = '';

    // Trim userName to remove leading and trailing spaces
    userName = userName.trim();

    if (!userName) {
        isValid = false;
        message += 'Please enter your name.\n';
    }
    if (appointmentDateIds.length === 0) {
        isValid = false;
        message += 'Please select at least one date.';
    }

    return {isValid, message};
};

const postVote = async (data) => {
    return await fetch(constants.apiUrl + '/votes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });
};

const displayFlashMessage = (message, success) => {
    const flashMessage = $('#flash-message');
    if (success) {
        $(flashMessage).removeClass('alert-danger').addClass('alert-success');
    } else {
        $(flashMessage).removeClass('alert-success').addClass('alert-danger');
    }
    // Make the element focusable, show the message for 3 seconds, and focus on it
    $(flashMessage).attr('tabindex', -1).text(message).show().focus().delay(3000).fadeOut();
}

const handleVoteResponse = async (response) => {
    if (response.ok) {
        $('#appointment-container').hide();
        const appointmentId = $('#appointment-title').data('appointment-id');
        await displayAppointment(appointmentId);
        displayFlashMessage('Your vote has been submitted.', true)
    } else {
        let error = await response.json();
        displayFlashMessage(Object.values(error['errors'])[0][0], false)
    }
};

const submitVote = async () => {
    const userName = $('#user-name').val();
    const appointmentDateIds = $('input[name="appointmentDateIds[]"]:checked').map((_, el) => el.value).get();
    const comment = $('#comment').val();

    const {isValid, message} = validateVote(userName, appointmentDateIds);

    if (isValid) {
        try {
            const data = {user_name: userName, appointmentDateIds, comment};
            const response = await postVote(data);
            await handleVoteResponse(response);
        } catch (error) {
            displayFlashMessage('An error occurred while submitting your vote. Please try again.', false)
        }
    } else {
        displayFlashMessage(message, false);
    }
};

const addSubmitEvent = () => {
    $('#vote-form').on('submit', (event) => {
        event.preventDefault();
        submitVote();
    });
}


const renderAppointment = (appointmentData) => {
    const {title, location, description, expiration_date, appointmentDates, votes, comments, id} = appointmentData;

    renderAppointmentHeader(title, location, description, expiration_date, id);
    renderDateHeaders(appointmentDates);
    renderUserRows(votes, appointmentDates);
    if (new Date(expiration_date) >= new Date()) renderForm(appointmentDates);
    renderComments(comments);

    addSubmitEvent();
};


export const displayAppointment = async (appointmentId) => {
    prepareAppointment();
    const appointment = await fetchAppointment(appointmentId);
    renderAppointment(appointment)
    $(appointmentContainer).show();
}