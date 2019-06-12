package me.medicusys.medicussystem;

public class RecipeRecord {
    long id;
    String rp;
    String dtdn;
    String signa;

    RecipeRecord(long id, String rp, String dtdn, String signa) {
        this.id = id;
        this.rp = rp;
        this.dtdn = dtdn;
        this.signa = signa;
    }
}
