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
        23 => 'Invalid user token'
    ];
}