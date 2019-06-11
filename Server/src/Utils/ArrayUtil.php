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

}