package me.medicusys.medicussystem;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import android.widget.Toast;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Date;

public class FragmentHealth extends Fragment implements JSONReceiver {
    View fragmentView;
    TextView messageView;

    public FragmentHealth() {
    }

    public static FragmentHealth newInstance() {
        FragmentHealth fragment = new FragmentHealth();
        return fragment;
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        fragmentView = inflater.inflate(R.layout.fragment_health, container, false);
        messageView = fragmentView.findViewById(R.id.healthMessageView);

        request();
        return fragmentView;
    }

    @Override
    public void onAttach(Context context) {
        super.onAttach(context);
    }

    @Override
    public void onDetach() {
        super.onDetach();
    }

    @Override
    public void request() {
        String URL = DataSystem.SERVER_URL + "/api/v1/diagnosis";
        String data = "token=" + DataSystem.TOKEN + "&user_id=" + DataUserPersonal.userID + "&user_token=" + DataUserPersonal.userToken;
        new NetworkJSONReceiver(this).execute(URL, data);
    }

    @Override
    public void setResponse(JSONObject responseData) {
        try {
            if (responseData != null) {
                if (StatusCodeManager.isGoodResponse(responseData, getActivity())) {
                    JSONArray diagnosis = responseData.getJSONArray("data");
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
                    initRecycler();
                }
            } else {

                try {
                    Thread.sleep(5000);
                } catch (InterruptedException e) {
                    e.printStackTrace();
                }
                request();
            }
        } catch (JSONException e) {
            e.printStackTrace();
        }
    }

    @Override
    public void printError(final String errorMessage) {
        getActivity().runOnUiThread(new Runnable() {

            @Override
            public void run() {
                messageView.setText(errorMessage);
            }
        });
    }

    private void initRecycler() {
        RecyclerView recyclerView = fragmentView.findViewById(R.id.recycler);
        RecyclerAdapterDiagnoses recyclerAdapter = new RecyclerAdapterDiagnoses(fragmentView.getContext());
        recyclerView.setAdapter(recyclerAdapter);
        recyclerView.setLayoutManager(new LinearLayoutManager(fragmentView.getContext()));
    }
}
