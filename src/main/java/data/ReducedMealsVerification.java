package data;

import org.apache.commons.io.FilenameUtils;

import java.sql.Timestamp;
import java.text.SimpleDateFormat;
import java.util.Calendar;

public class ReducedMealsVerification implements java.io.Serializable {

    public static final long serialVersionUID = 1;

    private int reducedMealsVerificationID;
    private int studentID;
    private String filePath;
    private String readableTimestamp;
    private boolean deleted;
    private boolean isValid = false;

    public ReducedMealsVerification(int studentID, String filePath) {
        setStudentID(studentID);
        setFilePath(filePath);
    }

    public ReducedMealsVerification(int reducedMealsVerificationID, int studentID, String filePath, boolean deleted) {
        setReducedMealsVerificationID(reducedMealsVerificationID);
        setStudentID(studentID);
        setFilePath(filePath);
        setDateAndTime(filePath);
        setIsValid();
        setDeleted(deleted);
    }

    public int getReducedMealsVerificationID() {
        return this.reducedMealsVerificationID;
    }

    public void setReducedMealsVerificationID(int gradeReportID) {
        this.reducedMealsVerificationID = gradeReportID;
    }

    public int getStudentID() {
        return this.studentID;
    }

    public void setStudentID(int studentID) {
        this.studentID = studentID;
    }

    public String getFilePath() {
        return this.filePath;
    }

    public void setFilePath(String filePath) {
        this.filePath = filePath;
    }

    public void setDateAndTime(String filePath) {
        String baseName = FilenameUtils.getBaseName(filePath);
        baseName = baseName.replace('_', ' ');
        int indexAfterSpace = baseName.indexOf(' ') + 1;
        baseName = baseName.substring(0, indexAfterSpace) + baseName.substring(indexAfterSpace).replace('-', ':');
        Timestamp timestamp = Timestamp.valueOf(baseName);
        this.readableTimestamp = new SimpleDateFormat("EEE, MMM dd, yyyy 'at' hh:mm aaa").format(timestamp);
    }

    public boolean getIsValid(){
        return isValid;
    }

    public void setIsValid(){
        int Calendaryear = Calendar.getInstance().get(Calendar.YEAR);
        int year = Integer.valueOf(this.readableTimestamp.substring(13,17));
        if (Calendaryear == year)
            isValid = true;
    }

    public String getReadableTimestamp() {
        return this.readableTimestamp;
    }

    public boolean isDeleted() {
        return deleted;
    }

    public void setDeleted(boolean deleted) {
        this.deleted = deleted;
    }
}
