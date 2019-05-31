package me.medicusys.medicussystem;

import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.TextView;

public class MainActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
    }

    public void LogIn(View view) {
        String login = ((TextView)findViewById(R.id.loginInput)).getText().toString();
        String password = ((TextView)findViewById(R.id.passwordInput)).getText().toString();
        TextView debugLogInResponse = findViewById(R.id.debugLogInResponse);
        new Network(debugLogInResponse, "UTF-8").execute("http://192.168.0.103/api/v1/login", "login=" + login + "&password=" + password);
    }
}
