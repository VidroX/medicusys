package me.medicusys.medicussystem;

import org.json.JSONObject;

public interface JSONReceiver {
    void request();

    void setResponse(JSONObject responseData);

    void printError(String errorMessage);
}
