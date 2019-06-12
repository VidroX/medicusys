package me.medicusys.medicussystem;

import android.app.AlarmManager;
import android.app.Notification;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.SystemClock;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;

public class DataUserMedical {
    public static ActivityDiagnose activityDiagnose;
    public static ArrayList<DiagnosisRecord> diagnosisRecords = new ArrayList<>();
    public static ArrayList<RecipeRecord> diagnoseRecipesRecords = new ArrayList<>();
    private static AlarmManager alarmMgr;
    private static PendingIntent alarmIntent;

    static {

    }

    public static void SaveDiagnoses() throws JSONException {
        SharedPreferences dataPreferences = activityDiagnose.getSharedPreferences("Diagnoses", Context.MODE_PRIVATE);
        JSONObject data = new JSONObject();
        JSONArray records = new JSONArray();
        for (int i = 0; i < diagnosisRecords.size(); i++) {
            JSONObject record = new JSONObject();

            DiagnosisRecord diagnosisRecord = diagnosisRecords.get(i);
            JSONArray symptoms = new JSONArray();
            for (int j = 0; j < diagnosisRecord.symptoms.size(); j++) {
                symptoms.put(diagnosisRecord.symptoms.get(j));
            }
            record.put("id", diagnosisRecord.id)
                    .put("name", diagnosisRecord.name)
                    .put("detectionDate", DataSystem.dateToString(diagnosisRecord.detectionDate))
                    .put("symptoms", symptoms);
            records.put(record);
        }
        data.put("data", records);
        dataPreferences.edit().putString(Long.toString(DataUserPersonal.userID), data.toString());
    }

    public static void LoadDiagnoses(JSONObject data) {
        try {
            JSONArray diagnosis = data.getJSONArray("data");
        DataUserMedical.diagnosisRecords.clear();
        for (int i = 0; i < diagnosis.length(); i++) {
            JSONObject diagnose = diagnosis.getJSONObject(i);

            JSONArray symptoms = diagnose.getJSONArray("symptoms");
            JSONObject diagnoseData = diagnose.getJSONObject("data");
            ArrayList<String> symptomsSet = new ArrayList<>();
            for (int j = 0; j < symptoms.length(); j++) {
                symptomsSet.add(symptoms.getString(j));
            }

            long diagnoseId = diagnoseData.getLong("id");
            String diagnoseName = diagnoseData.getString("name");
            Date detectionDate = DataSystem.parseDate(diagnoseData.getString("detection_date"));
            DiagnosisRecord diagnosisRecord = new DiagnosisRecord(diagnoseId, diagnoseName, detectionDate, symptomsSet);
            DataUserMedical.diagnosisRecords.add(diagnosisRecord);
        }
        } catch (JSONException e) {
            e.printStackTrace();
        }
    }

    public static void LoadDiagnoses() {
        SharedPreferences dataPreferences = activityDiagnose.getSharedPreferences("Diagnoses", Context.MODE_PRIVATE);
        String dataString = dataPreferences.getString(Long.toString(DataUserPersonal.userID), null);
        if (dataString != null) {
            try {
                LoadDiagnoses(new JSONObject(dataString));
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }
}
