package me.medicusys.medicussystem;

import android.os.AsyncTask;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedOutputStream;
import java.io.BufferedWriter;
import java.io.IOException;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.Scanner;

class NetworkJSONReceiver extends AsyncTask<String, Void, String> {
    JSONObject response;
    JSONReceiver jsonReceiver;

    NetworkJSONReceiver(JSONReceiver jsonReceiver)
    {
        super();
        this.jsonReceiver = jsonReceiver;
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 2) {
            return "Not enough params: two params of url and request data required.";
        }
        String urlName = params[0];
        String requestData = params[1];
        OutputStream out;
        HttpURLConnection urlConnection = null;
        String result = "";
        try {
            URL url = new URL(urlName);
            urlConnection = (HttpURLConnection) url.openConnection();
            urlConnection.setRequestMethod("POST");
            urlConnection.setDoInput(true);
            urlConnection.setDoOutput(true);

            out = new BufferedOutputStream(urlConnection.getOutputStream());
            BufferedWriter writer = new BufferedWriter(new OutputStreamWriter(out, "UTF-8"));
            writer.write(requestData);
            writer.flush();
            writer.close();
            out.close();

            urlConnection.connect();
            Scanner scanner = new Scanner(urlConnection.getInputStream());
            StringBuilder stringBuilder = new StringBuilder();
            while (scanner.hasNextLine()) {
                stringBuilder.append(scanner.nextLine());
            }
            response = new JSONObject(stringBuilder.toString());

        } catch (MalformedURLException e) {
            jsonReceiver.printError("Bad URL: " + urlName);
        } catch (UnsupportedEncodingException e) {
            jsonReceiver.printError("Unsupported encoding: UTF-8");
        } catch (IOException e) {
            jsonReceiver.printError("Bad internet connection: " + e.getMessage());
            e.printStackTrace();
        } catch (JSONException e) {
            jsonReceiver.printError("Bad response.");
            e.printStackTrace();
        } finally {
            if (urlConnection != null) {
                urlConnection.disconnect();
            }
        }
        return result;
    }

    @Override
    protected void onPostExecute(String result) {
        jsonReceiver.setResponse(response);
        super.onPostExecute(result);
    }
}
