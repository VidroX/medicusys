<?php

namespace App\Models;

interface StatusCodes {
    const STATUS = [
        0 => 'PDO Exception',
        1 => 'Invalid E-Mail or password',
        2 => 'Invalid password.',
        3 => 'Invalid E-Mail',
        4 => 'Invalid first name',
        5 => 'Invalid last name',
        6 => 'Invalid patronymic',
        7 => 'Invalid birth date',
        8 => 'Invalid mobile phone number',
        9 => 'Invalid home address',
        10 => 'Invalid doctor',
        11 => 'Patient registered successfully',
        12 => 'Doctor registered successfully',
        13 => 'This E-Mail already exists',
        14 => 'This mobile phone already exists',
        15 => 'Password should be minimum 6 characters long.',
        16 => 'Invalid specialty',
        17 => 'User is already logged in',
        18 => 'Login successful',
        19 => 'Recorder registered successfully',
        20 => 'Unexpected error. Please contact server administrator.',
        21 => 'Please provide login and password.',
        22 => 'Invalid token',
        23 => 'Invalid user token',
        24 => 'Not signed in',
        25 => 'Not allowed',
        26 => 'Invalid page',
        27 => 'OK',
        28 => 'There was an error with getting information from the server',
        29 => 'Invalid gender id (Allowed: 1 = Male, 2 = Female)',
        30 => 'No access to the patient',
        31 => 'Invalid user id',
        32 => 'Invalid patient id'
    ];
}