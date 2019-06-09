package me.medicusys.medicussystem;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.Date;

public class SystemData {
    public static String fcm_reg_token;
    public static int userID;
    public static String userToken;

    public static String firstName;
    public static String lastName;
    public static String patronymic;
    public static Date birthDate;
    public static boolean isMan;
    public static String phone;
    public static String email;
    public static String address;
    public static NotificationForm notificationForm;

    public static LoginActivity loginActivity;

    public static void logIn(JSONObject loginData) {
        try {
            userID = loginData.getInt("id");
            firstName = loginData.getString("firstName");
            lastName = loginData.getString("lastName");
            patronymic = loginData.getString("patronymic");
            String[] dateParts = loginData.getString("birthDate").split("-");
            birthDate = new Date(Integer.parseInt(dateParts[0]), Integer.parseInt(dateParts[1]), Integer.parseInt(dateParts[2]));
            isMan = (loginData.getInt("gender") == 1 ? true : false);
            phone = loginData.getString("mobilePhone");
            email = loginData.getString("email");
            address = loginData.getString("homeAddress");
            userToken = loginData.getString("userToken");
            loginActivity.startActivity(new Intent(loginActivity, CabinetActivity.class));
//            notificationForm = new NotificationForm(loginActivity);
//            notificationForm.showNotification("Authorization", "Successful login, \n" + firstName + " " + patronymic + " " + lastName);

            SharedPreferences sharedPreferences = loginActivity.getSharedPreferences("Login", Context.MODE_PRIVATE);
            SharedPreferences.Editor preferencesEditor = sharedPreferences.edit();
            preferencesEditor.putString("id", Integer.toString(userID));
            preferencesEditor.putString("firstName", firstName);
            preferencesEditor.putString("lastName", lastName);
            preferencesEditor.putString("patronymic", patronymic);
            preferencesEditor.putLong("birthDate", birthDate.getTime());
            preferencesEditor.putBoolean("gender", isMan);
            preferencesEditor.putString("mobilePhone", phone);
            preferencesEditor.putString("email", email);
            preferencesEditor.putString("address", address);
            preferencesEditor.putString("userToken", userToken);
            preferencesEditor.commit();

        } catch (JSONException e) {
            e.printStackTrace();
        }
    }
}
