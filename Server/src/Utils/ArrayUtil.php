<?php

namespace App\Utils;

class ArrayUtil {

    /**
     * Find array key by element
     *
     * @param int $diagnosisId
     * @param array $diagnosesArr
     *
     * @return int
     */
    public static function searchForDiagnosis($diagnosisId, $diagnosesArr) {
        if($diagnosisId == null || $diagnosesArr == null) {
            return null;
        }

        foreach ($diagnosesArr as $key => $val) {
            if ($val['data']['id'] == $diagnosisId) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Find appointment
     *
     * @param int $doctorId
     * @param string $date
     * @param array $appointments
     *
     * @return bool
     */
    public static function searchForAppointment($doctorId, $date, $appointments) {
        if($date == null || $appointments == null) {
            return false;
        }

        foreach ($appointments as $key => $val) {
            if ($val['doctor_id'] == $doctorId && $val['visit_date'] == $date) {
                return true;
            }
        }

        return false;
    }

}