package me.medicusys.medicussystem;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.view.View;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.Date;

public class DataUserPersonal {
    public static long userID;
    public static String userToken;
    public static String firstName;
    public static String lastName;
    public static String patronymic;
    public static Date birthDate;
    public static boolean isMan;
    public static String phone;
    public static String email;
    public static String address;

    public static ActivityLogin loginActivity;

    public static void logIn(JSONObject loginData) {
        try {
            userID = loginData.getInt("id");
            firstName = loginData.getString("firstName");
            lastName = loginData.getString("lastName");
            patronymic = loginData.getString("patronymic");
            birthDate = DataSystem.parseDate(loginData.getString("birthDate"));
            isMan = (loginData.getInt("gender") == 1 ? true : false);
            phone = loginData.getString("mobilePhone");
            email = loginData.getString("email");
            address = loginData.getString("homeAddress");
            userToken = loginData.getString("userToken");
            loginActivity.startActivity(new Intent(loginActivity, ActivityCabinet.class));
            loginActivity.finish();

            loginActivity.getSharedPreferences("Authorization", Context.MODE_PRIVATE)
                    .edit()
                    .putLong("id", userID)
                    .putString("firstName", firstName)
                    .putString("lastName", lastName)
                    .putString("patronymic", patronymic)
                    .putLong("birthDate", birthDate.getTime())
                    .putBoolean("gender", isMan)
                    .putString("mobilePhone", phone)
                    .putString("email", email)
                    .putString("address", address)
                    .putString("userToken", userToken)
                    .commit();

        } catch (JSONException e) {
            e.printStackTrace();
        }
    }

    public static boolean logIn() {
        SharedPreferences sharedPreferences = loginActivity.getSharedPreferences("Authorization", Context.MODE_PRIVATE);
        userID = sharedPreferences.getLong("id", -1);
        firstName = sharedPreferences.getString("firstName", null);
        lastName = sharedPreferences.getString("lastName", null);
        patronymic = sharedPreferences.getString("patronymic", null);
        birthDate = new Date(sharedPreferences.getLong("birthDate", Long.MIN_VALUE));
        isMan = sharedPreferences.getBoolean("gender", false);
        phone = sharedPreferences.getString("mobilePhone", null);
        email = sharedPreferences.getString("email", null);
        address = sharedPreferences.getString("address", null);
        userToken = sharedPreferences.getString("userToken", null);

        if (userID >= 0 &&
                firstName != null &&
                lastName != null &&
                patronymic != null &&
                birthDate.getTime() != Long.MIN_VALUE &&
                phone != null &&
                email != null &&
                address != null &&
                userToken != null) {
            loginActivity.startActivity(new Intent(loginActivity, ActivityCabinet.class));
            loginActivity.finish();
            return true;
        }
        else {
            return false;
        }

    }

    public static void logOut(Activity activity) {
        loginActivity.getSharedPreferences("Authorization", Context.MODE_PRIVATE)
                .edit()
                .putLong("id", -1)
                .commit();
        Intent intent = new Intent(activity, ActivityLogin.class);
        activity.startActivity(intent);
        activity.finish();
    }
}
