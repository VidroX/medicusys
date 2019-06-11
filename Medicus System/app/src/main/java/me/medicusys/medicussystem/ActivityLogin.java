package me.medicusys.medicussystem;

import android.support.annotation.NonNull;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.TextView;

import com.google.android.gms.tasks.OnCompleteListener;
import com.google.android.gms.tasks.Task;
import com.google.firebase.iid.FirebaseInstanceId;
import com.google.firebase.iid.InstanceIdResult;

import org.json.JSONException;
import org.json.JSONObject;

public class ActivityLogin extends AppCompatActivity implements JSONReceiver {
    TextView debugLogInResponse;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);
        FirebaseInstanceId.getInstance().getInstanceId().addOnCompleteListener(new OnCompleteListener<InstanceIdResult>() {
            @Override
            public void onComplete(@NonNull Task<InstanceIdResult> task) {
                if (!task.isSuccessful()) {
                    task.getException().printStackTrace();
                    return;
                }
                DataSystem.fcm_reg_token = task.getResult().getToken();
            }
        });

        debugLogInResponse = findViewById(R.id.debugLogInResponse);

        DataUserPersonal.loginActivity = this;
        if (DataUserPersonal.logIn()) {
            return;
        }
    }

    public void LogIn(View view) {
        printError("");
        request();
    }

    @Override
    public void request() {
        String login = ((TextView) findViewById(R.id.loginInput)).getText().toString();
        String password = ((TextView) findViewById(R.id.passwordInput)).getText().toString();

        new NetworkJSONReceiver(this).execute(DataSystem.SERVER_URL + "/api/v1/login",
                "login=" + login + "&password=" + password + "&token=" + DataSystem.TOKEN + "&fcm_reg_token=" + DataSystem.fcm_reg_token);
    }

    @Override
    public void setResponse(JSONObject responseData) {
        if (responseData != null) {
            try {
                if ("18".equals(responseData.getString("status"))) {
                    JSONObject userData = responseData.getJSONObject("data");
                    DataUserPersonal.logIn(userData);
                }
                else {
                    printError(responseData.getString("message"));
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }

    @Override
    public void printError(final String errorMessage) {
        runOnUiThread(new Runnable() {

            @Override
            public void run() {
                debugLogInResponse.setText(errorMessage);
                debugLogInResponse.setVisibility(View.VISIBLE);
            }
        });
    }
}
