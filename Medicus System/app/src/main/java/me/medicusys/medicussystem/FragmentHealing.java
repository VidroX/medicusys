package me.medicusys.medicussystem;

import android.content.Context;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import org.json.JSONObject;

public class FragmentHealing extends Fragment implements JSONReceiver {
    View fragmentView;
    TextView messageView;


    public FragmentHealing() {
    }

    public static FragmentHealing newInstance() {
        FragmentHealing fragment = new FragmentHealing();
        return fragment;
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        fragmentView = inflater.inflate(R.layout.fragment_healing, container, false);
        messageView = fragmentView.findViewById(R.id.healingMessageView);

        return fragmentView;
    }

    @Override
    public void onAttach(Context context) {
        super.onAttach(context);
    }

    @Override
    public void onDetach() {
        super.onDetach();
    }

    @Override
    public void request() {
        String URL = DataSystem.SERVER_URL + "/api/v1/recipe";
        String data = "token=" + DataSystem.TOKEN + "&user_id=" + DataUserPersonal.userID + "&user_token=" + DataUserPersonal.userToken;
        new NetworkJSONReceiver(this).execute(URL, data);
    }

    @Override
    public void setResponse(JSONObject responseData) {
        messageView.setText(responseData.toString());
    }

    @Override
    public void printError(final String errorMessage) {
        getActivity().runOnUiThread(new Runnable() {
            @Override
            public void run() {
                messageView.setText(errorMessage);
            }
        });
    }
}
