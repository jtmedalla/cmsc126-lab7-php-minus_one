<!DOCTYPE html>
<html>
    <head>
        <title>Student Registration Form</title>
    </head>
    <body>
        <h1>Student Registration Form</h1>
        <h2>All fields marked * are required</h2>
        <!-- Student Infomation form -->
        <form id="stdForm">
            <h3>Personal Information</h3>
            <label for="stdNname">Name</label><br>
            <input type="text" id="stdName" maxlength="40" required><br><br>
            <label for="stdAge">Age</label><br>
            <input type="number" id="stdAge" min="0" max="99" placeholder="0-99" required><br><br>
            <label for="stdEmail">Email</label><br>
            <input type="email" id="stdEmail" maxlength="40" required><br><br>
            <small>Must be a valid email address (max 40 characters)</small>
            <h3>Academic Information</h3>
            <label for="stdCourse">Course</label><br>
            <input type="text" id="stdCourse" maxlength="40" required><br><br>
            <label for="stdYearLevel">Year Level</label><br>
            <select id="stdYearLevel" required>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select><br><br>
            <p>Graduating this year?</p>
            <input type="checkbox" id="stdGraduating" value="true">
            <label for="stdGraduating">Yes</label>
            <h3>Profile Photo</h3>
            <label for="stdImage">Profile Image</label><br>
            <input type="file" id="stdImage" required><br><br>
            <input type="submit">
            <input type="reset">
        </form>
        <br><br><br><br>
        <!-- Student Record Section-->
        <h3>Student Record Search</h3>
        <label for="stdSearch">Look up by Student ID</label><br>
        <input type="number" id="stdSearch">
        <button type="button" id="btnSearch">Search</button>
        <button type="button" id="btnUpdate">Update</button>
        <button type="button" id="btnDelete">Delete</button>
    </body>
</html>
