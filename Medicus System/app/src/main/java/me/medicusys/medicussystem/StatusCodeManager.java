package me.medicusys.medicussystem;

import android.app.Activity;
import android.widget.Toast;

import org.json.JSONException;
import org.json.JSONObject;

public final class StatusCodeManager {
    public static boolean isGoodResponse(JSONObject response, Activity activity) throws JSONException {
        if (response == null) {
            return false;
        }
        switch (response.getInt("status")) {
            case 27:
                return true;
            case 53:
                Toast.makeText(activity, "Сплив час активної сесії. Увійдіть ще раз", Toast.LENGTH_LONG);
                DataUserPersonal.logOut(activity);
                break;
            default:
                break;
        }
        return false;
    }
}
