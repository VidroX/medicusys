package me.medicusys.medicussystem;

import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.v7.app.ActionBar;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.View;
import android.widget.TextView;

public class ActivityPersonal extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_personal);
        Toolbar toolbar = findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        ActionBar actionBar = getSupportActionBar();
        actionBar.setDisplayHomeAsUpEnabled(true);
        actionBar.setDisplayShowHomeEnabled(true);
        actionBar.setTitle("Персональна інформація");

        TextView personalFirstNameView = findViewById(R.id.personalFirstNameView);
        personalFirstNameView.setText(DataUserPersonal.firstName);
        TextView personalPatronymicView = findViewById(R.id.personalPatronymicView);
        personalPatronymicView.setText(DataUserPersonal.patronymic);
        TextView personalLastNameView = findViewById(R.id.personalLastNameView);
        personalLastNameView.setText(DataUserPersonal.lastName);
        TextView personalBirthDateView = findViewById(R.id.personalBirthDateView);
        personalBirthDateView.setText(DataSystem.dateToString(DataUserPersonal.birthDate));
        TextView personalGenderView = findViewById(R.id.personalGenderView);
        personalGenderView.setText((DataUserPersonal.isMan ? "чоловіча" : "жіноча"));
        TextView personalPhoneView = findViewById(R.id.personalPhoneView);
        personalPhoneView.setText(DataUserPersonal.phone);
        TextView personalEmailView = findViewById(R.id.personalEmailView);
        personalEmailView.setText(DataUserPersonal.email);
        TextView personaHomeAddressView = findViewById(R.id.personaHomeAddressView);
        personaHomeAddressView.setText(DataUserPersonal.address);
    }

    @Override
    public boolean onSupportNavigateUp() {
        onBackPressed();
        return true;
    }
}
