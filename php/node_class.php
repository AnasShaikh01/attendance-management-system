<?php
require_once 'defines.php';

class Node {

    private $teacher;
    private $subjectCode;
    private $section;
    private $year;
    private $semester;
    private $numberOfDays = 0;
    private $records = [];

    /* ==============================
       CONSTRUCTOR
    ================================= */

    public function __construct(
        $code,
        $teacher_uid,
        $year,
        $semester,
        $section,
        $startRoll,
        $endRoll
    ) {
        $this->subjectCode = strtoupper(trim($code));
        $this->teacher     = intval($teacher_uid);
        $this->year        = intval($year);
        $this->semester    = intval($semester);
        $this->section     = intval($section);

        $this->initializeRecords($startRoll, $endRoll);

        if (!$this->saveNode()) {
            throw new Exception("Failed to create class.");
        }
    }

    /* ==============================
       RECORD INITIALIZATION
    ================================= */

    private function initializeRecords($start, $end) {

        if (!preg_match('/^([0-9]+)\/[A-Z]{2}\/[0-9]{2}$/', $start) ||
            !preg_match('/^([0-9]+)\/[A-Z]{2}\/[0-9]{2}$/', $end)) {
            throw new Exception("Invalid roll format.");
        }

        preg_match('/^([0-9]+)(\/[A-Z]+\/[0-9]{2})$/', $start, $sMatch);
        preg_match('/^([0-9]+)(\/[A-Z]+\/[0-9]{2})$/', $end, $eMatch);

        $startNum = intval($sMatch[1]);
        $endNum   = intval($eMatch[1]);
        $suffix   = $sMatch[2];

        if ($startNum > $endNum) {
            throw new Exception("Invalid roll range.");
        }

        for ($i = $startNum; $i <= $endNum; $i++) {
            $roll = $i . $suffix;
            $this->records[$roll] = [
                'present'  => 0,
                'timeline' => []
            ];
        }
    }

    /* ==============================
       DATABASE METHODS
    ================================= */

    public function saveNode() {
        $con = connectTo();

        // ✅ DO NOT escape serialized string manually
        $serialized = serialize($this);

        $stmt = $con->prepare("
            SELECT uid FROM objects
            WHERE teacher_uid = ? AND code = ? AND section = ? AND year = ?
        ");

        $stmt->bind_param(
            "isii",
            $this->teacher,
            $this->subjectCode,
            $this->section,
            $this->year
        );

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $update = $con->prepare("
                UPDATE objects
                SET object = ?
                WHERE teacher_uid = ? AND code = ? AND section = ? AND year = ?
            ");

            $update->bind_param(
                "sisii",
                $serialized,
                $this->teacher,
                $this->subjectCode,
                $this->section,
                $this->year
            );

            $update->execute();
            $update->close();

        } else {

            $insert = $con->prepare("
                INSERT INTO objects (teacher_uid, code, year, section, object)
                VALUES (?, ?, ?, ?, ?)
            ");

            $insert->bind_param(
                "isiis",
                $this->teacher,
                $this->subjectCode,
                $this->year,
                $this->section,
                $serialized
            );

            $insert->execute();
            $insert->close();
        }

        $stmt->close();
        $con->close();

        return true;
    }

    public static function retrieveObjecti($class_id, $teacher_uid) {
        $con = connectTo();

        $stmt = $con->prepare("
            SELECT object FROM objects
            WHERE uid = ? AND teacher_uid = ?
        ");

        $stmt->bind_param("ii", $class_id, $teacher_uid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            $data = $row['object'];

            // Safe unserialize
            $object = @unserialize($data);

            if ($object === false) {
                return false;
            }

            return $object;
        }

        return false;
    }

    /* ==============================
       ATTENDANCE METHODS
    ================================= */

    public function setPresence($roll, $newPresentCount, $timestamp) {
        if (!isset($this->records[$roll])) return false;

        $this->records[$roll]['timeline'][$timestamp] =
            ($this->records[$roll]['present'] < $newPresentCount) ? 1 : 0;

        $this->records[$roll]['present'] = $newPresentCount;
        return true;
    }

    public function deleteRoll($roll) {
        if (!isset($this->records[$roll])) return false;
        unset($this->records[$roll]);
        return true;
    }

    public function incrementDay() {
    $this->numberOfDays++;
    }

    /* ==============================
       GETTERS
    ================================= */

    public function getCode()      { return $this->subjectCode; }
    public function getSection()   { return $this->section; }
    public function getYear()      { return $this->year; }
    public function getSemester()  { return $this->semester; }
    public function getDays()      { return $this->numberOfDays; }
    public function getRecords()   { return $this->records; }
    public function getTeacherID() { return $this->teacher; }
}