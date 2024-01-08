<?php

namespace Tests;

use App\Exceptions\AppointmentNotFoundException;
use App\Exceptions\AppointmentNotSavedException;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;

class AppointmentServiceTest extends TestCase
{

    public function testGetAllAppointments()
    {
        // Arrange: create some appointments
        Appointment::factory()->count(3)->create();

        // Act: call the method
        $service = new AppointmentService();
        $appointments = $service->getAllAppointments();

        // Assert: check if the returned value is a collection and has the correct number of appointments
        $this->assertCount(3, $appointments);
    }

    public function testDeleteNonExistentAppointmentThrowsException()
    {
        $this->expectException(AppointmentNotFoundException::class);

        $service = new AppointmentService();
        $service->deleteAppointment(999); // Assuming this ID does not exist
    }

    public function testCreateAppointment()
    {
        $data = [
            'title' => 'Test Title',
            'location' => 'Test Location',
            'description' => 'Test Description',
            'expiration_date' => '2023-01-01',
            'appointment_dates' => [
                ['date' => '2023-01-02', 'start_time' => '09:00', 'end_time' => '10:00'],
                ['date' => '2023-01-03', 'start_time' => '09:00', 'end_time' => '10:00'],
                ['date' => '2023-01-04', 'start_time' => '09:00', 'end_time' => '10:00']
            ],
        ];

        $service = new AppointmentService();

        // Act: call the method to create an appointment
        $appointment = $service->createAppointment($data);

        // Assert: check if the appointment was created
        $this->assertEquals('Test Title', $appointment->title);
        $this->assertEquals('Test Location', $appointment->location);
        $this->assertEquals('Test Description', $appointment->description);
        $this->assertEquals('2023-01-01', $appointment->expiration_date->format('Y-m-d'));

        // Check appointment dates
        foreach ($data['appointment_dates'] as $appointmentDate) {
            $found = $appointment->appointmentDates()
                ->where('date', Carbon::parse($appointmentDate['date']))
                ->where('start_time', $appointmentDate['start_time'])
                ->where('end_time', $appointmentDate['end_time'])
                ->exists();
            $this->assertTrue($found, "Appointment date not found in database.");
        }
    }


    public function testCreateAppointmentWithInvalidDataThrowsException()
    {
        $this->expectException(AppointmentNotSavedException::class);

        $service = new AppointmentService();
        $service->createAppointment([]); // Empty data, should throw exception
    }


}