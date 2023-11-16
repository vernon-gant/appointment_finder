/*
import { addAppointmentButton } from './constants.js';
import { addAppointment } from './appointment.mjs';

addAppointmentButton.addEventListener('click', addAppointment);
*/


import {backButton, form} from "./constants.js";
import {addAppointment, displayAppointments} from "./appointments.mjs";



$(document).ready(() => {
    displayAppointments();
    backButton.addEventListener('click', displayAppointments);
    form.addEventListener('submit', addAppointment);

});

