<?php

namespace App\Models;

class Visit {
    private $id;
    private $doctorId;
    private $patientId;
    private $visitDate;
    private $visited;

    /**
     * Visit constructor.
     * @param int $id
     * @param int $doctorId
     * @param int $patientId
     * @param string $visitDate
     * @param bool $visited
     */
    public function __construct($id = null, $doctorId = null, $patientId = null, $visitDate = null, $visited = null)
    {
        $this->id = $id;
        $this->doctorId = $doctorId;
        $this->patientId = $patientId;
        $this->visitDate = $visitDate;
        $this->visited = $visited;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getDoctorId()
    {
        return $this->doctorId;
    }

    /**
     * @param int $doctorId
     */
    public function setDoctorId($doctorId)
    {
        $this->doctorId = $doctorId;
    }

    /**
     * @return int
     */
    public function getPatientId()
    {
        return $this->patientId;
    }

    /**
     * @param int $patientId
     */
    public function setPatientId($patientId)
    {
        $this->patientId = $patientId;
    }

    /**
     * @return string
     */
    public function getVisitDate()
    {
        return $this->visitDate;
    }

    /**
     * @param string $visitDate
     */
    public function setVisitDate($visitDate)
    {
        $this->visitDate = $visitDate;
    }

    /**
     * @return bool
     */
    public function isVisited()
    {
        return $this->visited;
    }

    /**
     * @param bool $visited
     */
    public function setVisited($visited)
    {
        $this->visited = $visited;
    }
}