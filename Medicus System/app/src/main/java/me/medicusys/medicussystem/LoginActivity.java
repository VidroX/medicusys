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

public class LoginActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);
        FirebaseInstanceId.getInstance().getInstanceId().addOnCompleteListener(new OnCompleteListener<InstanceIdResult>() {
            @Override
            public void onComplete(@NonNull Task<InstanceIdResult> task) {
                if (!task.isSuccessful()) {
                    System.out.println("getInstanceId failed");
                    task.getException().printStackTrace();
                    return;
                }
                SystemData.fcm_reg_token = task.getResult().getToken();
            }
        });
        SystemData.loginActivity = this;
    }

    public void LogIn(View view) {
        String login = ((TextView) findViewById(R.id.loginInput)).getText().toString();
        String password = ((TextView) findViewById(R.id.passwordInput)).getText().toString();
        String serverURL = ((TextView) findViewById(R.id.serverURLInput)).getText().toString();
        TextView debugLogInResponse = findViewById(R.id.debugLogInResponse);

        new Network(debugLogInResponse, "UTF-8").execute(serverURL + "/api/v1/login",
                "login=" + login + "&password=" + password + "&token=PUsecR0B6brOYUcrI9LhiXU8w5SlFRorlFrdrlV" + "&fcm_reg_token=" + SystemData.fcm_reg_token);
    }
}
