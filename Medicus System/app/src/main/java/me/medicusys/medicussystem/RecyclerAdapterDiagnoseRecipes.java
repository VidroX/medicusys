package me.medicusys.medicussystem;

import android.content.Context;
import android.support.annotation.NonNull;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import java.util.ArrayList;

public class RecyclerAdapterDiagnoseRecipes extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    Context context;
    ArrayList<RecipeRecord> records;

    RecyclerAdapterDiagnoseRecipes(Context context, ArrayList<RecipeRecord> records) {
        this.context = context;
        this.records = records;
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup viewGroup, int i) {
        View view = LayoutInflater.from(viewGroup.getContext()).inflate(R.layout.list_item_recipe, viewGroup, false);
        return new RecyclerAdapterDiagnoseRecipes.ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder viewHolder, int i) {
        final RecyclerAdapterDiagnoseRecipes.ViewHolder holder = (RecyclerAdapterDiagnoseRecipes.ViewHolder)viewHolder;

        RecipeRecord recipeRecord = records.get(i);
        holder.rpView.setText(recipeRecord.rp);
        holder.dtdnView.setText(recipeRecord.dtdn);
        holder.signaView.setText(recipeRecord.signa);
    }

    @Override
    public int getItemCount() {
        return records.size();
    }

    public class ViewHolder extends RecyclerView.ViewHolder {
        TextView rpView;
        TextView dtdnView;
        TextView signaView;

        public ViewHolder(View itemView) {
            super(itemView);
            rpView = itemView.findViewById(R.id.rpView);
            dtdnView = itemView.findViewById(R.id.dtdnView);
            signaView = itemView.findViewById(R.id.signaView);
        }
    }
}
