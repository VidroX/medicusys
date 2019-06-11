package me.medicusys.medicussystem;

import android.support.v7.app.ActionBar;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.support.v7.widget.Toolbar;
import android.widget.TextView;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;

public class ActivityDiagnose extends AppCompatActivity implements JSONReceiver {
    Toolbar toolbar;
    TextView detectionDateText;
    TextView detectionDateView;
    TextView symptomsText;
    ArrayList<RecipeRecord> recipeRecords = new ArrayList<>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_diagnose);

        toolbar = findViewById(R.id.toolbar);
        detectionDateText = findViewById(R.id.detectionDateText);
        detectionDateView = findViewById(R.id.detectionDateView);
        symptomsText = findViewById(R.id.symptomsText);

        setSupportActionBar(toolbar);

        ActionBar actionBar = getSupportActionBar();
        actionBar.setDisplayHomeAsUpEnabled(true);
        actionBar.setDisplayShowHomeEnabled(true);

        toolbar.setTitle(DataUserMedical.diagnosisRecords.get(DataSystem.currentDiagnose).name);

        detectionDateView.setText(DataSystem.dateToString(DataUserMedical.diagnosisRecords.get(DataSystem.currentDiagnose).detectionDate));
        viewSymptomsRecycler();
        request();
    }

    private void viewSymptomsRecycler() {
        RecyclerView symptomsRecycler = findViewById(R.id.symptomsRecycler);
        RecyclerAdapterSymptoms symptomsRecyclerAdapter = new RecyclerAdapterSymptoms(this);
        symptomsRecycler.setAdapter(symptomsRecyclerAdapter);
        symptomsRecycler.setLayoutManager(new LinearLayoutManager(this));
    }

    private void viewRecipesRecycler() {
        RecyclerView recipesRecycler = findViewById(R.id.recipesRecycler);
        RecyclerAdapterDiagnoseRecipes recyclerAdapterDiagnoseRecipes = new RecyclerAdapterDiagnoseRecipes(this, recipeRecords);
        recipesRecycler.setAdapter(recyclerAdapterDiagnoseRecipes);
        recipesRecycler.setLayoutManager(new LinearLayoutManager(this));
    }

    @Override
    public void request() {
        String URL = DataSystem.SERVER_URL + "/api/v1/recipe";
        String data = "token=" + DataSystem.TOKEN
                    + "&user_id=" + DataUserPersonal.userID
                    + "&user_token=" + DataUserPersonal.userToken
                    + "&diagnosis_id=" + DataUserMedical.diagnosisRecords.get(DataSystem.currentDiagnose).id;
        new NetworkJSONReceiver(this).execute(URL, data);
    }

    @Override
    public void setResponse(JSONObject responseData) {
        if (responseData != null) {
            try {
                if (responseData.getInt("status") == 27) {
                    JSONArray recipes = responseData.getJSONArray("data");
                    recipeRecords.clear();
                    for (int i = 0; i < recipes.length(); i++) {
                        JSONObject recipe = recipes.getJSONObject(i);
                        long id = recipe.getLong("id");
                        String rp = recipe.getString("rp");
                        String dtdn = recipe.getString("dtdn");
                        String signa = recipe.getString("signa");
                        recipeRecords.add(new RecipeRecord(id, rp, dtdn, signa));
                    }
                    viewRecipesRecycler();
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }

    @Override
    public void printError(String errorMessage) {

    }

    @Override
    public boolean onSupportNavigateUp() {
        onBackPressed();
        return true;
    }
}
