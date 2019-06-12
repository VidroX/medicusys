package me.medicusys.medicussystem;

import java.util.ArrayList;
import java.util.Date;
import java.util.TreeSet;

public class DiagnosisRecord implements Comparable {
    long id;
    String name;
    Date detectionDate;
    ArrayList<String> symptoms;

    DiagnosisRecord(long id, String name, Date detectionDate, ArrayList<String> symptoms) {
        this.id = id;
        this.name = name;
        this.detectionDate = detectionDate;
        this.symptoms = symptoms;
    }

    @Override
    public int compareTo(Object o) {
        if (this == o) {
            return 0;
        }
        DiagnosisRecord otherRecord = (DiagnosisRecord)o;
        return (int)Math.signum(otherRecord.id - this.id);
    }
}
